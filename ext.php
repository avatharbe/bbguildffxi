<?php
/**
 * bbGuild FFXI Extension
 *
 * @package   bbguildffxi v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguildffxi;

use phpbb\extension\base;

class ext extends base
{
	const MIN_PHP_VERSION = '8.1.0';
	const MIN_PHPBB_VERSION = '3.3.0';

	public function is_enableable()
	{
		$errors = [];

		$user = $this->container->get('user');
		$user->add_lang_ext('avathar/bbguildffxi', 'info_ext');

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$errors[] = $user->lang('BBGUILDFFXI_PHP_VERSION_FAIL', self::MIN_PHP_VERSION, PHP_VERSION);
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '<'))
		{
			$errors[] = $user->lang('BBGUILDFFXI_PHPBB_VERSION_FAIL', self::MIN_PHPBB_VERSION, PHPBB_VERSION);
		}

		$ext_manager = $this->container->get('ext.manager');
		if (!$ext_manager->is_enabled('avathar/bbguild'))
		{
			$errors[] = $user->lang('BBGUILDFFXI_REQUIRES_BBGUILD');
		}

		return empty($errors) ? true : $errors;
	}
}
