<?php

namespace PersonalModule;

use \Model\Doc\Directory;

/**
 * @generator MScaffolder
 */
final class DocDirectoryPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Doc\Directory";
   protected static $parentClass = "\Model\Doc\Directory";
   protected static $parentColumn = "parent_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:doc_directory")
			      ->where("%and", array("parent_id" => $this->getParam("parent")))
      ));


      // columns
      $grid->addColumn("name", "Název")->setSortable(true);

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Directory::create(), "add");
		      });


      $grid->addButton("sub0", "Podsložky »")->setLink(function ($row) use($pres) {
			 return $pres->link("DocDirectory:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);
      $grid->addButton("sub1", "Soubory »")->setLink(function ($row) use($pres) {
			 return $pres->link("File:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->id));
		      })->setIcon("pencil")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Directory::create(), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Directory::create(), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("name", "Název")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 60);

      $form->addCheckbox("public", "Veřejná složka");


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   protected function checkParent($parent) {
      if ($parent !== null) {
	 parent::checkParent($parent);
      }
   }

   protected function setRelations(\Nette\Application\UI\Form $form) {
      if ($this->getAction() == "add") {
	 $this->currentRecord->created = new \DateTime();
      }
   }

   // </editor-fold>
}

