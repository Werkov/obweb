<?php

namespace ApplicationModule;

use \Model\App\AccountTransaction;
use Model\App\BalanceException;

/**
 * @generator MScaffolder
 */
final class AccountTransactionPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\App\AccountTransaction";
   protected static $parentClass = "\Model\App\Account";
   protected static $parentColumn = "account_id";

   const DEPOSIT = "deposit";
   const WITHDRAWAL = "withdrawal";
   /**
    *
    * @var string "deposit" or "withdrawal"
    */
   protected $mode;

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      return \OOB\AccountHistoryGrid::create($this, $name, $this->parentRecord, false);
   }

   protected function createComponentTransactionForm($name) {
      if ($this->getAction() == "deposit") {
	 return $this->createComponentForm($name, true);
      } else if ($this->getAction() == "withdrawal") {
	 return $this->createComponentForm($name, false);
      }
   }

   protected function createComponentForm($name, $deposit = false) {
      $form = new \Nette\Application\UI\Form($this, $name);


      $form->addText("amount", "Částka")
	      ->addRule(\Nette\Forms\Form::FILLED, "Vyplňte částku.")
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 9)
	      ->addRule(\Nette\Forms\Form::FLOAT, "Částka musí být číslo.")
	      ->addRule(\Nette\Forms\Form::RANGE, "Částka musí být kladná.", array(0, null));


      $form->addTextArea("note", "Poznámka")
	      ->addRule(\Nette\Forms\Form::FILLED, "Vyplňte poznámku.");

      $form->addHidden("deposit", $deposit);

      $form->addSubmit('save', $deposit ? 'Vložit' : 'Vybrat');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>

   public function actionDeposit($id, $parent) {
      parent::actionAdd($id, $parent);
      $this->setView("add");
   }

   public function actionWithdrawal($id, $parent) {
      parent::actionAdd($id, $parent);
      $this->setView("add");
   }

   protected function setRelations(\Nette\Application\UI\Form $form) {
      parent::setRelations($form);
      $values = $form->getValues();

      $this->currentRecord->datetime = new \DateTime();
      $this->currentRecord->user_id = $this->getUser()->getId();
      $this->currentRecord->account_id = $this->getParam("parent"); //action is not 'add', therefore it must be set here

      if (!$values["deposit"])
	 $this->currentRecord->amount *= - 1;
   }

   protected function saveRecord(\Nette\Application\UI\Form $form) {
      if ($this->currentRecord->Account->getBalance(true) < $this->currentRecord->amount * -1) {
	 throw new BalanceException("Nedostatečný zůstatek na účtě.", 100);
      }
      parent::saveRecord($form);
   }

   protected function ACLadd($record = null) {
      if ($record === null)
	 $record = $this->parentRecord;

      return $this->getUser()->isAllowed($record, "transactions");
   }

   protected function ACLdelete($record = null) {
      if ($record === null)
	 $record = $this->parentRecord;

      return $this->getUser()->isAllowed($record, "transactions");
   }

   protected function ACLedit($record = null) {
      if ($record === null)
	 $record = $this->parentRecord;

      return $this->getUser()->isAllowed($record, "transactions");
   }

   protected function ACLlist($record = null) {
      if ($record === null)
	 $record = $this->parentRecord;

      return $this->getUser()->isAllowed($record, "transactions");
   }
   
   public static function ttRecordChanged($record) {
      if($record->amount < 0){
	 return "Částka " . \OOB\Helpers::currency(-$record->amount) . " byla vybrána.";
      }
      else{
	 return "Částka " . \OOB\Helpers::currency($record->amount) . " byla vložena.";
      }
      
   }

}

