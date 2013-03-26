<?php

namespace PersonalModule;

use \Model\Doc\File;

/**
 * @generator MScaffolder
 */
final class FilePresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Doc\File";
   protected static $parentClass = "\Model\Doc\Directory";
   protected static $parentColumn = "directory_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("f.id as fid, CONCAT(f.name, '.', t.extension) AS name, t.icon, f.size, f.published, CONCAT(u.name, ' ', u.surname) AS author, u.id AS uid")
			      ->from(":t:doc_file AS f")
			      ->leftJoin(":t:doc_filetype AS t")->on("t.id = f.filetype_id")
			      ->leftJoin(":t:system_user AS u")->on("u.id = f.author_id")
			      ->where("directory_id = %i", $this->getParam("parent"))
      ));
      $grid->getModel()->setPrimaryKey("f.id");

      // columns
      $grid->addColumn("name", "Název")->setSortable(true);
      $grid->addColumn("published", "Nahráno")->setSortable(true);
      $grid->addColumn("author", "Nahrál")->setSortable(true);
      $grid->addColumn("size", "Velikost")->setSortable(true)
	      ->setRenderer(function($row) {
			 echo \Nette\Templating\DefaultHelpers::bytes($row->size);
		      });

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(File::create(), "add");
		      });



      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->fid));
		      })->setIcon("pencil")
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(File::create(array("author_id" => $row->uid)), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(File::create(array("author_id" => $row->uid)), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addUpload("file", "Soubor")
	      ->addRule(\Nette\Forms\Form::FILLED);

      $form->addText("name", "Název")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 255);


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   protected function setRelations(\Nette\Application\UI\Form $form) {
      $this->currentRecord->author_id = $this->getUser()->getId();
      $this->currentRecord->published = new \DateTime();
   }

   protected function saveRecord(\Nette\Application\UI\Form $form) {
      $values = $form->getValues();
      $this->currentRecord->saveWithFile($values["file"]);
   }

   // </editor-fold>

   protected function checkParent($parent) {
      //check existence
      $this->parentRecord = \call_user_func(static::$parentClass . "::find", ($parent === null) ? 0 : $parent);
      if (!$this->parentRecord) {
	 throw new \Nette\Application\BadRequestException(static::ttParentNotFound(), 404);
      }

      //check ACL
      if (!$this->getUser()->isAllowed($this->parentRecord, "list")) {
	 throw new \Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->parentRecord, "edit"), 403);
      }
   }

}

