<?php

namespace AdminModule;


use \Model\Survey\Survey2user;

/**
 * NOTE: useless!
* @generator MScaffolder
*/
final class Survey2userPresenter extends \RecordPresenter {

	// <editor-fold desc="Fields">
	protected static $class = "\Model\Survey\Survey2user";
	protected static $parentClass = "\Model\Survey\Survey";
	protected static $parentColumn = "survey_id";

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name)
   {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
	 ->from(":t:survey_survey2user")
	 ->where("survey_id = %i", $this->getParam("parent"))
      ));


      // columns
      $grid->addColumn("survey_id", "survey_id")->setSortable(true);

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	     ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	     ->setIcon("document");



      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres)
      {
	    return $pres->link("edit", array("id" => $row->survey_id));
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

   protected function createComponentForm($name, $new = false)
   {
      $form = new \Nette\Application\UI\Form($this, $name);

      $items = \dibi::select("id, name")->from(":t:system_user")->orderBy("name")->fetchPairs("id", "name");
      $form->addSelect("user_id", "user_id")
	     ->setItems($items)
	     ;

      $items = \dibi::select("id, id")->from(":t:survey_answer")->orderBy("id")->fetchPairs("id", "id");
      $form->addSelect("answer_id", "answer_id")
	     ->setItems($items)
	     ;


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
      }

   // </editor-fold>
   }

