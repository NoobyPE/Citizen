<?php

namespace nooby\CitizenLibrary\factory;

use nooby\citizen\entity\Citizen;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class CitizenFactory 
{

  /**
    * @var Citizen[] $citizens
    */
	private array $citizens = [];

  /**
    * @param Citizen $citizen
    * @return void
    */
	public function add(Citizen $citizen): void 
	{
    EntityFactory::getInstance()->register(Citizen::class, function(World $world, CompoundTag $nbt): Citizen {
      return new Citizen(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
    }, ["Citizen"]);
		$this->citizens[$citizen->getId()] = $citizen;
	}

  /**
    * @param int $id
    * @return void
    */
	public function remove(int $id): void 
	{
    if (isset($this->citizens[$id])) {
      unset($this->citizens[$id]);
    }
	}

  /**
    * @param int $id
    * @return Citizen|null
    */
	public function get(int $id): ?Citizen
	{
		return $this->citizens[$id] ?? null;
	}

  /**
    * @return Citizen[]
    */
	public function getAll(): array 
	{
		return $this->citizens;
	}
	
}