<?php

namespace SlotMachine\listener;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\player\Player;
use SlotMachine\Main;
use jojoe77777\FormAPI\SimpleForm;
use SlotMachine\slot\SlotMachineTask;

class SlotInteractListener implements Listener {

    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $config = $this->plugin->getSlotConfig();

        // Cek apakah slot sudah diset
        $slots = $this->plugin->getSlotManager()->getSlotPositions();
        if (count($slots) !== 3) {
            $player->sendMessage($this->plugin->getLang("missing_slot"));
            return;
        }

        // Trigger hanya jika block tombol (opsional: bisa diatur block ID)
        if (!$block->getTypeId() === 77 && !$block->getTypeId() === 143) return; // stone & wooden button

        $event->cancel();

        // Tampilkan form taruhan
        $form = new SimpleForm(function (Player $player, ?int $data) use ($config, $slots): void {
            if ($data === null) {
                $player->sendMessage($this->plugin->getLang("bet_cancelled"));
                return;
            }

            $bet = $config->get("bet_options", [10, 50, 100])[$data] ?? 10;
            $economy = EconomyAPI::getInstance();

            if ($economy->myMoney($player) < $bet) {
                $player->sendMessage($this->plugin->getLang("not_enough_money"));
                return;
            }

            // Potong uang
            $economy->reduceMoney($player, $bet);
            $player->sendMessage($this->plugin->getLang("spinning"));

            // Jalankan spin
            $this->plugin->getScheduler()->scheduleRepeatingTask(new SlotMachineTask(
                $player,
                $slots,
                $bet,
                $this->plugin->getSlotConfig()->get("reward_multiplier", 3)
            ), 5);
        });

        $form->setTitle("🎰 Slot Machine");
        $form->setContent($this->plugin->getLang("choose_bet"));
        foreach ($config->get("bet_options", [10, 50, 100]) as $bet) {
            $form->addButton("§l💰 " . $bet);
        }
        $player->sendForm($form);
    }
}
