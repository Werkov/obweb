<?php

namespace Model\App;

/**
 * Adds behavior to entities that can change balances of accounts.
 *
 */
class BalanceBehavior implements \Ormion\Behavior\IBehavior {

   /**
    * Name of the column that specifies account to invalidate. When null all accounts are affected.
    * @var string|null
    */
   public $accountColumn;
   /**
    * Name of the column that could have changed the balabnce of an account.
    * @var string
    */
   public $priceColumn;

   public function __construct($priceColumn, $accountColumn = null) {
      $this->priceColumn = $priceColumn;
      $this->accountColumn = $accountColumn;
   }

   public function setUp(\Ormion\IRecord $record) {


      $behavior = $this;
      $oneUpdate = function(\Ormion\IRecord $record) use($behavior) {
		 if ($behavior->priceColumn !== null) {
		    //if (!$record->isValueModified($behavior->priceColumn)) mapper clear modified
		    //   return;
		 }
		 if ($behavior->accountColumn === null) {
		    $accounts = Account::findAll()->fetchAll();
		 } else {
		    $accounts = array(Account::find($record->{$behavior->accountColumn}));
		 }

		 foreach ($accounts as $account) {
		    $account->invalidateBalance();
		 }
	      };

      $biUpdate = function(\Ormion\IRecord $record) use($behavior) {
		 if ($behavior->priceColumn !== null) {
		    //if (!$record->isValueModified($behavior->priceColumn)) mapper clear modified
		    //   return;
		 }
		 if ($behavior->accountColumn === null) {
		    $accounts = Account::findAll()->fetchAll();
		 } else {
		    if ($record->isValueModified($behavior->accountColumn)) {   //modify both accounts, old and new
		       $original = $record->find($record->getPrimary());

		       $accounts = array(
			   Account::find($record->{$behavior->accountColumn}),
			   Account::find($original->{$behavior->accountColumn}),
		       );
		    } else {
		       $accounts = array(Account::find($record->{$behavior->accountColumn}));
		    }
		 }
		 foreach ($accounts as $account) {
		    $account->invalidateBalance();
		 }
	      };
      $record->onAfterInsert[] = $oneUpdate;
      $record->onAfterDelete[] = $oneUpdate;
      $record->onAfterUpdate[] = $biUpdate;
   }

}
