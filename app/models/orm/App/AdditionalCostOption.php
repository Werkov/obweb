<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_additionalCostOption
 * @hasOne(name = Race, referencedEntity = \Model\App\Race, column = race_id)
 * @hasOne(name = AdditionalCost, referencedEntity = \Model\App\AdditionalCost, column = cost_id)
 * @@hasMany(name = SelectedOptions, referencedEntity = \Model\App\SelectedOption, column = option_id)
 */
class AdditionalCostOption extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "app_race";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->race_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Race::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    protected function init() {
        parent::init();
        $this->addBehavior(new BalanceBehavior("price"));
    }

}
