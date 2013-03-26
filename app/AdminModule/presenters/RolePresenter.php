<?php

namespace AdminModule;

use \Model\System\Role;

/**
 * @generator MScaffolder
 */
final class RolePresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\System\Role";
   protected static $parentClass = "\Model\System\Role";
   protected static $parentColumn = "parent_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:system_role")
			      ->where("%and", array("parent_id" => $this->getParam("parent")))
      ));


      // columns
      $grid->addColumn("name", "name")->setSortable(true);
      $grid->addColumn("desc", "Popis");

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document");


      $grid->addButton("sub0", "Roles »")->setLink(function ($row) use($pres) {
			 return $pres->link("Role:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("sub1", "ACL »")->setLink(function ($row) use($pres) {
			 return $pres->link("Acl:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
		 return $pres->link("edit", array("id" => $row->id));
	      })->setIcon("pencil");

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("name", "name")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50)
      ;

      $form->addText("table", "table")
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50)
      ;

      $form->addTextArea("desc", "Popis")
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 255)
      ;


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>
   protected function checkParent($parent) {
      if ($parent !== null) {
	 parent::checkParent($parent);
      }
   }

}

