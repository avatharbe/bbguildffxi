# bbGuild - Final Fantasy XI

**Current version:** 2.1.0

[![Tests](https://github.com/avatharbe/bbguildffxi/actions/workflows/tests.yml/badge.svg)](https://github.com/avatharbe/bbguildffxi/actions/workflows/tests.yml)

**Documentation:** [avatharbe.github.io/bbguildffxi](https://avatharbe.github.io/bbguildffxi/)

Final Fantasy XI is notorious for making almost nothing soloable — endgame content demanded a coordinated linkshell, not just a list of names, and that coordination is exactly what a guild management tool should make easier. bbguildffxi covers all 22 jobs and 5 races, the four nations (Bastok, San d'Oria, Windurst, Jeuno), and US/EU/JP regions, with boss/zone links straight to FFXI Allakhazam. If your linkshell still runs on an old forum thread and a shared spreadsheet, this gives it an actual roster, recruitment board, and character claiming instead.

## Features

- **FFXI Jobs** - 22 jobs (Warrior, Monk, White Mage, Black Mage, Red Mage, Thief, Paladin, Dark Knight, Beastmaster, Bard, Ranger, Samurai, Ninja, Dragoon, Summoner, Blue Mage, Corsair, Puppetmaster, Dancer, Scholar, Geomancer, Rune Fencer)
- **FFXI Races** - 5 playable races (Hume, Elvaan, Tarutaru, Mithra, Galka)
- **Nations** - 4 nations: Bastok, San d'Oria, Windurst, Jeuno
- **Localization** - Job and race names in English, French, and German
- **Regions** - US, EU, and JP server regions
- **Allakhazam Links** - Boss and zone database URLs linked to FFXI Allakhazam

## Requirements

- phpBB >= 3.3.0
- PHP >= 8.1.0
- **bbGuild core** (`avathar/bbguild`) must be installed and enabled

## Installation

1. Ensure bbGuild core (`avathar/bbguild`) is installed and enabled.
2. Copy the `bbguildffxi` folder to `/ext/avathar/bbguildffxi/`.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Look for `bbGuild - Final Fantasy XI` under Disabled Extensions and click `Enable`.
5. Go to ACP > bbGuild > Games and install the **Final Fantasy XI** game.

## Uninstall

1. Navigate in the ACP to `Customise -> Extension Management -> Extensions`.
2. Find `bbGuild - Final Fantasy XI` under Enabled Extensions and click `Disable`.
3. To permanently uninstall, click `Delete Data` and then delete the `/ext/avathar/bbguildffxi` folder.

**Note:** Disabling the extension does not delete existing guild or player data. Your roster and player records remain intact in bbGuild core.

## Game Data

### Nations

| ID | Nation |
|----|--------|
| 1 | Bastok |
| 2 | San d'Oria |
| 3 | Windurst |
| 4 | Jeuno |

### Jobs (22)

| ID | Job |
|----|-----|
| 0 | Unknown |
| 1 | Warrior |
| 2 | Monk |
| 3 | Thief |
| 4 | White Mage |
| 5 | Black Mage |
| 6 | Blue Mage |
| 7 | Red Mage |
| 8 | Paladin |
| 9 | Dark Knight |
| 10 | Dragoon |
| 11 | Ninja |
| 12 | Samurai |
| 13 | Summoner |
| 14 | Ranger |
| 15 | Dancer |
| 16 | Scholar |
| 17 | Corsair |
| 18 | Bard |
| 19 | Beastmaster |
| 20 | Puppetmaster |
| 21 | Geomancer |
| 22 | Rune Fencer |

### Races (5)

Unknown, Hume, Elvaan, Tarutaru, Mithra, Galka

## License

[GNU General Public License v2](http://opensource.org/licenses/gpl-2.0.php)

## Links

- [bbGuild Core](https://github.com/avatharbe/bbguild)
- [FFXI Allakhazam](http://ffxi.allakhazam.com/)
- [Issue Tracker](https://github.com/avatharbe/bbguildffxi/issues)
