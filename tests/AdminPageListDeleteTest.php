<?php
namespace fremeo\page\tests;

use fremeo\core\Link;
use PHPUnit\Framework\TestCase;
use phploader\CData;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Integrationstest: admin__page.list.php löscht markierte Seiten samt SEO-Links erst beim Speichern
 * und übernimmt aus dem Formular nur Active und Follow.
 */
class AdminPageListDeleteTest extends TestCase {

	private string $tmp;
	private array $C;
	private CData $db;

	protected function setUp(): void {
		$this->tmp = sys_get_temp_dir() . '/fremeo_page_list_' . uniqid();
		mkdir($this->tmp);

		$this->db = new CData(['DB' => ['FILENAME' => "{$this->tmp}/page.db", 'FILENAME_C' => "{$this->tmp}/page_c.db"]]);
		$this->db->registerPattern([
			'LINK' => ['Active' => ['Type' => 'checkbox'], 'FromURL' => ['Type' => 'text'], 'ToURL' => ['Type' => 'text']],
			'PAGE' => [
				'Active' => ['Type' => 'checkbox'],
				'Follow' => ['Type' => 'checkbox'],
				'Title'  => ['Type' => 'text'],
				'D'      => ['LANGUAGE' => [
					'Active' => ['Type' => 'checkbox'], 'Title' => ['Type' => 'text'], 'Text' => ['Type' => 'text'], 'LINK' => ['Type' => 'id'],
				]],
			],
		]);

		$this->C = ['fremeo/page' => ['CData' => $this->db], 'Link' => new Link($this->db)];

		foreach (['p1' => 'eins', 'p2' => 'zwei', 'p3' => 'drei'] as $id => $seo) {
			$linkId = $this->C['Link']->createOne($seo, 'frontend__page', 'fremeo/page', "R[Id]={$id}&R[LanguageId]=DE");
			$d = ['PAGE' => ['D' => [$id => [
				'Active' => 1, 'Follow' => 1,
				'LANGUAGE' => ['D' => ['DE' => ['Active' => 1, 'Title' => "Seite {$id}", 'LINK' => $linkId]]],
			]]]];
			$this->db->set_object($d);
		}
	}

	protected function tearDown(): void {
		unset($this->C, $this->db);
		gc_collect_cycles();
		(new Filesystem())->remove($this->tmp);
	}

	/** Führt admin__page.list.php aus (wie ein Klick auf "Save") und gibt $D mit der Ausgabe zurück. */
	private function saveList(array $post, bool $save = true): array {
		$C = &$this->C;
		$D = ($save ? ['ACTION' => 'save'] : []) + ['PAGE' => ['D' => $post]];
		(function () use (&$C, &$D) {
			include __DIR__ . '/../system/admin__page.list.php';
		})();
		return $D;
	}

	private function pages(): array {
		$f['PAGE']['LANGUAGE'] = [];
		$this->db->get_object($d, $f);
		return (array)($d['PAGE']['D'] ?? []);
	}

	private function seoUrls(): array {
		$f['LINK'] = [];
		$this->db->get_object($d, $f);
		return array_column($d['LINK']['D'] ?? [], 'FromURL');
	}

	public function testMarkedPageIsDeletedWithItsSeoLinks(): void {
		$D = $this->saveList(['p1' => ['Active' => 1, '__DELETE__' => 1], 'p2' => ['Active' => 1]]);

		$this->assertEqualsCanonicalizing(['p2', 'p3'], array_keys($this->pages()));
		$this->assertEqualsCanonicalizing(['zwei', 'drei'], $this->seoUrls());
		$this->assertEqualsCanonicalizing(['p2', 'p3'], array_keys($D['PAGE']['D']), 'die Ausgabe zeigt die gelöschte Seite nicht mehr');
	}

	public function testNothingIsDeletedWithoutSave(): void {
		$this->saveList(['p1' => ['__DELETE__' => 1]], false);

		$this->assertEqualsCanonicalizing(['p1', 'p2', 'p3'], array_keys($this->pages()));
		$this->assertCount(3, $this->seoUrls());
	}

	public function testSeveralPagesCanBeDeletedAtOnce(): void {
		$this->saveList(['p1' => ['__DELETE__' => 1], 'p3' => ['__DELETE__' => 1]]);

		$this->assertSame(['p2'], array_keys($this->pages()));
		$this->assertSame(['zwei'], $this->seoUrls());
	}

	public function testOnlyActiveAndFollowAreTakenFromTheForm(): void {
		$this->saveList(['p2' => ['Active' => 0, 'Follow' => 0, 'Title' => 'Manipuliert', 'Evil' => 'x']]);

		$page = $this->pages()['p2'];
		$this->assertEquals(0, $page['Active']);
		$this->assertEquals(0, $page['Follow']);
		$this->assertArrayNotHasKey('Evil', $page);
		$this->assertNotSame('Manipuliert', $page['Title'] ?? null);
		$this->assertSame('Seite p2', $page['LANGUAGE']['D']['DE']['Title']);
	}

	public function testInvalidIdsAreIgnored(): void {
		$this->saveList(['../evil' => ['Active' => 1], 'p 9' => ['__DELETE__' => 1]]);

		$this->assertEqualsCanonicalizing(['p1', 'p2', 'p3'], array_keys($this->pages()));
	}

	public function testUnknownPageToDeleteIsIgnored(): void {
		$this->saveList(['gibtesnicht' => ['__DELETE__' => 1]]);

		$this->assertCount(3, $this->pages());
		$this->assertCount(3, $this->seoUrls());
	}
}
