<?php
namespace fremeo\page\tests;

use fremeo\core\Link;
use PHPUnit\Framework\TestCase;
use phploader\CData;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Integrationstest: admin__page.edit.php legt SEO-Links über die Link-Klasse an, benennt sie um und löscht sie.
 * Die SEO-URL wird nur gespeichert, wenn das Feld FromURL gesendet wird (der Vorschlag aus dem Titel entsteht im Browser).
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

	/** Führt admin__page.edit.php aus; $lang = Sprachdaten der Seite $id (Sprache DE). Gibt $D zurück. */
	private function savePage(?array $lang, array $page = ['Active' => 1], string $id = 'p1'): array {
		$C = &$this->C;
		$D = ['ACTION' => $lang === null ? null : 'save'];
		if ($lang !== null) $D['PAGE']['D'][$id] = $page + ['LANGUAGE' => ['D' => ['DE' => $lang]]];
		$R = ['Id' => $id];
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

	/** SEO-URL der Sprache DE aus $D.LINK.D (wie im Template), null = kein Link. */
	private function seo(array $D, string $page = 'p1'): ?string {
		$link = $D['LINK']['D'][$D['PAGE']['D'][$page]['LANGUAGE']['D']['DE']['LINK'] ?? ''] ?? null;
		return $link === null ? null : (string)($link['FromURL'] ?? '');
	}

	private function isHome(array $D, string $page = 'p1'): bool {
		return $this->seo($D, $page) === '';
	}

	public function testServerNeverCreatesSeoUrlFromTitle(): void {
		// Das Formular sendet das unveränderte Feld nicht mit. Der Vorschlag aus dem Titel kommt per JS in das Feld, nicht vom Server.
		$D = $this->savePage(['Active' => 1, 'Title' => 'Über uns & mehr']);

		$this->assertSame([], $this->linkIds($this->db));
		$this->assertNull($this->seo($D), 'kein Link');
		$this->assertSame('Über uns & mehr', $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['Title']);
	}

	public function testSeoUrlSentByBrowserIsSavedNormalized(): void {
		$D = $this->savePage(['Active' => 1, 'Title' => 'Egal', 'FromURL' => 'Mein Impressum']);

		$this->assertSame('mein_impressum', $this->seo($D));
		$this->assertSame($this->id('mein_impressum'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$this->id('mein_impressum')], $this->linkIds($this->db));
	}

	public function testSeoUrlTakenInOtherModuleGetsSuffix(): void {
		(new Link($this->other))->createOne('team', 'frontend__other', 'fremeo/other'); // nur im anderen Modul

		$D = $this->savePage(['Active' => 1, 'Title' => 'Team', 'FromURL' => 'team']);

		$this->assertSame('team_1', $this->seo($D));
		$this->assertNotEmpty($D['R']['Info']);
	}

	public function testExistingLinkIsKeptWhenFieldIsNotSentAndTitleChanges(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Erster Titel', 'FromURL' => 'erster_titel']);
		$linkId = $this->id('erster_titel');

		// wie im Formular: LINK und FromURL werden nicht mitgesendet (unveränderte Felder)
		$D = $this->savePage(['Active' => 1, 'Title' => 'Neuer Titel']);

		$this->assertSame('erster_titel', $this->seo($D));
		$this->assertSame([$linkId], $this->linkIds($this->db));
	}

	public function testSaveWithoutChangesKeepsTheLink(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Behält', 'FromURL' => 'behalt']);
		$linkId = $this->id('behalt');

		$D = $this->savePage(['Active' => 1]);
		$D = $this->savePage(['Active' => 1]);

		$this->assertSame('behalt', $this->seo($D));
		$this->assertSame($linkId, $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$linkId], $this->linkIds($this->db));
	}

	public function testSaveWithoutChangesKeepsTheText(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Mit Text', 'Text' => '<p>Inhalt</p>']);

		$D = $this->savePage(['Active' => 1]);

		$this->assertSame('<p>Inhalt</p>', $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['Text']);
	}

	public function testChangedSeoUrlRenamesTheLinkWithoutSuffix(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Alt', 'FromURL' => 'alt']);

		$D = $this->savePage(['Active' => 1, 'FromURL' => 'neu']);

		$this->assertSame('neu', $this->seo($D));
		$this->assertEmpty($D['R']['Info']);
		$this->assertSame($this->id('neu'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
		$this->assertSame([$this->id('neu')], $this->linkIds($this->db));
	}

	public function testEmptySeoUrlMakesThePageTheStartPage(): void {
		// Der Benutzer löscht die SEO-URL bewusst: leeres Feld wird gesendet, aus dem Titel entsteht nichts
		$D = $this->savePage(['Active' => 1, 'Title' => 'Willkommen', 'FromURL' => '']);

		$this->assertSame([$this->id('')], $this->linkIds($this->db));
		$this->assertSame('', $this->seo($D)); // Link mit leerer URL, nicht "kein Link" (null)
		$this->assertTrue($this->isHome($D));
		$this->assertSame($this->id(''), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
	}

	public function testClearingAnExistingSeoUrlMakesItTheStartPage(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Start', 'FromURL' => 'start']);

		$D = $this->savePage(['Active' => 1, 'FromURL' => '']);

		$this->assertTrue($this->isHome($D));
		$this->assertSame([$this->id('')], $this->linkIds($this->db));
	}

	public function testStartPageStaysStartPageWhenSavedAgain(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Start', 'FromURL' => '']);

		$D = $this->savePage(['Active' => 1, 'Title' => 'Neuer Titel']); // Feld nicht gesendet: Titel ändert nichts

		$this->assertTrue($this->isHome($D));
		$this->assertSame([$this->id('')], $this->linkIds($this->db));
	}

	public function testEmptySeoUrlTakenByAnotherPageGetsSuffixLikeAnyOtherUrl(): void {
		$this->C['Link']->createOne('', 'frontend__page', 'fremeo/page'); // die Startseite gibt es schon

		$D = $this->savePage(['Active' => 1, 'Title' => 'Zweite', 'FromURL' => ''], ['Active' => 1], 'p2');

		$this->assertSame('_1', $this->seo($D, 'p2'));
		$this->assertNotEmpty($D['R']['Info']);
		$this->assertEqualsCanonicalizing([$this->id(''), $this->id('_1')], $this->linkIds($this->db));
	}

	public function testDeletedLanguageRemovesTheLink(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Weg', 'FromURL' => 'weg']);

		$this->savePage(['Active' => -2]);

		$this->assertSame([], $this->linkIds($this->db));
	}

	public function testBrokenLinkReferenceIsReplacedWhenSeoUrlIsSent(): void {
		// LANGUAGE.LINK zeigt auf einen nicht (mehr) vorhandenen Link
		$d = ['PAGE' => ['D' => ['p1' => ['Active' => 1, 'LANGUAGE' => ['D' => ['DE' => ['Active' => 1, 'Title' => 'Defekt', 'LINK' => '00000000']]]]]]];
		$this->db->set_object($d);

		$D = $this->savePage(['Active' => 1, 'Title' => 'Defekt', 'FromURL' => 'defekt']);

		$this->assertSame('defekt', $this->seo($D));
		$this->assertSame($this->id('defekt'), $D['PAGE']['D']['p1']['LANGUAGE']['D']['DE']['LINK']);
	}

	public function testLinkTargetsTheFrontendPage(): void {
		$this->savePage(['Active' => 1, 'Title' => 'Ziel', 'FromURL' => 'ziel']);

		$d = $this->C['Link']->getBySeo('ziel')['LINK']['D'][$this->id('ziel')];
		$this->assertSame('R[Page]=frontend__page&R[ModuleId]=fremeo/page&R[Id]=p1&R[LanguageId]=DE', $d['ToURL']);
	}
}
