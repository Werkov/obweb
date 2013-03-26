<?php

namespace OrganizationModule;

use \Model\Org\Race;

/**
 * @generator MScaffolder
 */
final class RacePresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Org\Race";
    protected static $parentClass = "\Model\Org\Event";
    protected static $parentColumn = "event_id";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("id, name")
                                ->from(":t:org_race")
                                ->where("event_id = %i", $this->getParam("parent"))
                                ->orderBy("order")
        ));



        // columns

        $f = new \Nette\Forms\Controls\TextInput();
        $grid->addColumn("name", "Název")->setSortable(false)
                ->setField("name")
                ->setControl($f->addRule(\Nette\Forms\Form::FILLED));


        // buttons
        $pres = $this;
        $grid->setShowAdd(true);

        $grid->addButton("fill", "Informace »")->setLink(function ($row) use($pres) {
                            return $pres->link("InformationValues:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("details", "Rozpis »")->setLink(function ($row) use($pres) {
                            return $pres->link("InformationValues:details", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("instructions", "Pokyny »")->setLink(function ($row) use($pres) {
                            return $pres->link("InformationValues:instructions", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("shiftUp", "Nahoru")
                ->setHandler(callback($this, "shiftUpRecord"))
                ->setIcon("arrowthick-1-n")
                ->setAjax(true)->setShowText(false);

        $grid->addButton("shiftDown", "Dolů")
                ->setHandler(callback($this, "shiftDownRecord"))
                ->setIcon("arrowthick-1-s")
                ->setAjax(true)->setShowText(false);


        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

        $eid = $this->parentRecord->id;
        $grid->setSubmitCallback(function($form) use($eid, $pres) {
                    $values = $form->getValues();

                    if ($values["editId"] == -1) {
                        $r = Race::create(array("event_id" => $eid));
                        if (!$pres->getUser()->isAllowed($r, "add"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "add"), 403);
                    } else {
                        $r = Race::find($values["editId"]);
                        if (!$pres->getUser()->isAllowed($r, "edit"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "edit"), 403);
                    }

                    if (!$r)
                        return;

                    $r->name = $values["name"];
                    $r->save();

                    $pres["grid"]->flashMessage("Závod uložen.");
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

