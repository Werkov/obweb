<?php

namespace AdminModule;

use \Model\System\User;
use \Model\App\Backer;
use Nette\Forms\Form;

/**
 */
final class BackerPresenter extends UserChildPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\App\Backer";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(
                                \dibi::select("b.user_id AS buser_id, CONCAT(u.name, ' ', u.surname) AS name, u.surname, u.registration AS registration, m.active AS mactive, " .
                                        "b.active AS bactive")
                                ->from(":t:app_backer AS b")
                                ->leftJoin(":t:system_user AS u")->on("u.id = b.user_id")
                                ->leftJoin(":t:app_member AS m")->on("u.id = m.user_id")
        ));

        $grid->getModel()->setPrimaryKey("b.user_id");
        $grid->getModel()->setSorting('surname', 'ASC');

        // columns
        $grid->addColumn("surname", "Jméno")->setSortable(true)
                ->setRenderer(function($row) {
                            echo $row->name;
                        });
        $grid->addColumn("registration", "Registrace")->setSortable(true);
        $grid->addColumn("bactive", "Stav")->setSortable(true);

        $grid->setRowClass(function($iterator, $row) {
                    if (!$row->bactive)
                        return "inactive";
                    return null;
                });

        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document")
                ->setVisible(Backer::getPossibleUsers()->count() > 0);



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->buser_id));
                })->setIcon("pencil");

        $grid->addButton("toggleActivity", "Stav")
                ->setHandler(callback($this, "toggleActivity"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(true)
                ->setLabel(function($row) {
                            if ($row->bactive)
                                return "Deaktivovat";
                            else if ($row->mactive == 1)
                                return "Obnovit ze člena";
                            else
                                return "Obnovit";
                        });

        //settings
        $grid->setItemsPerPage(self::IPP);
        $grid->setRowClass(function($iterator, $row) {
                    return $row->bactive ? "" : "inactive";
                });

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText("registration", "Registrace")
                        ->addRule(Form::MAX_LENGTH, null, 4)
                        ->addRule(Form::FILLED, "Je třeba vyplnit registraci.")
                        ->getControlPrototype()->class = "autocomplete";

        $this->template->autocompleteOptions = User::getAvailableRegistrations();


        if ($new) {
            $items = Backer::getPossibleUsers()->fetchPairs("id", "name");
            $form->addSelect("user_id", "Uživatel")
                    ->setItems($items);
        } else {
            $userContainer = $form->addContainer('user');
            UserPresenter::createUserFormGroup($userContainer);
        }

        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>

    public function toggleActivity($id) {
        $user = User::find($id);
        if (!$user)
            return;


        if (!$user->active) { //restore
            $user->active = 1;

            $user->Backer->active = 1;

            $msg = "Přízivce obnoven.";
        } else if ($user->Member && $user->Member->active) { //restore from member
            $user->Backer->active = 1;
            $user->Member->active = 0;
            $msg = "Příznivce obnoven ze člena.";
        } else if ($user->Backer->active == 1) { //deactivate backer
            $user->Backer->active = 0;
            $msg = "Příznivce deaktivován.";
        } else { //restore backer
            $user->Backer->active = 1;
            $msg = "Příznivce obnoven.";
        }

        try {
            $user->save();
            $this->flashMessage($msg);
        } catch (\Exception $exc) {
            $this->flashMessage("Operace se nezdařila.", \BasePresenter::FLASH_ERROR);
        }
    }

}

