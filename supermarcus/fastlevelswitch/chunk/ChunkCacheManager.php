<?php
namespace supermarcus\fastlevelswitch\chunk;

use pocketmine\level\Level;
use pocketmine\utils\Binary;
use supermarcus\fastlevelswitch\FastLevelSwitch;

class ChunkCacheManager {
    /** @var string[] */
    private $caches;

    /** @var FastLevelSwitch */
    private $fastLevelSwitch;

    public function __construct(FastLevelSwitch $fastLevelSwitch){
        $this->caches = [];
        $this->fastLevelSwitch = $fastLevelSwitch;
    }

    /**
     * @param $level
     * @param $x
     * @param $z
     * @param $payload
     */
    public function saveChunk($level, $x, $z, $payload){
        $this->caches[Binary::writeInt($level).Level::chunkHash($x, $z)] = $payload;
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
        if($this->isChunkCached($level, $x, $z)){
            unset($this->caches[Binary::writeInt($level).Level::chunkHash($x, $z)]);
        }
    }

    /**
     * @param $levelID
     * @param $x
     * @param $z
     * @return bool
     */
    public function isChunkCached($levelID, $x, $z){
        return isset($this->caches[Binary::writeInt($levelID).Level::chunkHash($x, $z)]);
    }

    /**
     * @return array
     */
    public function getCachedChunks(){
        $result = [];
        $x = null;
        $z = null;
        foreach($this->caches as $k => $cache){
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
        if($this->isChunkCached($level, $x, $z)){
            return $this->caches[Binary::writeInt($level).Level::chunkHash($x, $z)];
        }
        return null;
    }
}