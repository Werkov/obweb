<?php

namespace AdminModule;

use \Model\System\User;
use \Model\App\Member;
use Nette\Forms\Form;

/**
 * @generator MScaffolder
 */
final class MemberPresenter extends UserChildPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\App\Member";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(
                                \dibi::select("m.user_id AS muser_id, CONCAT(u.name, ' ', u.surname) AS name, u.surname, u.registration AS registration, m.active AS mactive, " .
                                        "b.active AS bactive")
                                ->from(":t:app_member AS m")
                                ->leftJoin(":t:system_user AS u")->on("u.id = m.user_id")
                                ->leftJoin(":t:app_backer AS b")->on("u.id = b.user_id")
        ));

        $grid->getModel()->setPrimaryKey("m.user_id");
        $grid->getModel()->setSorting('surname', 'ASC');

        // columns
        $grid->addColumn("surname", "Jméno")->setSortable(true)
                ->setRenderer(function($row) {
                            echo $row->name;
                        });
        $grid->addColumn("registration", "Registrace")->setSortable(true);
        $grid->addColumn("mactive", "Stav")->setSortable(true);

        $grid->setRowClass(function($iterator, $row) {
                    if (!$row->mactive)
                        return "inactive";
                    return null;
                });
        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document")
                ->setVisible(Member::getPossibleUsers()->count() > 0);



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->muser_id));
                })->setIcon("pencil");

        $grid->addButton("toggleActivity", "Stav")
                ->setHandler(callback($this, "toggleActivity"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(true)
                ->setLabel(function($row) {
                            if ($row->mactive)
                                return "Deaktivovat";
                            else if ($row->bactive == 1)
                                return "Obnovit z příznivce";
                            else
                                return "Obnovit";
                        });
        //settings
        $grid->setItemsPerPage(self::IPP);
        $grid->setRowClass(function($iterator, $row) {
                    return $row->mactive ? "" : "inactive";
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

        $form->addText("SI", "Číslo SI")
                ->addRule(Form::MAX_LENGTH, null, 10);

        $form->addText("licence", "Licence")
                ->addRule(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, null, 1);

        if ($new) {
            $items = Member::getPossibleUsers()->fetchPairs("id", "name");
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

            $user->Member->active = 1;

            $msg = "Člen obnoven.";
        } else if ($user->Backer && $user->Backer->active) { //restore from backer
            $user->Backer->active = 0;
            $user->Member->active = 1;
            $msg = "Člen obnoven z příznivce.";
        } else if ($user->Member->active == 1) { //deactivate member
            $user->Member->active = 0;
            $msg = "Člen deaktivován.";
        } else { //restore member
            $user->Member->active = 1;
            $msg = "Člen obnoven.";
        }

        try {
            $user->save();
            $this->flashMessage($msg);
        } catch (\Exception $exc) {
            $this->flashMessage("Operace se nezdařila.", \BasePresenter::FLASH_ERROR);
        }
    }

}

