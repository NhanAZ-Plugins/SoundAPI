# SoundAPI

SoundAPI lets another [Axolotl-PM](https://github.com/axolotl-pm/PocketMine-MP) plugin send or stop Bedrock sound packets for a player. It exposes three public methods on the `NhanAZ\SoundAPI\Main` plugin instance: `playSound`, `playSoundCustom` and `stopSound`.

## Requirements

- Axolotl-PM API 5.0.0. The [build workflow](.github/workflows/build.yml) records pinned server revisions and test results. Compatibility with every 5.x revision has not been verified.
- PHP 8.1 or newer, as required by Axolotl-PM 5.

## Installation

Download the PHAR from [GitHub Releases](https://github.com/NhanAZ-Plugins/SoundAPI/releases), place it in the server's `plugins` directory and restart the server. SoundAPI has no commands or configuration file.

## Use from another plugin

Declare SoundAPI as a required plugin in the consumer's `plugin.yml`:

```yaml
depend: [SoundAPI]
```

Resolve the plugin instance and send a sound in a join listener:

```php
use NhanAZ\SoundAPI\Main as SoundAPI;
use pocketmine\event\player\PlayerJoinEvent;

$soundApi = $this->getServer()->getPluginManager()->getPlugin("SoundAPI");
if (!$soundApi instanceof SoundAPI) {
    throw new \RuntimeException("SoundAPI is unavailable");
}

$soundApi->playSound("ambient.weather.lightning.impact", $event->getPlayer());
```

The example assumes `$event` is a `PlayerJoinEvent` inside an event listener. The other methods keep their existing argument order:

```php
$soundApi->playSoundCustom("block.bell.hit", 4.5, 5.5, 6.5, 0.4, 1.2, $player);
$soundApi->stopSound("ambient.cave", true, $player);
```

`playSound` uses the player's current position with volume and pitch set to `1.0`. `playSoundCustom` uses the supplied coordinates, volume and pitch. In `stopSound`, `true` requests that the client stop all sounds. The client decides whether a sound name exists and how it is rendered.

## Development and validation

The [build workflow](.github/workflows/build.yml) checks the packet fields emitted by all three methods against pinned Axolotl-PM source revisions. It then uses [DevTools](https://github.com/NhanAZ/DevTools) to run PHPStan at maximum level, build the PHAR and inspect the artifact. Exact tested revisions are recorded in the workflow and its [GitHub Actions runs](https://github.com/NhanAZ-Plugins/SoundAPI/actions).

The tests check outgoing packet fields. Audio playback on a Bedrock client has not been verified.

## Support

Join [NhanAZ Discord](https://discord.gg/j2X83ujT6c) for support.

## License

SoundAPI is distributed under the [GNU General Public License version 3](LICENSE).
