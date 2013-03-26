<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_race2category
 * @hasOne(name = Race, referencedEntity = \Model\App\Race, column = race_id) //<del>can't be used because of dependency cycle</del> should be fixed
 * @hasOne(name = Category, referencedEntity = \Model\App\Category, column = category_id)
 */
class Race2category extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "app_race2category";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->race_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

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
