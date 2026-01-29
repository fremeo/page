<?php


#Seo Link Registrierung
/*
$D['LINK']['D'][ hash("crc32b", 'admin/link.list.html') ] = [
	'FromURL' => "admin/link.list.html",
	'ToURL' => "D[_PAGE]=admin__link.list&R[ModulId]=papp/framework",
	'Active' => 1,
];
*/
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

$C['CData']->registerPattern($Pattern);