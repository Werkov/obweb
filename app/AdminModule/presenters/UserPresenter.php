<?php

namespace AdminModule;

use \Model\System\User;
use \Nette\Forms\Form;

/**
 * @generator MScaffolder
 */
final class UserPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\System\User";

    // </editor-fold>
// <editor-fold desc="Render views">
    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);
        self::setRolesValue($this->currentRecord, $this['editForm']);
    }

    public static function setRolesValue($user, \Nette\Forms\Container $container) {
        if (is_array($user->Roles)) {
            $val = \array_map(function($rc) {
                        return $rc->id;
                    }, $user->Roles);
        } else {
            $val = $user->Roles->fetchColumn("id");
        }

        $container->setDefaults(array(
            "roles_id" => $val,
        ));
    }

    // </editor-fold>
// <editor-fold desc="Signals">
    protected function setRelations(\Nette\Application\UI\Form $form) {
        self::setUserBeforeSave($this->currentRecord, $form);
    }

    public static function setUserBeforeSave($user, \Nette\Forms\Container $container) {
        $values = $container->getValues();

        $user->Roles = array_map(function ($id) {
                    return \Model\System\Role::create($id);
                }, $values["roles_id"]);

        //ugly workaround not to reset password when field is empty
        if ($values["password"] == "")
            $user->loadValues(array("password"));
        else
	    $user->password = $user->hashPassword($values["password"]);
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(
                                \dibi::select("u.id AS uid, CONCAT(u.name, ' ', u.surname) AS name, u.surname, u.login, u.lastLog, u.lastIP, u.active, " .
                                        " m.active AS mactive, b.active AS bactive")
                                ->from(":t:system_user AS u")
                                ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
                                ->leftJoin(":t:app_backer AS b")->on("b.user_id = u.id")
        ));
        $grid->getModel()->setPrimaryKey("u.id");
        $grid->getModel()->setSorting('surname', 'ASC');

        // columns
        $grid->addColumn("surname", "Jméno")->setSortable(true)
                ->setRenderer(function($row) {
                            echo $row->name;
                        });
        ;
        $grid->addColumn("login", "Login")->setSortable(true);
        $grid->addColumn("lastLog", "Poslední přihlášení")->setSortable(true)
                ->setRenderer(function($row) {
                            echo $row->lastLog ? $row->lastLog->format("Y-m-d") . " z&nbsp;" . $row->lastIP : "nikdy";
                        });
        $grid->addColumn("active", "Stav")->setSortable(true);
        //$grid->addColumn("lastIP", "")->setSortable(true);
        $grid->setRowClass(function($iterator, $row) {
                    if (!$row->active)
                        return "inactive";
                    return null;
                });
        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document");



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->uid));
                })->setIcon("pencil");

        $grid->addButton("toggleActivity", "Stav")
                ->setHandler(callback($this, "toggleActivity"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setLabel(function($row) {
                            if ($row->active)
                                return "Deaktivovat";
                            else
                                return "Obnovit";
                        });

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);


        self::createUserFormGroup($form);


        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    public static function createUserFormGroup(\Nette\Forms\Container $container) {

        $container->addText("login", "Login")
                ->addRule(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, null, 20);

        $container->addPassword("password", "Heslo")
                ->addRule(Form::MAX_LENGTH, null, 32);

        $container->addText("name", "Jméno")
                ->addRule(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, null, 50);

        $container->addText("surname", "Příjmení")
                ->addRule(Form::FILLED)
                ->addRule(Form::MAX_LENGTH, null, 50);

        $items = array("M" => "muž", "F" => "žena",);
        $container->addRadioList("sex", "Pohlaví")
                ->addRule(Form::FILLED)
                ->setItems($items);


        $container->addText("phone", "Telefon")
                ->addRule(Form::MAX_LENGTH, null, 20);

        $container->addText("address", "Adresa")
                ->addRule(Form::MAX_LENGTH, null, 150);

        $container->addText("email", "Email")
                ->addRule(Form::MAX_LENGTH, null, 100)
                ->addCondition(Form::FILLED)->addRule(Form::EMAIL, "Neplatný email.");

        $container->addText("IM", "IM")
                ->addRule(Form::MAX_LENGTH, null, 30);

        $container->addTextArea("other", "Poznámka");

        $container->addCheckbox("public", "Veřejné údaje");

        /* $container->addText("account_id", "account_id")
          ->addRule(Form::FILLED)
          ->addRule(Form::MAX_LENGTH, null, 11); */

        /* $container->addText("registration", "registration")
          ->addRule(Form::MAX_LENGTH, null, 4); */

        $items = array();

        foreach (\Model\System\Role::findAll()->orderBy('name') as $item) {
            $el = \Nette\Utils\Html::el('span', array('title' => $item->desc));
            $el->setText($item->name);
            $items[$item->id] = $el;
        }
        $container->addCheckboxList("roles_id", "Přidělené role")
                ->setItems($items);
//      $container->addMultiSelect("roles_id", "Přidělené role")
//	      //->addRule(Form::FILLED)
//	      ->setItems($items);
    }

    // </editor-fold>

    public function toggleActivity($id) {
        $user = User::find($id);
        if (!$user)
            return;


        if ($user->active) { //deactivate
            $user->active = 0;

            if ($user->Member) {
                $user->Member->active = 0;
            }

            if ($user->Backer) {
                $user->Backer->active = 0;
            }

            $msg = "Účet deaktivován.";
        } else { //restore
            $user->active = 1;
            $msg = "Účet obnoven.";
        }

        try {
            $user->save();
            $this->flashMessage($msg);
        } catch (\Exception $exc) {
            $this->flashMessage("Operace se nezdařila.", \BasePresenter::FLASH_ERROR);
        }
    }

}

