<?php

namespace OOB;

use Model\System\User;
use Model\App\Race;
use Model\App\Entry;

class EntryInfo extends \Nette\Application\UI\Control {
   /**
    * @var User
    */
   protected $entry;
   
   public function getEntry() {
      return $this->entry;
   }

   public function setEntry($entry) {
      $this->entry = $entry;
   }

   
      

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/info.latte");
      $template->registerHelperLoader('\OOB\Helpers::loader');
      return $template;
   }
   
   public function render(){
      $this->template->entry = $this->entry;
      
      $this->template->render();
   }

}