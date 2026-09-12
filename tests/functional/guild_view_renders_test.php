<?php
/**
 * bbGuild FFXI Extension — guild view rendering test
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Adapted from functional-tests.md test #3. Inserts a guild fixture with
 * game_id='ffxi' plus one roster module registration and one player
 * (class_id=1 'Warrior'/PLATE, race_id=3 'Hume'), then GETs
 * /guild/{guild_id} (view_controller::handleview(), the primary route —
 * see bbguild/CLAUDE.md) as an authenticated user.
 *
 * bbguild core seeds portal modules (bb_portal_modules) only for its own
 * built-in demo guild id=1 (migrations/v200b3), so any other guild needs
 * its own module row inserted to have anything render in a column — this
 * mirrors exactly what that migration's seed_portal_layout() does for the
 * roster module (module_column=2 "center", module_order=1).
 *
 * Asserts:
 * - Response is 200
 * - The roster module rendered the player row (player name present)
 * - The class image src resolves under ext/avathar/bbguildffxi/images/
 *   (portal/modules/roster.php::get_game_images_path() only resolves
 *   there when the game_registry has a provider registered for 'ffxi' —
 *   otherwise it falls back to ext/avathar/bbguild/images/)
 *
 * Catches: guild_context wiring, image path resolution, portal module
 * registration/rendering regressions.
 *
 * @group functional
 */
class avathar_bbguildffxi_guild_view_renders_test extends phpbb_functional_test_case
{
	/** guild_id used by this fixture — distinct from core's own id=0/1 demo rows */
	const GUILD_ID = 90101;

	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->seed_fixture();
	}

	private function seed_fixture(): void
	{
		$db = $this->get_db();
		$prefix = $this->get_table_prefix();
		$guild_id = self::GUILD_ID;

		// Idempotent: clean up any partial leftover from a previous run.
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_players WHERE player_guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_ranks WHERE guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_portal_modules WHERE guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_guild WHERE id = ' . $guild_id);

		$db->sql_query('INSERT INTO ' . $prefix . 'bb_guild ' . $db->sql_build_array('INSERT', array(
			'id'             => $guild_id,
			'name'           => 'FFXI Test Guild',
			'realm'          => 'Test Realm',
			'region'         => 'us',
			'roster'         => 1,
			'players'        => 1,
			'emblemurl'      => '',
			'game_id'        => 'ffxi',
			'game_edition'   => 'retail',
			'min_armory'     => 0,
			'rec_status'     => 0,
			'guilddefault'   => 0,
			'armory_enabled' => 0,
			'armoryresult'   => '',
			'recruitforum'   => 0,
			'faction'        => 0,
		)));

		$db->sql_query('INSERT INTO ' . $prefix . 'bb_ranks ' . $db->sql_build_array('INSERT', array(
			'guild_id'    => $guild_id,
			'rank_id'     => 0,
			'rank_name'   => 'Member',
			'rank_hide'   => 0,
			'rank_prefix' => '',
			'rank_suffix' => '',
		)));

		// Mirrors bbguild core's own seed_portal_layout() (migrations/v200b3)
		// for the roster module, just against this test's own guild_id.
		$db->sql_query('INSERT INTO ' . $prefix . 'bb_portal_modules ' . $db->sql_build_array('INSERT', array(
			'guild_id'            => $guild_id,
			'module_classname'    => '\avathar\bbguild\portal\modules\roster',
			'module_column'       => 2,
			'module_order'        => 1,
			'module_name'         => 'BBGUILD_PORTAL_ROSTER',
			'module_image_src'    => '',
			'module_icon'         => '',
			'module_icon_size'    => 16,
			'module_image_width'  => 16,
			'module_image_height' => 16,
			'module_group_ids'    => '',
			'module_status'       => 1,
		)));

		$db->sql_query('INSERT INTO ' . $prefix . 'bb_players ' . $db->sql_build_array('INSERT', array(
			'game_id'         => 'ffxi',
			'player_name'     => 'Testchar',
			'player_region'   => 'us',
			'player_realm'    => 'Test Realm',
			'player_title'    => '',
			'player_level'    => 75,
			'player_race_id'  => 3, // Hume
			'player_class_id' => 1, // Warrior (PLATE)
			'player_rank_id'  => 0,
			'player_guild_id' => $guild_id,
			'player_status'   => 1,
			'phpbb_user_id'   => 0,
		)));
	}

	protected function tearDown(): void
	{
		$db = $this->get_db();
		$prefix = $this->get_table_prefix();
		$guild_id = self::GUILD_ID;

		$db->sql_query('DELETE FROM ' . $prefix . 'bb_players WHERE player_guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_ranks WHERE guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_portal_modules WHERE guild_id = ' . $guild_id);
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_guild WHERE id = ' . $guild_id);

		parent::tearDown();
	}

	public function test_guild_page_renders_roster_with_class_image()
	{
		$this->create_user('bbguildffxi_viewer');
		$this->login('bbguildffxi_viewer');

		self::request('GET', 'app.php/guild/' . self::GUILD_ID);
		self::assert_response_status_code(200);

		$html = self::$client->getResponse()->getContent();

		$this->assertStringContainsString('Testchar', $html, 'roster module should render the seeded player row');
		$this->assertStringContainsString('ext/avathar/bbguildffxi/images/', $html, 'class image should resolve under this plugin\'s own images path');

		$this->logout();
	}
}
