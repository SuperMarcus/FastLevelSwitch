# FastLevelSwitch
A third PocketMine-MP library that teleport player safely (Fix player in the stone)

By default, you should use the static methods in class `\supermarcus\fastlevelswitch\FastLevelSwitch`

### Useful method
`FastLevelSwitch::teleport(Player $player, $position[, $delay = 2 * 20])`

The default method to teleport a player with chunk pre-loading

`FastLevelSwitch::preLoadChunks(Player $player[, Vector3 $pos = null])`

Pre-load a selection chunk for a player

`FastLevelSwitch::cashChunk($x, $z, Level $level)`

Cash a selection chunk of a level
