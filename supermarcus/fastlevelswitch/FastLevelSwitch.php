<?php
namespace supermarcus\fastlevelswitch;

use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use supermarcus\fastlevelswitch\chunk\ChunkCashManager;
use supermarcus\fastlevelswitch\task\AnvilChunkRequest;
use supermarcus\fastlevelswitch\task\ChunkUpdateTask;
use supermarcus\fastlevelswitch\task\DelayPlayerTeleportTask;
use supermarcus\fastlevelswitch\task\McRegionChunkRequest;

/**
 * Class FastLevelSwitch
 *
 * This is a third PocketMine-MP library used to teleport player safely by pre-sending some chunks that will used
 * (A safe way to fix player in the stone by level switch)
 * You may use a delay task to teleport player after the chunks sent
 *
 * please only use the static method
 *
 * @package supermarcus\fastlevelswitch
 */
class FastLevelSwitch {
    /**
     * The default period of update chunks task
     */
    const UPDATE_PERIOD = 20 * 5;

    public static $instance = null;

    /**
     * @var bool
     *
     * true will cause more cpu usage
     * disable this if you are running a mini game server (Chunks will not often changes)
     * otherwise player keep this option true
     *
     * change the default value here, or use method 'setUpdateChunks' to edit when server is running
     */
    public static $update = true;

    /**
     * @return FastLevelSwitch
     */
    public static function getInstance(){
        if(!(FastLevelSwitch::$instance instanceof FastLevelSwitch)){
            FastLevelSwitch::$instance = new FastLevelSwitch(Server::getInstance());
        }
        return FastLevelSwitch::$instance;
    }

    public static function setUpdateChunks($value = true){
        FastLevelSwitch::$update = (bool) $value;
    }

    /**
     * Teleport a player with pre-load chunks
     * By default, you should only use this method
     *
     * @param Player $player
     * @param $position
     * @param int $delay
     */
    public static function teleport(Player $player, $position, $delay = 2 * 20){
        FastLevelSwitch::preLoadChunks($player, $position);
        FastLevelSwitch::getInstance()->getServer()->getScheduler()->scheduleDelayedTask(new DelayPlayerTeleportTask($player, $position), $delay);
    }

    /**
     * Pre-load chunks for a player
     *
     * @param Player $player
     * @param Position $pos
     */
    public static function preLoadChunks(Player $player, Position $pos = null){
        $level = ($pos->getLevel() instanceof Level) ? $pos->getLevel() : $player->getLevel();

        if($level instanceof Level){
            $middleX = $pos->getFloorX() >> 4;
            $middleZ = $pos->getFloorZ() >> 4;
            for($x = $middleX - 2; $x < $middleX + 2; ++$x){
                for($z = $middleZ - 2; $z < $middleZ + 2; ++$z){
                    if(FastLevelSwitch::getInstance()->getChunkManager()->isChunkCashed($level->getId(), $x, $z)){
                        $player->sendChunk($x, $z, FastLevelSwitch::getInstance()->getChunkManager()->getChunk($level, $x, $z));
                    }else{
                        FastLevelSwitch::cashChunk($x, $z, $level);
                    }
                }
            }
        }
    }

    public static function cashChunk($x, $z, Level $level){
        $provider = $level->getProvider();
        if($provider !== null){
            if($provider instanceof Anvil){
                FastLevelSwitch::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(new AnvilChunkRequest($provider, $level->getId(), $x, $z));
            }elseif($provider instanceof McRegion){
                FastLevelSwitch::getInstance()->getServer()->getScheduler()->scheduleAsyncTask(new McRegionChunkRequest($provider, $level->getId(), $x, $z));
            }else{
                FastLevelSwitch::getInstance()->getServer()->getLogger()->warning("Unknown level provider \"".(new \ReflectionClass($provider))->getShortName()."\"");
            }
        }
    }

    public static function isChunkCashed($x, $z, Level $level){
        return FastLevelSwitch::getInstance()->getChunkManager()->isChunkCashed($level->getId(), $x, $z);
    }

    /** @var Server */
    private $server;

    /** @var ChunkCashManager */
    private $chunkManager;

    public function __construct(Server $server){
        $this->server = $server;
        $this->chunkManager = new ChunkCashManager($this);
        $server->getScheduler()->scheduleRepeatingTask(new ChunkUpdateTask($this), FastLevelSwitch::UPDATE_PERIOD);
    }

    /**
     * @return ChunkCashManager
     */
    public function getChunkManager(){
        return $this->chunkManager;
    }

    /**
     * @return Server
     */
    public function getServer(){
        return $this->server;
    }
}