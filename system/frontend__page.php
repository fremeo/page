<?php
if($R['Id']??null) {
	$F['PAGE']['W'][0]['ID'] = [$R['Id']];
	$F['PAGE']['W'][0]['Active'] = 1;
	$F['PAGE']['W'][0]['LANGUAGE']['W'][0]['ID'] = ($R['LanguageId']??'DE'); #Todo: Sprache Übergabe
	$F['PAGE']['W'][0]['LANGUAGE']['W'][0]['Active'] = 1;

	$F['PAGE']['LANGUAGE']['W'][0]['ID'] = 'DE'; #Todo: Sprache Übergabe


	$C['fremeo/page']['CData']->get_object($D,$F);
	unset($F['PAGE']);#ToDo: get_object aus index entfernen, dann kann diese Zeile entfernt werden.

	$C['Smarty']->assign('D',$D);
	if(isset($D['PAGE']['D'][ $R['Id'] ])) {
		$D['PAGE']['D'][ $R['Id'] ]['LANGUAGE']['D'][ 'DE' ]['Text'] = $C['Smarty']->fetch('string:'.$D['PAGE']['D'][ $R['Id'] ]['LANGUAGE']['D'][ 'DE' ]['Text']);
	} else {
		header("HTTP/1.1 404 Not Found");
	}
} else {
	header("HTTP/1.1 404 Not Found");
	#header("?R[Page]=error.404&R[MuduleId]=fremeo/core");
}