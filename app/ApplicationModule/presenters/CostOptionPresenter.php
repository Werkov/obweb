<?php

namespace ApplicationModule;

use Model\App\AdditionalCostOption;

/**
 * @generator MScaffolder
 */
final class CostOptionPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\App\AdditionalCostOption";
   protected static $parentClass = "\Model\App\Race";
   protected static $parentColumn = "race_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\EGrid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("co.id AS coid, co.name AS name, co.price AS price, c.name AS cname, co.cost_id AS cid")
			      ->from(":t:app_additionalCostOption AS co")
			      ->leftJoin(":t:app_additionalCost AS c")->on("co.cost_id = c.id")
			      ->where("race_id = %i", $this->getParam("parent"))
      ));

      $grid->getModel()->setPrimaryKey("co.id");


      // columns

      $f = new \Nette\Forms\Controls\TextInput();
      $grid->addColumn("name", "Název volby")->setSortable(true)
	      ->setField("name")
	      ->setControl($f->addRule(\Nette\Forms\Form::FILLED));

      $f = new \Nette\Forms\Controls\TextInput();
      $grid->addColumn("price", "Cena")->setSortable(true)
	      ->setRenderer(function($row, $column) {
			 echo \OOB\Helpers::currency($row->price);
		      })
	      ->setCellClass("text-right")
	      ->setField("price")
	      ->setControl($f->addRule(\Nette\Forms\Form::FILLED)
		      ->addRule(\Nette\Forms\Form::FLOAT));

      $f = new \Nette\Forms\Controls\SelectBox();
      $f->setItems(\dibi::select("id, name")->from(":t:app_additionalCost")->orderBy('name')->fetchPairs("id", "name"));
      $grid->addColumn("cname", "Poplatek")->setSortable(true)
	      ->setField("cid")
	      ->setControl($f);


      // buttons
      $pres = $this;
      $grid->setShowAdd(true);




      /*      $grid->addToolbarButton("add", "Přidat")
        ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
        ->setIcon("document");



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
        return $pres->link("edit", array("id" => $row->id));
        })->setIcon("pencil"); */

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

      $rid = $this->parentRecord->id;
      $grid->setSubmitCallback(function($form) use($rid, $pres) {
		 $values = $form->getValues();

		 if ($values["editId"] == -1) {
		    $r = AdditionalCostOption::create(array("race_id" => $rid));
		 } else {
		    $r = AdditionalCostOption::find($values["editId"]);
		 }

		 if (!$r)
		    return;

		 $r->cost_id = $values["cid"];
		 $r->price = $values["price"];
		 $r->name = $values["name"];
		 $r->save();

		 $pres["grid"]->flashMessage("Poplatek uložen.");
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

