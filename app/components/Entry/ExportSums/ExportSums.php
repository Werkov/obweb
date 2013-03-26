<?php

namespace OOB;

use Model\System\User;
use Model\App\Race;
use Model\App\Entry;
use Nette\Utils\Strings;

class ExportSums extends \Nette\Application\UI\Control {

   /**
    *
    * @var Race
    */
   protected $race;

   public function getRace() {
      return $this->race;
   }

   public function setRace($race) {
      $this->race = $race;
   }

   /**
    *
    * @var \Gridito\Grid
    */
   protected $grid;

   public function __construct($parent = NULL, $name = NULL) {
      parent::__construct($parent, $name);
      $this->grid = new \Gridito\Grid($this, "sums");
   }

   public function render() {
      //find out what other cost we should print
      $fl = \dibi::select("DISTINCT c.id, c.name")
	      ->from(":t:app_additionalCost AS c")
	      ->rightJoin(":t:app_additionalCostOption AS o")->on("o.cost_id = c.id")
	      ->where("o.race_id = %i", $this->race->id)
	      ->orderBy("name");
      $costs = $fl->fetchAll();

      //create dynamic query with columns
      $fl = \dibi::select("1  AS tag, 1, u.registration, IFNULL(e.racerName, CONCAT(u.name, ' ', u.surname)) AS name")
	      ->select("c.name AS category, e.SINumber")
	      ->select("rc.price, a.name AS account")
	      ->from(":t:app_entry AS e")
	      ->leftJoin(":t:system_user AS u")->on("u.id = e.racer_id")
	      ->leftJoin(":t:app_race2category AS rc")->on("rc.id = e.presentedCategory_id")
	      ->leftJoin(":t:app_category AS c")->on("c.id = rc.category_id")
	      ->leftJoin(":t:app_account AS a")->on("a.id = e.account_id")
	      ->where("rc.race_id = %i", $this->race->id);

      foreach ($costs as $cost) {
	 $fl->select("%n.name AS %n, %n.price AS %n", "co" . $cost->id, "co" . $cost->id . "_name", "co" . $cost->id, "co" . $cost->id . "_price");
	 $fl->leftJoin(":t:app_selectedOption AS %n", "so" . $cost->id)->on("%n.entry_id = e.id AND %n.cost_id = %i", "so" . $cost->id, "so" . $cost->id, $cost->id);
	 $fl->leftJoin(":t:app_additionalCostOption AS %n", "co" . $cost->id)->on("%n.id = %n.option_id AND %n.cost_id = %i", "co" . $cost->id, "so" . $cost->id, "co" . $cost->id, $cost->id);
      }
      //append running sums
      $flR = clone $fl; //running
      $flT = clone $fl; //total

      $flR->select(false);
      $flR->select("2  AS tag, 1, NULL, %s, NULL, NULL, SUM(rc.price) AS price, a.name", "Všichni");
      foreach ($costs as $cost) {
	 $flR->select("NULL, SUM(%n.price) AS %n", "co" . $cost->id, "co" . $cost->id . "_price");
      }
      $flR->groupBy("a.id");

      $fl->where("1"); //necessary to switch fluent to append after which
      $fl->unionAll($flR);

      //append total sum

      $flT->select(false);
      $flT->select("3 AS tag, 2, NULL, %s, NULL, NULL, SUM(rc.price) AS price, %s", "Všichni", 'Všechny');
      foreach ($costs as $cost) {
	 $flT->select("NULL, SUM(%n.price) AS %n", "co" . $cost->id, "co" . $cost->id . "_price");
      }

      //$fl->where("1"); //necessary to switch fluent to append after which
      $fl->unionAll($flT);

      //sort
      $fl->orderBy("2, account, 1");


      //configure columns
      $this->grid->setModel(new \Gridito\DibiFluentModel($fl));
      $this->grid->addColumn("name", "Závodník")
	      ->setRenderer(function($row, $column) {
			 if ($row->registration) {
			    echo $row->name . " (" . $row->registration . ")";
			 } else {
			    echo $row->name;
			 }
		      });
      //->setCellClass("text-right");
      $this->grid->addColumn("category", "Kategorie");
      $this->grid->addColumn("SINumber", "SI");
      $this->grid->addColumn("account", "Účet");
      $this->grid->addColumn("price", "Startovné")
	      ->setRenderer(function($row, $column) {
			 echo Helpers::currency($row->price);
		      })
	      ->setCellClass("text-right");
      foreach ($costs as $cost) {
	 $cid = $cost->id;
	 $this->grid->addColumn("co" . $cost->id . "_name", $cost->name)
		 ->setRenderer(function($row, $column) use($cid) {
			    $col = "co" . $cid . "_price";
			    echo Helpers::currency($row->{$col});
			 })
		 ->setCellClass("text-right");
      }

      $this->grid->addColumn("suma", "Suma")
	      ->setRenderer(function($row, $column) use($costs) {
			 $sum = $row->price;
			 foreach ($costs as $cost) {
			    $col = "co" . $cost->id . "_price";
			    $sum += $row->{$col};
			 }
			 echo Helpers::currency($sum);
		      })
	      ->setCellClass("text-right sumCell");


      //configure rows
      $this->grid->setRowClass(function($iterator, $row) {
		 if ($row->tag > 1) {
		    return "sumRow";
		 } else {
		    return null;
		 }
	      });
      $this->grid->setItemsPerPage(1000);

      $this->grid->render();
   }

}