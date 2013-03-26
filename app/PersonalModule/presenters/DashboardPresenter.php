<?php

namespace PersonalModule;

final class DashboardPresenter extends \AuthenticatedPresenter {

    protected function createComponentControlPanel($name) {
        $panel = new \OOB\ControlPanel($this, $name);
        if ($this->getUser()->isAllowed('publication_brief', 'add'))
            $panel->addAction(':Personal:Brief:add');
        if ($this->getUser()->isAllowed('publication_article', 'add'))
            $panel->addAction(':Personal:Article:add');
        if ($this->getUser()->isAllowed('gallery_gallery', 'add'))
            $panel->addAction(':Personal:Gallery:add');
        if ($this->getUser()->isAllowed('app_race', 'add'))
            $panel->addAction(':Application:Race:add');
        $panel->addAction(':Personal:PersonalSettings:default');
        $this->addSuggestedAction($panel);
        
        return $panel;
    }

    private function addSuggestedAction(\OOB\ControlPanel $panel) {
        $frecency = $this->getContext()->getService('frecency');
        $allowed = array(
            ':Organization:',
            ':Application:Account:list',
            ':Application:Entry:list',
            ':Application:Entry:plist',
            ':Application:Race:edit',
            ':Admin:User:',
            ':Admin:Member:',
            ':Admin:Backer:',
            ':Personal:Photo:list',
        );
        foreach ($frecency->getAll() as $item) {
            foreach ($allowed as $prefix) {
                if (strpos('%' . $item->action, '%' . $prefix) !== false) {
                    $panel->addAction($item->action, false, $item->parameters);
                }
            }
        }
    }

    public function handleClean() {
        $frecency = $this->getContext()->getService('frecency');
        $frecency->clean();
        if ($this->isAjax()) {
            $this->invalidateControl();
        } else {
            $this->redirect('this');
        }
    }

}

