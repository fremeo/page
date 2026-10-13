<?php

// Globales Link-Objekt: geschrieben wird in die Datenbank des aktiven Moduls (page), vergebene SEO-URLs werden in allen Modulen geprüft.
$Link = $C['Link'];
$E = ['Info' => [], 'SEO' => []];

if(($D['ACTION']??null) == 'save') {

	// Das Formular sendet nur geänderte Felder (siehe input.tpl, ischanged): LINK, FromURL und Title fehlen im POST, wenn sie
	// unverändert sind. Der aktuelle Stand (Link-ID, Titel) kommt daher aus der Datenbank.
	$old = [];
	if($posted = array_keys((array)($D['PAGE']['D'] ?? []))) {
		$q['PAGE']['W'][0]['ID'] = $posted;
		$q['PAGE']['LANGUAGE'] = [];
		$C['fremeo/page']['CData']->get_object($old, $q);
	}

	foreach((array)($D['PAGE']['D'] ?? []) AS $kPAG => $PAG) {
		foreach((array)($PAG['LANGUAGE']['D'] ?? []) AS $kLAN => $LAN) {
			$row    = &$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN];
			$cur    = $old['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN] ?? [];
			$linkId = $cur['LINK'] ?? '';

			// Löschen: Sprache (oder Seite) wird gelöscht, dann auch der zugehörige SEO-Link
			if(($LAN['Active'] ?? null) == -2 || ($PAG['Active'] ?? null) == -2) {
				if($linkId) $Link->deleteById($linkId);
				$row['Active'] = -2;
				unset($row);
				continue;
			}

			// aktuelle SEO-URL (existiert der Link nicht mehr, gilt die Seite als ohne Link)
			$seoNow = $linkId ? ($Link->getById($linkId)['LINK']['D'][$linkId]['FromURL'] ?? '') : '';
			if(!$seoNow) $linkId = '';

			// Nur eine geänderte Eingabe ändert den Link. Leer + noch kein Link = automatisch aus dem Titel.
			$seo = $Link->normalize(trim($LAN['FromURL'] ?? ''));
			if(!$seo && !$linkId) $seo = $Link->normalize($LAN['Title'] ?? $cur['Title'] ?? '');

			if($seo && $seo !== $seoNow) {
				if($linkId) {
					$res   = $Link->rename(['OldSeoId' => $linkId, 'NewSeo' => $seo])[0];
					$final = $res['NewSeo'];
					if($res['Success']) $linkId = $res['NewId'];
				} else {
					$res   = $Link->create(['SeoURL' => $seo, 'Page' => 'frontend__page', 'ModuleId' => 'fremeo/page', 'Param' => "R[Id]={$kPAG}&R[LanguageId]={$kLAN}"])[0];
					$final = $res['SeoUrl'];
					$linkId = $res['LinkId'];
				}
				if($final !== $seo) $E['Info'][] = "Die SEO-URL \"{$seo}\" war schon vergeben, verwendet wurde \"{$final}\".";
				$row['LINK'] = $linkId; // nur bei neuem/geändertem Link schreiben, sonst bleibt der gespeicherte Wert
			}

			if(isset($row['Text'])) $row['Text'] = str_replace('-textarea>','textarea>', $row['Text']);
			unset($row);
		}
	}

	$C['fremeo/page']['CData']->set_object($D); 
}

$F['PAGE']['W'][0]['ID'] = [($R['Id']??null)];
$F['PAGE']['LANGUAGE'] = [];

$C['fremeo/page']['CData']->get_object($D,$F);

unset($F['PAGE']);

// SEO-URLs für die Anzeige: [Seite][Sprache] => URL
$ids = [];
foreach((array)($D['PAGE']['D'] ?? []) AS $PAG) {
	foreach((array)($PAG['LANGUAGE']['D'] ?? []) AS $LAN) {
		if(!empty($LAN['LINK'])) $ids[] = $LAN['LINK'];
	}
}
$links = $ids ? ($Link->getById($ids)['LINK']['D'] ?? []) : [];
foreach((array)($D['PAGE']['D'] ?? []) AS $kPAG => $PAG) {
	foreach((array)($PAG['LANGUAGE']['D'] ?? []) AS $kLAN => $LAN) {
		$E['SEO'][$kPAG][$kLAN] = $links[$LAN['LINK'] ?? '']['FromURL'] ?? '';
	}
}
$R['PageEdit'] = $E; // Hilfswerte fürs Template ($D.R.PageEdit), $D enthält nur die Datenbankstruktur

