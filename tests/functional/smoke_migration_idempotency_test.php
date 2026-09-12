<?php
/**
 * bbGuild FFXI Extension — migration idempotency smoke test
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Disables bbguildffxi (data preserved) and re-enables it, then asserts
 * seeded rows were not duplicated. Disable does not revert schema/data,
 * so re-enabling re-runs every migration's effectively_installed() check
 * against data that's already there — this is the only way to exercise
 * that path without a second fresh install. Catches migrations that
 * mistakenly re-seed or re-create on a second run.
 *
 * Unlike bbguildwow, this plugin has no bb_specializations rows yet
 * (specs are a separate, unimplemented ticket — see bbguild#331 Phase 4
 * follow-up), so this checks bb_games/bb_classes/bb_races/bb_language
 * plus the migrations table instead of bb_specializations.
 *
 * @group smoke
 */
class avathar_bbguildffxi_smoke_migration_idempotency_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	private function count_rows(string $table, string $where): int
	{
		$db = $this->get_db();
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $table . ' WHERE ' . $where;
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('cnt');
		$db->sql_freeresult($result);

		return $count;
	}

	public function test_reenable_does_not_duplicate_seeded_data()
	{
		$before_games = $this->count_rows($this->get_table_prefix() . 'bb_games', "game_id = 'ffxi'");
		$before_classes = $this->count_rows($this->get_table_prefix() . 'bb_classes', "game_id = 'ffxi'");
		$before_races = $this->count_rows($this->get_table_prefix() . 'bb_races', "game_id = 'ffxi'");
		$before_language = $this->count_rows($this->get_table_prefix() . 'bb_language', "game_id = 'ffxi'");
		$before_migrations = $this->count_rows($this->get_table_prefix() . 'migrations', "migration_name LIKE '%bbguildffxi%'");

		$this->disable_ext('avathar/bbguildffxi');
		$this->install_ext('avathar/bbguildffxi');

		$after_games = $this->count_rows($this->get_table_prefix() . 'bb_games', "game_id = 'ffxi'");
		$after_classes = $this->count_rows($this->get_table_prefix() . 'bb_classes', "game_id = 'ffxi'");
		$after_races = $this->count_rows($this->get_table_prefix() . 'bb_races', "game_id = 'ffxi'");
		$after_language = $this->count_rows($this->get_table_prefix() . 'bb_language', "game_id = 'ffxi'");
		$after_migrations = $this->count_rows($this->get_table_prefix() . 'migrations', "migration_name LIKE '%bbguildffxi%'");

		$this->assertSame(1, $before_games, 'expected exactly one ffxi row in bb_games before re-enable');
		$this->assertSame($before_games, $after_games, 'bb_games ffxi row was duplicated on re-enable');
		$this->assertSame(23, $before_classes, 'expected 23 ffxi class rows before re-enable');
		$this->assertSame($before_classes, $after_classes, 'bb_classes ffxi rows were duplicated on re-enable');
		$this->assertSame(6, $before_races, 'expected 6 ffxi race rows before re-enable');
		$this->assertSame($before_races, $after_races, 'bb_races ffxi rows were duplicated on re-enable');
		$this->assertSame($before_language, $after_language, 'bb_language ffxi rows were duplicated on re-enable');
		$this->assertSame($before_migrations, $after_migrations, 'bbguildffxi migration rows changed on re-enable');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}
}
