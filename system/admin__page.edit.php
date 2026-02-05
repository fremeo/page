<?php

if(($D['ACTION']??null) == 'save') {

	#erstelle neues SEO Link weiterleitung und lösche alte und weise die Link-ID der Seite neu zu.
	foreach((array)$D['PAGE']['D'] AS $kPAG => $PAG) {
		foreach((array)$PAG['LANGUAGE']['D'] AS $kLAN => $LAN) {

			$D['LINK']['D'][ $LAN['LINK'] ]['Active'] = -2; #Alte URL löschen
			if($LAN['Active'] != -2 && $PAG != -2) {
				$hURL = hash("crc32b", $LAN['FromURL']);
				$D['LINK']['D'][$hURL] = [
					'Active'	=> 1,
					'FromURL'	=> $LAN['FromURL'],
					'ToURL'		=> "R[Page]=frontend__page&R[ModuleId]=papp/page&R[Id]={$kPAG}&R[LanguageId]={$kLAN}",
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

	$C['CData']->set_object($D); 
}

$F['PAGE']['W'][0]['ID'] = [($R['Id']??null)];
$F['PAGE']['LANGUAGE'] = [];

$C['CData']->get_object($D,$F);

unset($F['PAGE']);

if($R['Id']??null) {
	foreach((array)$D['PAGE']['D'][ $R['Id'] ]['LANGUAGE']['D'] AS $kLAN => $LAN) {
		$F['LINK']['W'][0]['ID'][] = $LAN['LINK'];
	}
}

