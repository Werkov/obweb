<?php

namespace OrganizationModule;

use \Model\Org\Brief;

/**
 * @generator MScaffolder
 */
final class BriefPresenter extends \RecordPresenter {

// <editor-fold desc="Fields">
    protected static $class = "\Model\Org\Brief";
    protected static $parentClass = "\Model\Org\Event";
    protected static $parentColumn = "event_id";

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);


        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("b.id AS bid, CONCAT(u.name, ' ', u.surname) AS author, [b.text], b.published AS published, u.id AS uid")
                                ->from(":t:org_brief AS b")
                                ->leftJoin(":t:system_user AS u")->on("u.id = b.author_id")
                                ->where("b.event_id = %i", $this->getParam("parent"))
        ));

        // columns
        $grid->getModel()->setPrimaryKey("b.id");
        $grid->addColumn("published", "Vydáno")->setSortable(true);
        $grid->addColumn("text", "Text")->setSortable(true)
                ->setRenderer(function($record, $col) {
                            echo \Nette\Utils\Strings::truncate(\strip_tags($record->text), 30);
                        });
        $grid->addColumn("author", "Autor")->setSortable(true);

        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add", array("parent" => $this->getParam("parent"))))
                ->setIcon("document")
                ->setVisible($this->ACLadd());


        $testRecord = Brief::create(array("Event" => $this->parentRecord));
        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                            return $pres->link("edit", array("id" => $row->bid));
                        })->setIcon("pencil")
                ->setVisible(function($row) use($pres, $testRecord) {
                            $testRecord->author_id = $row->uid;
                            return $pres->getUser()->isAllowed($testRecord, "edit");
                        });


        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
                ->setVisible(function($row) use($pres, $testRecord) {
                            $testRecord->author_id = $row->uid;
                            return $pres->getUser()->isAllowed($testRecord, "delete");
                        });

//settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);


        $f = new \OOB\Texyla('Text');
        $f->addRule(\Nette\Forms\Form::FILLED);
        $form->addComponent($f, 'text_src');
        $f->setTexyConfiguration('texy');


        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

// </editor-fold>
    protected function setRelations(\Nette\Application\UI\Form $form) {
        parent::setRelations($form);
        if ($this->action == "add") {
            $this->currentRecord->published = new \DateTime();
            $this->currentRecord->author_id = $this->getUser()->getId();
        }
    }

}

