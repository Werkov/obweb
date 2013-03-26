<?php

namespace Model\System;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table system_privilege
 * @hasMany(name = Acls, referencedEntity = \Model\System\Acl, column = privilege_id)
 */
class Privilege extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "system_privilege";
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
