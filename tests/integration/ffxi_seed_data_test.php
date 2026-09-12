<?php
/**
 * bbGuild FFXI Extension — seed data structural correctness
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Per bbguildwow's tests/integration-tests.md "Notes for other plugins":
 * for a non-API plugin, the only relevant integration test is
 * fixture-loading correctness — load the seed data into a real DB (via
 * enabling the extension, which runs migrations/basics/data.php ->
 * ffxi_installer::install()) and assert structural correctness deeper
 * than the functional tests bother with. No HTTP mocks apply here; this
 * plugin has no external API.
 *
 * Extends \phpbb_functional_test_case, not \phpbb_database_test_case —
 * per the same doc's 2026-09 correction, that's what actually gives a
 * real DB connection and a real installed extension in this test
 * framework; no in-process DI container is reachable, so table names are
 * built from self::$config['table_prefix'] directly.
 *
 * @group integration
 */
class avathar_bbguildffxi_ffxi_seed_data_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	private function fetch_all(string $sql): array
	{
		$db = $this->get_db();
		$result = $db->sql_query($sql);
		$rows = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$db->sql_freeresult($result);

		return $rows;
	}

	public function test_every_class_has_a_valid_armor_type(): void
	{
		$valid = array('CLOTH', 'LEATHER', 'MAIL', 'PLATE');
		$rows = $this->fetch_all(
			'SELECT class_id, class_armor_type FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'ffxi'"
		);

		$this->assertNotEmpty($rows, 'expected seeded ffxi classes');
		foreach ($rows as $row)
		{
			$this->assertContains(
				$row['class_armor_type'], $valid,
				"class_id {$row['class_id']} has invalid armor type '{$row['class_armor_type']}'"
			);
		}
	}

	public function test_no_duplicate_class_id_per_game(): void
	{
		$rows = $this->fetch_all(
			'SELECT class_id FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'ffxi'"
		);
		$ids = array_column($rows, 'class_id');

		$this->assertSame(count($ids), count(array_unique($ids)), 'duplicate class_id found for game_id=ffxi');
	}

	public function test_every_race_references_a_valid_faction(): void
	{
		$factions = $this->fetch_all(
			'SELECT faction_id FROM ' . $this->get_table_prefix() . "bb_factions WHERE game_id = 'ffxi'"
		);
		$valid_faction_ids = array_column($factions, 'faction_id');
		$valid_faction_ids = array_map('intval', $valid_faction_ids);
		$valid_faction_ids[] = 0; // 0 == unassigned/no faction, always valid

		$races = $this->fetch_all(
			'SELECT race_id, race_faction_id FROM ' . $this->get_table_prefix() . "bb_races WHERE game_id = 'ffxi'"
		);

		$this->assertNotEmpty($races, 'expected seeded ffxi races');
		foreach ($races as $row)
		{
			$this->assertContains(
				(int) $row['race_faction_id'], $valid_faction_ids,
				"race_id {$row['race_id']} references non-existent faction_id {$row['race_faction_id']}"
			);
		}
	}

	public function test_no_duplicate_race_id_per_game(): void
	{
		$rows = $this->fetch_all(
			'SELECT race_id FROM ' . $this->get_table_prefix() . "bb_races WHERE game_id = 'ffxi'"
		);
		$ids = array_column($rows, 'race_id');

		$this->assertSame(count($ids), count(array_unique($ids)), 'duplicate race_id found for game_id=ffxi');
	}

	public function test_every_class_id_has_a_language_row_per_seeded_language(): void
	{
		$prefix = $this->get_table_prefix();

		$languages = $this->fetch_all(
			"SELECT DISTINCT language FROM {$prefix}bb_language WHERE game_id = 'ffxi' AND attribute = 'class'"
		);
		$languages = array_column($languages, 'language');
		$this->assertNotEmpty($languages, 'expected at least one seeded language for ffxi classes');

		$class_ids = array_column(
			$this->fetch_all("SELECT class_id FROM {$prefix}bb_classes WHERE game_id = 'ffxi'"),
			'class_id'
		);

		foreach ($languages as $language)
		{
			$lang_rows = $this->fetch_all(
				"SELECT attribute_id FROM {$prefix}bb_language
					WHERE game_id = 'ffxi' AND attribute = 'class' AND language = '" . $this->get_db()->sql_escape($language) . "'"
			);
			$lang_attribute_ids = array_map('intval', array_column($lang_rows, 'attribute_id'));

			foreach ($class_ids as $class_id)
			{
				$this->assertContains(
					(int) $class_id, $lang_attribute_ids,
					"class_id $class_id has no '$language' bb_language row"
				);
			}
		}
	}

	public function test_every_race_id_has_a_language_row_per_seeded_language(): void
	{
		$prefix = $this->get_table_prefix();

		$languages = $this->fetch_all(
			"SELECT DISTINCT language FROM {$prefix}bb_language WHERE game_id = 'ffxi' AND attribute = 'race'"
		);
		$languages = array_column($languages, 'language');
		$this->assertNotEmpty($languages, 'expected at least one seeded language for ffxi races');

		$race_ids = array_column(
			$this->fetch_all("SELECT race_id FROM {$prefix}bb_races WHERE game_id = 'ffxi'"),
			'race_id'
		);

		foreach ($languages as $language)
		{
			$lang_rows = $this->fetch_all(
				"SELECT attribute_id FROM {$prefix}bb_language
					WHERE game_id = 'ffxi' AND attribute = 'race' AND language = '" . $this->get_db()->sql_escape($language) . "'"
			);
			$lang_attribute_ids = array_map('intval', array_column($lang_rows, 'attribute_id'));

			foreach ($race_ids as $race_id)
			{
				$this->assertContains(
					(int) $race_id, $lang_attribute_ids,
					"race_id $race_id has no '$language' bb_language row"
				);
			}
		}
	}
}
