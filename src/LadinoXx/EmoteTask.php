<?php

namespace LadinoXx\EmoteFromEntity;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\EmotePacket;

class EmoteTask extends Task
{
  
  private $plugin;
  
  public function __construct($plugin) {
    $this->plugin = $plugin;
  }
  
  public function onRun() : void {
    foreach ($this->plugin->data->get("saves") as $key) {
      $pos = $this->positionFromString($key["pos"]);
      $world = $pos->getWorld();
      $NPC = $world->getNearestEntity($pos, 2);
      if ($NPC != null) {
        $this->plugin->getServer()->broadcastPackets($NPC->getViewers(), [EmotePacket::create($NPC->getId(), $key["emojiuuid"], 1 << 0)]);
      }
    }
  }
  
  public function positionFromString(string $string): Position {
    $coords = explode("_", $string);
    $vector3 = new Vector3(floatval($coords[0]), floatval($coords[1]), floatval($coords[2]));
    $level = $this->plugin->getServer()->getWorldManager()->getWorldByName($coords[3]);
    return Position::fromObject($vector3, $level);
  }
  
}
