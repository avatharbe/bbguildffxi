<?php
/**
 * bbGuild FFXI Extension — extension enable test
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Enables bbguild core, then bbguildffxi on top (via setup_extensions()).
 * Asserts the plugin's on-enable seeding actually ran: 'ffxi' row present
 * in bb_games, this plugin's classes seeded in bb_classes for
 * game_id='ffxi' (via migrations/basics/data.php calling
 * ffxi_installer::install()), and the plugin's own version constant
 * matches composer.json.
 *
 * Adapted from functional-tests.md test #1 ("Notes for other plugins"):
 * no ACP module assertion — this plugin registers none of its own.
 *
 * Catches: migration regressions, services.yml misconfig, missing tables.
 *
 * @group functional
 */
class avathar_bbguildffxi_extension_enable_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	public function test_ffxi_game_row_seeded()
	{
		$db = $this->get_db();
		$sql = 'SELECT game_id, game_name FROM ' . $this->get_table_prefix() . "bb_games WHERE game_id = 'ffxi'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->assertNotFalse($row, "no 'ffxi' row found in bb_games after enabling bbguildffxi");
		$this->assertSame('Final Fantasy XI', $row['game_name']);
	}

	public function test_ffxi_classes_seeded()
	{
		$db = $this->get_db();
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'ffxi'";
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('cnt');
		$db->sql_freeresult($result);

		$this->assertSame(23, $count, 'expected 23 seeded FFXI classes in bb_classes');
	}

	public function test_version_constant_matches_composer_json()
	{
		$composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

		$this->assertSame(
			$composer['version'],
			\avathar\bbguildffxi\ext::BBGUILDFFXI_VERSION,
			'ext::BBGUILDFFXI_VERSION must match composer.json "version"'
		);
	}
}
