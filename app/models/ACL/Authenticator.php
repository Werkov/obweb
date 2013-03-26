<?php

namespace ACL;

use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Model\System\User;

/**
 * Users authenticator.
 *
 */
class Authenticator extends Object implements IAuthenticator {
   const INVALID_CREDENTIAL_MSG = "Nesprávné přihlašovací údaje.";

   /**
    * Performs an authentication
    * @param  array
    * @return IIdentity
    * @throws AuthenticationException
    */
   public function authenticate(array $credentials) {
      $user = User::find(array("login" => $credentials[self::USERNAME], "active" => 1));

      if (!$user)
	 throw new AuthenticationException(self::INVALID_CREDENTIAL_MSG, self::INVALID_CREDENTIAL);

      if (!$user->checkPassword($credentials[self::PASSWORD]))
	 throw new AuthenticationException(self::INVALID_CREDENTIAL_MSG, self::INVALID_CREDENTIAL);
      
      $user->lastLog = new \DateTime();
      $user->lastIP = \Nette\Environment::getHttpRequest()->getRemoteAddress();
      $user->save();
      
      return $user;
      
      //User je identita
      /*$identity = new \Nette\Security\Identity($user->id, null, $user);

      $roleNames = $user->Roles->fetchColumn("name");
      $roles = \array_map(function($name) use($identity) {
			 return new Role($name, $identity);
		      }, $roleNames, \array_fill(0, count($roleNames), $user->id));
      if (($user->Member && $user->Member->active) || ($user->Backer && $user->Backer->active)) {
	 $roles[] = "registered";
      }

      $identity->setRoles($roles);

      //return new Nette\Security\Identity($user->id, $user->Roles->fetchColumn("name"), $user);
      return $identity;*/
   }

}
