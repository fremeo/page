<?php

if(($D['ACTION']??null) == 'save') {

#Link erstellen oder aktuallisieren
	foreach((array)$D['PAGE']['D'] AS $kPAG => $PAG) {
		foreach((array)$PAG['LANGUAGE']['D'] AS $kLAN => $LAN) {
			$_delLink[] = $LAN['LINK'];

			if($LAN['Active'] != -2 && $PAG != -2) {

				$_newLink["{$kPAG}-{$kLAN}"] = [
					'Page' => 'frontend__page',
					'ModuleId' => 'fremeo/page',
					'Param' => "R[Id]={$kPAG}&R[LanguageId]={$kLAN}",
					'SeoURL' => $LAN['FromURL'],
				];


				$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Text'] = str_replace('-textarea>','textarea>',$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Text']);
			} else { #WennGelöscht werden soll, soll dann dazu gehörige Link entfernt werden
				$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Active'] = -2;
				if($PAG == -2) {
					$D['PAGE']['D'][$kPAG]['Active'] = -2;
				}
			}
		}
	}

	if($_delLink) {
		$C['fremeo~core']['Link']->deleteById($_delLink);

		if($_newLink) {
				$ret = $C['fremeo~core']['Link']->create($_newLink);


				foreach((array)$D['PAGE']['D'] AS $kPAG => $PAG) {
					foreach((array)$PAG['LANGUAGE']['D'] AS $kLAN => $LAN) {
						if(isset($ret["{$kPAG}-{$kLAN}"])) {
							$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['LINK'] = $ret["{$kPAG}-{$kLAN}"]['LinkId'];

						}
					}
				}
		}
	}
/*
	#erstelle neues SEO Link weiterleitung und lösche alte und weise die Link-ID der Seite neu zu.
	foreach((array)$D['PAGE']['D'] AS $kPAG => $PAG) {
		foreach((array)$PAG['LANGUAGE']['D'] AS $kLAN => $LAN) {

			
			$D['LINK']['D'][ $LAN['LINK'] ]['Active'] = -2; #Alte URL löschen
			if($LAN['Active'] != -2 && $PAG != -2) {
				$hURL = hash("crc32b", $LAN['FromURL']);
				$D['LINK']['D'][$hURL] = [
					'Active'	=> 1,
					'FromURL'	=> $LAN['FromURL'],
					'ToURL'		=> "R[Page]=frontend__page&R[ModuleId]=fremeo/page&R[Id]={$kPAG}&R[LanguageId]={$kLAN}",
				];
				$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['LINK'] = $hURL;
				$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Text'] = str_replace('-textarea>','textarea>',$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Text']);
				
			}
			else { #WennGelöscht werden soll, soll dann dazu gehörige Link entfernt werden
				$D['LINK']['D'][ $LAN['LINK'] ]['Active'] = -2;
				$D['PAGE']['D'][$kPAG]['LANGUAGE']['D'][$kLAN]['Active'] = -2;
				if($PAG == -2) {
					$D['PAGE']['D'][$kPAG]['Active'] = -2;
				}
			}
		}
	}
*/
	$C['fremeo~page']['CData']->set_object($D); 
}

$F['PAGE']['W'][0]['ID'] = [($R['Id']??null)];
$F['PAGE']['LANGUAGE'] = [];

$C['fremeo~page']['CData']->get_object($D,$F);

unset($F['PAGE']);

if($R['Id']??null) {
	foreach((array)$D['PAGE']['D'][ $R['Id'] ]['LANGUAGE']['D'] AS $kLAN => $LAN) {
		$F['LINK']['W'][0]['ID'][] = $LAN['LINK'];
	}
}

