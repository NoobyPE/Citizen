<?php
declare(strict_types=1);

namespace nooby\citizen\entity;

use nooby\citizen\CitizenLibrary;
use nooby\citizen\task\EmoteRepeatingTask;
use nooby\citizen\task\EmoteRepeatingTimerTask;
use nooby\citizen\attribute\TagEditor;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\LegacySkinAdapter;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\LongMetadataProperty;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\player\Player;

class Citizen extends Human
{
    private $tagEditor;

    static function create(Player $player): self
    {
        return new self($player->getLocation(), $player->getSkin());
    }

    function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
    {
        $this->tagEditor = new TagEditor($this);
        parent::__construct($location, $skin, $nbt);
    }

    function getTagEditor(): TagEditor
    {
        return $this->tagEditor;
    }

    function executeEmote(string $emoteId, bool $nonStop, int $seconds): void
    {
        if ($nonStop) {
            CitizenLibrary::getInstance()->getPlugin()->getScheduler()->scheduleRepeatingTask(new EmoteRepeatingTask($emoteId, $this, $seconds), 20);
        } else {
            CitizenLibrary::getInstance()->getPlugin()->getScheduler()->scheduleRepeatingTask(new EmoteRepeatingTimerTask($emoteId, $this, $seconds), 20);
        }
    }

    function spawnTo(Player $player): void
	{
		$skinAdapter = new LegacySkinAdapter();
		$packets[] = PlayerListPacket::add([PlayerListEntry::createAdditionEntry(parent::$uuid, parent::$id, "", $skinAdapter->toSkinData($this->skin))]);
		$flags =
			1 << EntityMetadataFlags::CAN_SHOW_NAMETAG |
			1 << EntityMetadataFlags::ALWAYS_SHOW_NAMETAG |
			1 << EntityMetadataFlags::IMMOBILE;
		$actorMetadata = [
			EntityMetadataProperties::FLAGS => new LongMetadataProperty($flags),
			EntityMetadataProperties::SCALE => new FloatMetadataProperty($this->scale)
		];
		$packets[] = AddPlayerPacket::create(
			parent::$uuid,
			"",
			parent::$id,
			"",
			parent::$location,
			null,
			parent::$location->pitch,
			parent::$location->yaw,
			parent::$location->yaw,
			ItemStackWrapper::legacy(ItemStack::null()),
			0,
			$actorMetadata,
			new PropertySyncData([], []),
			UpdateAbilitiesPacket::create(new AbilitiesData(CommandPermissions::NORMAL, PlayerPermissions::VISITOR, parent::$id, [
				new AbilitiesLayer(
					AbilitiesLayer::LAYER_BASE,
					array_fill(0, AbilitiesLayer::NUMBER_OF_ABILITIES, false),
					0.0,
					0.0
				)
			])),
			[],
			"",
			DeviceOS::UNKNOWN
		);

		$packets[] = PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]);
		foreach ($this->tagEditor->getLines() as $tag) {
			$tag->spawnTo($player);
		}

        $id = spl_object_id($player);
		if (!isset(parent::$hasSpawned[$id]) && $player->getWorld() === parent::getWorld()) {
			parent::$hasSpawned[$id] = $player;
		}
		foreach ($packets as $pk) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}

	function despairFrom(Player $player): void
	{
		$player->getNetworkSession()->sendDataPacket(RemoveActorPacket::create(parent::$id));
		foreach ($this->tagEditor->getLines() as $tag) {
			$tag->despairFrom($player);
		}
		unset(parent::$hasSpawned[spl_object_id($player)]);
	}
}