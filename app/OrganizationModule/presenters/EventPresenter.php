<?php

namespace OrganizationModule;

use \Model\Org\Event;
use \Nette\Caching\Cache;

/**
 * @generator MScaffolder
 */
final class EventPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Org\Event";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $fl = \dibi::select("DISTINCT e.*")
                        ->from(":t:org_event AS e")
                        ->leftJoin(":t:org_event2user AS eu")->on("eu.event_id = e.id");
        Event::SqlVisibility($this->getUser(), $fl);
        $grid->setModel(new \Gridito\DibiFluentModel($fl));


        // columns
        $grid->addColumn("name", "Název")->setSortable(true);
        $grid->addColumn("start", "Datum")->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::mydate($row->start);
                        });
        ;

        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document");


        $grid->addButton("sub0", "Novinky »")->setLink(function ($row) use($pres) {
                            return $pres->link("Brief:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);
        $grid->addButton("sub1", "Závody »")->setLink(function ($row) use($pres) {
                            return $pres->link("Race:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("")
                ->setAjax(false);

        $grid->addButton("preview", "Náhled")->setLink(function ($row) use($pres) {
                            return $pres->link(":Organization:Homepage:default", array(
                                        HomepagePresenter::PREVIEW_PARAM => $row->url,
                                    ));
                        })->setIcon("zoomin")
                ->setShowText(false)
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
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 80);

        $form->addDatePicker("start", "Začátek/datum konání")
                ->addRule(\Nette\Forms\Form::FILLED);

        $form->addDatePicker("end", "Konec konání");


        $form->addText("url", "URL identifikátor")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::PATTERN, "Identifikátor musí být tvořen jen malými písmeny, číslicemi a spojovníkem." .
                        "Měl by být alespoň tři znaky dlouhý.", "[-a-z0-9]{3,20}")
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 20);

        foreach (\Model\System\User::findAll(array("active" => 1))->orderBy('surname, name')->fetchAll() as $user) {
            $items[$user->id] = $user->getFullName();
        }
        $form->addSelect("manager_id", "Hlavní správce")
                ->setItems($items);

        $form->addRadioList("visibility", "Viditelnost")
                ->setItems(array(
                    Event::VISIBILITY_ALL => "kdokoli",
                    Event::VISIBILITY_LOGGED => "autentizovaní uživatelé",
                    Event::VISIBILITY_MANAGER => "správcové",
                ));

        $form->addMultipleTextSelect("administrators_id", new \Model\Org\EventAdministratorModel(), "Další správci")
                ->setUnknownMode(\OOB\MultipleTextSelect::N_IGNORE)
                ->addRule(\Nette\Forms\Form::VALID, "Jména nejsou správně.")
                ->setSize(100);

        $form->addCheckbox("legacy", "Závod ve starém systému");

        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>
    protected function setRelations(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();

        $this->currentRecord->Administrators = array_map(function ($id) {
                    return \Model\System\Role::create($id);
                }, $values["administrators_id"]);
    }

    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);
        $this["editForm"]->setDefaults(array(
            "administrators_id" => $this->currentRecord->Administrators->fetchColumn("id"),
        ));
    }

    protected function saveRecord(\Nette\Application\UI\Form $form) {
        $path = $this->context->parameters['organization']['eventsPath'];
        if ($this->currentRecord->getState() == \Ormion\Record::STATE_NEW) {
            mkdir($path . '/' . $this->currentRecord->url);
        } else {
            $original = Event::getMapper()->find($this->currentRecord->id, true);
            rename($path . '/' . $original->url, $path . '/' . $this->currentRecord->url);
        }
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('org_event'),
        ));
        parent::saveRecord($form);
    }

    public function deleteRecord($id) {
        $record = Event::find(($id === null) ? 0 : $id);
        $path = $this->context->parameters['organization']['eventsPath'];

        if ($record && !$record->legacy) {
            rmdir($path . '/' . $this->currentRecord->url); //TODO
        }
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('org_event'),
        ));
        parent::deleteRecord($id);
    }

}

