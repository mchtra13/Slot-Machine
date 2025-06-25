<?php

namespace SlotMachine\slot;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\item\VanillaItems;
use onebone\economyapi\EconomyAPI;
use SlotMachine\Main;
use pocketmine\entity\object\ArmorStand;

class SlotMachineTask extends Task {

    private Player $player;
    private array $positions; // Vector3[]
    private int $tick = 0;
    private array $currentItems = [];
    private int $bet;
    private int $rewardMultiplier;

    private array $stopTick = [20, 30, 40]; // Delay slot 1, 2, 3

    private static array $slotItems = [
        "iron" => "Iron Ingot",
        "gold" => "Gold Ingot",
        "diamond" => "Diamond",
    ];

    public function __construct(Player $player, array $positions, int $bet, int $multiplier) {
        $this->player = $player;
        $this->positions = $positions;
        $this->bet = $bet;
        $this->rewardMultiplier = $multiplier;
    }

    public function onRun(): void {
        if (!$this->player->isOnline()) {
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        $world = $this->player->getWorld();
        $allStopped = true;

        for ($i = 0; $i < 3; $i++) {
            $pos = $this->positions[$i];
            $entity = $this->getArmorStandAt($world, $pos);
            if (!$entity instanceof ArmorStand) {
                $this->player->sendMessage(Main::getInstance()->getLang("armorstand_not_found", ["index" => $i + 1]));
                Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
                return;
            }

            // Jika belum mencapai waktu berhenti slot ke-i
            if ($this->tick <= $this->stopTick[$i]) {
                $itemKey = array_rand(self::$slotItems);
                $item = match ($itemKey) {
                    "iron" => VanillaItems::IRON_INGOT(),
                    "gold" => VanillaItems::GOLD_INGOT(),
                    "diamond" => VanillaItems::DIAMOND()
                };

                $entity->getInventory()->setItemInHand($item);
SlotEffects::playSpin($world, $pos);


                if ($this->tick === $this->stopTick[$i]) {
                    $this->currentItems[$i] = $itemKey;
                }

                $allStopped = false;
            }
        }

        $this->tick++;

        // Semua slot sudah berhenti → evaluasi hasil
        if ($allStopped) {
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());

            if (count(array_unique($this->currentItems)) === 1) {
                $win = $this->bet * $this->rewardMultiplier;
                EconomyAPI::getInstance()->addMoney($this->player, $win);
                $this->player->sendMessage(Main::getInstance()->getLang("win", ["money" => $win]));
                SlotEffects::playWin($world, $this->positions[1]);
            } else {
                $this->player->sendMessage(Main::getInstance()->getLang("lose"));
                SlotEffects::playLose($world, $this->positions[1]);
            }
        }
    }

    private function getArmorStandAt(World $world, Vector3 $pos): ?ArmorStand {
        foreach ($world->getNearbyEntities($pos->expand(0.5, 1, 0.5), 1, null) as $entity) {
            if ($entity instanceof ArmorStand) {
                $ePos = $entity->getPosition();
                if (round($ePos->x) === round($pos->x) && round($ePos->y) === round($pos->y) && round($ePos->z) === round($pos->z)) {
                    return $entity;
                }
            }
        }
        return null;
    }
}
