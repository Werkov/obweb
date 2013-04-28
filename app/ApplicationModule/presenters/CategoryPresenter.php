<?php

namespace ApplicationModule;

use Model\App\Category;

final class CategoryPresenter extends \RecordPresenter {

// <editor-fold desc="Fields">
    protected static $class = "\Model\App\Category";

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\EGrid($this, $name);

// model
        $grid->setModel(new \Gridito\DibiFluentModel($this->getCategoriesQuery()));

        $grid->getModel()->setPrimaryKey("id");


// columns

        $f = new \Nette\Forms\Controls\TextInput();
        $grid->addColumn("name", "Název kategorie")->setSortable(true);
        /* ->setField("name")
          ->setControl($f->addRule(\Nette\Forms\Form::FILLED)); */

        $f = new \Nette\Forms\Controls\TextInput();
        $grid->addColumn("defaultPrice", "Výchozí startovné")->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo \OOB\Helpers::currency($row->defaultPrice);
                        })
                ->setCellClass("text-right")
                ->setField("defaultPrice")
                ->setControl($f->addRule(\Nette\Forms\Form::FILLED)
                        ->addRule(\Nette\Forms\Form::FLOAT));

// buttons
        $pres = $this;
        $grid->setShowAdd(false);
        $grid->setShowEdit(true);




        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();

                    $r = Category::find($values["editId"]);

                    if (!$r)
                        return;

                    $r->defaultPrice = $values["defaultPrice"];
                    $r->save();
                    $pres->flashMessage("Výchozí startovné u " . $r->name . " změněno.");
                });
//settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        throw new \Nette\Application\ApplicationException("Not implementd.");
    }

    protected function createComponentPriceEdit($name) {
        $component = new \OOB\CategoryPriceEdit();

        $categories = $this->getCategoriesQuery();
        $component->setDataSource(function() use($categories) {
                    return $categories->fetchPairs('name', 'defaultPrice');
                });

        $pres = $this;
        $categoriesData = $categories->fetchAll();
        $component->setDataHandler(function($data) use($pres, $categoriesData) {
                    $report = array();

                    // process existing categories
                    foreach ($categoriesData as $row) {
                        $name = $row['name'];
                        if (isset($data[$name])) {
                            $category = \Model\App\Category::create($row);
                            if ($category->defaultPrice != $data[$name]) {
                                $report[] = 'Startovné kategorie ' . $name . ' změněno z ' . $category->defaultPrice . ' na ' . $data[$name] . '.';
                                $category->defaultPrice = $data[$name];
                                $category->save();
                            }
                            unset($data[$name]);
                        }
                    }

                    // report ignored entries in CSV
                    foreach ($data as $category) {
                        $report[] = 'Neexistující kategorie ' . $category . ' v CSV.';
                    }

                    foreach ($report as $message) {
                        $pres->flashMessage($message);
                    }
                    $pres->flashMessage('CSV importováno.');
                });

        $component->setFilename('defaults.csv');

        return $component;
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

// </editor-fold>

    private function getCategoriesQuery() {
        return \dibi::select("id, name, defaultPrice")
                        ->from(":t:app_category AS co");
    }

}

