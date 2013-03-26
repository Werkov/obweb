<?php

namespace AdminModule;

use \Model\Survey\Answer;

/**
 * @generator MScaffolder
 */
final class AnswerPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Survey\Answer";
   protected static $parentClass = "\Model\Survey\Survey";
   protected static $parentColumn = "survey_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:survey_answer")
			      ->where("survey_id = %i", $this->getParam("parent"))
      ));


      // columns
      $grid->addColumn("text", "text"); //->setSortable(true);
      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document")
	      ->setVisible($this->parentRecord->getVotes() == 0);



      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->id));
		      })->setIcon("pencil")
	      ->setVisible($this->parentRecord->getVotes() == 0);

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible($this->parentRecord->getVotes() == 0);

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("text", "Text odpovědi")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50)
      ;


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>

   public function actionAdd($id, $parent) {
      parent::actionAdd($id, $parent);

      if ($this->parentRecord->getVotes() > 0)
	 throw new \Nette\Application\ApplicationException("Nelze upravovat anketu s hlasy.");
   }

   public function actionEdit($id, $parent) {
      parent::actionEdit($id, $parent);

      if ($this->parentRecord->getVotes() > 0)
	 throw new \Nette\Application\ApplicationException("Nelze upravovat anketu s hlasy.");
   }

   public function deleteRecord($id) {
      if ($this->parentRecord->getVotes() > 0)
	 throw new \Nette\Application\ApplicationException("Nelze upravovat anketu s hlasy.");
   }

}

