<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_additionalCost
 * @hasMany(name = AdditionalCostOptions, referencedEntity = \Model\App\AdditionalCostOption, column = cost_id)
 * @hasMany(name = SelectedOptions, referencedEntity = \Model\App\SelectedOption, column = cost_id)
 */
class AdditionalCost extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "app_additionalCost";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = AdditionalCost::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
