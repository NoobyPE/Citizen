<?php
declare(strict_types=1);

namespace nooby\citizen;

use nooby\citizen\controller\Controller;
use nooby\citizen\controller\DefaultController;
use nooby\citizen\factory\CitizenFactory;
use nooby\citizen\entity\Citizen;

use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class CitizenLibrary
{
    use SingletonTrait;

    private PluginBase $plugin;

    private CitizenFactory $factory;

    static function create(PluginBase $plugin): self
    {
        return new self($plugin, new DefaultController());
    }

    function __construct(PluginBase $plugin, Controller $controller)
    {
        self::setInstance($this);
        $this->plugin = $plugin;
        $this->factory = new CitizenFactory();
        EntityFactory::getInstance()->register(Citizen::class, function(World $world, CompoundTag $nbt): Citizen {
            return new Citizen(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
          }, ["Citizen"]);
        $plugin->getServer()->getPluginManager()->registerEvents($controller, $plugin);
    }

    function getPlugin(): PluginBase
    {
        return $this->plugin;
    }

    function getFactory(): CitizenFactory
    {
        return $this->factory;
    }
}