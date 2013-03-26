<?php

namespace OOB;

use Model\System\User;
use Model\App\Member;
use Model\App\Backer;

class UserProfile extends \Nette\Application\UI\Control {
   /**
    * @var User
    */
   protected $user;
   
   /**
    *
    * @var boolean
    */
   protected $full = false;
   
   public function getUser() {
      return $this->user;
   }

   public function setUser($user) {
      $this->user = $user;
   }
   public function getFull() {
      return $this->full;
   }

   public function setFull($full) {
      $this->full = $full;
   }

      

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/profile.latte");
      $template->registerHelperLoader('\OOB\Helpers::loader');
      return $template;
   }
   
   public function render(){
      $this->template->listedUser = $this->user;
      $this->template->full = $this->full;
      $this->template->render();
   }

}