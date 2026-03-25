<?php 
namespace papp\page;
class install() {

	function up() {
		#SEO Links anlegen
	
		$C['Link']->createOne('admin/page_list','admin__page.list', 'papp/page');
		$C['Link']->createOne('admin/page_edit','admin__page.edit', 'papp/page');

		#Beispiel Seite erstellen
		#$C['papp/page']['Page']->('Beispiel','test','Das ist eine Beispiel Seite');
	}
}