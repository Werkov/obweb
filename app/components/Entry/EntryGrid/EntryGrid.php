<?php

namespace OOB;

use \Nette\Forms\Form;
use \Model\App\Entry;
use \Model\App\Race;

class EntryGrid {

   /**
    *
    * @param \Nette\Application\UI\Control $parent
    * @param string $name
    * @param \Model\System\User	$user	  user who is viewing the table (it's used for correct ACL and when race is not set, only his entries are shown)
    * @param Race $race	  race for which show the entries
    * @param bool $readonly		  allows to edit/add entries, grid must be then part of compatible RecordPresenter
    * @return \Nette\Application\UI\Form
    */
   public static function create($parent, $name, $publicMode = true, $race = null, $readonly = true) {
      Race::updateStatus();
      $grid = new \Gridito\Grid($parent, $name);
      $user = $parent->getUser();
      if ($publicMode)
	 $p = "p";
      else
	 $p="";
      // model
      $fl = \dibi::select("e.id AS eid, IFNULL(racerName, CONCAT(u.name, ' ', u.surname)) AS name, u.registration AS registration, " .
			      "e.SINumber as SI, c.name AS category, racer_id, e.presentedCategory_id") //cena??
		      ->from(":t:app_entry AS e")
		      ->leftJoin(":t:app_race2category AS rc")->on("rc.id = e.presentedCategory_id")
		      ->leftJoin(":t:app_category AS c")->on("c.id = rc.category_id")
		      ->leftJoin(":t:system_user AS u")->on("u.id = e.racer_id");
      if ($race) {
	 $fl->where("rc.race_id = %i", $race->id);
	 $fl->orderBy("u.registration");
      } else {
	 $fl->select("e.datetime AS ts, r.name AS rname");
	 $fl->leftJoin(":t:app_race AS r")->on("rc.race_id = r.id");
	 $fl->where("e.racer_id = %i", $user->getId());
	 $fl->orderBy("ts DESC");
      }

      $grid->setModel(new \Gridito\DibiFluentModel($fl));

      $grid->getModel()->setPrimaryKey("e.id");

      // columns
      if ($race) {
	 $grid->addColumn("name", "Jméno")->setSortable(true);
      } else {
	 $grid->addColumn("ts", "Datum")->setSortable(true)
		 ->setRenderer(function($row, $column) {
			    echo Helpers::mydate($row->ts);
			 });
	 ;
	 $grid->addColumn("rname", "Závod")->setSortable(true);
      }

      $grid->addColumn("registration", "Registrace")->setSortable(true);
      $grid->addColumn("SI", "Číslo SI")->setSortable(true);
      $grid->addColumn("category", "Kategorie")->setSortable(true);

      // buttons
      $pres = $parent;
      if (!$readonly) {
	 if ($race) {
	    $grid->addToolbarButton("add", "Přidat")
		    ->setLink($pres->link($p . "add", array("parent" => $race->id)))
		    ->setVisible(function($row) use($pres, $user, $race) {
			       $entry = Entry::create();
			       return ($race->status == Race::STATUS_APP && $user->isAllowed($entry, "add")) ||
			       $user->isAllowed($race, "applications");
			       ;
			    })
		    ->setIcon("document");
	 }


	 $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres, $p) {
			    return $pres->link($p . "edit", array("id" => $row->eid));
			 })->setIcon("pencil")
		 ->setVisible(function($row) use($pres, $user, $race) {
			    return ($race->status == Race::STATUS_APP && $user->isAllowed(Entry::create(array(
					"racer_id" => $row->racer_id,
				    )), "edit")) ||
			    $user->isAllowed($race, "applications");
			    ;
			 });


	 $grid->addButton("delete", "Smazat")
		 ->setHandler(callback($pres, "deleteRecord"))
		 ->setIcon("trash")
		 ->setAjax(true)->setShowText(false)
		 ->setVisible(function($row) use($pres, $user, $race) {
			    return ($race->status == Race::STATUS_APP && $user->isAllowed(Entry::create(array(
					"racer_id" => $row->racer_id,
				    )), "delete")) ||
			    $user->isAllowed($race, "applications");
			 })
		 ->setConfirmationQuestion(callback($pres, "ttDeleteQuestion"));
      }
      $grid->addButton("show", "Podrobnosti")->setLink(function ($row) use($pres, $p) {
			 return $pres->link($p . "show", array("id" => $row->eid));
		      })->setIcon("zoomin")
	      ->setVisible(function($row) use($pres, $user, $race) {
			 return ($user->isAllowed(Entry::create(array(
				     "racer_id" => $row->racer_id,
				 )), "edit")) ||
			 $user->isAllowed($race, "applications");
		      });
      //settings
      $grid->setItemsPerPage(\RecordPresenter::IPP);

      return $grid;
   }

}