<?php

namespace yuy;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use pocketmine\math\Vector3;

use pocketmine\Player;
use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;

use yuy\task\ZoneTask;

class Yzone extends PluginBase implements Listener
{

function onEnable(){
    
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    
    @mkdir($this->getDataFolder());
    $this->data = new Config($this->getDataFolder() . 'Zone.yml', Config::YAML);
    $this->db = $this->data->getAll();
       
    $task = new ZoneTask($this);
    $this->getScheduler()->scheduleRepeatingTask($task, 20);
    
    }
  
function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    
      $cmd = $command->getName();
      
    if ($cmd === "zonec") {
    
    if(!$sender instanceof Player){
        
        self::sendMessage($sender,"콘솔에서는 실행이 불가능합니다");
        
        return true;
        
    }
        
    if(!isset($this->zone[$sender->getName()][1]) or !isset($this->zone[$sender->getName()][0])){
        
        self::sendMessage($sender,"구역이 설정 되어있지 않습니다.");
        
        return true;
        
    }
        
	if (!isset($args[2])){
	    
  self::sendMessage($sender,"/zonec (Zone Name) (Player|Console) (Command)");
                          
self::sendMessage($sender,"명령어를 제대로 입력해주세요");

        return true;
        
        }
        
        $c = strtolower($args[1]);
        
    if($c != "player" and $c != "console"){
            
self::sendMessage($sender,"실행주체를 올바르게 써주세요!");

      return true;
      
        }
        
        $zone = $this->zone[$sender->getName()];
        
        $pos1 = explode(":",$zone[0]);
        $pos2 = explode(":",$zone[1]);
        
  $nx = min($pos1[0], $pos2[0]);
  $xx = max($pos1[0], $pos2[0]);
  $ny = min($pos1[1], $pos2[1]);
  $xy = max($pos1[1], $pos2[1]);
  $nz = min($pos1[2], $pos2[2]);
  $xz = max($pos1[2], $pos2[2]);
    
    $this->db[$args[0]] = ['xx' => $xx,'nx' => $nx,'xy' => $xy,'ny' => $ny,'xz' => $xz,'nz' => $nz,'w' => $pos1[3],'sender' => strtolower($args[1]),'command' => $args[2]];
    $this->db['ZONE'][$args[0]] = $args[0];
    unset($this->zone[$sender->getName()]);
    
self::sendMessage($sender,"생성 했습니다");
        
    } 
    if ($cmd === "zoned") {
    
     if(!$sender instanceof Player){
        
        self::sendMessage($sender,"콘솔에서는 실행이 불가능합니다");
        
        return true;
        
    }
        
        if(!isset($args[0])){
            
self::sendMessage($sender,"/zoned (Zone Name)");
                          
self::sendMessage($sender,"구역이름을 제대로 입력해주세요");
      
      return true;
        }
    
    if(!isset($this->db[$args[0]])){
        
        self::sendMessage($sender,"그런 구역은 없습니다");
    
    return true;
    
    }
    
    unset($this->db[$args[0]],$this->db['ZONE'][$args[0]]);
    
    self::sendMessage($sender,"구역을 제거 했습니다");

}
return true;
    
}

function sendMessage($p, $m){
    
    $p->sendMessage("§l§b[zone] §7".$m);
    
}

function onDisable(){
    
    $this->data->setAll($this->db); 
    $this->data->save();
    
}

function PosCheck($pl){
    
    $x = (int) $pl->x;
    
	$y = (int) $pl->y;
	
	$z = (int) $pl->z;
	
    $w = $pl->level->getFolderName();
    
    if(!isset($this->db['ZONE'])) return true;
    
    foreach($this->db['ZONE'] as $zn){
        
    if(isset($this->db[$zn])){
        
        $zone = $this->db[$zn];
        $zw = $zone['w'];
    if($zw == $w){
        
        $xx = $zone['xx'];
        $nx = $zone['nx'];
        $xy = $zone['xy'];
        $ny = $zone['ny'];
        $xz = $zone['xz'];
        $nz = $zone['nz'];
        $sender = $zone['sender'];
        
    if($nx <= $x and $x <= $xx and $ny <= $y and $y <= $xy and $nz <= $z and $z <= $xz){
    
    if(!isset($this->PosCheck[$zn][$pl->getName()])){
        
        
        if($sender == "player"){
            
            $this->getServer()->dispatchCommand($pl, $zone['command']);
            
        }
        if($sender == "console"){
                 $console = new ConsoleCommandSender();
        
        $this->getServer()->dispatchCommand($console, $zone['command']);
        
        }
        $this->PosCheck[$zn][$pl->getName()] = 'isset';
        
    }
        
    } else {
        
        unset($this->PosCheck[$zn][$pl->getName()]);
        
    }
    }
        
    }
    
    }
    
}

function onBreak(BlockBreakEvent $ev){
    
    $bl = $ev->getBlock();
    $nn = $ev->getPlayer()->getName();
     $pos = $bl->x.":".$bl->y.":".$bl->z.":".$bl->level->getFolderName();
     
     $it = $ev->getItem()->getId();
     
     if($it == 286){
            
            $this->zone[$nn][0] = $pos;
        
        self::sendMessage($ev->getPlayer(), "1번째 지점을 설정 했습니다");
            
        }
     
}

function onInteract(PlayerInteractEvent $ev){
    
    $bl = $ev->getBlock();
    $nn = $ev->getPlayer()->getName();
    $pos = $bl->x.":".$bl->y.":".$bl->z.":".$bl->level->getFolderName();
    $it = $ev->getItem()->getId();
    $ac = $ev->getAction();
    
    if($it == 286){
        
        if($ac == 1){
            
            $this->zone[$nn][1] = $pos;
            
            self::sendMessage($ev->getPlayer(), "2번째 지점을 설정 했습니다");
            
        }
        
    }
    
}
    
}