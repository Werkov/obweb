<?php

namespace Model\System;

/**
 *
 * @table system_token
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = user_id)
 * @hasOne(name = Type, referencedEntity = \Model\System\TokenType, column = type_id)
 */
class Token extends \Navigation\Record {

   /**
    * Checks whether given token is valid.
    * @param string $token
    * @param int $expiration -1 for infinite, no. of second when token is valid
    * @return User|boolean  false when token is invalid, true or bound user otherwise
    */
   public static function checkToken($token, $expiration = -1) {
      if ($expiration != -1) {
	 $tokenCol = self::findAll()->where("token = %s AND [datetime] > DATE_SUB(NOW(), INTERVAL %i SECONDS", $token, $expiration);
      } else {
	 $tokenCol = self::findAll()->where("token = %s", $token);
      }

      if ($tokenCol->count() == 0) {
	 return false;
      }
      
      $token = $tokenCol->fetch();
      if ($token->User) {
	 if ($token->User->active) {
	    return $token->User;
	 } else {
	    return false;
	 }
      } else {
	 return true;
      }
   }

   /**
    *
    * @param TokenType $type
    * @param User|null $user user for who we want the token, null is used only when generate == true
    * @param boolean $generate when true generates new token or updates actual
    * @return Token|boolean  existing/generated token or null when not found
    */
   public static function getToken($type, $user = null, $generate = true) {
      if ($user !== null) {
	 if ($generate) {
	    return self::generateToken($type, $user);
	 } else {
	    $token = self::find(array("type_id" => $type->id, "user_id" => $user->id));
	    return $token;
	 }
      } else {
	 if ($generate) {
	    return self::generateToken($type);
	 } else {
	    throw new \Nette\InvalidStateException("User cannot be null and generate false.");
	 }
      }
   }

   protected static function generateToken($type, $user = null) {
      do {
	 $key = \Nette\Utils\Strings::random(32, '0-9a-zA-Z');
      } while (\dibi::fetchSingle("SELECT COUNT(*) FROM :t:system_token WHERE token = %s", $key) > 0);

      $token = self::create();
      $token->token = $key;
      $token->type_id = $type->id;
      $token->datetime = new \DateTime();

      if ($user) {
	 $token->user_id = $user->id;

	 $oldToken = self::find(array("type_id" => $type->id, "user_id" => $user->id));
	 $oldToken && $oldToken->delete();
      }

      $token->save();

      return $token;
   }

}
