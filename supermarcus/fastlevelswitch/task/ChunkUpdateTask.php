<?php
namespace supermarcus\fastlevelswitch\task;

use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use supermarcus\fastlevelswitch\FastLevelSwitch;

class ChunkUpdateTask extends Task {
    /** @var FastLevelSwitch */
    private $fastLevelSwitch;

    public function __construct(FastLevelSwitch $fastLevelSwitch){
        $this->fastLevelSwitch = $fastLevelSwitch;
    }

    public function onRun($currentTick){
        if(FastLevelSwitch::$update){
            foreach($this->fastLevelSwitch->getChunkManager()->getCashedChunks() as $chunk){
                $level = $chunk[0];
                if(($level = $this->fastLevelSwitch->getServer()->getLevel($level)) instanceof Level){
                    FastLevelSwitch::cashChunk($chunk[1], $chunk[2], $level);
                }else{
                    $this->fastLevelSwitch->getChunkManager()->removeChunk(...$chunk);
                }
            }
        }
    }
}