<?php

namespace PersonalModule;

use \Model\Gallery\Gallery;

/**
 * @generator MScaffolder
 */
final class GalleryPresenter extends \RecordPresenter {

// <editor-fold desc="Fields">
   protected static $class = "\Model\Gallery\Gallery";

//protected static $parentClass = "\Model\Gallery\Directory";
//protected static $parentColumn = "directory_id";
// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

// model
      $fluent = \dibi::select("g.id as gid, g.name AS name, g.published AS published, d.name AS directory")
		      ->from(":t:gallery_gallery AS g")
		      ->leftJoin(":t:gallery_directory AS d")->on("d.id = g.directory_id")
		      ->orderBy("published DESC");

      if ($this->getParam("parent") !== null) {
	 $fluent->where("directory_id = %i", $this->getParam("parent"));
      }

      $grid->setModel(new \Gridito\DibiFluentModel($fluent));
      $grid->getModel()->setPrimaryKey("g.id");


// columns
      $grid->addColumn("published", "Datum")->setSortable(true);
      $grid->addColumn("name", "Název")->setSortable(true);
      $grid->addColumn("directory", "Složka")->setSortable(true);

// buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
	      ->setIcon("document")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Gallery::create(), "add");
		      });


      $grid->addButton("sub0", "Fotografie »")->setLink(function ($row) use($pres) {
			 return $pres->link("Photo:list", array(
			     "parent" => $row->gid,
			 ));
		      })->setIcon("")
	      ->setAjax(false);

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->gid));
		      })->setIcon("pencil")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Gallery::create(), "edit");
		      });


      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Gallery::create(), "delete");
		      });


//settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("name", "Název")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);

      $form->addTextArea("desc", "Popisek");

      $items = \dibi::select("id, name")->from(":t:gallery_directory")->orderBy("name")->fetchPairs("id", "name");
      $form->addSelect("directory_id", "Složka")
	      ->setItems($items);

      $form->addCheckbox("public", "Veřejná galerie");


      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   protected function createComponentUploadForm($name) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addMultipleFileUpload("upload", "Fotografie", 30)
	      ->addRule("MultipleFileUpload::validateFilled", "Musíte odeslat alespoň jeden soubor!");


      $form->addSubmit('save', 'Nahrát');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'uploadFormSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

// </editor-fold>

   protected function setRelations(\Nette\Application\UI\Form $form) {
      if ($this->getAction() == "add") {
	 $this->currentRecord->published = new \DateTime();
      }
   }

   public function actionUpload($id) {
      if (!Gallery::find((int) $id)) {
	 throw new \Nette\Application\BadRequestException("Neexistující galerie.");
      }
   }

   public function uploadFormSubmitted(\Nette\Application\UI\Form $form) {
      $data = $form->getValues();

      foreach ($data["upload"] as $file) {
	 try {
	    $photo = \Model\Gallery\Photo::create();
	    $photo->gallery_id = $this->getParam("id");
	    $photo->author_id = $this->getUser()->getId();
	    $photo->published = new \DateTime();

	    $photo->saveWithFile($file);

	    $this->flashMessage("Soubor $file->name byl uložen.");
	 } catch (\Exception $exc) {
	    $this->flashMessage("Soubor $photoFile->name nebyl uložen.", \BasePresenter::FLASH_ERROR);
	 }
      }

      $this->redirect("Photo:list", array("parent" => $this->getParam("id")));
   }

}

