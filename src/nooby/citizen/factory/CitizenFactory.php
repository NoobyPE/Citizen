<?php

namespace nooby\citizen\factory;

use nooby\citizen\entity\Citizen;

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