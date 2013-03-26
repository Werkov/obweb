<?php

/**
 * Description of RecordPresenter
 *
 * @author michal
 */
abstract class RecordPresenter extends AuthenticatedPresenter {

   /**
    * (non-phpDoc)
    *
    * @see Nette\Application\Presenter#startup()
    */
   protected function startup() {
      parent::startup();
   }

   /**
    * Items per page in Gridito
    */
   const IPP = 20;

   // <editor-fold desc="Variables">
   /**
    * @var Ormion\Record
    */
   protected $currentRecord;
   /**
    * @var Ormion\Record
    */
   protected $parentRecord = null;
   /**
    * @var string
    */
   protected static $class;
   /**
    * @var string
    */
   protected static $parentClass = null;
   /**
    * @var string
    */
   protected static $parentColumn = null;

   // </editor-fold>
   // <editor-fold desc="Actions">
   public function actionList($id, $parent) {
      //canonicalize
      if ($id !== null) {
	 $this->redirect("this", array("id" => null, "parent" => $parent));
      }

      $this->checkParent($parent);

      //check ACL
      $this->currentRecord = \call_user_func(static::$class . "::create");
      //assign parent
      if ($this->parentRecord) {
         $this->currentRecord->{static::$parentColumn} = $this->parentRecord->getPrimary();
      }
      if (!$this->ACLlist($this->currentRecord)) {
	 throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess(null, "list"), 403);
      }
   }

   public function actionAdd($id, $parent) {
      //canonicalize
      if ($id !== null) {
	 $this->redirect("this", array("id" => null, "parent" => $parent));
      }

      $this->checkParent($parent);

      //create current record
      $this->currentRecord = \call_user_func(static::$class . "::create");
      
      //assign parent
      if ($this->parentRecord) {
         $this->currentRecord->{static::$parentColumn} = $this->parentRecord->getPrimary();
      }

      //check ACL
      if (!$this->ACLadd()) {
	 throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->currentRecord, "add"), 403);
      }
   }

   public function actionEdit($id, $parent) {
      //canonicalize - not used
      //check parent - not used
      //TODO: make clear how to do it when editing child record without edit privilegy to parent, now possible
      //create current record
      $this->currentRecord = \call_user_func(static::$class . "::find", ($id === null) ? 0 : $id);

      //check existence
      if (!$this->currentRecord) {
	 throw new Nette\Application\BadRequestException(static::ttRecordNotFound(), 404);
      }
      //load parent
      if (static::$parentColumn !== null) {
	 $this->parentRecord = \call_user_func(static::$parentClass . "::find", $this->currentRecord->{static::$parentColumn});
      }
      //check ACL
      if (!$this->ACLedit()) {
	 throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->currentRecord, "edit"), 403);
      }
   }

   // </editor-fold>
   // <editor-fold desc="Render views">
   public function renderEdit($id, $parent) {
      $this["editForm"]->setDefaults($this->currentRecord);

      //get fresh'n'cool hash (needed in case of form reload)
      $record = \call_user_func(static::$class . "::find", $this->currentRecord->getPrimary());
      //set it
      $this["editForm"]["occ_hash"]->setValue($record->getHash());
   }

   // </editor-fold>
   // <editor-fold desc="Signal handlers">
   public function deleteRecord($id) {
      $record = \call_user_func(static::$class . "::find", ($id === null) ? 0 : $id);

      if (!$record) {
	 $this->flashMessage(static::ttRecordNotFound(), BasePresenter::FLASH_ERROR);
      } else {
	 if (static::$parentColumn !== null) {
	    $this->checkParent($record->{static::$parentColumn});
	 }

	 if (!$this->ACLdelete($record)) {
	    throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess($record, "delete"), 403);
	 }

	 try {
	    $record->delete();
	    $this->flashMessage(static::ttRecordDeleted($record), BasePresenter::FLASH_OK);
	 } catch (DibiDriverException $e) {
	    if ($e->getCode() == 1451) {
	       $this->flashMessage(static::ttRecordNotDeleted($record), BasePresenter::FLASH_ERROR);
	    } else {
	       throw $e;
	    }
	 }
      }
   }

   protected function shiftRecord($id, $step) {
      $record = \call_user_func(static::$class . "::find", $id);
      if (!$record) {
	 $this->flashMessage(static::ttRecordNotFound(), BasePresenter::FLASH_ERROR);
      }

      if (static::$parentColumn !== null) {
	 $this->checkParent($record->{static::$parentColumn});
      }

      if (!$this->ACLedit($record)) {
	 throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess($record, "delete"), 403);
      }

      $record->order += $step;
      $record->save();
   }

   public function shiftDownRecord($id) {
      $this->shiftRecord($id, 1);
   }

   public function shiftUpRecord($id) {
      $this->shiftRecord($id, -1);
   }

   public function formSubmitted(Nette\Application\UI\Form $form) {
      if ($form['save']->isSubmittedBy()) {
	 //uložit


	 $this->currentRecord->setValues($form->getValues());


	 if ($this->getAction() == "add" && static::$parentColumn !== null) {
	    $this->currentRecord->{static::$parentColumn} = $this->getParam("parent");
	 }


	 try {
	    $this->setRelations($form);

	    $this->saveRecord($form);

	    if ($this->getAction() == "add") {
	       $message = static::ttRecordAdded($this->currentRecord);
	    } elseif ($this->getAction() == "edit") {
	       $message = static::ttRecordEdited($this->currentRecord);
	    } else {
	       $message = static::ttRecordChanged($this->currentRecord);
	    }

	    $this->flashMessage($message, BasePresenter::FLASH_OK);
	 } catch (ModelException $e) {
	    if ($e->getCode() == ModelException::CODE_CONCURRENCY_ISSUE) {
	       $form->addError(static::ttConcurrencyIssue($this->currentRecord));
	    } else {
	       $form->addError($e->getMessage());
	    }

	    return;
	 } catch (Nette\Application\ApplicationException $e) {
	    $form->addError($e->getMessage());
	    return;
	 }
      }

      if (static::$parentClass) {
	 $this->redirect("list", array("parent" => ($this->getParam("parent") ? $this->getParam("parent") : $this->getParentValue())));
      } else {
	 $this->redirect("list");
      }
   }

   protected function saveRecord(Nette\Application\UI\Form $form) {
      $this->currentRecord->save($form["occ_hash"]->getValue());
   }

   protected function getParentValue() {
      return $this->currentRecord->{static::$parentColumn};
   }

   protected function setRelations(Nette\Application\UI\Form $form) {
      
   }

// </editor-fold>
   // <editor-fold desc="Component factories">
   protected abstract function createComponentGrid($name);

   protected abstract function createComponentForm($name, $new = false);

   protected function createComponentEditForm($name) {
      return $this->createComponentForm($name, false);
   }

   protected function createComponentAddForm($name) {
      return $this->createComponentForm($name, true);
   }

// </editor-fold>
   // <editor-fold desc="UI texts">
   /**
    *
    * @param DibiRow $row
    * @return string
    */
   public static function ttDeleteQuestion($row) {
      return "Vážně chcete smazat záznam?";
   }

   /**
    * @return string
    */
   public static function ttParentNotFound() {
      return "Nenalezen rodič.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @param string $privilegy
    * @return string
    */
   public static function ttUnauthorizedAccess($record, $privilegy) {
      return "Nepovolený přístup.";
   }

   /**
    *
    * @return string
    */
   public static function ttRecordNotFound() {
      return "Záznam nenalezen.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttRecordDeleted($record) {
      return "Záznam smazán.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttRecordNotDeleted($record) {
      return "Záznam nesmazán.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttRecordAdded($record) {
      return "Záznam přidán.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttRecordEdited($record) {
      return "Záznam upraven.";
   }

   /**
    * Called when no actin (edit/add) matches.
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttRecordChanged($record) {
      return "Záznam upraven.";
   }

   /**
    *
    * @param Ormion\Record $record
    * @return string
    */
   public static function ttConcurrencyIssue($record) {
      return "Záznam byl po dobu editace upraven někým jiným.";
   }

   // </editor-fold>
   // <editor-fold desc="Utils">

   /**
    * @throws Nette\Application\BadRequestException
    */
   protected function checkParent($parent) {
      if (static::$parentClass !== null) {
	 //check existence
	 $this->parentRecord = \call_user_func(static::$parentClass . "::find", ($parent === null) ? 0 : $parent);
	 if (!$this->parentRecord) {
	    throw new Nette\Application\BadRequestException(static::ttParentNotFound(), 404);
	 }

	 //check ACL
	 if (!$this->getUser()->isAllowed($this->parentRecord, "edit")) {
	    throw new Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->parentRecord, "edit"), 403);
	 }
      }
   }

   /**
    *
    * @param Ormion\Record $record
    * @return bool
    */
   protected function ACLadd($record = null) {

      if ($record === null)
	 $record = $this->currentRecord;
      return $this->getUser()->isAllowed($record, "add");
   }

   /**
    *
    * @param Ormion\Record $record
    * @return bool
    */
   protected function ACLedit($record = null) {
      if ($record === null)
	 $record = $this->currentRecord;

      return $this->getUser()->isAllowed($record, "edit");
   }

   /**
    *
    * @param Ormion\Record $record
    * @return bool
    */
   protected function ACLdelete($record = null) {
      if ($record === null)
	 $record = $this->currentRecord;

      return $this->getUser()->isAllowed($record, "delete");
   }

   /**
    *
    * @param Ormion\Record $record
    * @return bool
    */
   protected function ACLlist($record = null) {
      if ($record === null)
	 $record = $this->currentRecord;

      return $this->getUser()->isAllowed($record, "list");
   }

   // </editor-fold>
}