<?php

namespace AdminModule;

use \Model\Con\StaticPage;

/**
 * @generator MScaffolder
 */
final class StaticPagePresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Con\StaticPage";
    protected static $parentClass = "\Model\Con\StaticPage";
    protected static $parentColumn = "parent_id";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
                                ->from(":t:con_staticPage")
                                ->where("%and", array("parent_id" => $this->getParam("parent")))
        ));



        // columns
        $grid->addColumn("name", "Název")->setSortable(true);

        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
                ->setIcon("document");


        $grid->addButton("sub0", "StaticPages »")->setLink(function ($row) use($pres) {
                            return $pres->link("StaticPage:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->id));
                })->setIcon("pencil");

        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"));

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText("name", "Název")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 40);


        $f = new \OOB\Texyla('Obsah');
        $form->addComponent($f, 'content_src');
        $f->addRule(\Nette\Forms\Form::FILLED);
        $f->setTexyConfiguration('texy');


//      $form->addText("url", "URL identifikátor")
//	      ->addRule(\Nette\Forms\Form::FILLED)
//	      ->addRule(\Nette\Forms\Form::PATTERN, "Identifikátor musí být tvořen jen malými písmeny, číslicemi a spojovníkem." .
//		      "Měl by být alespoň tři znaky dlouhý.", "[-a-z0-9]{3,50}")
//	      ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);



        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>
    //<editor-fold desc="Checks">
    protected function checkParent($parent) {
        if ($parent !== null) {
            parent::checkParent($parent);
        }
    }

//</editor-fold>
    //<editor-fold desc="Overrides">
    protected function setRelations(\Nette\Application\UI\Form $form) {
        $this->currentRecord->lastModDate = new \DateTime();
        $this->currentRecord->lastModUser = $this->getUser()->getId();
    }

//</editor-fold>
}

