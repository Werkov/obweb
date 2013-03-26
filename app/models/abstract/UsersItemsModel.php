<?php

namespace Model;

use Model\System\User;

abstract class UsersItemsModel implements \OOB\Forms\IItemsModel {

   /**
    * @var array
    */
   private $idToName = null;
   /**
    * @var array
    */
   private $nameToId = null;

   public function __construct() {
      $this->idToName = array();
      $this->nameToId = array();

      foreach(User::findAll(array("active" => 1))->fetchAll() as $user){
	 if(!$this->IsUserValid($user))
	    continue;
	 $this->idToName[$user->id] = $user->getFullName();
	 $this->nameToId[$user->getFullName()] = $user->id;
      }
   }
   
   protected function GetUserCollection() {
       return User::findAll(array("active" => 1))->fetchAll();
   }
   
   abstract protected function IsUserValid(User $user);

   public function GetAllItems() {
      return $this->idToName;
   }

   public function IdToName($id) {
      if (\array_key_exists($id, $this->idToName))
	 return $this->idToName[$id];
      else
	 return "no name";
   }

   public function NameToId($name, $insert = false) {
      if (\array_key_exists($name, $this->nameToId)) {
	 return $this->nameToId[$name];
      } else {
	 return null;
      }
   }

}