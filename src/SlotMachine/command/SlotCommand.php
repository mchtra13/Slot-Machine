<?php

namespace SlotMachine\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use SlotMachine\Main;

class SlotCommand extends Command {

    private Main $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("slot", "Slot machine setup");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if (!$sender instanceof Player) return;
        if (!$sender->hasPermission("slotmachine.command")) return;

        switch ($args[0] ?? "") {
            case "givewand":
                $item = VanillaItems::STICK()->setCustomName("§r§dSlot Wand");
                $item->getNamedTag()->setByte("slot_wand", 1);
                $sender->getInventory()->addItem($item);
                $sender->sendMessage("§aSlot Wand diberikan.");
                break;

            case "set":
                $this->plugin->getSlotManager()->saveSlots($sender);
                break;

            default:
                $sender->sendMessage("§cGunakan: /slot givewand | /slot set");
        }
    }
}