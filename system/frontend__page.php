<?php
	$F['PAGE']['W'][0]['ID'] = [$D['_ID']];
	$F['PAGE']['W'][0]['Active'] = 1;
	$F['PAGE']['W'][0]['LANGUAGE']['W'][0]['ID'] = 'DE'; #Todo: Sprache Übergabe
	$F['PAGE']['W'][0]['LANGUAGE']['W'][0]['Active'] = 1;

	$F['PAGE']['LANGUAGE']['W'][0]['ID'] = 'DE'; #Todo: Sprache Übergabe


	$C['CData']->get_object($D,$F);
	unset($F['PAGE']);#ToDo: get_object aus index entfernen, dann kann diese Zeile entfernt werden.

	$C['Smarty']->assign('D',$D);
	$D['PAGE']['D'][ $D['_ID'] ]['LANGUAGE']['D'][ 'DE' ]['Text'] = $C['Smarty']->fetch('string:'.$D['PAGE']['D'][ $D['_ID'] ]['LANGUAGE']['D'][ 'DE' ]['Text']);