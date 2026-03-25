<?php

if(($D['ACTION']??null) == 'save') {
	$C['papp~page']['CData']->set_object($D); 
}

#$F['PAGE']['W'][0]['ID'] = [$D['_ID']];
$F['PAGE']['LANGUAGE'] = [];

##$CData->backup(['DestinationPath' => 'data/backup/']);
$C['papp~page']['CData']->get_object($D,$F);
unset($F['BLOG']);