<?php

namespace SlotMachine;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use SlotMachine\command\SlotCommand;
use SlotMachine\listener\SlotWandListener;
use SlotMachine\listener\SlotInteractListener;
use SlotMachine\slot\SlotManager;

class Main extends PluginBase {
    private static Main $instance;
    private Config $config;
    private SlotManager $slotManager;

    public function onEnable(): void {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();
        $this->slotManager = new SlotManager($this);

        $this->getServer()->getCommandMap()->register("slot", new SlotCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new SlotWandListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SlotInteractListener($this), $this);
    }

    public static function getInstance(): Main {
        return self::$instance;
    }

    public function getLang(string $key, array $params = []): string {
        $msg = $this->config->get("messages")[$key] ?? $key;
        foreach ($params as $k => $v) $msg = str_replace("{${k}}", $v, $msg);
        return $msg;
    }

    public function getSlotManager(): SlotManager {
        return $this->slotManager;
    }

    public function getSlotConfig(): Config {
        return $this->config;
    }
}