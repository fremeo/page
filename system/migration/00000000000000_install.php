<?php 
namespace fremeo\page;
class install() {

	function up() {
		#SEO Links anlegen
	
		$C['Link']->createOne('admin/page_list','admin__page.list', 'fremeo/page');
		$C['Link']->createOne('admin/page_edit','admin__page.edit', 'fremeo/page');

		#Beispiel Seite erstellen
		#$C['fremeo/page']['Page']->('Beispiel','test','Das ist eine Beispiel Seite');
	}
}