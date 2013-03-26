<?php

namespace AdminModule;

use Model\Publication\Event;
use \Nette\Caching\Cache;

/**
 */
final class EventPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "Model\Publication\Event";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
                                ->from(":t:public_event")
                                ->orderBy("start DESC")
        ));



        // columns

        $f = new \OOB\DatePicker();
        $f->getControlPrototype()->style('width:80px');
        $f->addRule(\Nette\Forms\Form::FILLED, 'Začátek/okamžik události musí být vyplněn.');
        $grid->addColumn("start", "Datum")->setSortable(true)
                ->setField("start")
                ->setControl($f);

        $f = new \OOB\DatePicker();
        $f->getControlPrototype()->style('width:80px');
        $grid->addColumn("end", "(Konec)")->setSortable(true)
                ->setField("end")
                ->setControl($f);

        $f = new \Nette\Forms\Controls\TextInput();
        $f->getControlPrototype()->style('width:100px');
        $f->addRule(\Nette\Forms\Form::FILLED, 'Událost musí být vyplněna.');
        $f->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Omezení na 100 znaků.', 100);
        $grid->addColumn("summary", "Událost")->setSortable(true)
                ->setField("summary")
                ->setControl($f);

        $f = new \Nette\Forms\Controls\TextInput();
        $f->getControlPrototype()->style('width:80px');
        $f->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Omezení na 100 znaků.', 100);
        $grid->addColumn("location", "Místo")->setSortable(true)
                ->setField("location")
                ->setControl($f);

        $f = new \OOB\TextUrl();
        $f->getControlPrototype()->style('width:80px');
        $f->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Omezení na 1024 znaků.', 1024);
        $grid->addColumn("url", "URL")->setSortable(true)
                ->setField("url")
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
                        $r = Event::create();
                        if (!$pres->getUser()->isAllowed($r, "add"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "add"), 403);
                    } else {
                        $r = Event::find($values["editId"]);
                        if (!$pres->getUser()->isAllowed($r, "edit"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "edit"), 403);
                    }

                    if (!$r)
                        return;

                    $r->setValues($values);
                    $r->save();

                    $pres["grid"]->flashMessage("Událost uložena.");
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
        parent::actionList($id, $parent);
    }

    // </editor-fold>
}

