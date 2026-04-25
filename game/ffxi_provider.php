<?php
/**
 * FFXI Game Provider
 *
 * @package   bbguild_ffxi v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguild_ffxi\game;

use avathar\bbguild\model\games\game_provider_interface;
use avathar\bbguild\model\games\game_install_interface;
use avathar\bbguild\model\games\game_api_interface;

class ffxi_provider implements game_provider_interface
{
	/** @var ffxi_installer */
	private $installer;

	/** @var \phpbb\extension\manager */
	private $ext_manager;

	public function __construct(ffxi_installer $installer, \phpbb\extension\manager $ext_manager)
	{
		$this->installer = $installer;
		$this->ext_manager = $ext_manager;
	}

	public function get_game_id(): string
	{
		return 'ffxi';
	}

	public function get_game_name(): string
	{
		return 'Final Fantasy XI';
	}

	public function get_installer(): game_install_interface
	{
		return $this->installer;
	}

	public function get_boss_base_url(): string
	{
		return 'http://ffxi.allakhazam.com/db/bestiary.html?fmob=%s';
	}

	public function get_zone_base_url(): string
	{
		return 'http://ffxi.allakhazam.com/db/areas.html?farea=%s';
	}

	public function get_images_path(): string
	{
		return $this->ext_manager->get_extension_path('avathar/bbguild_ffxi', true) . 'images/';
	}

	public function has_api(): bool
	{
		return false;
	}

	public function get_api(): ?game_api_interface
	{
		return null;
	}

	public function get_regions(): array
	{
		return array(
			'us' => 'US',
			'eu' => 'EU',
			'jp' => 'JP',
		);
	}

	public function get_api_locales(): array
	{
		return array();
	}

	public function get_armor_types(): array
	{
		return array(
			'CLOTH'   => 'Cloth',
			'LEATHER' => 'Leather',
			'MAIL'    => 'Mail',
			'PLATE'   => 'Plate',
		);
	}
}
