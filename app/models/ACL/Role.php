<?php

namespace ACL;

/**
 *
 * @author Michal
 */
class Role implements \Nette\Security\IRole {
   /**
    *
    * @var string  name of the role
    */
   private $name;

   /**
    *
    * @var \Nette\Security\Identity  identity who has given role
    */
   private $identity;

   public function __construct($name, $identity) {
      $this->name = $name;
      $this->identity = $identity;
   }

   /**
    *
    * @return string
    */
   public function getRoleId() {
      return $this->name;
   }

   /**
    *
    * @return \Nette\Web\Identity
    */
   public function getIdentity() {
      return $this->identity;
   }

}
