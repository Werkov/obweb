<?php

namespace PublicModule;

use \Model\Org\Event;

final class OrganizationPresenter extends PublicPresenter {

    // <editor-fold defaultstate="collapsed" desc="Components">
    //</editor-fold>
    //<editor-fold desc="Public">


    protected function createComponentGrdFutureEvents($name) {
        return $this->createComponentGrdEvents($name, false);
    }

    protected function createComponentGrdPastEvents($name) {
        return $this->createComponentGrdEvents($name, true);
    }

    private function createComponentGrdEvents($name, $old) {

        $grid = new \Gridito\Grid($this, $name);

        // model
        $fl = \dibi::select("DISTINCT e.start, e.end, e.url, e.name")
                        ->from(":t:org_event AS e")
                        ->leftJoin(":t:org_event2user AS eu")->on("eu.event_id = e.id");
        Event::SqlVisibility($this->getUser(), $fl);

        if ($old == true) {
            $fl->where("IFNULL(e.end, e.start) < NOW()");
        } else {
            $fl->where("IFNULL(e.end, e.start) >= NOW()");
        }


        $pres = $this;

        // columns & buttons
        $grid->addColumn("start", "Datum")->setSortable(true)->setRenderer(function($row, $column) {
                    echo \OOB\Helpers::mydate($row->start);
                });
        $grid->addColumn("name", "Název")->setSortable(true);

        $grid->addButton("info", "Informace »")->setLink(function ($row) use($pres) {
                            return $pres->link(":Organization:Homepage:default", array(\OrganizationModule\SitePresenter::URL_PARAM => $row->url));
                        })->setIcon("zoomin")
                ->setShowText(false)
                ->setAjax(false);

        $grid->setModel(new \Gridito\DibiFluentModel($fl));
        if ($old == true) {
            $grid->getModel()->setSorting("start", "DESC");
        } else {
            $grid->getModel()->setSorting("start", "ASC");
        }        


        //settings
        $grid->setItemsPerPage(\RecordPresenter::IPP);

        return $grid;
    }

    public function actionPublicList() {
        
    }

    //</editor-fold>
}

