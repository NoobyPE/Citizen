<?php
declare(strict_types=1);

namespace nooby\citizen\entity;

use nooby\citizen\CitizenLibrary;
use nooby\citizen\task\EmoteRepeatingTask;
use nooby\citizen\task\EmoteRepeatingTimerTask;
use nooby\CitizenLibrary\attributes\TagEditor;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\nbt\tag\CompoundTag;
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
}