<?php

namespace SlotMachine\slot;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\World;
use SlotMachine\Main;
use SlotMachine\listener\SlotWandListener;
use pocketmine\math\Vector3;

class SlotManager {

    private Main $plugin;
    private Config $config;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->config = new Config($plugin->getDataFolder() . "slots.yml", Config::YAML);
    }

    public function saveSlots(Player $player): void {
        /** @var SlotWandListener $listener */
        $listener = $this->plugin->getServer()->getPluginManager()->getRegisteredListeners($this->plugin)[SlotWandListener::class]->getListener();

        $slots = $listener->getPendingSlots($player->getName());
        if (count($slots) !== 3) {
            $player->sendMessage("§cKamu harus memilih 3 slot terlebih dahulu.");
            return;
        }

        $data = [];
        foreach ($slots as $pos) {
            $data[] = [
                "world" => $pos->getWorld()->getFolderName(),
                "x" => $pos->getX(),
                "y" => $pos->getY(),
                "z" => $pos->getZ(),
            ];
        }

        $this->config->set("slot_positions", $data);
        $this->config->save();
        $listener->clearPendingSlots($player->getName());

        $player->sendMessage($this->plugin->getLang("config_saved"));
    }

    /**
     * @return Vector3[]
     */
    public function getSlotPositions(): array {
        $result = [];

        $raw = $this->config->get("slot_positions", []);
        foreach ($raw as $entry) {
            if (!isset($entry["world"])) continue;
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($entry["world"]);
            if (!$world instanceof World) continue;
            $result[] = new Vector3((float)$entry["x"], (float)$entry["y"], (float)$entry["z"]);
        }

        return $result;
    }
}
