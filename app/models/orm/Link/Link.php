<?php

namespace Model\Link;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table link_link
 * @hasOne(name = Category, referencedEntity = \Model\Link\Category, column = category_id)
 */
class Link extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "link_link";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->category_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Category::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
