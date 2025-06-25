<?php

namespace SlotMachine\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\object\ArmorStand;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\world\Position;
use SlotMachine\Main;

class SlotWandListener implements Listener {

    private Main $plugin;
    private array $pendingSlots = [];

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onUseWand(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if (!$this->isSlotWand($item)) return;
        $event->cancel();

        $pos = $event->getBlock()?->getPosition();
        if (!$pos instanceof Position) return;

        $index = count($this->pendingSlots[$player->getName()] ?? []);
        if ($index >= 3) {
            $player->sendMessage("§cKamu sudah memilih 3 slot. Gunakan /slot set.");
            return;
        }

        // Tambah lokasi ke pending
        $this->pendingSlots[$player->getName()][] = $pos;

        // Spawn ArmorStand invisible + item dummy + floating text
        $this->spawnArmorStandWithText($pos, $index + 1);

        $player->sendMessage($this->plugin->getLang("set_slot", ["index" => $index + 1]));
    }

    private function isSlotWand(Item $item): bool {
        return $item->getNamedTag()->getTag("slot_wand") !== null;
    }

    private function spawnArmorStandWithText(Position $pos, int $index): void {
        $world = $pos->getWorld();
        $location = new Location($pos->getX() + 0.5, $pos->getY(), $pos->getZ() + 0.5, $world, 0, 0);

        $nbt = EntityDataHelper::createBaseNBT($location);
        $nbt->setByte("Invisible", 1);
        $nbt->setByte("ShowArms", 1);
        $nbt->setByte("NoBasePlate", 1);
        $nbt->setByte("Marker", 1);
        $nbt->setString("CustomName", "§e🎰 Slot #$index");
        $nbt->setByte("CustomNameVisible", 1);

        $stand = new ArmorStand($location, $nbt);
        $stand->spawnToAll();
    }

    public function getPendingSlots(string $playerName): array {
        return $this->pendingSlots[$playerName] ?? [];
    }

    public function clearPendingSlots(string $playerName): void {
        unset($this->pendingSlots[$playerName]);
    }
}
