<?php

namespace PersonalModule;

use \Model\Publication\Article;

/**
 * @generator MScaffolder
 */
final class ArticlePresenter extends \RecordPresenter {

    // <editor-fold desc="Fields">
    protected static $class = "\\Model\\Publication\\Article";

    // </editor-fold>
// <editor-fold desc="Render views">
    public function renderEdit($id, $parent) {
        parent::renderEdit($id, $parent);

        if (is_array($this->currentRecord->Tags)) {
            $val = \array_map(function($rc) {
                        return $rc->id;
                    }, $this->currentRecord->Tags);
        } else {
            $val = $this->currentRecord->Tags->fetchColumn('id');
        }

        $this["editForm"]->setDefaults(array(
            "tags_id" => $val,
        ));
    }

    // </editor-fold>
// <editor-fold desc="Signals">
    protected function setRelations(\Nette\Application\UI\Form $form) {
        $values = $form->getValues();
        $this->currentRecord->Tags = array_map(function ($id) {
                    return \Model\Publication\Tag::create($id);
                }, $values["tags_id"]);
        if ($this->action == "add") {
            $this->currentRecord->author_id = $this->getUser()->getId();
            $this->currentRecord->published = new \DateTime();
        }
    }

    // </editor-fold>
    // <editor-fold defaultstate="collapsed" desc="Components">


    protected function createComponentGrid($name) {
        $grid = new \Gridito\Grid($this, $name);

        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("a.id AS aid, a.title AS title, CONCAT(u.name, ' ', u.surname) AS author, [a.perex], a.published AS published, u.id AS uid")
                                ->from(":t:public_article AS a")
                                ->leftJoin(":t:system_user AS u")->on("u.id = a.author_id")
                                ->orderBy('a.published DESC')
        ));


        // columns
        $grid->getModel()->setPrimaryKey("a.id");
        $grid->addColumn("published", "Vydáno")->setSortable(true);
        $grid->addColumn("title", "Nadpis")->setSortable(true);
        $grid->addColumn("author", "Autor")->setSortable(true);


        // buttons
        $pres = $this;

        $grid->addToolbarButton("add", "Přidat")
                ->setLink($this->link("add"))
                ->setIcon("document")
                ->setVisible($pres->getUser()->isAllowed(Article::create(), "add"));


        /* $grid->addButton("sub0", "Comments »")->setLink(function ($row) use($pres) {
          return $pres->link("Comment:list", array(
          "parent" => $row->id,
          ));
          })->setIcon("")
          ->setAjax(false); */

        $grid->addButton("edit", "Upravit")->setLink(function ($row) use($pres) {
                            return $pres->link("edit", array("id" => $row->aid));
                        })->setIcon("pencil")
                ->setVisible(function($row) use($pres) {
                            return $pres->getUser()->isAllowed(Article::create(array("author_id" => $row->uid)), "edit");
                        });

        $grid->addButton("delete", "Smazat")
                ->setHandler(callback($this, "deleteRecord"))
                ->setIcon("trash")
                ->setAjax(true)->setShowText(false)
                ->setConfirmationQuestion(callback($this, "ttDeleteQuestion"))
                ->setVisible(function($row) use($pres) {
                            return $pres->getUser()->isAllowed(Article::create(array("author_id" => $row->uid)), "delete");
                        });

        //settings
        $grid->setItemsPerPage(self::IPP);

        return $grid;
    }

    protected function createComponentForm($name, $new = false) {
        $form = new \Nette\Application\UI\Form($this, $name);

        $form->addText("title", "Nadpis")
                ->addRule(\Nette\Forms\Form::FILLED)
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, null, 50);

        $f = new \OOB\Texyla('Perex');
        $form->addComponent($f, 'perex_src');
        $f->setTexyConfiguration('texy');

        $f = new \OOB\Texyla('Text');
        $form->addComponent($f, 'text_src');
        $f->addRule(\Nette\Forms\Form::FILLED);
        $f->setTexyConfiguration('texy');

        $form->addCheckbox("public", "Veřejný článek");


        $form->addMultipleTextSelect("tags_id", new \Model\Publication\TagItemModel(), "Štítky")
                ->setUnknownMode(\OOB\MultipleTextSelect::N_INSERT)
                ->addRule(\Nette\Forms\Form::VALID, "Štítky nejsou správně.");
        

        $form->addSubmit('save', $new ? 'Přidat' : 'Uložit');
        $form->addSubmit('cancel', 'Storno')->setValidationScope(NULL);

        $form->onSuccess[] = callback($this, 'formSubmitted');

        $form->addProtection('Vypršela časová platnost formuláře, prosím odešlete znova.');

        $form->addHidden("occ_hash", null);
        return $form;
    }

    // </editor-fold>
}

