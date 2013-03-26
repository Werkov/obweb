<?php

namespace OOB;

use \Nette\Forms\Form;
use \Model\App\Account;
use \Model\App\Race;

class EntryForm {

    /**
     *
     * @param \Nette\Application\UI\Control $parent
     * @param string $name
     * @param Race $race	  race for which the form is meant to be
     * @param \Model\App\Entry|null $entr  entry for which it's editing/creating entry
     * @return \Nette\Application\UI\Form
     */
    public static function reducedCreate($parent, $name, Race $race, $entry) {
        Race::updateStatus();
        $form = new \Nette\Application\UI\Form($parent, $name);
        $user = $parent->getUser();

        $fl = \dibi::select("u.registration, u.id, u.name, u.surname, u.account_id")
                ->from(":t:system_user AS u")
                ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
                ->leftJoin(":t:app_backer AS b")->on("b.user_id = u.id")
                ->where("(u.id NOT IN (" . \dibi::select("ee.racer_id")
                        ->from(":t:app_entry AS ee")
                        ->leftJoin(":t:app_race2category AS erc")->on("erc.id = ee.presentedCategory_id")
                        ->where("erc.race_id = %i", $race->id)
                        ->and("ee.racer_id IS NOT NULL")
                        ->__toString() . ")") //not applied yet
                ->and("u.active = 1") //active user ??redundant
                ->and("(m.active = 1 OR b.active = 1))"); //active member or backer

        if ($entry->getState() == \Ormion\Record::STATE_EXISTING) {
            $fl->or("u.id = %i", $entry->racer_id);
        } else {
            $entry = \Model\App\Entry::create();
            $entry->Category = \Model\App\Race2category::create();
            $entry->Category->Race = $race;
        }

        $fl->orderBy('u.surname, u.name');
        $people = new \Ormion\Collection($fl, '\Model\System\User');


        $form->addGroup("Závodník");
        $racers = array();
        $defSI = array();
        $defAccount = array();
        \Nette\Diagnostics\Debugger::barDump("preloading");
        \Model\App\Member::findAll()->fetchAll(); // load cache
        \Nette\Diagnostics\Debugger::barDump("preloaded");
        foreach ($people->fetchAll() as $racer) {
            if (!($user->getIdentity()->canApply($racer) || $user->isAllowed($race, "applications")))
                continue;

            $racers[$racer->id] = $racer->getFullname();
            if ($racer->Member)
                $defSI[$racer->id] = $racer->Member->SI;  //bottleneck

            $defAccount[$racer->id] = $racer->account_id;
        }

        $racer = $form->addSelect("racer_id", "Závodník");
        $racer->setItems($racers)
                ->setPrompt("-- Závodník --")
                ->getControlPrototype()->setClass("toggleOff");


        if ($user->isAllowed($race, "apply") && $user->isAllowed(Account::create(), "transaction")) {
            $anonymous = true;
            $accountSelection = true;
        } else {
            $anonymous = false;
            $accountSelection = false;
        }


        if ($anonymous) {

            $form->addCheckbox("anonymous", "Neregistrovaný")->getControlPrototype()->setClass("toggle");
            $txt = $form->addText("racerName", "Jméno");
            $txt->getControlPrototype()->setClass("toggleOn");
            $txt->addConditionOn($form["anonymous"], Form::EQUAL, true)->addRule(Form::FILLED, "Jméno musí být vyplněno.");

            /* $form["anonymous"]->getControlPrototype()
              ->onclick("if($(this).attr('checked')){" .
              "$('#" . $form["racer_id"]->getHtmlId() . "').attr('disabled', true);" .
              "$('#" . $form["racerName"]->getHtmlId() . "').attr('disabled', false);" .
              "}else{" .
              "$('#" . $form["racer_id"]->getHtmlId() . "').attr('disabled', false);" .
              "$('#" . $form["racerName"]->getHtmlId() . "').attr('disabled', true);" .
              "}"); */
            $racer->addConditionOn($form["anonymous"], Form::EQUAL, false)
                    ->addRule(Form::FILLED, "Prosím vyberte závodníka");
        } else {
            $racer->addRule(Form::FILLED, "Prosím vyberte závodníka");
        }

        $form->addGroup("Informace");

        \Model\App\Category::findAll()->fetchAll(); // preload
        $categories = array();
        $priceCategories = array();
        $rawCategories = $race->Categories->fetchAll();
        usort($rawCategories, function($a, $b) {
                    return strcmp($a->Category->name, $b->Category->name);
                });
        foreach ($rawCategories as $category) {
            $categories[$category->id] = $category->Category->name;  //bottleneck
            $priceCategories[$category->id] = $category->price;
        }

        $form->addSelect("presentedCategory_id", "Kategorie")
                ->setItems($categories)
                ->setPrompt("-- Kategorie --")
                ->addRule(Form::FILLED, "Prosím zvolte kategorii");

        $form->addText("SINumber", "Číslo SI")
                ->addCondition(Form::FILLED)->addRule(Form::NUMERIC, "Číslo SI není číslo.");

        $form->addTextArea("note", "Poznámka");

        $form->addGroup("Poplatky");

        $fl = \dibi::select("c.name AS cname, c.id AS cid, o.name AS oname, o.id AS oid, o.price AS price")
                ->from(":t:app_additionalCostOption AS o")
                ->leftJoin(":t:app_additionalCost AS c")->on("c.id = o.cost_id")
                ->leftJoin(":t:app_race AS r")->on("r.id = o.race_id")
                ->where("r.id = %i", $race->id);

        $cont = $form->addContainer("cost");

        $priceCosts = array();
        foreach ($fl->fetchAssoc("cid,oid") as $cost) {
            $cost = array_values($cost);

            $options = array();
            foreach ($cost as $option) {
                $options[$option["oid"]] = $option["oname"] . " (" . Helpers::currency($option["price"]) . ")";
                $priceCosts[$option["oid"]] = $option["price"];
            }
            $cont->addRadioList($cost[0]["cid"], $cost[0]["cname"])
                    ->setItems($options)
                    ->addRule(Form::FILLED, "Prosím vyberte možnost u poplatku '{$cost[0]['cname']}'.");
//            $cont->addSelect($cost[0]["cid"], $cost[0]["cname"])
//                    ->setItems($options)
//                    ->setPrompt("-- Zvolte --")
//                    ->addRule(Form::FILLED, "Prosím vyberte možnost u poplatku '{$cost[0]['cname']}'.");
        }


        if ($accountSelection) {
            $form->addSelect("account_id", "Platit z účtu")
                    ->setItems(Account::findAll(array("active" => 1))->orderBy('name')->fetchPairs("id", "name"))
                    ->setPrompt("-- Účet --")
                    ->addRule(Form::FILLED, "Prosím vyberte účet");
        }

        $form->addText("price", "Cena")
                ->setValue("---")
                ->getControlPrototype()->setReadonly("readonly");

        $form->setCurrentGroup();


        $form->addSubmit('save', $entry->getState() == \Ormion\Record::STATE_NEW ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        //it is there from somewhere else ??
        //$form->addProtection("Vypršela časová platnost formuláře, zkontrolujte jej a odešlete prosím znova.");

        $form->setRenderer(new EntryFormRenderer($priceCategories, $priceCosts, array(), $defSI, $defAccount, $accountSelection));

        return $form;
    }

}

class EntryFormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer {

    /**
     * Translates category id to price
     * @var array of double
     */
    protected $priceCategories;

    /**
     * Translates cost option id to price
     * @var array of double
     */
    protected $priceCosts;

    /**
     * Translates user id to category id
     * @var array of int
     */
    protected $defCategories;

    /**
     * Translates user id to SI number
     * @var array of int
     */
    protected $defSI;

    /**
     * Translates user id to account number
     * @var array of int
     */
    protected $defAccount;

    /**
     * @var bool
     */
    protected $accountSelection;

    /**
     *
     * @param array $priceCategories
     * @param array $priceCosts
     * @param array $defCategories
     * @param array $defSI
     * @param array $defAccount
     * @param bool  $accountSelection
     */
    public function __construct($priceCategories, $priceCosts, $defCategories, $defSI, $defAccount, $accountSelection) {
        $this->priceCategories = $priceCategories;
        $this->priceCosts = $priceCosts;
        $this->defCategories = $defCategories;
        $this->defSI = $defSI;
        $this->defAccount = $defAccount;
    }

    public function render(Form $form, $mode = NULL) {

        $template = new \Nette\Templating\FileTemplate(dirname(__FILE__) . DIRECTORY_SEPARATOR . "template.latte");
        $template->registerFilter(new \Nette\Latte\Engine());

        $template->form = parent::render($form, $mode);


        $template->priceCategories = $this->priceCategories;
        $template->priceCosts = $this->priceCosts;
        $template->defCategories = $this->defCategories;
        $template->defSI = $this->defSI;
        $template->defAccount = $this->defAccount;

        $template->selRacer = $form["racer_id"]->getHtmlId();
        $template->selCategory = $form["presentedCategory_id"]->getHtmlId();
        $template->accountSelection = $this->accountSelection;
        if ($this->accountSelection) {
            $template->selAccount = $form["account_id"]->getHtmlId();
        }
        $template->txtSI = $form["SINumber"]->getHtmlId();
        $template->txtPrice = $form["price"]->getHtmlId();

        $template->costList = array();

        foreach ($form["cost"]->getControls() as $costType) {
            $template->costList[] = $costType->getHtmlName();
        }




        return (string) $template;
    }

}