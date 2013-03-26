<?php

namespace AdminModule;

use \Model\Gallery\Directory;

/**
 * @generator MScaffolder
 */
final class GalDirectoryPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Gallery\Directory";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
                                ->from(":t:gallery_directory")
        ));


        // columns
        $f = new \Nette\Forms\Controls\TextInput();
        $f->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);

        $grid->addColumn("name", "Název")->setSortable(true)
                ->setControl($f)
                ->setField('name');

        // buttons
        $pres = $this;
        $grid->setShowAdd($pres->getUser()->isAllowed(Directory::create(), "add"));


        $grid->addButton("sub0", "Galerie »")->setLink(function ($row) use($pres) {
                            return $pres->link(":Personal:Gallery:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->setShowEdit($pres->getUser()->isAllowed(Directory::create(), "edit"));


        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
                ->setVisible(function($row) use($pres) {
                            return $pres->getUser()->isAllowed(Directory::create(), "delete");
                        });

        //settings
        $grid->setItemsPerPage(self::IPP);

        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();

                    if ($values["editId"] == -1) {
                        $r = Directory::create();
                    } else {
                        $r = Directory::find($values["editId"]);
                    }

                    if (!$r)
                        return;

                    $r->name = $values["name"];
                    $r->save();

                    $pres["grid"]->flashMessage("Složka změněna.");
                });

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText("name", "Název")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);


        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>
}

