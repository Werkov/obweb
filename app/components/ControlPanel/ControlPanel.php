<?php

namespace OOB;

use Model\System\User;
use Model\App\Member;
use Model\App\Backer;

class ControlPanel extends \Nette\Application\UI\Control {

    private $actions = array();
    private $columnsCount = 5;

    public function getColumnsCount() {
        return $this->columnsCount;
    }

    public function setColumnsCount($columnsCount) {
        $this->columnsCount = $columnsCount;
    }

    public function addAction($action, $forcePosition = true, $parameters = null, $icon = null) {
        $data = new \stdClass();
        $data->action = $action;
        $data->parameters = $parameters;
        $data->icon = $icon ? $icon : $this->actionToIcon($action);
        $data->link = $parameters ? $this->presenter->link($action, $parameters) : $this->presenter->link($action); // to match parameter count
        $data->name = $this->presenter->getComponent('navigation')->getTitle($action, $parameters, ' – ');
        $data->order = count($this->actions);
        $data->forcePosition = $forcePosition;
        $this->actions[] = $data;
    }

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/template.latte");
        $template->registerHelperLoader('\OOB\Helpers::loader');
        return $template;
    }

    public function render() {
        $this->sortActions();
        $this->template->actions = $this->actions;
        $this->template->render();
    }

    

    /**
     * Sort actions acording to (frecency, order of insertion)
     * @return void
     */
    protected function sortActions() {
        $frecency = $this->getPresenter()->getContext()->getService('frecency');
        array_walk($this->actions, function(&$item, $index) use($frecency) {
                    $item->frecency = $frecency->getFrecency($item->action, $item->parameters);
                });

        usort($this->actions, function($a, $b) use($frecency) {
                    if ($a->forcePosition && $b->forcePosition) {
                        $d = $b->frecency - $a->frecency;
                        if (abs($d) < 1e-3) {
                            return $a->order - $b->order;
                        } else {
                            return ($d > 0) ? 1 : -1;
                        }
                    } else if (!$a->forcePosition && $b->forcePosition) {
                        return 1;
                    } else if ($a->forcePosition && !$b->forcePosition) {
                        return 0;
                    } else if (!$a->forcePosition && !$b->forcePosition) {
                        $d = $b->frecency - $a->frecency;
                        if (abs($d) < 1e-3) {
                            return $a->order - $b->order;
                        } else {
                            return ($d > 0) ? 1 : -1;
                        }
                    }
                });
    }

    protected function actionToIcon($action) {
        static $prefixToIcon = array(
    ':Personal:Article:add' => 'newspaper',
    ':Personal:Brief:add' => 'star',
    ':Personal:Gallery:add' => 'image',
    ':Application:Race:add' => 'app',
    ':Personal:PersonalSettings:default' => 'spanner',
    ':Organization:Brief' => 'star',
    ':Organization:' => 'circle-green',
    ':Application:Account:list' => 'table',
    ':Application:Entry:list' => 'app',
    ':Application:Entry:plist' => 'app',
    ':Application:Race:edit' => 'app',
    ':Admin:User:' => 'user',
    ':Admin:Member:' => 'users-two',
    ':Admin:Backer:' => 'users-two',
    ':Personal:Photo:list' => 'image',
        );
        foreach ($prefixToIcon as $prefix => $value) {
            if (strpos('%' . $action, '%' . $prefix) !== false) {

                return $value;
            }
        }
        return 'circle-blue';
    }

}