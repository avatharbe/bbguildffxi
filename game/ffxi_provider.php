<?php
/**
 * FFXI Game Provider
 *
 * @package   bbguildffxi v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguildffxi\game;

use avathar\bbguild\model\games\game_provider_interface;
use avathar\bbguild\model\games\specialization_provider_interface;

class ffxi_provider implements game_provider_interface, specialization_provider_interface
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

	public function get_installer(): \avathar\bbguild\model\games\game_install_interface
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
		return $this->ext_manager->get_extension_path('avathar/bbguildffxi', true) . 'images/';
	}

	public function has_api(): bool
	{
		return false;
	}

	public function get_api(): ?\avathar\bbguild\model\games\game_api_interface
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

	/**
	 * Specialization catalog (issue #331), keyed by class_id (see
	 * game/ffxi_installer.php's install_classes() for the id map).
	 *
	 * Deliberately empty for every class. Unlike WoW (talent
	 * specializations) or GW2 (Elite Specializations), FFXI has no
	 * additional named subclass/spec layer sitting "underneath" a job:
	 * each of the 22 jobs seeded by install_classes() (Warrior through
	 * Rune Fencer, including the advanced jobs like Puppetmaster,
	 * Geomancer and Rune Fencer) is already a complete, terminal class
	 * identity in the real game. The mechanics that exist beyond the
	 * base job — Support Job (any job slotted in as a half-level
	 * secondary job, not a spec of the main job), Merit Points, Job
	 * Points, and Master Levels — are all numeric progression/currency
	 * systems with no set of discrete named specializations comparable
	 * to a WoW talent spec or a GW2 elite spec. Confirmed via web
	 * research (bg-wiki.com's Job Points/Master Levels/Merit Points
	 * pages, Final Fantasy Wiki's Support Job page) before writing this
	 * method — not invented to satisfy the interface.
	 *
	 * Returning [] here (rather than fabricating subclasses) is the
	 * honest answer per issue #331: core's seeding logic simply inserts
	 * zero bb_specializations rows for this game, and
	 * ffxi_installer::install_specs() no-ops accordingly.
	 *
	 * @return array<int, list<array{spec_name:string,role_id:int,spec_icon:string,spec_order:int}>>
	 */
	public static function spec_catalog(): array
	{
		return array();
	}

	/**
	 * @inheritdoc
	 */
	public function get_spec_label(): string
	{
		return 'Specialization';
	}

	/**
	 * Interface implementation: delegates to the static catalog.
	 *
	 * @return array<int, list<array{spec_name:string,role_id:int,spec_icon:string,spec_order:int}>>
	 */
	public function get_specializations(): array
	{
		return self::spec_catalog();
	}
}
