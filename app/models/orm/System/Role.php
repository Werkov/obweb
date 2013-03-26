<?php

namespace Model\System;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table system_role
 * @manyToMany(name = Users, referencedEntity = \Model\System\User, connectingTable = system_user2role, referencedKey = user_id, localKey = role_id)
 * @hasMany(name = Acls, referencedEntity = \Model\System\Acl, column = role_id)
 * @hasOne(name = Role, referencedEntity = \Model\System\Role, column = parent_id)
 * @hasMany(name = Roles, referencedEntity = \Model\System\Role, column = parent_id)
 */
class Role extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "system_role";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = self::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

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
