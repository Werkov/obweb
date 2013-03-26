<?php

namespace Model\Link;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table link_category
 * @hasMany(name = Links, referencedEntity = \Model\Link\Link, column = category_id)
 */
class Category extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "link_category";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
