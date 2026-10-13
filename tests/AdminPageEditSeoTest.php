<?php
namespace fremeo\page\tests;

use fremeo\core\Link;
use PHPUnit\Framework\TestCase;
use phploader\CData;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Integrationstest: admin__page.edit.php legt SEO-Links über die Link-Klasse an, benennt sie um und löscht sie.
 * Arbeitet mit temporären Datenbanken (Modul "page" und ein zweites Modul "other" für die modulübergreifende Prüfung).
 */
class AdminPageEditSeoTest extends TestCase {

	private string $tmp;
	private array $C;
	private CData $db;
	private CData $other;

	private const LINK = [
		'Active'  => ['Type' => 'checkbox'],
		'FromURL' => ['Type' => 'text'],
		'ToURL'   => ['Type' => 'text'],
	];

	protected function setUp(): void {
		$this->tmp = sys_get_temp_dir() . '/fremeo_page_' . uniqid();
		mkdir($this->tmp);

		$this->db = new CData(['DB' => ['FILENAME' => "{$this->tmp}/page.db", 'FILENAME_C' => "{$this->tmp}/page_c.db"]]);
		$this->db->registerPattern([
			'LINK' => self::LINK,
			'PAGE' => [
				'Active' => ['Type' => 'checkbox'],
				'Follow' => ['Type' => 'checkbox'],
				'D'      => ['LANGUAGE' => [
					'Active' => ['Type' => 'checkbox'], 'Title' => ['Type' => 'text'], 'Text' => ['Type' => 'text'], 'LINK' => ['Type' => 'id'],
				]],
			],
		]);

		$this->other = new CData(['DB' => ['FILENAME' => "{$this->tmp}/other.db", 'FILENAME_C' => "{$this->tmp}/other_c.db"]]);
		$this->other->registerPattern(['LINK' => self::LINK]);

		$this->C = [
			'fremeo/page' => ['CData' => $this->db],
			'Link'        => new Link($this->db, [$this->other]),
		];
	}

	protected function tearDown(): void {
		unset($this->C, $this->db, $this->other);
		gc_collect_cycles();
		(new Filesystem())->remove($this->tmp);
	}

	/** Führt admin__page.edit.php aus; $lang = Sprachdaten der Seite "p1" (Sprache DE). Gibt $D zurück. */
	private function savePage(?array $lang, array $page = ['Active' => 1]): array {
		$C = &$this->C;
		$D = ['ACTION' => $lang === null ? null : 'save'];
		if ($lang !== null) $D['PAGE']['D']['p1'] = $page + ['LANGUAGE' => ['D' => ['DE' => $lang]]];
		$R = ['Id' => 'p1'];
		$D['R'] = &$R; // wie im Core: $D['R'] verweist auf $R
		(function () use (&$C, &$D, &$R) {
			include __DIR__ . '/../system/admin__page.edit.php';
		})();
		return $D;
	}

	private function linkIds(CData $db): array {
		$f['LINK'] = [];
		$db->get_object($d, $f);
		return array_keys($d['LINK']['D'] ?? []);
	}

	private function id(string $seo): string {
		return $this->C['Link']->id($seo);
	}

	public function testTitleBecomesSeoUrlWhenFieldIsEmpty(): void {
		$D = $this->savePage(['Active' => 1, 'Title' => 'Über uns & mehr', 'FromURL' => '']);

		$this->assertSame('uber_uns_mehr', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame($this->id('uber_uns_mehr'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$this->id('uber_uns_mehr')], $this->linkIds($this->db));
	}

	public function testGivenSeoUrlIsNormalized(): void {
		$D = $this->savePage(['Active' => 1, 'Title' => 'Egal', 'FromURL' => 'Mein Impressum']);

		$this->assertSame('mein_impressum', $D['R']['PageEdit']['SEO']['p1']['DE']);
	}

	public function testSeoUrlTakenInOtherModuleGetsSuffix(): void {
		$this->C['Link']->createOne('kontakt', 'frontend__other', 'fremeo/other'); // schreibt in $this->db
		$taken = new Link($this->other);
		$taken->createOne('team', 'frontend__other', 'fremeo/other');             // nur im anderen Modul

		$D = $this->savePage(['Active' => 1, 'Title' => 'Team', 'FromURL' => '']);

		$this->assertSame('team_1', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertNotEmpty($D['R']['PageEdit']['Info']);
	}

	public function testExistingLinkIsKeptWhenFieldIsEmptyAndTitleChanges(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Erster Titel', 'FromURL' => '']);
		$linkId = $this->id('erster_titel');

		// wie im Formular: LINK wird nicht mitgesendet (unverändertes Feld), FromURL leer
		$D = $this->savePage(['Active' => 1, 'Title' => 'Neuer Titel', 'FromURL' => '']);

		$this->assertSame('erster_titel', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame([$linkId], $this->linkIds($this->db));
	}

	public function testSaveWithoutChangesKeepsTheLink(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Behält', 'FromURL' => '']);
		$linkId = $this->id('behalt');

		// das Formular sendet unveränderte Felder (LINK, FromURL, Title) nicht mit
		$D = $this->savePage(['Active' => 1]);
		$D = $this->savePage(['Active' => 1]);

		$this->assertSame('behalt', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame($linkId, $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$linkId], $this->linkIds($this->db));
	}

	public function testSaveWithoutChangesKeepsTheText(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Mit Text', 'Text' => '<p>Inhalt</p>', 'FromURL' => '']);

		$D = $this->savePage(['Active' => 1]);

		$this->assertSame('<p>Inhalt</p>', $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['Text']);
	}

	public function testSettingOwnSeoUrlRenamesWithoutSuffix(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Start', 'FromURL' => '']);

		// nur FromURL geändert, LINK nicht mitgesendet
		$D = $this->savePage(['Active' => 1, 'FromURL' => 'willkommen']);
		$this->assertSame('willkommen', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertEmpty($D['R']['PageEdit']['Info']);

		// erneut speichern ohne Änderung: bleibt stehen
		$D = $this->savePage(['Active' => 1]);
		$this->assertSame('willkommen', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame([$this->id('willkommen')], $this->linkIds($this->db));
	}

	public function testChangedSeoUrlRenamesTheLink(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Alt', 'FromURL' => '']);

		$D = $this->savePage(['Active' => 1, 'Title' => 'Alt', 'FromURL' => 'neu']);

		$this->assertSame('neu', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame($this->id('neu'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$this->id('neu')], $this->linkIds($this->db));
	}

	public function testDeletedLanguageRemovesTheLink(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Weg', 'FromURL' => '']);

		$this->savePage(['Active' => -2]);

		$this->assertSame([], $this->linkIds($this->db));
	}

	public function testBrokenLinkReferenceIsReplaced(): void {
		// LANGUAGE.LINK zeigt auf einen nicht (mehr) vorhandenen Link
		$d = ['PAGE' => ['D' => ['p1' => ['Active' => 1, 'LANGUAGE' => ['D' => ['DE' => ['Active' => 1, 'Title' => 'Defekt', 'LINK' => '00000000']]]]]]];
		$this->db->set_object($d);

		$D = $this->savePage(['Active' => 1, 'Title' => 'Defekt']);

		$this->assertSame('defekt', $D['R']['PageEdit']['SEO']['p1']['DE']);
		$this->assertSame($this->id('defekt'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
	}

	public function testLinkTargetsTheFrontendPage(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Ziel', 'FromURL' => '']);

		$d = $this->C['Link']->getBySeo('ziel')['LINK']['D'][$this->id('ziel')];
		$this->assertSame('R[Page]=frontend__page&R[ModuleId]=fremeo/page&R[Id]=p1&R[LanguageId]=DE', $d['ToURL']);
	}
}
