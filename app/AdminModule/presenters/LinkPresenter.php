<?php

namespace AdminModule;

use \Model\Link\Link;

/**
 * @generator MScaffolder
 */
final class LinkPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Link\Link";
   protected static $parentClass = "\Model\Link\Category";
   protected static $parentColumn = "category_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
			      ->from(":t:link_link")
			      ->where("category_id = %i", $this->getParam("parent"))
      ));


      // columns
      $grid->addColumn("name", "Odkaz")->setSortable(true)
	      ->setRenderer(function($row, $col) {
			 echo \Nette\Utils\Html::el("a")->href($row->URL)->setText($row->name);
		      });


      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Link::create(), "add");
		      });



      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->id));
		      })->setIcon("pencil")
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Link::create(), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Link::create(), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("URL", "URL")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 2048);

      $form->addText("name", "name")
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 70);

      $form->addTextArea("desc", "desc");


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>

   protected function setRelations(\Nette\Application\UI\Form $form) {
      if (substr($this->currentRecord->URL, 0, 7) != "http://") {
	 $this->currentRecord->URL = "http://" . $this->currentRecord->URL;
      }
   }

}

