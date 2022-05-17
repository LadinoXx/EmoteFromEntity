<?php

declare(strict_types=1);

namespace LadinoXx\EmoteFromEntity;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\event\entity\EntityDamageByEntityEvent as EntityTabEvent;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener
{
  
  public $idSetup = [];
  public $data;
  
  public function onEnable() : void {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    @mkdir($this->getDataFolder());
    $this->data = new Config($this->getDataFolder(). "data.yml", Config::YAML, ["saves" => []]);
    $this->getScheduler()->scheduleRepeatingTask(new EmoteTask($this), 8*20);
  }
  
  public function entityTab(EntityTabEvent $ev) : void {
    $entity = $ev->getEntity();
    $player = $ev->getDamager();
    if (!$entity instanceof Player and $player instanceof Player and isset($this->idSetup[$player->getName()])) {
      //pegar a posição da entidade 
      $position = $entity->getPosition();
      $saveddata = [
        "pos" => $position->getX() . "_" . $position->getY() . "_" . $position->getZ() . "_" . $position->getWorld()->getFolderName(),
        "number" => count($this->data->get("saves")) + 1,
        "emojiuuid" => $this->idSetup[$player->getName()]
        ];
      unset($this->idSetup[$player->getName()]);
      $key = count($this->data->get("saves")) + 1;
      $this->data->setNested("saves." . $key, $saveddata);
      $this->data->save();
      $player->sendMessage("§aEmoji definido");
    }
  }
  
  public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool {
    if ($player instanceof Player) {
      switch ($command->getName()) {
        case "setemoji":
          $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
          if ($api != null) {
            $form = $api->createSimpleForm(function(Player $player, $args = null){
              if ($args == null) {
                return;
              }
              $this->idSetup[$player->getName()] = $args;
              $player->sendMessage("§aClique na entidade que deseja por o emote");
            });
            $form->setTitle("§8SELECIONE O EMOTE");
            $form->setContent("§7Selecione o emote que queira por na entidade, veja mais em §bhttps://github.com/JustTalDevelops/bedrock-emotes");
            $form->addButton("Abduction?", -1, "", "18891e6c-bb3d-47f6-bc15-265605d86525");
            $form->addButton("Acting Like a Dragon", -1, "", "c2a47805-c792-4882-a56d-17c80b6c57a8");
            $form->addButton("Ahh Choo!", -1, "", "f9345ebb-4ba3-40e6-ad9b-6bfb10c92890");
            $form->addButton("Ballerina Twirl", -1, "", "79452f7e-ffa0-470f-8283-f5063348471d");
            $form->addButton("Big Chuckles", -1, "", "819f2f36-2a16-440c-8e46-94c6b003a2e0");
            $form->addButton("Bored", -1, "", "7a314ecf-f94c-42c0-945f-76903c923808");
            $form->addButton("Bow", -1, "", "ddfa6f0e-88ca-46de-b189-2bd5b18e96a0");
            $form->addButton("Breakdance", -1, "", "1dbaa006-0ec6-42c3-9440-a3bfa0c6fdbe");
            $form->addButton("Calling a Dragon", -1, "", "9f5d4732-0513-4a0a-8ea2-b6b8d7587e74");
            $form->addButton("Cartwheel", -1, "", "5cf9d5a3-6fa0-424e-8ae4-d1f877b836da");
            $form->addButton("Chatting", -1, "", "59d9e78c-f0bb-4f14-9e9b-7ab4f58ffbf5");
            $form->addButton("Clicking Heels", -1, "", "495d686a-4cb3-4f0b-beb5-bebdcb95eed9");
            $form->addButton("Cowpoke Dancin'", -1, "", "f99ccd35-ebda-4122-b458-ff8c9f9a432f");
            $form->addButton("DJing", -1, "", "beb74219-e90c-46aa-8a4b-a1c175f6cab5");
            $form->addButton("Dancing Like Toothless", -1, "", "a12252fa-4ec8-42e0-a7d0-d44fbc90d753");
            $form->sendToPlayer($player);
          }
          break;
          case "rmemoji":
            $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
          if ($api != null) {
            $form = $api->createSimpleForm(function(Player $player, $args = null){
              if ($args == null) {
                return;
              }
              $this->data->removeNested("saves." . $args);
              $this->data->save();
              $player->sendMessage("§aEmoji removido do NPC");
            });
            $form->setTitle("§8SELECIONE O NPC");
            $form->setContent("§7Escolha e NPC que deseja tirar o emoji (pela name tag)");
            foreach ($this->data->get("saves") as $key) {
              $pos = $this->positionFromString($key["pos"]);
              $world = $pos->getWorld();
              $NPC = $world->getNearestEntity($pos, 2);
              if ($NPC != null) {
                $form->addButton($NPC->getNameTag(), -1, "", $key["number"] . "");
              }
            }
            $form->sendToPlayer($player);
          }
            break;
      }
    }
    return true;
  }
  
  public function positionFromString(string $string): Position
    {
      $coords = explode("_", $string);
      $vector3 = new Vector3(floatval($coords[0]), floatval($coords[1]), floatval($coords[2]));
      $level = $this->getServer()->getWorldManager()->getWorldByName($coords[3]);
      return Position::fromObject($vector3, $level);
    }
  
}
