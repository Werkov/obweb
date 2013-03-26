<?php

namespace Model\System;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table system_acl
 * @hasOne(name = Privilege, referencedEntity = \Model\System\Privilege, column = privilege_id)
 * @hasOne(name = Resource, referencedEntity = \Model\System\Resource, column = resource_id)
 * @hasOne(name = Role, referencedEntity = \Model\System\Role, column = role_id)
 */
class Acl extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "system_role";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = Acl::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->role_id;

        return $res;
    }

    public function save($oldHash = null, $asReference = false) {
        parent::save($oldHash, $asReference);

        $cache = \Nette\Environment::getCache("ACL");
        unset($cache["authorizator"]);
    }

    public function delete() {
        parent::delete();

        $cache = \Nette\Environment::getCache("ACL");
        unset($cache["authorizator"]);
    }

}
