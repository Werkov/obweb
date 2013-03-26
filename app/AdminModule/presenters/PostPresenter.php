<?php

namespace AdminModule;


use \Model\Forum\Post;

/**
* @generator MScaffolder
*/
final class PostPresenter extends \RecordPresenter {

	// <editor-fold desc="Fields">
	protected static $class = "\Model\Forum\Post";
	protected static $parentClass = "\Model\Forum\Thread";
	protected static $parentColumn = "thread_id";

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name)
   {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
	 ->from(":t:forum_post")
	 ->where("thread_id = %i", $this->getParam("parent"))
      ));


      // columns
      $grid->addColumn("id", "id")->setSortable(true);

      // buttons
      $pres = $this;

      /*$grid->addToolbarButton("add", "Přidat")
	     ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	     ->setIcon("document");*/



      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres)
      {
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

   protected function createComponentForm($name, $new = false)
   {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addTextArea("text", "text")
	     ->addRule(\Nette\Forms\Form::FILLED)
;

      $form->addText("author", "author")
	     ->addRule(\Nette\Forms\Form::FILLED)
	     ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50)
;

      $form->addText("IP", "IP")
	     ->addRule(\Nette\Forms\Form::FILLED)
	     ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 15)
;

      $form->addText("datetime", "datetime")
	     ->addRule(\Nette\Forms\Form::FILLED)
;

      $form->addText("lft", "lft")
	     ->addRule(\Nette\Forms\Form::FILLED)
	     ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 11)
;

      $form->addText("rgt", "rgt")
	     ->addRule(\Nette\Forms\Form::FILLED)
	     ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 11)
;

      $form->addText("depth", "depth")
	     ->addRule(\Nette\Forms\Form::FILLED)
	     ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 6)
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

