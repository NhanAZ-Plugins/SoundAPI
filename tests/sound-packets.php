<?php

declare(strict_types=1);

use NhanAZ\SoundAPI\Main;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\StopSoundPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;

$source = $argv[1] ?? null;
if ($source === null || !is_file($source . '/vendor/autoload.php')) {
	fwrite(STDERR, "Usage: php tests/sound-packets.php /path/to/Axolotl-PM-source\n");
	exit(2);
}
require $source . '/vendor/autoload.php';
require $argv[2] ?? dirname(__DIR__) . '/src/Main.php';

final class SoundAPITestSession extends NetworkSession {
	/** @var list<ClientboundPacket> */
	public array $packets = [];

	public function sendDataPacket(ClientboundPacket $packet, bool $immediate = false): bool {
		$this->packets[] = $packet;
		return true;
	}
}

final class SoundAPITestPlayer extends Player {
	public SoundAPITestSession $testSession;

	public function getPosition(): Position {
		return new Position(1.25, 2.5, -3.75, null);
	}

	public function getNetworkSession(): NetworkSession {
		return $this->testSession;
	}

	public function __destruct() {}
}

$session = (new ReflectionClass(SoundAPITestSession::class))->newInstanceWithoutConstructor();
$player = (new ReflectionClass(SoundAPITestPlayer::class))->newInstanceWithoutConstructor();
$player->testSession = $session;
$plugin = (new ReflectionClass(Main::class))->newInstanceWithoutConstructor();

$plugin->playSound('ambient.weather.lightning.impact', $player);
$plugin->playSoundCustom('block.bell.hit', 4.5, 5.5, 6.5, 0.4, 1.2, $player);
$plugin->stopSound('ambient.cave', true, $player);

if (count($session->packets) !== 3
	|| !($session->packets[0] instanceof PlaySoundPacket)
	|| $session->packets[0]->soundName !== 'ambient.weather.lightning.impact'
	|| $session->packets[0]->x !== 1.25 || $session->packets[0]->y !== 2.5 || $session->packets[0]->z !== -3.75
	|| $session->packets[0]->volume !== 1.0 || $session->packets[0]->pitch !== 1.0
	|| !($session->packets[1] instanceof PlaySoundPacket)
	|| $session->packets[1]->soundName !== 'block.bell.hit'
	|| $session->packets[1]->x !== 4.5 || $session->packets[1]->y !== 5.5 || $session->packets[1]->z !== 6.5
	|| $session->packets[1]->volume !== 0.4 || $session->packets[1]->pitch !== 1.2
	|| !($session->packets[2] instanceof StopSoundPacket)
	|| $session->packets[2]->soundName !== 'ambient.cave' || !$session->packets[2]->stopAll) {
	throw new RuntimeException('SoundAPI packet values changed');
}

echo "PASS: SoundAPI play and stop packet fields\n";
