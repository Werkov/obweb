<?php

namespace Model\Con;

/**
 * @generator MScaffolder
 *
 * @table con_sideblock
 */
class Sideblock extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "con_sideblock";
    }

    protected function init() {
        $this->addBehavior(new \Ormion\Behavior\Sortable('order', 'position'));
    }

}
