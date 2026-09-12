<?php
/**
 * @package bbGuild FFXI Extension
 * @copyright (c) 2026 avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\bbguildffxi\tests\game;

use PHPUnit\Framework\TestCase;
use avathar\bbguildffxi\game\ffxi_installer;

class ffxi_installer_test extends TestCase
{
	/** @var ffxi_installer */
	protected $installer;

	/** @var array Captured sql_multi_insert calls: array of [table, data] */
	protected $inserted = array();

	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	protected function setUp(): void
	{
		parent::setUp();

		$this->inserted = array();

		$this->db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		// Capture sql_multi_insert calls
		$this->db->method('sql_multi_insert')
			->willReturnCallback(function ($table, $data) {
				$this->inserted[] = array('table' => $table, 'data' => $data);
			});

		// sql_query (DELETE statements) — no-op
		$this->db->method('sql_query')->willReturn(true);
		$this->db->method('sql_escape')->willReturnCallback(function ($v) { return $v; });

		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$config = new \phpbb\config\config(array());
		$user = $this->getMockBuilder(\phpbb\user::class)
			->disableOriginalConstructor()
			->getMock();

		$this->installer = new ffxi_installer($this->db, $cache, $config, $user);

		// Set table_names and game_id via reflection (normally set by install())
		$ref = new \ReflectionClass($this->installer);

		$tn = $ref->getProperty('table_names');
		$tn->setAccessible(true);
		$tn->setValue($this->installer, array(
			'bb_factions_table'  => 'phpbb_bb_factions',
			'bb_classes_table'   => 'phpbb_bb_classes',
			'bb_races_table'     => 'phpbb_bb_races',
			'bb_language_table'  => 'phpbb_bb_language',
		));

		$gid = $ref->getProperty('game_id');
		$gid->setAccessible(true);
		$gid->setValue($this->installer, 'ffxi');
	}

	/**
	 * Invoke a protected method on the installer.
	 */
	private function invoke_protected(string $method_name): void
	{
		$this->inserted = array();
		$method = new \ReflectionMethod(ffxi_installer::class, $method_name);
		$method->setAccessible(true);
		$method->invoke($this->installer);
	}

	// ── Factions ───────────────────────────────────────────

	public function test_install_factions_count(): void
	{
		$this->invoke_protected('install_factions');
		$this->assertCount(1, $this->inserted);
		$this->assertCount(4, $this->inserted[0]['data']);
	}

	public function test_install_factions_ids(): void
	{
		$this->invoke_protected('install_factions');
		$factions = $this->inserted[0]['data'];
		$ids = array_column($factions, 'faction_id');
		$this->assertContains(1, $ids, 'Bastok faction_id=1');
		$this->assertContains(2, $ids, "San d'Oria faction_id=2");
		$this->assertContains(3, $ids, 'Windurst faction_id=3');
		$this->assertContains(4, $ids, 'Jeuno faction_id=4');
	}

	public function test_install_factions_names(): void
	{
		$this->invoke_protected('install_factions');
		$factions = $this->inserted[0]['data'];
		$names = array_column($factions, 'faction_name');
		$this->assertContains('Bastok', $names);
		$this->assertContains('San d\'Oria', $names);
		$this->assertContains('Windurst', $names);
		$this->assertContains('Jeuno', $names);
	}

	public function test_install_factions_game_id(): void
	{
		$this->invoke_protected('install_factions');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertSame('ffxi', $row['game_id']);
		}
	}

	// ── Classes ────────────────────────────────────────────

	public function test_install_classes_count(): void
	{
		$this->invoke_protected('install_classes');
		// First insert: class rows, second insert: language rows
		$this->assertCount(2, $this->inserted);
		$this->assertCount(23, $this->inserted[0]['data']);
	}

	public function test_install_classes_valid_armor_types(): void
	{
		$this->invoke_protected('install_classes');
		$valid = array('CLOTH', 'LEATHER', 'MAIL', 'PLATE');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertContains($row['class_armor_type'], $valid, "class_id {$row['class_id']} has valid armor type");
		}
	}

	public function test_install_classes_language_coverage(): void
	{
		$this->invoke_protected('install_classes');
		$lang_rows = $this->inserted[1]['data'];
		$languages = array_unique(array_column($lang_rows, 'language'));
		sort($languages);
		// This plugin seeds three languages (en, fr, de) — unlike bbguildwow's
		// four (en, fr, de, it). Confirmed by reading ffxi_installer.php's
		// install_classes()/install_races() before writing this assertion,
		// not assumed from the wow reference test.
		$this->assertSame(array('de', 'en', 'fr'), $languages);
	}

	public function test_install_classes_language_entries_per_lang(): void
	{
		$this->invoke_protected('install_classes');
		$lang_rows = $this->inserted[1]['data'];
		$per_lang = array_count_values(array_column($lang_rows, 'language'));
		// 23 classes x 3 languages = 69 total
		foreach ($per_lang as $lang => $count)
		{
			$this->assertSame(23, $count, "$lang has 23 class name entries");
		}
	}

	// ── Races ──────────────────────────────────────────────

	public function test_install_races_count(): void
	{
		$this->invoke_protected('install_races');
		// First insert: race rows, second insert: language rows
		$this->assertCount(2, $this->inserted);
		$this->assertCount(6, $this->inserted[0]['data']);
	}

	public function test_install_races_valid_factions(): void
	{
		$this->invoke_protected('install_races');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertContains($row['race_faction_id'], array(0, 1, 2, 3, 4), "race_id {$row['race_id']} has valid faction");
		}
	}

	public function test_install_races_language_coverage(): void
	{
		$this->invoke_protected('install_races');
		$lang_rows = $this->inserted[1]['data'];
		$languages = array_unique(array_column($lang_rows, 'language'));
		sort($languages);
		$this->assertSame(array('de', 'en', 'fr'), $languages);
	}

	public function test_install_races_language_entries_per_lang(): void
	{
		$this->invoke_protected('install_races');
		$lang_rows = $this->inserted[1]['data'];
		$per_lang = array_count_values(array_column($lang_rows, 'language'));
		// 6 races x 3 languages = 18 total
		foreach ($per_lang as $lang => $count)
		{
			$this->assertSame(6, $count, "$lang has 6 race name entries");
		}
	}

	// ── has_api_support() ──────────────────────────────────

	public function test_has_api_support_is_false(): void
	{
		// ffxi_installer does not override has_api_support(); the
		// abstract_game_install default (false) must hold, matching
		// ffxi_provider::has_api() === false.
		$method = new \ReflectionMethod(ffxi_installer::class, 'has_api_support');
		$method->setAccessible(true);
		$this->assertFalse($method->invoke($this->installer));
	}

	// ── install_roles() / install_specs() are NOT overridden ──

	public function test_does_not_override_install_roles(): void
	{
		// Confirms this plugin relies on abstract_game_install's shared
		// default role seeding rather than declaring its own — asserting
		// this pins the "no custom roles" assumption so a future edit that
		// silently adds an override doesn't go unnoticed.
		$method = new \ReflectionMethod(ffxi_installer::class, 'install_roles');
		$this->assertSame(
			\avathar\bbguild\model\games\abstract_game_install::class,
			$method->getDeclaringClass()->getName()
		);
	}

	// ── Specializations (install_specs) ─────────────────────
	//
	// FFXI genuinely has no additional named subclass/spec layer
	// beneath its 22 jobs (see ffxi_provider::spec_catalog()'s
	// docblock — Support Job, Merit Points, Job Points and Master
	// Levels are all numeric progression systems, not named specs).
	// ffxi_installer now overrides install_specs() (mirroring
	// gw2_installer's guard structure) but the catalog it reads from
	// is intentionally empty, so no rows are ever inserted.

	private function set_table_name(string $key, ?string $value): void
	{
		$ref = new \ReflectionClass($this->installer);
		$tn = $ref->getProperty('table_names');
		$tn->setAccessible(true);
		$current = $tn->getValue($this->installer);

		if ($value === null)
		{
			unset($current[$key]);
		}
		else
		{
			$current[$key] = $value;
		}

		$tn->setValue($this->installer, $current);
	}

	public function test_install_specs_is_overridden(): void
	{
		// Pins that this plugin has explicitly opted into the specs hook
		// (rather than silently relying on the abstract no-op), even
		// though its own catalog is empty.
		$method = new \ReflectionMethod(ffxi_installer::class, 'install_specs');
		$this->assertSame(
			ffxi_installer::class,
			$method->getDeclaringClass()->getName()
		);
	}

	public function test_install_specs_inserts_nothing_when_table_wired(): void
	{
		$this->set_table_name('bb_specializations_table', 'phpbb_bb_specializations');

		$this->invoke_protected('install_specs');

		$this->assertCount(0, $this->inserted, 'FFXI has no spec layer beneath its 22 jobs, so the catalog is empty and no rows are inserted even when the table is wired in');
	}

	public function test_install_specs_skips_when_table_not_wired(): void
	{
		$this->set_table_name('bb_specializations_table', null);

		$this->invoke_protected('install_specs');

		$this->assertCount(0, $this->inserted, 'install_specs() must no-op when bb_specializations_table is not in table_names');
	}

	public function test_spec_catalog_is_empty(): void
	{
		$this->assertSame(array(), \avathar\bbguildffxi\game\ffxi_provider::spec_catalog(), 'FFXI has no named specialization/subclass layer beneath its 22 jobs');
	}

	public function test_provider_implements_specialization_interface_with_empty_label_default(): void
	{
		$installer = $this->getMockBuilder(ffxi_installer::class)
			->disableOriginalConstructor()
			->getMock();
		$ext_manager = $this->getMockBuilder(\phpbb\extension\manager::class)
			->disableOriginalConstructor()
			->getMock();
		$provider = new \avathar\bbguildffxi\game\ffxi_provider($installer, $ext_manager);

		$this->assertInstanceOf(\avathar\bbguild\model\games\specialization_provider_interface::class, $provider);
		$this->assertSame(array(), $provider->get_specializations());
		$this->assertSame('Specialization', $provider->get_spec_label());
	}
}
