<?php

namespace OrganizationModule;

use \Model\Org\InformationValues;

/**
 * @generator MScaffolder
 */
final class InformationValuesPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Org\InformationValues";
    protected static $parentClass = "\Model\Org\Race";
    protected static $parentColumn = "race_id";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">
    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("v.id AS vid, v.value AS value, i.id AS iid, i.name AS iname")
                                ->from(":t:org_informationValues AS v")
                                ->leftJoin(":t:org_information AS i")->on("i.id = v.information_id")
                                ->where("v.race_id = %i", $this->getParam("parent"))
                                ->orderBy("iname")
        ));

        $grid->getModel()->setPrimaryKey("v.id");

        $pres = $this;


// columns



        $f = new \Nette\Forms\Controls\SelectBox();
        $grid->addColumn("iname", "Kousek informace")->setSortable(true)
                ->setField("iid")
                ->setControl($f);

        $f = new \Nette\Forms\Controls\TextArea();
        $f->setAttribute('cols', '50');
        $f->setAttribute('rows', '5');
        $grid->addColumn("value", "Hodnota")->setSortable(true)
                ->setRenderer(function($record, $col) {
                            echo \Nette\Utils\Strings::truncate(\strip_tags($record->value), 30);
                        })
                ->setField("value")
                ->setControl($f->addRule(\Nette\Forms\Form::FILLED));

        $grid->setUpdateForm(function($form, $editId) use($pres) {
                    $fl = \dibi::select("i.id, i.name")
                            ->from(":t:org_information AS i")
                            ->leftJoin(":t:org_informationValues AS v")->on("i.id = v.information_id AND v.race_id = %i", $pres->getParam("parent"))
                            ->orderBy("i.name");
                    if ($editId != -1) {
                        $iv = InformationValues::find($editId);
                        $fl->where("v.id IS NULL OR i.id = %i", $iv->information_id);
                    } else {
                        $fl->where("v.id IS NULL");
                    }
                    $form["iid"]->setItems($fl->fetchPairs("id", "name"));
                });

        // buttons
        $grid->setShowAdd(\dibi::select("i.id, i.name")
                        ->from(":t:org_information AS i")
                        ->leftJoin(":t:org_informationValues AS v")->on("i.id = v.information_id AND v.race_id = %i", $pres->getParam("parent"))
                        ->where("v.id IS NULL")->count() > 0); //ISSUE doesn't work with AJAX, need to reload page


        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

        $rid = $this->getParam("parent");
        $grid->setSubmitCallback(function($form) use($pres, $rid) {
                    $values = $form->getValues();
                    $race = \Model\Org\Race::create(array("id" => $rid));
                    if ($values["editId"] == -1) {
                        $r = InformationValues::create(array("race_id" => $rid));
                        if (!$pres->ACLadd($race))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "add"), 403);
                    } else {
                        $r = InformationValues::find($values["editId"]);
                        if (!$pres->ACLedit($race))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "edit"), 403);
                    }

                    if (!$r)
                        return;

                    $r->value = $values["value"];
                    $r->information_id = $values["iid"];

                    $r->save();

                    $pres["grid"]->flashMessage("Informace uložena.");
                });
        //settings
        //$grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createOrderGrd($name, $column) {
        $grid = new \Gridito\EGrid($this, $name);


        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("v.id AS vid, v.value AS value, i.id AS iid, i.name AS iname")
                                ->from(":t:org_informationValues AS v")
                                ->leftJoin(":t:org_information AS i")->on("i.id = v.information_id")
                                ->where("v.race_id = %i", $this->getParam("parent"))
                                ->where("%n IS NOT NULL", $column)
                                ->orderBy("%n", $column)
        ));

        $grid->getModel()->setPrimaryKey("v.id");

        $pres = $this;


// columns



        $f = new \Nette\Forms\Controls\SelectBox();
        $grid->addColumn("iname", "Kousek informace")->setSortable(false)
                ->setField("iid")
                ->setControl($f);


        $grid->addColumn("value", "Hodnota")->setSortable(false)
                ->setRenderer(function($record, $col) {
                            echo \Nette\Utils\Strings::truncate(\strip_tags($record->value), 30);
                        })
                ->setField("value");


        $grid->setUpdateForm(function($form, $editId) use($pres, $column) {
                    $fl = \dibi::select("i.id, i.name")
                            ->from(":t:org_informationValues AS v")
                            ->leftJoin(":t:org_information AS i")->on("i.id = v.information_id AND v.race_id = %i", $pres->getParam("parent"))
                            ->orderBy("i.name")
                            ->where("%n IS NULL", $column);

                    $form["iid"]->setItems($fl->fetchPairs("id", "name"));
                });

        // buttons
        $grid->setShowAdd(\dibi::select("COUNT(*)")
                        ->from(":t:org_informationValues AS v")
                        ->where("v.race_id = %i", $pres->getParam("parent"))
                        ->where("%n IS NULL", $column)->fetchSingle() > 0); //ISSUE doesn't work with AJAX, need to reload page
        $grid->setShowEdit(false);

        $grid->addButton("delete", "Odebrat")
                ->setHandler(function($id) use($pres, $column) {
                            $race = \Model\Org\Race::create(array("id" => $pres->getParam("parent")));
                            if (!$pres->getUser()->isAllowed($race, "editInformation")) {
                                throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($race, "editInformation"), 403);
                            }
                            $iv = InformationValues::find($id);
                            if (!$iv)
                                return;
                            \dibi::query("UPDATE :t:org_informationValues SET %n = %n - 1 WHERE %n > %i AND race_id = %i", $column, $column, $column, $iv->detailsOrder, $race->id);
                            $iv->{$column} = null;
                            $iv->save();
                            $pres["grid"]->flashMessage("Informace odebrána.");
                        })
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false);

        $shift = function($id, $n) use($pres, $column) {
                    $race = \Model\Org\Race::create(array("id" => $pres->getParam("parent")));
                    if (!$pres->getUser()->isAllowed($race, "editInformation")) {
                        throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($race, "editInformation"), 403);
                    }
                    $iv = InformationValues::find($id);
                    if (!$iv)
                        return;
                    $iv->addBehavior(new \Ormion\Behavior\Sortable($column, "race_id"));
                    $iv->{$column} += $n;
                    $iv->save();
                };
        $grid->addButton("shiftUp", "Nahoru")
                ->setHandler(function($id) use($shift) {
                            \call_user_func($shift, $id, -1);
                        })
                ->setIcon("arrowthick-1-n")
                ->setAjax(true)->setShowText(false);
        $grid->addButton("shiftDown", "Dolů")
                ->setHandler(function($id) use($shift) {
                            \call_user_func($shift, $id, 1);
                        })
                ->setIcon("arrowthick-1-s")
                ->setAjax(true)->setShowText(false);






        $grid->setSubmitCallback(function($form) use($pres, $column) {
                    $race = \Model\Org\Race::create(array("id" => $pres->getParam("parent")));
                    if (!$pres->getUser()->isAllowed($race, "editInformation")) {
                        throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($race, "editInformation"), 403);
                    }
                    $values = $form->getValues();
                    $iv = InformationValues::find(array("race_id" => $race->id, "information_id" => $values["iid"]));

                    if (!$iv)
                        return;

                    $max = \dibi::fetchSingle("SELECT MAX(%n) FROM :t:org_informationValues WHERE race_id = %i", $column, $race->id);
                    $max = $max === null ? 0 : $max;


                    $iv->{$column} = $max + 1;
                    $iv->save();

                    $pres["grid"]->flashMessage("Informace přidána.");
                });
        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentGrdDetails($name) {
        return $this->createOrderGrd($name, "detailsOrder");
    }

    protected function createComponentGrdInstructions($name) {
        return $this->createOrderGrd($name, "instructionsOrder");
    }

    // </editor-fold>

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

    /* functions are public because of use in lmabda function */

    public function ACLadd($record = null) {
        if ($record === null)
            $record == $this->parentRecord;

        return $this->getUser()->isAllowed($record, "editInformation");
    }

    public function ACLedit($record = null) {
        if ($record === null)
            $record == $this->parentRecord;

        return $this->getUser()->isAllowed($record, "editInformation");
    }

    public function ACLdelete($record = null) {
        if ($record === null)
            $record == $this->parentRecord;

        return $this->getUser()->isAllowed($record, "editInformation");
    }

    protected function ACLlist($record = null) {
        if ($record === null)
            $record == $this->parentRecord;

        return $this->getUser()->isAllowed($record, "editInformation");
    }

    public function actionDetails($id, $parent) {
        //empty body because of giving 'parent' parameter
    }

    public function actionInstructions($id, $parent) {
        //empty body because of giving 'parent' parameter
    }

}

