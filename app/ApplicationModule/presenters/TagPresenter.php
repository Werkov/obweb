<?php

namespace ApplicationModule;

use \Model\App\Tag;

/**
 * @generator MScaffolder
 */
final class TagPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\App\Tag";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:app_tag")
      ));


      // columns
      $grid->addColumn("name", "Název")->setSortable(true);
      $grid->addColumn("color", "Barva")
	      ->setSortable(true)
	      ->setRenderer(function($row, $column) {
			 echo "<span style=\"background-color:#" . \htmlspecialchars($row->color) . "\">#" . \htmlspecialchars($row->color) . "</span>";
		      });

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add"))
	      ->setIcon("document");



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

      $form->addText("name", "Název")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 30);

      $form->addColorPicker("color", "Barva")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 7);


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>
}

