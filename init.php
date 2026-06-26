<?php


#DB-----------------

$Pattern = [];
$Pattern['PAGE'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'Follow'		=> ['Type' => 'checkbox'], #1=follow; 0/null=nofollow + bei nofollow wird Inhalt mit base64 verschlüsselt
];
$Pattern['PAGE']['D']['LANGUAGE'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'Title'			=> ['Type' => 'text'],
	'Text'			=> ['Type' => 'text'],
	'LINK'			=> ['Type' => 'id'], #ID für LINK je Sprache
];

##$C['CData']->registerPattern($Pattern);


$C['fremeo~page']['CData'] = new \phploader\CData( [ 'DB' => ['FILENAME' => PROJECT_ROOT.'data/fremeo~page/data.db', 'FILENAME_C' => PROJECT_ROOT.'data_c/fremeo~page/data.db' ] ] );
$C['fremeo~page']['CData']->registerPattern($Pattern);

/*
$F['PAGE']['LANGUAGE'] = [];
$C['CData']->get_object($D1,$F);
$C['fremeo~page']['CData']->set_object($D1);
*/