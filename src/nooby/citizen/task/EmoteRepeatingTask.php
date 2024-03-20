<?php

namespace nooby\citizen\task;

use nooby\citizen\entity\Citizen;

use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\EmotePacket;

class EmoteRepeatingTask extends Task
{

  private string $emoteId;
  
  private Citizen $citizen;
  
  private int $interval;
  
  private int $modifiableInterval;

  public function __construct(string $emoteId, Citizen $citizen, int $interval)
  {
    $this->emoteId = $emoteId;
    $this->citizen = $citizen;
    $this->interval = $interval;
    $this->modifiableInterval = $interval;
  }

  public function onRun(): void
  {
    $this->modifiableInterval--;
    if ($this->modifiableInterval <= 0) {
      $pk = EmotePacket::create($this->citizen->getId(), $this->emoteId, "", "", 0);
      foreach ($this->citizen->getViewers() as $viewer) {
        $viewer->getNetworkSession()->sendDataPacket($pk);
      }
    $this->modifiableInterval = $this->interval;
    }
  }
  
}