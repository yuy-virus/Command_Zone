<?php

namespace yuy\task;

use yuy\Yzone;

use pocketmine\scheduler\Task;

class ZoneTask extends Task{

  private $p;

  public function __construct(Yzone $p){

    $this->p = $p;
  }
  public function onRun($currentTick){
  
    foreach($this->p->getServer()->getOnlinePlayers() as $player){
        
      $this->p->PosCheck($player);
      
    }
  }
}
