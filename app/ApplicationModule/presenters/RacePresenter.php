<?php

namespace ApplicationModule;

use \Model\App\Race;
use \Nette\Caching\Cache;

/**
 * @generator MScaffolder
 */
final class RacePresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\App\Race";

    /**
     *
     * @var Race
     */
    protected $race;

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);
        Race::updateStatus();
        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("begin, r.name AS name, r.id AS id, COUNT(e.id) AS entries, r.status AS status, r.deadline AS deadline")
                                ->from(":t:app_race AS r")
                                ->leftJoin(":t:app_race2category AS rc")->on("rc.race_id = r.id")
                                ->leftJoin(":t:app_entry AS e")->on("e.presentedCategory_id = rc.id")
                                ->groupBy("r.id")
        ));
        $grid->getModel()->setSorting("begin", "DESC");

        // columns
        $grid->addColumn("begin", "Datum")->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::mydate($row->begin);
                        });
        $grid->addColumn("name", "Název")->setSortable(true);
        $grid->addColumn("status", "Stav")->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::iconRaceStatus($row->status);
                        });

        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document");


        $grid->addButton("sub0", "Poplatky »")->setLink(function ($row) use($pres) {
                            return $pres->link("CostOption:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("money")
                ->setAjax(false)
                ->setShowText(false)
                ->setVisible(function($row) {
                            return $row->entries == 0;
                        });

        $grid->addButton("sub1", "Ceny kategorií »")->setLink(function ($row) use($pres) {
                            return $pres->link("categories", array(
                                        "id" => $row->id,
                                    ));
                        })->setIcon("table")
                ->setAjax(false)
                ->setShowText(false)
                ->setVisible(function($row) {
                            return $row->entries == 0;
                        });

        $grid->addButton("sub2", "Přihlášky »")->setLink(function ($row) use($pres) {
                            return $pres->link("Entry:list", array(
                                        "parent" => $row->id,
                                    ));
                        })->setIcon("applications")
                ->setShowText(false)
                ->setAjax(false);

        $grid->addButton("open", "Zveřejnit")
                ->setHandler(function($id) use($pres) {
                            $race = Race::find($id);
                            if (!$race)
                                return;
                            $race->status = Race::STATUS_APP;
                            $race->save();
                            $pres->flashMessage("Závod $race->name byl otevřen pro přihlašování.");
                        })
                ->setIcon("unlocked")
                ->setAjax(true)->setShowText(false)
                ->setVisible(function($row) {
                            return $row->status == Race::STATUS_EDIT;
                        });
        $grid->addButton("open2", "Otevřít pro přihlašování")
                ->setHandler(function($id) use($pres) {
                            $race = Race::find($id);
                            if (!$race)
                                return;
                            $race->status = Race::STATUS_APP;
                            $race->save();
                            $pres->flashMessage("Závod $race->name byl (znovu)otevřen pro přihlašování.");
                        })
                ->setIcon("unlocked")
                ->setAjax(true)->setShowText(false)
                ->setVisible(function($row) {
                            return $row->status == Race::STATUS_CLOSED && (($row->deadline->getTimestamp() + 86400) > time());
                        });


        /* $grid->addButton("close", "Editační mód")
          ->setHandler(function($id) {

          })
          ->setIcon("pencil")
          ->setAjax(true)->setShowText(false)
          ->setVisible(function($row) {
          return $row->status == 1;
          })
          ->setConfirmationQuestion("Vážně chcete vrátit závod do editačního režimu? Budou smazány všechny jeho přihlášky."); */

        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                    return $pres->link("edit", array("id" => $row->id));
                })->setIcon("pencil")->setShowText(false);

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

//$form->addDatePicker("start", "Začátek");

        $form->addText("name", "Název")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);

        $form->addText("type", "Druh závodu")
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 40);

        $form->addText("organizer", "Pořadatel")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 30);

        $form->addText("place", "Shromaždiště")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 60);

        $form->addText("start", "Čas startu 00")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 40);

        $form->addDatePicker("begin", "Datum (začátku)")
                ->addRule(\Nette\Forms\Form::FILLED);

        $form->addDatePicker("end", "Datum (konce, jen vícedenní)");

        $form->addDatePicker("deadline", "Uzávěrka přihlášek")
                ->addRule(\Nette\Forms\Form::FILLED);

        $form->addMultipleTextSelect("categories", new \Model\App\CategoryItemModel(), "Kategorie")
                ->setDisabled(!$new && $this->currentRecord->getApplications()->count() > 0)
                ->setUnknownMode(\OOB\MultipleTextSelect::N_INSERT)
                ->addRule(\Nette\Forms\Form::VALID, "Seznam kategorií není správně.");

        $form->addTextUrl("web", "Web")
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 255);

        $form->addTextArea("note", "Poznámka");


        foreach (\Model\System\User::findAll(array("active" => 1))->orderBy('surname, name')->fetchAll() as $user) {
            $items[$user->id] = $user->getFullName();
        }
        $form->addSelect("manager_id", "Přihlašuje")
                ->setItems($items);

        $items = \dibi::select("id, name")->from(":t:app_tag")->orderBy("name")->fetchPairs("id", "name");
        $form->addSelect("tag_id", "Označení závodu")
                ->setItems($items);


        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    protected function createComponentGrdCategories($name) {
        $grid = new \Gridito\EGrid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("c.name AS name, p.price AS price, p.id AS id")
                                ->from(":t:app_race2category AS p")
                                ->leftJoin(":t:app_category AS c")->on("c.id = p.category_id")
                                ->where("p.race_id = %i", $this->race->id)
                                ->orderBy("c.name")
        ));


        // columns
        $grid->addColumn("name", "Název")->setSortable(true);

        $f = new \Nette\Forms\Controls\TextInput();
        $grid->addColumn("price", "Cena")->setSortable(true)
                ->setField("price")
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::currency($row->price);
                        })
                ->setCellClass("text-right")
                ->setControl($f->addRule(\Nette\Forms\Form::FILLED)
                        ->addRule(\Nette\Forms\Form::FLOAT));

        // buttons
        $pres = $this;

        $grid->setShowAdd(false);

        /* $grid->addToolbarButton("add", "Přidat")
          ->setLink($this->link("add"))
          ->setIcon("document");

          $grid->addToolbarButton("aadd", "Přidat 2")
          ->setLink($this->link("add"))
          ->setIcon("document"); */


        /* $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
          return $pres->link("edit", array("id" => $row->id));
          })->setIcon("pencil"); */

        $grid->addButton("delete", "Smazat")
                ->setHandler(function($id) use($pres) {
                            $rc = \Model\App\Race2category::find($id);
                            if ($rc) {
                                $rc->delete();
                                $pres->flashMessage("Kategorie smazána.");
                            }
                        })
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion("Vážně chcete smazat kategorii?");

        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();
                    $rc = \Model\App\Race2category::find($values["editId"]);
                    if ($rc) {
                        $rc->price = $values["price"];
                        $rc->save();
                        $pres->flashMessage("Cena upravena.");
                    }
                });


        /* $grid->addWindowButton("detail", "Detail", array(
          "handler" => function ($user) {
          echo "AHOJ";
          return;
          echo "<p><strong>$user->name </strong></p>";
          echo "<table>";
          echo "<tr><th>ID</th><td>$user->id</td></tr>";
          echo "<tr><th>Username</th><td></td></tr>";
          echo "<tr><th>E-mail</th><td>mail</td></tr>";
          echo "<tr><th>Active</th><td>ano</td></tr>";
          echo "</table>";
          },
          "icon" => "ui-icon-search",
          )); */

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    // </editor-fold>

    protected function setRelations(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();
        if ($this->action == "add") {
            $this->currentRecord->Categories = array_map(function ($id) {
                        return \Model\App\Race2category::create(array("category_id" => $id, "price" => 0));
                    }, $values["categories"]);
            $this->currentRecord->status = Race::STATUS_EDIT;
        } else if ($this->action == "edit" && $this->currentRecord->getApplications()->count() == 0) {
            $rid = $this->currentRecord->id;
            $this->currentRecord->Categories = array_map(function ($id) use($rid) {
                        $c = \Model\App\Race2category::find(array("race_id" => $rid, "category_id" => $id));
                        if (!$c)
                            $c = \Model\App\Race2category::create(array("category_id" => $id, "price" => 0));
                        return $c;
                    }, $values["categories"]);
        }
    }

    protected function saveRecord(\Nette\Application\UI\Form $form) {
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('app_race'),
        ));
        parent::saveRecord($form);
    }

    public function deleteRecord($id) {
        \Nette\Environment::getCache()->clean(array(
            Cache::TAGS => array('app_race'),
        ));
        parent::deleteRecord($id);
    }

    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);
        if (is_array($this->currentRecord->Categories)) {
            $val = \array_map(function($rc) {
                        return $rc->category_id;
                    }, $this->currentRecord->Categories);
        } else {
            $val = $this->currentRecord->Categories->fetchColumn("category_id");
        }

        $this["editForm"]->setDefaults(array(
            "categories" => $val,
        ));
    }

    //<editor-fold desc="Categories">

    public function actionCategories($id) {
        $this->race = Race::find(($id === null) ? 0 : $id);

        if (!$this->race) {
            throw new \Nette\Application\BadRequestException("Závod nenalezen.", 404);
        }
        //check ACL
        if (!$this->ACLedit($this->race)) {
            throw new \Nette\Application\BadRequestException(static::ttUnauthorizedAccess($this->race, "edit"), 403);
        }
    }

    public function renderCategories($id) {
        
    }

    //</editor-fold>
    //<editor-fold desc="Public">

    protected function startup() {
        if ($this->getAction() == "default" || $this->getAction() == "detail") {
            $this->suppressAuth = true;
        } else {
            $this->suppressAuth = false;
        }
        parent::startup();
    }

    protected function createComponentGrdRaces($name) {
        Race::updateStatus();
        $grid = new \Gridito\Grid($this, $name);

        // model
        $fl = \dibi::select("r.begin, r.url, r.name AS name, r.id AS id, COUNT(e.id) AS entries, r.status AS status, r.place, r.organizer")
                ->from(":t:app_race AS r")
                ->leftJoin(":t:app_race2category AS rc")->on("rc.race_id = r.id")
                ->leftJoin(":t:app_entry AS e")->on("e.presentedCategory_id = rc.id")
                ->groupBy("r.id")
                //->orderBy("begin ASC")
                ->where("r.status != 0");

        if ($this->getParam("old") == true) {
            $fl->where("IFNULL(r.end, r.begin) < NOW()");
        } else {
            $fl->where("IFNULL(r.end, r.begin) >= NOW()");
        }



        $pres = $this;

        // columns & buttons
        $grid->addColumn("begin", "Datum")->setSortable(true)->setRenderer(function($row, $column) {
                    echo \OOB\Helpers::mydate($row->begin);
                });
        $grid->addColumn("name", "Název")->setSortable(true);
        $grid->addColumn("place", "Místo")->setSortable(true);
        $grid->addColumn("organizer", "Pořadatel")->setSortable(true);

        if ($this->getUser()->isInRole("registered")) {
            $fl->select(\dibi::select("COUNT(*)")
                            ->from(":t:app_entry AS ie")
                            ->leftJoin(":t:app_race2category AS irc")->on("irc.id = ie.presentedCategory_id")
                            ->where("ie.racer_id = %i", $this->getUser()->getId())
                            ->and("irc.race_id = r.id"))->as("applied");
            $grid->addColumn("status", "Stav")->setSortable(true)
                    ->setRenderer(function($row, $column) {
                                echo \OOB\Helpers::iconRaceStatus($row->status);
                            });

            $grid->addColumn("applied", "Přihlášen")->setSortable(true)
                    ->setRenderer(function($row, $column) {
                                echo \OOB\Helpers::iconAppliactionStatus($row->applied);
                            });

            $grid->addButton("applications", "Přihlášky »")->setLink(function ($row) use($pres) {
                                return $pres->link("Entry:plist", array(
                                            "parent" => $row->id,
                                        ));
                            })/* ->setVisible(function ($row) use($pres) {
                      $race = Race::create($row);
                      $entry = \Model\App\Entry::create();
                      return ($race->status == \Model\App\Race::STATUS_APP && $pres->getUser()->isAllowed($entry, "add")) ||
                      $pres->getUser()->isAllowed($race, "applications");
                      }) */->setIcon("applications")
                    ->setShowText(false)
                    ->setAjax(false);
        }

        $grid->addButton("info", "Informace »")->setLink(function ($row) use($pres) {
                            return $pres->link("Race:detail", array(
                                        "id" => $row->id,
                                    ));
                        })/* ->setVisible(function ($row) use($pres) {
                  $race = Race::create($row);
                  $entry = \Model\App\Entry::create();
                  return ($race->status == \Model\App\Race::STATUS_APP && $pres->getUser()->isAllowed($entry, "add")) ||
                  $pres->getUser()->isAllowed($race, "applications");
                  }) */->setIcon("zoomin")
                ->setShowText(false)
                ->setAjax(false);

        $grid->setModel(new \Gridito\DibiFluentModel($fl));
        if ($this->getParam("old") == true) {
            $grid->getModel()->setSorting("begin", "DESC");
        } else {
            $grid->getModel()->setSorting("begin", "ASC");
        }




        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    public function actionDefault($old = false) { // formerly PublicList
    }

    public function actionDetail($id) {
        $r = Race::find($id);
        if (!$r) {
            throw new \Nette\Application\BadRequestException("Neexistující závod", 404);
        }

        if ($r->status == Race::STATUS_EDIT && !$this->getUser()->isAllowed($r, "edit")) {
            throw new \Nette\Application\BadRequestException("Závod není zveřejněn", 403);
        }
        
        
        $r->deadline->add(new \DateInterval('PT23H59M59S')); // correction as in DB is only date
        $this->template->race = $r;

        $fl = \dibi::select("c.name AS cname, c.id AS cid, o.name AS oname, o.id AS oid, o.price AS price")
                ->from(":t:app_additionalCostOption AS o")
                ->leftJoin(":t:app_additionalCost AS c")->on("c.id = o.cost_id")
                ->leftJoin(":t:app_race AS r")->on("r.id = o.race_id")
                ->where("r.id = %i", $r->id);

        $this->template->costs = array_values($fl->fetchAssoc("cid,oid"));
    }

    //</editor-fold>
}

