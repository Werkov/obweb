<?php

namespace OrganizationModule;

use \Model\Org\Information;

/**
 * @generator MScaffolder
 */
final class InformationPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Org\Information";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\EGrid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("id, name, html")
			      ->from(":t:org_information")
			      ->orderBy("name")
      ));

      $grid->getModel()->setPrimaryKey("id");

      // columns

      $f = new \Nette\Forms\Controls\TextInput();
      $grid->addColumn("name", "Název")->setSortable(true)
	      ->setField("name")
	      ->setControl($f->addRule(\Nette\Forms\Form::FILLED));

      $f = new \Nette\Forms\Controls\Checkbox();
      $grid->addColumn("html", "Povolit HTML")->setSortable(true)
	      ->setField("html")
	      ->setControl($f);


      // buttons
      $pres = $this;
      $grid->setShowAdd(true);


      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

      $grid->setSubmitCallback(function($form) use($pres) {
		 $values = $form->getValues();

		 if ($values["editId"] == -1) {
		    $r = Information::create();
		    if (!$pres->getUser()->isAllowed($r, "add"))
		       throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "add"), 403);
		 } else {
		    $r = Information::find($values["editId"]);
		    if (!$pres->getUser()->isAllowed($r, "edit"))
		       throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "edit"), 403);
		 }

		 if (!$r)
		    return;

		 $r->name = $values["name"];
		 $r->html = $values["html"];
		 $r->save();

		 $pres["grid"]->flashMessage("Kousek informace uložen.");
	      });
      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      throw new \Nette\Application\ApplicationException("Not implementd.");
   }

   public function actionAdd($id, $parent) {
      throw new \Nette\Application\BadRequestException("Not implemented.", 404);
   }

   public function actionEdit($id, $parent) {
      throw new \Nette\Application\BadRequestException("Not implemented.", 404);
   }

   public function actionList($id, $parent) {
      //$this->processSignal();
      parent::actionList($id, $parent);
   }

   // </editor-fold>
}

