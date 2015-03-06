<?php
namespace supermarcus\fastlevelswitch\task;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class DelayPlayerTeleportTask extends Task {
    /** @var Player */
    private $player;

    /** @var Position */
    private $target;

    public function __construct(Player $player, Position $target){
        $this->player = $player;
        $this->target = $target;
    }

    public function onRun($currentTicks){
        @$this->player->teleport($this->target);
    }
}