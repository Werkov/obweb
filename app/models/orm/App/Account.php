<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_account
 * @hasMany(name = AccountTransactions, referencedEntity = \Model\App\AccountTransaction, column = account_id)
 * @hasMany(name = Entries, referencedEntity = \Model\App\Entry, column = account_id)
 */
class Account extends \Navigation\Record implements \Nette\Security\IResource {

   public function getResourceId() {
      return "app_account";
   }

   public static function assertion(Permission $acl, $role, $resource, $privilege) {

   }

   public static function menuParentInfo($params = array()) {      
      $row = Account::find($params['id']);

      $res[\Navigation\Navigation::PINFO_PARAMS] = array();
      $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

      return $res;
   }

   /**
    *
    * @param bool $refresh
    * @return double
    */
   public function getBalance($refresh = false) {
      if ($refresh) {
	 $this->invalidateBalance();
	 //$this->loadValues(array("balance"));
      }
      return $this->balance;
   }

   public function invalidateBalance() {
      //balance is sum of all tranactions and prices of all
      $sum = 0;
      /* foreach($this->AccountTransactions as $transaction){
        $sum += $transaction->amount;
        }

        foreach($this->Entries as $entry){
        $sum -= $entry->getPrice();
        } */
      //this was unbearably slow, rewritten to SQL

      $sum = \dibi::fetchSingle("SELECT SUM(amount) FROM :t:app_accountTransaction AS a WHERE account_id = %i", $this->id);

      $appPrice = \dibi::fetchSingle("SELECT SUM(rc.price + (
					     SELECT IFNULL(SUM(co.price), 0)
					     FROM app_selectedOption AS so
					     LEFT JOIN app_additionalCostOption AS co ON co.id = so.option_id
					     WHERE so.entry_id = e.id)) AS amount
		     FROM app_entry AS e
		     LEFT JOIN app_race2category AS rc ON rc.id = e.presentedCategory_id
		     WHERE e.account_id = %i", $this->id);

      $this->balance = $sum - $appPrice;

      $this->save(null, true);  //save as referenced in order to avoid cycling (via invalidation of transactions)
   }

}
