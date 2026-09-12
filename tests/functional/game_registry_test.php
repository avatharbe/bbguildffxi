<?php
/**
 * bbGuild FFXI Extension — game registry test
 *
 * @package   bbguildffxi v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Adapted from functional-tests.md test #2. No in-process DI container is
 * reachable from inside phpbb_functional_test_case (see
 * tests/integration-tests.md's "no DI container is reachable in-process"
 * note in bbguildwow), so this can't call
 * avathar\bbguild\model\games\game_registry::get('ffxi') directly. Instead
 * it drives the one page in bbguild core whose own controller does that
 * lookup for us and renders the result: the ACP "Edit game" page
 * (controller/admin_games.php::showgame()) assigns HAS_API from
 * `$this->game_registry->get($game_id)->has_api()` and
 * adm/style/acp_editgames.html only renders the "enable_armory" checkbox
 * when HAS_API is true.
 *
 * Asserting the page loads with the FFXI game name and without that
 * checkbox proves both that this plugin's `bbguild.game_provider`-tagged
 * service is registered and resolvable via the core registry, and that
 * has_api() reports false — the two things the doc's suggested test
 * wants, unlike wow's has_api() === true.
 *
 * Catches: `bbguild.game_provider` tag missing in services.yml, broken
 * provider class.
 *
 * @group functional
 */
class avathar_bbguildffxi_game_registry_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildffxi');
	}

	public function test_ffxi_provider_is_registered_with_no_api()
	{
		$this->login();
		$this->admin_login();

		self::request('GET', 'adm/index.php?i=-avathar-bbguild-acp-game_module&mode=editgames&game_id=ffxi&sid=' . $this->sid);
		self::assert_response_status_code(200);

		$html = self::$client->getResponse()->getContent();
		$this->assertStringContainsString('Final Fantasy XI', $html, 'edit-game page should show the FFXI game name resolved via the game registry');
		$this->assertStringNotContainsString('id="enable_armory"', $html, 'armory/API checkbox must not render for a provider whose has_api() is false');

		$this->logout();
	}
}
