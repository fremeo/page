<?php 
namespace fremeo\page;
return new class($D) {

	function __construct($D) {
		$this->C = $D['C'];
		$this->ModuleId = 'fremeo/page';
	}

	function up() {
		#SEO Links anlegen
	
		$D['LINK']['D'][hash("crc32b", 'admin/page_list')] = ['Active' => 1, 'FromURL' => 'admin/page_list', 'ToURL' => 'R[Page]=admin__page.list'];
		$D['LINK']['D'][hash("crc32b", 'admin/page_edit')] = ['Active' => 1, 'FromURL' => 'admin/page_edit', 'ToURL' => 'R[Page]=admin__page.edit'];

		##$C['Link']->createOne('admin/page_list','admin__page.list', 'fremeo/page');
		##$C['Link']->createOne('admin/page_edit','admin__page.edit', 'fremeo/page');

		#Beispiel Seite erstellen
		#$C['fremeo/page']['Page']->('Beispiel','test','Das ist eine Beispiel Seite');

		$this->C[$this->ModuleId]['CData']->set_object($D);
	}
};