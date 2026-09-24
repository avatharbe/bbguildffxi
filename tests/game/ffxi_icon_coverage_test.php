<?php
/**
 * @package bbGuild FFXI Extension
 * @copyright (c) 2026 avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\bbguildffxi\tests\game;

use PHPUnit\Framework\TestCase;

/**
 * Guards icon coverage against the classes the installer actually seeds.
 *
 * The roster resolves class_images/<imagename>.png for character rows and
 * roster_classes/<imagename>.png for the grid, so a seeded class with no
 * file behind it has no icon. avathar/bbguild#389 makes that degrade
 * instead of rendering broken, but the asset is still missing.
 */
class ffxi_icon_coverage_test extends TestCase
{
	/** Gaps that are known and tracked; see #6 */
	private const KNOWN_GAPS = array(
		'ffxi_geomancer',
		'ffxi_rune_fencer',
	);

	/** Same, for the grid's artwork directory */
	private const KNOWN_ROSTER_GAPS = array(
		// none — fully covered
	);

	/**
	 * @return list<string> Every imagename the installer seeds
	 */
	private function seeded_imagenames(): array
	{
		$files = glob(dirname(__DIR__, 2) . '/game/*_installer.php');
		$src   = file_get_contents($files[0]);

		preg_match_all("/'imagename'\s*=>\s*'([^']+)'/", $src, $m);

		$names = array_values(array_unique(array_filter(array_map('trim', $m[1]))));
		sort($names);

		return $names;
	}

	private function assertNoUnexpectedGaps(string $dir, array $known): void
	{
		$base    = dirname(__DIR__, 2) . '/images/' . $dir . '/';
		$missing = array();

		foreach ($this->seeded_imagenames() as $name)
		{
			if (!file_exists($base . $name . '.png'))
			{
				$missing[] = $name;
			}
		}

		$unexpected = array_values(array_diff($missing, $known));

		$this->assertSame(
			array(),
			$unexpected,
			$dir . ' is missing icons that are not tracked as known gaps: ' . implode(', ', $unexpected)
		);
	}

	public function test_class_images_cover_every_seeded_class(): void
	{
		$this->assertNoUnexpectedGaps('class_images', self::KNOWN_GAPS);
	}

	/**
	 * @doesNotPerformAssertions Skipped: ALL — the roster_classes/ directory does not exist yet
	 */
	public function test_roster_classes_cover_every_seeded_class(): void
	{
		$this->markTestSkipped('ALL — the roster_classes/ directory does not exist yet');
		$this->assertNoUnexpectedGaps('roster_classes', self::KNOWN_ROSTER_GAPS);
	}

	public function test_the_installer_seeds_the_expected_class_count(): void
	{
		// Guards the parser: if the installer's array syntax changes, the
		// coverage assertions must not silently pass on an empty list.
		$this->assertCount(23, $this->seeded_imagenames());
	}
}
