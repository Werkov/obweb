<?php

namespace AdminModule;

use \Model\Forum\Thread;

/**
 * @generator MScaffolder
 */
final class ThreadPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Forum\Thread";
   protected static $parentClass = "\Model\Forum\Topic";
   protected static $parentColumn = "topic_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:forum_thread")
			      ->where("topic_id = %i", $this->getParam("parent"))
      ));


      // columns
      $grid->addColumn("name", "Název")->setSortable(true);
      $grid->addColumn("created", "Založeno")->setSortable(true);

      // buttons
      $pres = $this;

      /* $grid->addToolbarButton("add", "Přidat")
        ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
        ->setIcon("document"); */


      $grid->addButton("sub0", "Příspěvky »")->setLink(function ($row) use($pres) {
			 return $pres->link("Post:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->id));
		      })->setIcon("pencil")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Thread::create(), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Thread::create(), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("name", "Název")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 40);

      $form->addDatePicker("created", "Založeno")
	      ->addRule(\Nette\Forms\Form::FILLED);

      $form->addCheckbox("public", "Veřejné");


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>
}

