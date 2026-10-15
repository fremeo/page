<?php
// Admin: Seite bearbeiten. Template: admin__page.edit.tpl
//
// $D wird aufgebaut und 1:1 ans Template gereicht: $D.PAGE.D.<Id> (mit LANGUAGE.D) und $D.LINK.D.<LinkId> (SEO-URLs der Sprachen).
// Meldungen stehen in $R (Msg, Info), nicht in $D.
//
// Das Formular sendet nur geänderte Felder (siehe input.tpl, ischanged). Für die SEO-URL (FromURL je Sprache) gilt:
//   fehlt im POST = unverändert, gesendet und leer = Startseite, gesendet mit Text = diese SEO-URL (Suffix _1, _2 ... wenn vergeben).
// Der Vorschlag aus dem Titel entsteht im Browser (JS im Template), der Server erzeugt nichts aus dem Titel.

$DB   = $C['fremeo/page']['CData'];
$Link = $C['Link'];
$R['Info'] = [];

$valid = fn($s, $re = '/^[A-Za-z0-9_-]+$/') => (is_string($s) || is_int($s)) && preg_match($re, (string)$s) === 1;
$post  = (array)($D['PAGE']['D'] ?? []);
$ids   = array_map('strval', array_filter(array_keys($post), $valid));

if(($D['ACTION'] ?? null) == 'save' && $ids) {

	// Link-IDs kommen aus der Datenbank, weil unveränderte Felder nicht mitgesendet werden
	$f['PAGE']['W'][0]['ID'] = $ids;
	$f['PAGE']['LANGUAGE'] = [];
	$DB->get_object($old, $f);

	$U = [];
	foreach($ids AS $id) {
		$page    = (array)$post[$id];
		$deleted = ($page['Active'] ?? null) == -2;
		$entry   = array_intersect_key($page, ['Active' => 1, 'Follow' => 1]);

		// Sprachen: die gesendeten, beim Löschen der Seite alle vorhandenen (damit kein SEO-Link übrig bleibt)
		$langs = array_keys((array)($page['LANGUAGE']['D'] ?? []));
		if($deleted) $langs = [...$langs, ...array_keys((array)($old['PAGE']['D'][$id]['LANGUAGE']['D'] ?? []))];

		foreach(array_unique(array_filter($langs, fn($l) => $valid($l, '/^[A-Za-z]{2,8}$/'))) AS $lang) {
			$in     = (array)($page['LANGUAGE']['D'][$lang] ?? []);
			$linkId = (string)($old['PAGE']['D'][$id]['LANGUAGE']['D'][$lang]['LINK'] ?? '');

			if($deleted || ($in['Active'] ?? null) == -2) {
				if($linkId) $Link->deleteById($linkId);
				$entry['LANGUAGE']['D'][$lang] = ['Active' => -2];
				continue;
			}

			$row = array_intersect_key($in, ['Active' => 1, 'Title' => 1, 'Text' => 1]);
			if(isset($row['Text'])) $row['Text'] = str_replace('-textarea>', 'textarea>', (string)$row['Text']);

			// aktuelle SEO-URL (null = noch kein Link, auch wenn die Referenz ins Leere zeigt; eine leere URL ist die Startseite)
			$cur = $linkId ? ($Link->getById($linkId)['LINK']['D'][$linkId] ?? null) : null;
			$now = $cur ? (string)($cur['FromURL'] ?? '') : null;
			$seo = array_key_exists('FromURL', $in) ? $Link->normalize(trim((string)$in['FromURL'])) : null;

			if($seo !== null && $seo !== $now) {
				if($cur) {
					$res   = $Link->rename(['OldSeoId' => $linkId, 'NewSeo' => $seo])[0];
					$final = $res['NewSeo'];
					$row['LINK'] = $res['Success'] ? $res['NewId'] : $linkId;
				} else {
					$res   = $Link->create(['SeoURL' => $seo, 'Page' => 'frontend__page', 'ModuleId' => 'fremeo/page', 'Param' => "R[Id]={$id}&R[LanguageId]={$lang}"])[0];
					$final = $res['SeoUrl'];
					$row['LINK'] = $res['LinkId'];
				}
				if($final !== $seo) $R['Info'][] = "Die SEO-URL \"{$seo}\" war schon vergeben, verwendet wurde \"{$final}\".";
			}
			$entry['LANGUAGE']['D'][$lang] = $row;
		}
		if($entry) $U['PAGE']['D'][$id] = $entry;
	}

	if(!empty($U)) {
		$DB->set_object($U);
		$R['Msg'] = 'Gespeichert';
	}
}

// Anzeige: die Seite aus R[Id], sonst die gesendeten. Die gepostete Struktur wird verworfen und aus der Datenbank neu aufgebaut.
unset($D['PAGE']);
$show = $valid($R['Id'] ?? null) ? [(string)$R['Id']] : $ids;
if($show) {
	$f = ['PAGE' => ['W' => [['ID' => $show]], 'LANGUAGE' => []]];
	$DB->get_object($D, $f);
}
foreach($ids AS $id) {
	$D['PAGE']['D'][$id] ??= ['Active' => 1]; // neue, noch nicht gespeicherte Seite ("Neu" in der Liste)
}

// SEO-Links der Sprachen ebenfalls in $D holen: $D.LINK.D.<LinkId>
$linkIds = [];
foreach((array)($D['PAGE']['D'] ?? []) AS $page) {
	foreach((array)($page['LANGUAGE']['D'] ?? []) AS $lang) {
		if(!empty($lang['LINK'])) $linkIds[] = $lang['LINK'];
	}
}
if($linkIds) {
	$f = ['LINK' => ['W' => [['ID' => $linkIds]]]];
	$DB->get_object($D, $f);
}
