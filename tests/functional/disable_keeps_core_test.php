<?php
/**
 * bbGuild FFXI Extension — disable keeps core test
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Adapted from functional-tests.md test #8 — per that doc's "Notes for
 * other plugins" section, the single most important guardrail for a
 * non-flagship plugin: disabling it must not cascade-break bbguild core
 * or any other guild's page.
 *
 * Uses bbguild core's own built-in id=1 "Test Guild" (game_id='custom',
 * seeded unconditionally by core's migrations/v200b3 basics data) as the
 * control guild, so this test has no dependency on any other game plugin
 * being installed.
 *
 * @group functional
 */
class avathar_bbguildffxi_disable_keeps_core_test extends phpbb_functional_test_case
{
	/** bbguild core's own built-in demo guild (game_id='custom') */
	const CONTROL_GUILD_ID = 1;

	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	public function test_disabling_ffxi_does_not_break_core_or_other_guilds()
	{
		// Baseline: control guild renders before touching bbguildffxi at all.
		self::request('GET', 'app.php/guild/' . self::CONTROL_GUILD_ID);
		self::assert_response_status_code(200);

		$this->disable_ext('avathar/bbguildffxi');

		try
		{
			self::request('GET', 'app.php/guild/' . self::CONTROL_GUILD_ID);
			self::assert_response_status_code(200);

			$this->login();
			$this->admin_login();
			self::request('GET', 'adm/index.php?i=-avathar-bbguild-acp-game_module&mode=listgames&sid=' . $this->sid);
			self::assert_response_status_code(200);
			$this->logout();
		}
		finally
		{
			// Leave the board in the state setup_extensions() expects for
			// any other test class sharing this run (phpbb_functional_test_case
			// does not reset DB/extension state between test classes).
			$this->install_ext('avathar/bbguildffxi');
		}
	}
}
