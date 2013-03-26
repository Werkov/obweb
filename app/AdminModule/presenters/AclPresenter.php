<?php

namespace AdminModule;

use \Model\System\Acl;

/**
 * @generator MScaffolder
 */
final class AclPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\System\Acl";
   protected static $parentClass = "\Model\System\Role";
   protected static $parentColumn = "role_id";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\EGrid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("a.id AS aid, p.id AS pid, p.name AS pname, r.id AS rid, r.name AS rname, allowed, a.assertion")
			      ->from(":t:system_acl AS a")
			      ->leftJoin(":t:system_resource AS r")->on("r.id = a.resource_id")
			      ->leftJoin(":t:system_privilege AS p")->on("p.id = a.privilege_id")
			      ->where("role_id = %i", $this->parentRecord->id)
      ));

      $grid->getModel()->setPrimaryKey("a.id");
      $grid->setShowAdd(true);

      // columns
      //$grid->addColumn("id", "id")->setSortable(true);

      $f = new \Nette\Forms\Controls\SelectBox();
      $f->setItems(\dibi::select("id, name")->from(":t:system_resource")->fetchPairs("id", "name"));
      $f->setPrompt("-- Všechno --");
      $grid->addColumn("rname", "Resource")->setSortable(true)
	      ->setField("rid")
	      ->setRenderer(function ($row, $column) {
			 echo $row->rname == null ? "-- Všechno --" : $row->rname;
		      })
	      ->setControl($f);

      $f = new \Nette\Forms\Controls\SelectBox();
      $f->setItems(\dibi::select("id, name")->from(":t:system_privilege")->fetchPairs("id", "name"));
      $f->setPrompt("-- Všechno --");
      $grid->addColumn("pname", "Privilege")->setSortable(true)
	      ->setField("pid")
	      ->setRenderer(function ($row, $column) {
			 echo $row->pname == null ? "-- Všechno --" : $row->pname;
		      })
	      ->setControl($f);

      $f = new \Nette\Forms\Controls\SelectBox();
      $f->setItems(array("Y" => "Y", "N" => "N"));
      $grid->addColumn("allowed", "Allowed")->setSortable(true)
	      ->setField("allowed")
	      ->setControl($f);

      $f = new \Nette\Forms\Controls\Checkbox();
      $grid->addColumn("assertion", "Assertion")->setSortable(true)
	      ->setField("assertion")
	      ->setControl($f);

      $rid = $this->parentRecord->id;
      $pres = $this;

      $grid->setSubmitCallback(function($form) use($rid, $pres) {
		 $values = $form->getValues();

		 if ($values["editId"] == -1) {
		    $r = Acl::create(array("role_id" => $rid));
		 } else {
		    $r = Acl::find($values["editId"]);
		 }

		 if (!$r)
		    return;

		 $r->resource_id = $values["rid"];
		 $r->privilege_id = $values["pid"];
		 $r->allowed = $values["allowed"];
		 $r->assertion = $values["assertion"];
		 $r->save();

		 $pres["grid"]->flashMessage("ACL změněno.");
	      });

      // buttons
      $pres = $this;

      /* $grid->addToolbarButton("add", "Přidat")
        ->setLink($this->link("add", array("parent" => $this->parentRecord->id)))
        ->setIcon("document");



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
        return $pres->link("edit", array("id" => $row->id));
        })->setIcon("pencil"); */

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $items = array("Y" => "Y", "N" => "N",);
      $form->addSelect("allowed", "allowed")
	      ->setItems($items)
      ;

      $form->addCheckbox("assertion", "assertion")
      ;

      $items = \dibi::select("id, name")->from(":t:system_privilege")->orderBy("name")->fetchPairs("id", "name");
      $items[null] = "[Nic]";
      $form->addSelect("privilege_id", "privilege_id")
	      ->setItems($items)
      ;

      $items = \dibi::select("id, name")->from(":t:system_resource")->orderBy("name")->fetchPairs("id", "name");
      $items[null] = "[Nic]";
      $form->addSelect("resource_id", "resource_id")
	      ->setItems($items)
      ;

      $items = \dibi::select("id, name")->from(":t:system_role")->orderBy("name")->fetchPairs("id", "name");
      $form->addSelect("role_id", "role_id")
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

