<?php

namespace nooby\CitizenLibrary\controller;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

abstract class Controller implements Listener
{
    abstract function handleDataPacketReceive(DataPacketReceiveEvent $event);

    abstract function handlePlayerJoin(PlayerJoinEvent $event);
  
    abstract function handlePlayerQuit(PlayerQuitEvent $event);

    abstract function handleEntityTeleport(EntityTeleportEvent $event);
}