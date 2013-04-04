<?php

namespace ApplicationModule;

use \Model\App\Account;
use \Model\System\User;

/**
 * @generator MScaffolder
 */
final class AccountPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\App\Account";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">



    protected function createComponentGrid($name) {

        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("*")
                        ->from(":t:app_account")
        ));


        // columns
        $grid->addColumn("name", "Název")->setSortable(true);
        $grid->addColumn("balance", "Zůstatek")->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::currency($row->balance);
                        })
                ->setCellClass("text-right");
        //$grid->addColumn("active", "Stav")->setSortable(true);
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

        if ($this->getUser()->isAllowed(Account::create(), "refresh")) {
            $grid->addToolbarButton("refresh", "Obnovit cache zůstatků")
                    ->setLink($this->link("refresh!"))
                    ->setIcon("refresh");
        }



        $grid->addButton("sub0", "Transakce »")->setLink(function ($row) use($pres) {
                            return $pres->link("AccountTransaction:list", array(
                                        "parent" => $row->id,
                            ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->id));
                })->setIcon("pencil");

        $grid->addButton("toggle", "Deaktivovat")
                ->setLabel(function($row) {
                            if ($row->active == 0)
                                return "Aktivovat";
                            else
                                return "Deaktivovat";
                        })
                ->setHandler(function($id) use($pres) {
                            $account = Account::find($id);
                            if (!$account)
                                return;
                            $account->active = 0;
                            $account->save();
                        })
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false);
        //->setConfirmationQuestion("Vážně chcete deaktivovat účet?");
        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText("name", "Název")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 45);

        $form->addCheckbox("active", "Aktivní");

        $items = array();
        if (!$new) {
            foreach (User::findAll(array("active" => 1, "account_id" => $this->currentRecord->id))->orderBy('surname, name')->fetchAll() as $user) {
                $items[$user->id] = $user->getFullName();
            }

            $form->addSelect("users", "Uživatelé", null, 8)
                    ->setItems($items)
                    ->getControlPrototype()->setReadonly("readonly");
        }

        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    protected function createComponentGrdUsers($name) {

        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("u.name, u.surname, registration, u.id AS uid, a.name AS aname, a.id AS aid")
                        ->from(":t:system_user AS u")
                        ->leftJoin(":t:app_account AS a")->on("a.id = u.account_id")
                        ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
                        ->leftJoin(":t:app_backer AS b")->on("b.user_id = u.id")
                        ->where("u.active = 1")
                        ->and("(m.active = 1 OR b.active = 1)") //active member or backer
        ));

        $grid->getModel()->setPrimaryKey("u.id");

        // columns
        $grid->addColumn("registration", "Registrace");
        $grid->addColumn("name", "Uživatel")
                ->setSortable(true)
                ->setRenderer(function($row, $column) {
                            $user = User::create($row);
                            echo $user->getFullName(false);
                        });

        $f = new \Nette\Forms\Controls\SelectBox();
        $f->setItems(\dibi::select("id, name")->from(":t:app_account")->where("active = 1")->orderBy('name')->fetchPairs("id", "name"));
        $f->setPrompt("-- Žádný --");
        $grid->addColumn("aname", "Účet")->setSortable(true)
                ->setField("aid")
                ->setRenderer(function ($row, $column) {
                            echo $row->aname == null ? "-- Žádný --" : $row->aname;
                        })
                ->setControl($f);

        // buttons

        $grid->setShowAdd(false);
        $pres = $this;

        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();
                    $user = User::find($values["editId"]);
                    if ($user) {
                        if ($values["aid"] == "")
                            $user->account_id = null;
                        else
                            $user->account_id = $values["aid"];
                        $user->save();
                        $pres->flashMessage("Účet změněn.");
                    }
                });

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentGrdHistory($name) {
        $account = $this->getUser()->getIdentity()->Account;

        return \OOB\AccountHistoryGrid::create($this, $name, $account);
    }

    public function actionUsers() {
        if (!$this->getUser()->isAllowed(Account::create(), "edit")) {
            throw new \Nette\Application\BadRequestException("Nedostatečné oprávnění.", 403);
        }
    }

    public function actionDefault() {

        $this->template->account = $this->getUser()->getIdentity()->Account;
    }
    
    public function handleRefresh() {
        $accounts = Account::findAll();
        foreach($accounts as $account){
            $account->invalidateBalance();
        }
        $this->flashMessage('Cache zůstatků obnovena.');
        $this->redirect('this');
    }

    // </editor-fold>
}

