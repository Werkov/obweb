<?php

namespace ApplicationModule;

use \Model\App\Entry;
use OOB\EntryForm;

/**
 * @generator MScaffolder
 */
final class EntryPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\App\Entry";
   protected static $parentClass = "\Model\App\Race";
   protected static $parentColumn = null;

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">
   protected function createComponentGrdEntries($name) {

      return \OOB\EntryGrid::create($this, $name, null);
   }

   protected function createComponentGrid($name) {
      return \OOB\EntryGrid::create($this, $name, $this->getAction() == "plist", \Model\App\Race::create(array("id" => $this->getParam("parent"))), false);
   }

   protected function createComponentForm($name, $new = false) {
      if ($this->action == "add" || $this->action == "padd") {
	 $race = \Model\App\Race::find($this->getParam("parent"));
      } else {
	 $race = $this->currentRecord->Category->Race;
      }

      $form = EntryForm::reducedCreate($this, $name, $race, $this->currentRecord);


      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   protected function createComponentEntryInfo($name) {

      $info = new \OOB\EntryInfo($this, $name);

      $info->setEntry($this->currentRecord);


      return $info;
   }
   
   protected function createComponentExportCsob($name){
      $export = new \OOB\ExportCsob($this, $name);
      return $export;
   }
   
   protected function createComponentExportSums($name){
      $export = new \OOB\ExportSums($this, $name);
      
      $export->setRace($this->parentRecord);
      return $export;
   }

// </editor-fold>
//<editor-fold desc="Record saving">

   protected function setRelations(\Nette\Application\UI\Form $form) {
      $values = $form->getValues();
      $race = \Model\App\Race::create(array("id" => $this->getParam("parent")));
      $this->currentRecord->datetime = new \DateTime();
      if ($this->currentRecord->getState() == \Ormion\Record::STATE_NEW) {	 
	 if (!isset($this->currentRecord->account_id)) {
	    $this->currentRecord->account_id = $this->currentRecord->Racer->account_id;
	 }
      }

      //\dump($this->currentRecord);
   }

   protected function saveRecord(\Nette\Application\UI\Form $form) {
      if (!$this->currentRecord->Account) {
	 throw new \Model\App\EntryException("Závodník nemá přiřazen účet.", 100);
      }
      
      if (!$this->currentRecord->isPayable($this->currentRecord->Account)) {
	 throw new \Model\App\EntryException("Na účtu není dostatečný zůstatek.", 100);
      }


      //test uniqueness
      if ($this->currentRecord->getState() == \Ormion\Record::STATE_NEW) {
	 $num = \dibi::select("e.id")->from(":t:app_entry AS e")
		 ->leftJoin(":t:app_race2category AS rc")->on("rc.id = e.presentedCategory_id")
		 ->where("e.racer_id = %i", $this->currentRecord->racer_id)
		 ->and("rc.race_id = %i", $this->currentRecord->Category->race_id)
		 ->count();

	 if ($num > 0) {
	    throw new \Model\App\EntryException("Závodník je již na závod přihlášen.", 110);
	 }
      }
      \dibi::begin();
      parent::saveRecord($form);
      \dibi::commit();
   }

   public function formSubmitted(\Nette\Application\UI\Form $form) {
      if ($form['save']->isSubmittedBy()) {
	 //uložit
	 $this->currentRecord->setValues($form->getValues());

	 if ($this->getAction() == "add" || $this->getAction() == "padd") {
	    $message = static::ttRecordAdded($this->currentRecord);
	    if (static::$parentColumn !== null) {
	       $this->currentRecord->{static::$parentColumn} = $this->getParam("parent");
	    }
	 } elseif ($this->getAction() == "edit" || $this->getAction() == "pedit") {
	    $message = static::ttRecordEdited($this->currentRecord);
	 } else {
	    $message = "";
	 }


	 try {
	    $this->setRelations($form);
	    $this->saveRecord($form);
	    $this->flashMessage($message, \BasePresenter::FLASH_OK);
	 } catch (\ModelException $e) {
	    if ($e->getCode() == \ModelException::CODE_CONCURRENCY_ISSUE) {
	       $form->addError(static::ttConcurrencyIssue($this->currentRecord));
	    } else {
	       $form->addError($e->getMessage());
	    }

	    return;
	 } catch (\Nette\Application\ApplicationException $e) {
	    $form->addError($e->getMessage());
	    return;
	 }
      }

      if (substr($this->getAction(), 0, 1) == "p")
	 $p = "p";
      else
	 $p = "";

      if (static::$parentClass) {
	 $this->redirect($p . "list", array("parent" => ($this->getParam("parent") ? $this->getParam("parent") : $this->getParentValue())));
      } else {
	 $this->redirect($p . "list");
      }
   }

   protected function getParentValue() {
      return $this->currentRecord->Category->race_id;
   }

   protected function checkParent($parent) {
      $this->parentRecord = \call_user_func(static::$parentClass . "::find", ($parent === null) ? 0 : $parent);
      if (!$this->parentRecord) {
	 throw new \Nette\Application\BadRequestException(static::ttParentNotFound(), 404);
      }

      //check ACL
      if (!$this->getUser()->isAllowed($this->parentRecord, "listApplications")) {
	 throw new \Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->parentRecord, "edit"), 403);
      }
   }

   /**
    *
    * @param \Ormion\Record $record
    * @return bool
    */
   protected function ACLadd($record = null) {

      if ($record === null)
	 $record = $this->currentRecord;

      return ($this->parentRecord->status == \Model\App\Race::STATUS_APP && $this->getUser()->isAllowed($record, "add"))
      || $this->getUser()->isAllowed($this->parentRecord, "applications");
   }

   /**
    *
    * @param \Ormion\Record $record
    * @return bool
    */
   protected function ACLedit($record = null, $checkStatus = true) {
      if ($record === null)
	 $record = $this->currentRecord;

      $this->parentRecord = $record->Category->Race;

      if ($checkStatus) {
	 $st = $this->parentRecord->status == \Model\App\Race::STATUS_APP;
      } else {
	 $st = $this->parentRecord->status >= \Model\App\Race::STATUS_APP;
      }

      return ($st && $this->getUser()->isAllowed($record, "edit"))
      || $this->getUser()->isAllowed($this->parentRecord, "applications");
   }

   /**
    *
    * @param \Ormion\Record $record
    * @return bool
    */
   protected function ACLdelete($record = null) {
      if ($record === null)
	 $record = $this->currentRecord;

      $this->parentRecord = $record->Category->Race;

      return ($this->parentRecord->status == \Model\App\Race::STATUS_APP && $this->getUser()->isAllowed($record, "delete"))
      || $this->getUser()->isAllowed($this->parentRecord, "applications");
   }

   public function ACLExportCsob() {
      return $this->getUser()->isAllowed($this->parentRecord, "applications");
   }

   public function ACLExportSums() {
      $account = \Model\App\Account::create();
      return $this->getUser()->isAllowed($account, "transactions");
   }

   //</editor-fold>
   //<editor-fold desc="Actions & views">


   public function renderEdit($id, $parent) {
      parent::renderEdit($id, $parent);

      $costs = array();
      foreach ($this->currentRecord->SelectedOptions as $option) {
	 $costs[$option->cost_id] = $option->option_id;
      }

      $this["editForm"]->setDefaults(array(
	  "cost" => $costs,
	  "anonymous" => ($this->currentRecord->racer_id == null),
      ));
   }
   
   public function renderList($id, $parent) {
      //parent::renderList($id, $parent); //RP doesn't have

      $this->template->race = $this->parentRecord;
   }

   public function renderPedit($id, $parent) {
      $this->renderEdit($id, $parent);
   }

   public function renderShow($id, $parent) {
      
   }

   public function renderPshow($id, $parent) {
      $this->renderShow($id, $parent);
   }

   public function actionPadd($id, $parent) {
      $this->actionAdd($id, $parent);
      $this->setView("add");
   }

   public function actionPedit($id, $parent) {
      $this->actionEdit($id, $parent);
      $this->setView("edit");
   }

   public function actionPshow($id, $parent) {
      $this->actionShow($id, $parent);
      $this->setView("show");
   }

   public function actionPlist($id, $parent) {
      $this->actionList($id, $parent);
      $this->setView("list");
   }

   public function actionListMe() {
      
   }

   public function actionShow() {
      $this->currentRecord = Entry::find($this->getParam("id"));

      if (!$this->currentRecord) {
	 throw new \Nette\Application\BadRequestException("Neexistující přihláška.", 404);
      }

      if (!$this->ACLedit(null, false)) {
	 throw new \Nette\Application\BadRequestException("Nepřístupná přihláška.", 403);
      }
   }

   /**
    * Loads race for various exports
    */
   protected function exportLoad() {
      $this->parentRecord = \Model\App\Race::find($this->getParam("parent"));
      if (!$this->parentRecord) {
	 throw new \Nette\Application\BadRequestException("Neexistující závod.", 404);
      }
   }

   public function actionExportCsob() {
      $this->exportLoad();

      if (!$this->ACLExportCsob()) {
	 throw new \Nette\Application\BadRequestException("Nedostatečné oprávnění k exportu.", 403);
      }
   }
   
   public function actionExportSums() {
      $this->exportLoad();

      if (!$this->ACLExportSums()) {
	 throw new \Nette\Application\BadRequestException("Nedostatečné oprávnění k exportu.", 403);
      }
   }

   public function renderExportCsob() {
      //registration, categrory, SINumber, racer, licence, note
      $fl = \dibi::select("u.registration, c.name AS category, e.SINumber")
	      ->select("IFNULL(e.racerName, CONCAT(u.surname, ' ', u.name)) AS racer")
	      ->select("m.licence, e.note")
	      ->from(":t:app_entry AS e")
	      ->leftJoin(":t:system_user AS u")->on("u.id = e.racer_id")
	      ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
	      ->leftJoin(":t:app_race2category AS rc")->on("rc.id = e.presentedCategory_id")
	      ->leftJoin(":t:app_category AS c")->on("c.id = rc.category_id")
	      ->where("rc.race_id = %i", $this->getParam("parent"));
      $this["exportCsob"]->setEntries($fl);
      
      if($this->getParam("download", false)){
	 $this->setView("exportCsobRaw");
      }
      
   }

//</editor-fold>
}

