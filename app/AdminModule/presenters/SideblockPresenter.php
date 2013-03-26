<?php

namespace AdminModule;

use \Model\Con\Sideblock;
use \Nette\Caching\Cache;

/**
 */
final class SideblockPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Con\Sideblock";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
                                ->from(":t:con_sideblock")
                                ->orderBy("position, [order]")
        ));



        // columns

        $f = new \Nette\Forms\Controls\TextInput();
        $f->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Maximálně 50 znaků', 50);
        $grid->addColumn("name", "Název")->setSortable(false)
                ->setField("name")
                ->setControl($f);

        $f = new \Nette\Forms\Controls\SelectBox();
        $f->setItems(array(
            'left' => 'vlevo',
            'right' => 'vpravo',
        ));
        $grid->addColumn("position", "Umístění")->setSortable(false)
                ->setField("position")
                ->setControl($f);

        $f = new \Nette\Forms\Controls\TextArea(null, 40, 8);
        $f->addRule(\Nette\Forms\Form::FILLED);
        $grid->addColumn("content", "Obsah")->setSortable(false)
                ->setField("content")
                ->setControl($f);


        // buttons
        $pres = $this;
        $grid->setShowAdd(true);

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

        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();

                    if ($values["editId"] == -1) {
                        $r = Sideblock::create();
                        if (!$pres->getUser()->isAllowed($r, "add"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "add"), 403);
                    } else {
                        $r = Sideblock::find($values["editId"]);
                        if (!$pres->getUser()->isAllowed($r, "edit"))
                            throw new \Nette\Application\BadRequestException($pres->ttUnauthorizedAccess($r, "edit"), 403);
                    }

                    if (!$r)
                        return;

                    $r->setValues($values);
                    $r->save();

                    $pres["grid"]->flashMessage("Postranní blok uložen.");
                    \Nette\Environment::getCache()->clean(array(
                        Cache::TAGS => array('con_sideblock'),
                    ));
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

    protected function shiftRecord($id, $step) {
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('con_sideblock'),
        ));
        return parent::shiftRecord($id, $step);
    }

    public function deleteRecord($id) {
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('con_sideblock'),
        ));
        parent::deleteRecord($id);
    }

    // </editor-fold>
}

