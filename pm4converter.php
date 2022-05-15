<?php

/**
 * This script converts PocketMine-MP plugins with API3.x.x to API4.x.x
 * There are possible bugs, converting a plugin requires full testing after
 *
 * Please report any bugs you encounter while converting / testing (that are obviously caused by this script)
 *
 * 2022 - HimmelKreis4865
 */

$t = microtime(true) * 1000;
set_exception_handler(function ($exception): void {
	log_error($exception->getMessage());
	exit;
});

const IMPORT_REMAPS = [
	'pocketmine\Player' => 'pocketmine\player\Player',
	'pocketmine\OfflinePlayer' => 'pocketmine\player\OfflinePlayer',
	'pocketmine\IPlayer' => 'pocketmine\player\IPlayer',
	'pocketmine\tile' => 'pocketmine\block\tile',
	'pocketmine\effect\Effect' => 'pocketmine\entity\effect\Effect',
	'pocketmine\effect\EffectInstance' => 'pocketmine\entity\effect\EffectInstance',
	'pocketmine\entity\DataPropertyManager' => 'pocketmine\network\mcpe\protocol\types\entity\DataPropertyManager',
	'pocketmine\inventory\AnvilInventory' => 'pocketmine\block\inventory\AnvilInventory',
	'pocketmine\inventory\ChestInventory' => 'pocketmine\block\inventory\ChestInventory',
	'pocketmine\inventory\DoubleChestInventory' => 'pocketmine\block\inventory\DoubleChestInventory',
	'pocketmine\inventory\EnchantInventory' => 'pocketmine\block\inventory\EnchantInventory',
	'pocketmine\inventory\EnderChestInventory' => 'pocketmine\block\inventory\EnderChestInventory',
	'pocketmine\inventory\FurnaceInventory' => 'pocketmine\block\inventory\FurnaceInventory',
	'pocketmine\inventory\CraftingGrid' => 'pocketmine\crafting\CraftingGrid',
	'pocketmine\inventory\CraftingManager' => 'pocketmine\crafting\CraftingManager',
	'pocketmine\inventory\CraftingRecipe' => 'pocketmine\crafting\CraftingRecipe',
	'pocketmine\inventory\ShapedRecipe' => 'pocketmine\crafting\ShapedRecipe',
	'pocketmine\inventory\ShapelessRecipe' => 'pocketmine\crafting\ShapelessRecipe',
	'pocketmine\inventory\FurnaceRecipe' => 'pocketmine\crafting\FurnaceRecipe',
	'pocketmine\level\Location' => 'pocketmine\entity\Location',
	'pocketmine\level\Level' => 'pocketmine\world\World',
	'pocketmine\level' => 'pocketmine\world',
	'pocketmine\command\PluginIdentifiableCommand' => 'pocketmine\plugin\PluginOwned',
	'pocketmine\event\level' => 'pocketmine\event\world',
];
const REMAPS = [
	'/(public\sfunction\sonLoad\(\))\s*[:\s]*[^\{]*/i' => 'protected function onLoad(): void',
	'/(public\sfunction\sonEnable\(\))\s*[:\s]*[^\{]*/i' => 'protected function onEnable(): void',
	'/(public\sfunction\sonDisable\(\))\s*[:\s]*[^\{]*/i' => 'protected function onDisable(): void',
	'/(->getServer\(\)|Server::getInstance\(\))(->findEntity\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->generateLevel\()/i' => '$1->getWorldManager()->generateWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getAutoSave\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->setAutoSave\()/i' => '$1->getWorldManager()$2',
	'/(->getServer\(\)|Server::getInstance\(\))(->getDefaultLevel\()/i' => '$1->getWorldManager()->getDefaultWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevel\()/i' => '$1->getWorldManager()->getWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevelByName\()/i' => '$1->getWorldManager()->getWorldByName(',
	'/(->getServer\(\)|Server::getInstance\(\))(->getLevels\()/i' => '$1->getWorldManager()->getWorlds(',
	'/(->getServer\(\)|Server::getInstance\(\))(->isLevelGenerated\()/i' => '$1->getWorldManager()->isWorldGenerated(',
	'/(->getServer\(\)|Server::getInstance\(\))(->isLevelLoaded\()/i' => '$1->getWorldManager()->isWorldLoaded(',
	'/(->getServer\(\)|Server::getInstance\(\))(->loadLevel\()/i' => '$1->getWorldManager()->loadWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->unloadLevel\()/i' => '$1->getWorldManager()->unloadWorld(',
	'/(->getServer\(\)|Server::getInstance\(\))(->setDefaultLevel\()/i' => '$1->getWorldManager()->setDefaultWorld(',
	'/(->getLevelNonNull\(\))/i' => '->getWorld()',
	'/(->getLevel\(\))/i' => '->getWorld()',
	'/(->sendDataPacket\()/i' => '->getNetworkSession()$1',
	'/(->dataPacket\()/i' => '->getNetworkSession()->sendDataPacket(',
	'/(->getFood\()/i' => '->getHungerManager()$1',
	'/(->getMaxFood\()/i' => '->getHungerManager()$1',
	'/(->removeAllEffects\()/i' => '->getEffects()->clear(',
	'/(->getWorld()->getName\()/i' => '->getWorld()->getFolderName()',
	'/(->addEffect\()/i' => '->getEffects()->add(',
	'/(->addTitle\()/i' => '->sendTitle(',
	'/(->addSubTitle\()/i' => '->sendSubTitle(',
	'/(->setFood\()/i' => '->getHungerManager()$1',
	'/(->removeEffect\()/i' => '->getEffects()->remove(',
	'/(->getEffect\()/i' => '->getEffects()->get(',
	'/(->hasEffect\()/i' => '->getEffects()->has(',
	'/(->getEffects\()/i' => '->getEffects()->all(',
	'/(->isHungry\()/i' => '->getHungerManager()$1',
	'/(->getSaturation\()/i' => '->getHungerManager()$1',
	'/(->asVector3\()/i' => '->getPosition()$1',
	'/(->setSaturation\()/i' => '->getHungerManager()$1',
	'/(->addSaturation\()/i' => '->getHungerManager()$1',
	'/(->getExhaustion\()/i' => '->getHungerManager()$1',
	'/(->setExhaustion\()/i' => '->getHungerManager()$1',
	'/(->exhaust\()/i' => '->getHungerManager()$1',
	'/(->getXpLevel\()/i' => '->getXpManager()$1',
	'/(->setXpLevel\()/i' => '->getXpManager()$1',
	'/(->addXpLevels\()/i' => '->getXpManager()$1',
	'/(->subtractXpLevels\()/i' => '->getXpManager()$1',
	'/(->getXpProgress\()/i' => '->getXpManager()$1',
	'/(->setXpProgress\()/i' => '->getXpManager()$1',
	'/(->getCurrentTotalXp\()/i' => '->getXpManager()$1',
	'/(->setCurrentTotalXp\()/i' => '->getXpManager()$1',
	'/(->getLifetimeTotalXp\()/i' => '->getXpManager()$1',
	'/(->setLifetimeTotalXp\()/i' => '->getXpManager()$1',
	'/(->addXp\()/i' => '->getXpManager()$1',
	'/(->subtractXp\()/i' => '->getXpManager()$1',
	'/(->canPickupXp\()/i' => '->getXpManager()$1',
	'/(->resetXpCooldown\()/i' => '->getXpManager()$1',
	'/(->getDataPropertyManager\()/i' => '->getNetworkProperties(',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::getEffect\()/i' => '\\\pocketmine\\\data\\\bedrock\\\EffectIdMap::getInstance()->fromId(',
	'/\$effect->getId\(\)/i' => '\\\pocketmine\\\data\\\bedrock\\\EffectIdMap::getInstance()->toId($effect)',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::registerEffect\()/i' => '\\\pocketmine\\\data\\\bedrock\\\EffectIdMap::getInstance()->register(',
	'/(Effect|\\\pocketmine\\\entity\\\Effect)(::getEffectByName\()/i' => '\\\pocketmine\\\entity\\\effect\\\VanillaEffects::fromString(',
	'/(Block|\\\pocketmine\\\block\\\Block)(::get\()/i' => '\\\pocketmine\\\block\\\BlockFactory::getInstance()->get(',
	'/(Biome|\\\pocketmine\\\level\\\biome\\\Biome)(::getBiome\()/i' => '\\\pocketmine\\\world\\\biome\\\BiomeRegistry::getInstance()->getBiome(',
	'/(Item|\\\pocketmine\\\item\\\Item)(::get\()/i' => '\\\pocketmine\\\item\\\ItemFactory::getInstance()->get(',
	'/\$(p|player|target|sender)(->get(?:Floor)?[XYZ]\()/i' => '\$$1->getPosition()$2',
	'/\$(p|player|target|sender)(->getYaw\()/i' => '\$$1->getLocation()$2',
	'/\$(p|player|target|sender)(->getPitch\()/i' => '\$$1->getLocation()$2',
	'/(Vector3::SIDE_)(NORTH|SOUTH|EAST|WEST)/i' => '\pocketmine\math\Facing::$2',
	'/->getWorldHeight\(\)/i' => '->getMaxY()',
	'/BlockIds/' => 'BlockLegacyIds',
	'/(public|protected|private)\s(.*)(Level)(.*)/' => '$1 $2World$4', // converts Level object type in properties to World
	'/(function|fn.*\(.*)Level([^,]*\$.*\))/' => '$1World$2', // converts Level parameter type in functions to World
	'/(function .*\)\s*:\s*)Level(.*)/' => '$1World$2', // converts Level return type to World
	'/public function onRun\(int \$currentTick\)(\s*:[a-zA-Z0-9_\s]*)(.*)/i' => 'public function onRun(): void $2', // task behaviour changed
	'/->setHandler\(\);/' => '->setHandler(null);', // handler argument doesn't have a default - null should work for every other thing that is not around this context
	'/implements\sPluginIdentifiableCommand/' => 'implements PluginOwned',
	'/(Block|\\\pocketmine\\\block\\\Block)::([A-Z]*)/' => '\pocketmine\block\BlockLegacyIds::$2',
	'/(Item|\\\pocketmine\\\item\\\Item)::([A-Z]*)/' => '\pocketmine\item\ItemIds::$2',
	'/BlockFactory::getInstance\(\)->get\(([^,()]*)\)/' => 'BlockFactory::getInstance()->get($1, 0)',
	'/Level(Load|Unload)Event/' => 'World$1Event',
	'/\$(e|ev|event)->setCancelled\((?:true)?\)/' => '\$$1->cancel()',
	'/\$(e|ev|event)->setCancelled\(false\)/' => '\$$1->uncancel()',
	'/BaseLang/' => 'Language', // can we really convert this that easy?
	'/([\({,\s\.])Generator::(.*)/' => '$1\pocketmine\world\generator\GeneratorManager::getInstance()->$2',
	'/([\({,\s\.])GeneratorManager::(.*)/' => '$1\pocketmine\world\generator\GeneratorManager::getInstance()->$2',
	'/DestroyBlockParticle/' => 'BlockBreakParticle',
	'/(Entity|\\\pocketmine\\\entity\\\Entity)::registerEntity\((([^,]+)::class),*([^,]*),*(.*)\)/i' => '\\pocketmine\\entity\\EntityFactory::getInstance()->register($2, fn($world, $nbt) => new $3(\\pocketmine\\entity\\EntityDataHelper::parseLocation($nbt, $world), $nbt))'
];
const DANGEROUS_CODES = [
	'/getYaw\(/' => 'Position based functions have been removed from player, entity and block and can be accessed via getPosition() / getLocation()',
	'/getPitch\(/' => 'Position based functions have been removed from player, entity and block and can be accessed via getPosition() / getLocation()',
	'/getGamemode\(/' => 'Player GameMode was made a class instead of an int',
	'/getWorldManager\(\)->generateWorld\(/' => 'Parameters of World generation changed to WorldGenerateOptions',
	'/add\(([^,\)]*(?:,)?){1,2}\)/i' => 'Additions to vectors require 3 arguments', // they are not actively changed since
	'/subtract\(([^,\)]*(?:,)?){1,2}\)/i' => 'Subtractions of vectors require 3 arguments',
	'/PlayerInteractEvent::(RIGHT|LEFT)_CLICK_AIR/' => 'Air clicks have been removed from interaction types.',
	'/RemoteConsoleCommandSender/' => 'RemoteConsoleCommandSender was removed.',
	'/EntityArmorChangeEvent/' => 'EntityArmorChangeEvent was removed.',
	'/InventoryPickupArrowEvent/' => 'InventoryPickupArrowEvent was removed, use EntityItemPickupEvent instead.',
	'/InventoryPickupItemEvent/' => 'InventoryPickupItemEvent was removed,use EntityItemPickupEvent instead.',
	'/PlayerCheatEvent/' => 'PlayerIllegalMoveEvent was removed.',
	'/PlayerIllegalMoveEvent/' => 'PlayerIllegalMoveEvent was removed.',
	'/EntityLevelChangeEvent/' => 'EntityLevelChangeEvent was removed, use EntityTeleportEvent instead.',
	'/CustomInventory/' => 'CustomInventory was removed in PM4',
	'/InventoryEventProcessor/' => 'Class InventoryEventProcessor does no longer exist.',
	'/(->getServer\(\)|Server::getInstance\(\))(->reload\()/i' => 'Method \pocketmine\Server::reload() was removed.',
	'/(->getServer\(\)|Server::getInstance\(\))(->addPlayer\()/i' => 'Method \pocketmine\Server::addPlayer() was removed.',
	'/ItemFactory::fromString\(/' => 'ItemFactory::fromString() was removed.',
	'/Potion::getPotionEffectsById\(/' => 'Potion::getPotionEffectsById() was removed.',
	'/CreativeInventoryAction/' => 'CreativeInventoryAction was removed.',
	'/\$(e|ev|event)->setCancelled\(/' => 'Events are now cancelled with cancel() / uncancel() - Could not be replaced automatically'
];
$pluginFolder = load_plugin_folder($argv);
$outputFolder = __DIR__ . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . basename($pluginFolder) . DIRECTORY_SEPARATOR;

log_notice('Copying folder structure...');
copy_folder_structure($pluginFolder, $outputFolder);

log_notice('Loading plugin...');

repair_files($pluginFolder, $outputFolder);

log_notice('Repairing plugin.yml...');
convert_plugin_file($pluginFolder . 'plugin.yml', $outputFolder . 'plugin.yml', $mainPath);

log_notice('Completed plugin convert to API4 in ' . round((microtime(true) * 1000) - $t, 2) . 'ms.');
function repair_files(string $pluginFolder, string $outputFolder): void {
	$fileCount = count_files($pluginFolder);
	
	echo 'Repairing plugin files' . str_repeat(' ', strlen($fileCount)) . '(0/' . $fileCount . ')';
	$baseLen = 3 + strlen($fileCount);
	$count = 0;
	$warnings = [];
	scan_directory_recursively($pluginFolder, function (string $path) use ($pluginFolder, $outputFolder, $fileCount, $baseLen, &$count, &$warnings): void {
		if (is_dir($path)) return;
		$targetPath = $outputFolder . ($relative = substr($path, strlen($pluginFolder)));
		if (!str_ends_with($path, '.php')) {
			file_put_contents($targetPath, file_get_contents($path));
		} else {
			repair_php_file($path, $targetPath, $relative, $warnings);
		}
		
		++$count;
		echo "\x1b[" . ($baseLen + strlen($count)) . 'D(' . $count . '/' . $fileCount . ')';
	});
	echo PHP_EOL;
	log_warning('Found ' . count($warnings) . ' possible remaining bugs that cannot be fixed with this converter (they might be invalid):');
	foreach ($warnings as $w) log_warning(' - ' . $w);
}

function repair_php_file(string $path, string $targetPath, string $relativePath, array &$warnings): void {
	$content = file_get_contents($path);
	foreach (IMPORT_REMAPS as $old => $new) {
		$content = str_replace('\\' . $old, $new, $content);
		$content = str_replace($old, $new, $content);
	}
	foreach (REMAPS as $regex => $v) {
		$content = preg_replace($regex, $v, $content);
	}
	foreach (preg_split("/\r\n|\n|\r/", $content) as $k => $line) {
		foreach (DANGEROUS_CODES as $m => $msg) {
			if (preg_match($m, $line)) $warnings[] = $relativePath . ':' . ($k + 1) . '  ' . $msg;
		}
	}
	file_put_contents($targetPath, $content);
}

function count_files(string $directory): int {
	$count = 0;
	scan_directory_recursively($directory, function (string $path) use (&$count) {
		if (!is_dir($path)) $count++;
	});
	return $count;
}

function scan_directory_recursively(string $path, Closure $closure): void {
	$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	foreach (array_diff(scandir($path), ['.', '..']) as $file) {
		$closure($path . $file);
		if (is_dir($path . $file)) scan_directory_recursively($path . $file, $closure);
	}
}

function convert_plugin_file(string $path, string $outputPath, &$mainPath): void {
	$yaml = yaml_parse_file($path);
	$yaml['api'] = '4.0.0';
	$p = [];
	
	$recursion = function (array $permissions, Closure $handler) use (&$p) {
		foreach ($permissions as $str => $permissionData) {
			if (isset($permissionData['children'])) {
				$handler($permissionData['children'], $handler);
				unset($permissionData['children']);
			}
			$p[$str] = $permissionData;
		}
	};
	
	$recursion($yaml['permissions'] ?? [], $recursion);
	if (isset($yaml['permissions'])) $yaml['permissions'] = $p;
	yaml_emit_file($outputPath, $yaml);
	$mainPath = dirname($path) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $yaml['main'] . '.php';
}

function copy_folder_structure(string $src, string $target, bool $__do_not_change = true, &$failureCount = 0): void {
	foreach (array_diff(scandir($src), ['.', '..']) as $file) {
		if (is_dir($src . $file)) {
			if (!@mkdir($target . $file, 0777, true)) ++$failureCount;
			copy_folder_structure($src . $file . DIRECTORY_SEPARATOR, $target . $file . DIRECTORY_SEPARATOR, false, $failureCount);
		}
	}
	if ($__do_not_change and $failureCount) log_warning($failureCount . ' directories were unable to be generated, maybe already existent?');
}

function load_plugin_folder(array $input): string {
	if (!isset($input[1]) and !$input) throw new RuntimeException('Please enter an argument to a path the plugin is inside.');
	if (!is_dir($dir = $input[1]) and !is_dir($dir = __DIR__ . DIRECTORY_SEPARATOR . $dir)) throw new RuntimeException('Entered file path could not be found.');
	$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	if (!file_exists($dir . 'plugin.yml')) throw new RuntimeException('A plugin must contain a valid plugin.yml');
	if (!is_dir($dir . 'src')) throw new RuntimeException('A plugin must contain a src folder');
	return $dir;
}

function log_notice(string $str): void {
	echo "\033[92m" . $str . "\033[39m" . PHP_EOL;
}

function log_warning(string $str): void {
	echo "\033[93m" . $str . "\033[39m" . PHP_EOL;
}

function log_error(string $str): void {
	echo "\033[91m" . $str . "\033[39m" . PHP_EOL;
}