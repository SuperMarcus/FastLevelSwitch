<?php
namespace supermarcus\fastlevelswitch\chunk;

use pocketmine\level\Level;
use pocketmine\utils\Binary;
use supermarcus\fastlevelswitch\FastLevelSwitch;

class ChunkCashManager {
    /** @var string[] */
    private $cashes;

    /** @var FastLevelSwitch */
    private $fastLevelSwitch;

    public function __construct(FastLevelSwitch $fastLevelSwitch){
        $this->cashes = [];
        $this->fastLevelSwitch = $fastLevelSwitch;
    }

    /**
     * @param $level
     * @param $x
     * @param $z
     * @param $payload
     */
    public function saveChunk($level, $x, $z, $payload){
        $this->cashes[Binary::writeInt($level).Level::chunkHash($x, $z)] = $payload;
    }

    /**
     * @param $level
     * @param $x
     * @param $z
     */
    public function removeChunk($level, $x, $z){
        if($level instanceof Level){
            $level = $level->getId();
        }
        if($this->isChunkCashed($level, $x, $z)){
            unset($this->cashes[Binary::writeInt($level).Level::chunkHash($x, $z)]);
        }
    }

    /**
     * @param $levelID
     * @param $x
     * @param $z
     * @return bool
     */
    public function isChunkCashed($levelID, $x, $z){
        return isset($this->cashes[Binary::writeInt($levelID).Level::chunkHash($x, $z)]);
    }

    /**
     * @return array
     */
    public function getCashedChunks(){
        $result = [];
        $x = null;
        $z = null;
        foreach($this->cashes as $k => $cash){
            Level::getXZ(substr($k, 5), $x, $z);
            $result[] = [Binary::readInt(substr($k, 0, 4)), $x, $z];
        }
        return $result;
    }

    /**
     * @param $level
     * @param $x
     * @param $z
     * @return null|string
     */
    public function getChunk($level, $x, $z){
        if($level instanceof Level){
            $level = $level->getId();
        }
        if($this->isChunkCashed($level, $x, $z)){
            return $this->cashes[Binary::writeInt($level).Level::chunkHash($x, $z)];
        }
        return null;
    }
}