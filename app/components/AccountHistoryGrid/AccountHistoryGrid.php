<?php

namespace OOB;

use \Nette\Forms\Form;
use Model\App\Account;

class AccountHistoryGrid {

   /**
    *
    * @param \Nette\Application\UI\Control $parent
    * @param string $name
    * @param Account $account		  account for which show the table
    * @param bool $readonly		  allows to enter transaction when true (via signal deposit and withdrawal)
    * @return \Nette\Application\UI\Form
    */
   public static function create($parent, $name, $account, $readonly = true) {
      $grid = new \Gridito\Grid($parent, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(
			      \dibi::select("t.datetime AS ts, t.amount AS amount, t.note AS note, CONCAT(u.name, ' ', u.surname) AS user")
			      ->from(":t:app_accountTransaction AS t")
			      ->leftJoin(":t:system_user AS u")->on("u.id = t.user_id")
			      ->where("t.account_id = %i", $account->id)
			      ->unionAll(
				      \dibi::select("e.datetime AS ts, -1 * (rc.price + (" .
					      \dibi::select("IFNULL(SUM(co.price), 0)")
					      ->from(":t:app_selectedOption AS so")
					      ->leftJoin(":t:app_additionalCostOption AS co")->on("co.id = so.option_id")
					      ->where("so.entry_id = e.id")
					      ->__toString() . ")) AS amount")
				      ->select("r.name AS note, IFNULL(e.racerName, CONCAT(u.name, ' ', u.surname)) AS user")
				      ->from(":t:app_entry AS e")
				      ->leftJoin(":t:system_user AS u")->on("u.id = e.racer_id")
				      ->leftJoin(":t:app_race2category AS rc")->on("rc.id = e.presentedCategory_id")
				      ->leftJoin(":t:app_race AS r")->on("r.id = rc.race_id")
				      ->where("e.account_id = %i", $account->id))
	      //->orderBy("ts")
      ));
      $grid->getModel()->setSorting("ts", "DESC");


      // columns
      $grid->addColumn("ts", "Datum")->setSortable(true)->
	      setRenderer(function($row, $column) {
			 echo Helpers::mydate($row->ts);
		      });
      $grid->addColumn("amount", "Částka")->setSortable(true)
	      ->setRenderer(function($row, $column) {
			 echo Helpers::currency($row->amount);
		      })
	      ->setCellClass("text-right");
      $grid->addColumn("note", "Poznámka")->setSortable(true);
      $grid->addColumn("user", "Uživatel")->setSortable(true);

      // buttons
      $pres = $parent;
      if (!$readonly) {
	 $grid->addToolbarButton("deposit", "Vklad")
		 ->setLink($pres->link("deposit", array("parent" => $pres->getParam("parent"))))
		 ->setIcon("document");

	 $grid->addToolbarButton("withdrawal", "Výběr")
		 ->setLink($pres->link("withdrawal", array("parent" => $pres->getParam("parent"))))
		 ->setIcon("document");
      }


      //settings
      $grid->setItemsPerPage(\RecordPresenter::IPP);

      return $grid;
   }

}