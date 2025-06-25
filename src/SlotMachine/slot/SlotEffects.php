<?php

namespace SlotMachine\slot;

use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\sound\XpCollectSound;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\sound\LevelUpSound;
use pocketmine\world\sound\NotePlingSound;
use pocketmine\world\sound\BassSound;
use pocketmine\world\sound\GhastShootSound;

use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\CriticalParticle;

use pocketmine\world\World;

class SlotEffects {

    public static function playSpin(World $world, Vector3 $pos): void {
        $world->addSound($pos, new NotePlingSound());
        $world->addParticle($pos, new CriticalParticle());
    }

    public static function playWin(World $world, Vector3 $pos): void {
        $world->addSound($pos, new LevelUpSound());
        $world->addParticle($pos, new HappyVillagerParticle());
    }

    public static function playLose(World $world, Vector3 $pos): void {
        $world->addSound($pos, new BassSound());
        $world->addParticle($pos, new SmokeParticle());
    }
}
