<?php

namespace Model\System;

/**
 *
 * @table system_tokenType
 */
class TokenType extends \Navigation\Record {

   /**
    * Invalidates all tokens of this type older than date (or all)
    * @param \DateTime $date 
    */
   public function invalidate($date = null) {
      $fl = \dibi::delete(":t:system_tokenType")->where("type_id = %i", $this->id);

      if ($date) {
	 $fl->where("[datetime] < %t", $date);
      }
      
      $fl->execute();
   }

}
