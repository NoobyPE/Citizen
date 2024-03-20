<?php
declare(strict_types=1);

namespace nooby\citizen;

use nooby\citizen\controller\Controller;
use nooby\citizen\controller\DefaultController;
use nooby\citizen\factory\CitizenFactory;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class CitizenLibrary
{
    use SingletonTrait;

    private PluginBase $plugin;

    private $factory;

    static function create(PluginBase $plugin): self
    {
        return new self($plugin, new DefaultController());
    }

    function __construct(PluginBase $plugin, Controller $controller)
    {
        self::setInstance($this);
        $this->plugin = $plugin;
        $this->factory = new CitizenFactory();
        $plugin->getServer()->getPluginManager()->registerEvents($controller, $plugin);
    }

    function getPlugin(): PluginBase
    {
        return $this->plugin;
    }

    function getFactory()
    {
        return $this->factory;
    }
}