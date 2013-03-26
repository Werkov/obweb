<?php

namespace AdminModule;

use \Model\Link\Category;

/**
 * @generator MScaffolder
 */
final class LinkCategoryPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Link\Category";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:link_category")
      ));


      // columns
      $grid->addColumn("name", "Název")->setSortable(true);

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add"))
	      ->setIcon("document")
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Category::create(), "add");
		      });


      $grid->addButton("sub0", "Links »")->setLink(function ($row) use($pres) {
			 return $pres->link("Link:list", array(
			     "parent" => $row->id,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->id));
		      })->setIcon("pencil")
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Category::create(), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Category::create(), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("name", "Název")
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
}

