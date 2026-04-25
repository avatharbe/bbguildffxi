<?php
/**
 * bbGuild FFXI Extension
 *
 * @package   bbguild_ffxi v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguild_ffxi;

use phpbb\extension\base;

class ext extends base
{
	const MIN_PHP_VERSION = '8.1.0';
	const MIN_PHPBB_VERSION = '3.3.0';

	public function is_enableable()
	{
		$errors = [];

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$errors[] = 'This extension requires PHP ' . self::MIN_PHP_VERSION . ' or higher. You are running PHP ' . PHP_VERSION . '.';
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '<'))
		{
			$errors[] = 'This extension requires phpBB ' . self::MIN_PHPBB_VERSION . ' or higher. You are running phpBB ' . PHPBB_VERSION . '.';
		}

		$ext_manager = $this->container->get('ext.manager');
		if (!$ext_manager->is_enabled('avathar/bbguild'))
		{
			$errors[] = 'This extension requires the bbGuild core extension (avathar/bbguild) to be enabled first.';
		}

		return empty($errors) ? true : $errors;
	}
}
