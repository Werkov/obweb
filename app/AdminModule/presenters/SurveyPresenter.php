<?php

namespace AdminModule;

use \Model\Survey\Survey;

/**
 * @generator MScaffolder
 */
final class SurveyPresenter extends \RecordPresenter {

   // <editor-fold desc="Fields">
   protected static $class = "\Model\Survey\Survey";

   // </editor-fold>
   // <editor-fold defaultstate="collapsed" desc="Components">


   protected function createComponentGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("s.id AS sid, s.question, s.start, s.end, COUNT(u.user_id) AS [votes], COUNT(a.id) AS [poss]")
			      ->from(":t:survey_survey AS s")
			      ->leftJoin(":t:survey_survey2user AS u")->on("u.survey_id = s.id")
			      ->leftJoin(":t:survey_answer AS a")->on("a.survey_id = s.id")
			      ->groupBy("s.id")
			      ->orderBy("s.end DESC")
      ));

      $grid->getModel()->setPrimaryKey("s.id");


      // columns
      $grid->addColumn("question", "Otázka")->setSortable(true);
      $grid->addColumn("start", "Začátek")->setSortable(true);
      $grid->addColumn("end", "Konec")->setSortable(true);
      $grid->addColumn("votes", "Počet hlasů")->setSortable(true);

      // buttons
      $pres = $this;

      $grid->addToolbarButton("add", "Přidat")
	      ->setLink($this->link("add"))
	      ->setIcon("document")
	      ->setVisible(function() use($pres) {
			 return $pres->getUser()->isAllowed(Survey::create(), "add");
		      });



      $grid->addButton("answ", "Možnosti »")->setLink(function ($row) use($pres) {
			 return $pres->link("Answer:list", array(
			     "parent" => $row->sid,
			 ));
		      })->setIcon("")
	      ->setLabel(function ($row) {
			 return "Možnosti (" . $row->poss .") »";
		      })
	      ->setAjax(false)
	      ->setVisible(function($row) {
			 return $row->votes == 0;
		      });

      $grid->addButton("stats", "Statistiky »")->setLink(function ($row) use($pres) {
			 return $pres->link("Survey:stats", array(
			     "id" => $row->sid,
			 ));
		      })->setIcon("")
	      ->setAjax(false)
	      ->setVisible(function($row) {
			 return $row->votes != 0;
		      });
      /* $grid->addButton("sub1", "Survey2users »")->setLink(function ($row) use($pres)
        {
        return $pres->link("Survey2user:list", array(
        "parent" => $row->id,
        ));
        })->setIcon("")
        ->setAjax(false); */

      $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
			 return $pres->link("edit", array("id" => $row->sid));
		      })->setIcon("pencil")
	      ->setVisible(function($row) use($pres) {
			 return $row->votes == 0 && $pres->getUser()->isAllowed(Survey::create(), "edit");
		      });

      $grid->addButton("delete", "Smazat")
	      ->setHandler(callback($this, "deleteRecord"))
	      ->setIcon("trash")
	      ->setAjax(true)->setShowText(false)
	      ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
	      ->setVisible(function($row) use($pres) {
			 return $pres->getUser()->isAllowed(Survey::create(), "delete");
		      });

      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentStatsGrid($name) {
      $grid = new \Gridito\Grid($this, $name);

      // model
      $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("CONCAT(u.name, ' ', u.surname) AS name, [a.text] AS [text]")
			      ->from(":t:survey_survey2user AS su")
			      ->leftJoin(":t:system_user AS u")->on("u.id = su.user_id")
			      ->leftJoin(":t:survey_answer AS a")->on("a.survey_id = su.survey_id")
			      ->orderBy("[text], name")
      ));

      // columns
      $grid->addColumn("text", "Odpověď")->setSortable(true);
      $grid->addColumn("name", "Jméno")->setSortable(true);

      // buttons
      //settings
      $grid->setItemsPerPage(self::IPP);

      return $grid;
   }

   protected function createComponentForm($name, $new = false) {
      $form = new \Nette\Application\UI\Form($this, $name);

      $form->addText("question", "Otázka")
	      ->addRule(\Nette\Forms\Form::FILLED)
	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);

      $form->addDatePicker("start", "Začátek");

      $form->addDatePicker("end", "Konec");



      $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
      $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

      $form->onSuccess[] = callback($this, 'formSubmitted');

      $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

      $form->addHidden("occ_hash", null);
      return $form;
   }

   // </editor-fold>

   public function actionStats($id)
   {
      $survey = Survey::find($id);

      if(!$survey)
      {
	 throw new \Nette\Application\BadRequestException("Neexistující anketa.");
      }

      
   }
}

