<?php
namespace supermarcus\fastlevelswitch\task;

use pocketmine\level\format\mcregion\Chunk;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;
use pocketmine\utils\ChunkException;
use supermarcus\fastlevelswitch\FastLevelSwitch;

class McRegionChunkRequest extends AsyncTask {
    protected $levelId;
    protected $chunkX;
    protected $chunkZ;
    protected $compressionLevel;

    /** @var int[] */
    protected $biomeColors;
    protected $tiles;
    protected $chunkData;

    public function __construct(McRegion $level, $levelId, $chunkX, $chunkZ){
        $this->levelId = $levelId;
        $this->chunkX = $chunkX;
        $this->chunkZ = $chunkZ;
        $this->compressionLevel = Level::$COMPRESSION_LEVEL;

        $chunk = $level->getChunk($chunkX, $chunkZ, \false);
        if(!($chunk instanceof Chunk)){
            throw new ChunkException("Invalid Chunk sent");
        }

        $tiles = "";
        $nbt = new NBT(NBT::LITTLE_ENDIAN);
        foreach($chunk->getTiles() as $tile){
            if($tile instanceof Spawnable){
                $nbt->setData($tile->getSpawnCompound());
                $tiles .= $nbt->write();
            }
        }
        $this->tiles = $tiles;

        $this->chunkData = "";
        $this->chunkData .= $chunk->getBlockIdArray();
        $this->chunkData .= $chunk->getBlockDataArray();
        $this->chunkData .= $chunk->getBlockSkyLightArray();
        $this->chunkData .= $chunk->getBlockLightArray();
        $this->chunkData .= $chunk->getBiomeIdArray();

        $this->biomeColors = \pack("N*", ...$chunk->getBiomeColorArray());
    }

    public function onRun(){
        $ordered = \zlib_encode(
            \pack("V", $this->chunkX) . \pack("V", $this->chunkZ) .
            $this->chunkData .
            $this->biomeColors .
            $this->tiles
            , ZLIB_ENCODING_DEFLATE, $this->compressionLevel);
        $this->setResult($ordered);
    }

    public function onCompletion(Server $server){
        FastLevelSwitch::getInstance()->getChunkManager()->saveChunk($this->levelId, $this->chunkX, $this->chunkZ, $this->getResult());
    }
}