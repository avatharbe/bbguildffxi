<?php
/**
 * bbGuild FFXI plugin - Data seeding migration
 *
 * Seeds Final Fantasy XI factions, classes, races, and roles
 * by calling the existing installer service.
 *
 * @package   avathar\bbguild_ffxi
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguild_ffxi\migrations\basics;

class data extends \phpbb\db\migration\container_aware_migration
{
	public static function depends_on()
	{
		return [
			'\avathar\bbguild\migrations\basics\schema',
		];
	}

	public function effectively_installed()
	{
		$games_table = $this->table_prefix . 'bb_games';

		if (!$this->db_tools->sql_table_exists($games_table))
		{
			return false;
		}

		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $games_table . " WHERE game_id = 'ffxi'";
		$result = $this->db->sql_query($sql);
		$count = (int) $this->db->sql_fetchfield('cnt');
		$this->db->sql_freeresult($result);

		return $count > 0;
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'seed_game_data']]],
		];
	}

	public function revert_data()
	{
		return [
			['custom', [[$this, 'remove_game_data']]],
		];
	}

	public function seed_game_data()
	{
		$installer = $this->get_installer();
		$installer->install($this->get_table_names(), 'ffxi', 'Final Fantasy XI', '', '', 'us');
	}

	public function remove_game_data()
	{
		$installer = $this->get_installer();
		$installer->uninstall($this->get_table_names(), 'ffxi', 'Final Fantasy XI');
	}

	private function get_table_names()
	{
		return [
			'bb_games_table'     => $this->table_prefix . 'bb_games',
			'bb_factions_table'  => $this->table_prefix . 'bb_factions',
			'bb_classes_table'   => $this->table_prefix . 'bb_classes',
			'bb_races_table'     => $this->table_prefix . 'bb_races',
			'bb_gameroles_table' => $this->table_prefix . 'bb_gameroles',
			'bb_language_table'  => $this->table_prefix . 'bb_language',
			'bb_players_table'   => $this->table_prefix . 'bb_players',
		];
	}

	private function get_installer()
	{
		return new \avathar\bbguild_ffxi\game\ffxi_installer(
			$this->container->get('dbal.conn'),
			$this->container->get('cache.driver'),
			$this->container->get('config'),
			$this->container->get('user')
		);
	}
}
