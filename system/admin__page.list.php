<?php
// Admin: Liste der Seiten (Active/Follow ändern, Seiten löschen). Template: admin__page.list.tpl

$DB = $C['fremeo/page']['CData'];

if(($D['ACTION']??null) == 'save') {
	// Aus dem Formular werden nur Active und Follow vorhandener, gültiger Seiten übernommen (nie die ganze Anfrage in die Datenbank schreiben).
	// Zu löschende Seiten markiert das Template mit D[PAGE][D][<Id>][__DELETE__]=1, entfernt wird erst beim Speichern.
	$U = [];
	$delete = [];
	foreach((array)($D['PAGE']['D'] ?? []) AS $id => $row) {
		if(!preg_match('/^[A-Za-z0-9_-]+$/', (string)$id)) continue;

		if(!empty($row['__DELETE__'])) {
			$delete[] = (string)$id;
			continue;
		}
		foreach(['Active', 'Follow'] AS $field) {
			if(isset($row[$field])) $U['PAGE']['D'][$id][$field] = (int)$row[$field];
		}
	}

	if($delete) {
		// Seiten mit Sprachen laden, um die SEO-Links jeder Sprache zu kennen und mit zu löschen
		$q['PAGE']['W'][0]['ID'] = $delete;
		$q['PAGE']['LANGUAGE'] = [];
		$DB->get_object($old, $q);

		foreach($delete AS $id) {
			if(empty($old['PAGE']['D'][$id])) continue;

			foreach((array)($old['PAGE']['D'][$id]['LANGUAGE']['D'] ?? []) AS $LAN) {
				if(!empty($LAN['LINK']) && isset($C['Link'])) $C['Link']->deleteById((string)$LAN['LINK']);
			}
			unset($U['PAGE']['D'][$id]);
			$U['PAGE']['D'][$id] = '__DELETE__';
		}
	}

	if($U) $DB->set_object($U);
}
unset($D['PAGE']); // gepostete Daten nicht mit den Daten aus der Datenbank vermischen

$F['PAGE']['LANGUAGE'] = [];

##$CData->backup(['DestinationPath' => 'data/backup/']);
$DB->get_object($D,$F);
unset($F['BLOG']);
