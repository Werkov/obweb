<?php

namespace PersonalModule;

use \Model\Gallery\Photo;

/**
 * @generator MScaffolder
 */
final class PhotoPresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\Model\Gallery\Photo";
    protected static $parentClass = "\Model\Gallery\Gallery";
    protected static $parentColumn = "gallery_id";

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("p.id AS pid, p.id AS id, file, CONCAT(u.name, ' ', u.surname) AS author, u.id AS uid")
                                ->from(":t:gallery_photo AS p")
                                ->leftJoin(":t:system_user AS u")->on("u.id = p.author_id")
                                ->where("gallery_id = %i", $this->getParam("parent"))
        ));

        $grid->getModel()->setPrimaryKey("p.id");


        // columns
        $grid->addColumn("file", "Obrázek")->setSortable(true)
                ->setRenderer(function($row, $col) {
                            $photo = Photo::create($row);

                            $i = \Nette\Utils\Html::el("img");
                            $i->alt = $row->file;
                            $i->src = $photo->getThumbnailUrl(140);

                            echo $i;
                        });
        $grid->addColumn("author", "Vložil")->setSortable(true);

        // buttons
        $pres = $this;

        $grid->addToolbarButton("upload", "Nahrát fotografie")
                ->setLink($this->link("Gallery:upload", array("id" => $this->getParam("parent"))))
                ->setIcon("document")
                ->setVisible($pres->getUser()->isAllowed(Photo::create(), "add"));



        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                            return $pres->link("edit", array("id" => $row->pid));
                        })->setIcon("pencil")
                ->setVisible(function($row) use($pres) {
                            return $pres->getUser()->isAllowed(Photo::create(array("author_id" => $row->uid)), "edit");
                        });


        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
                ->setVisible(function($row) use($pres) {
                            return $pres->getUser()->isAllowed(Photo::create(array("author_id" => $row->uid)), "delete");
                        });

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);


        $form->addTextArea("desc", "Popis")
                ->addRule(\Nette\Forms\Form::FILLED);

        $form->addText("file", "Soubor")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->setDisabled(true);


        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>

    public function actionAdd($id, $parent) {
        throw new \Nette\Application\BadRequestException("Fotografie je nutno uploadovat hromadně.");
    }

    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);

        $this->template->registerHelper('thumb', 'LayoutHelpers::thumb');

        $baseUri = \Nette\Environment::getVariable('baseUrl');
        ;
        $path = \Nette\Environment::getConfig("gallery");
        $path = $path["path"];

        $this->template->img = $path . '/' . $this->currentRecord->file;
    }

}

