<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_member
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = user_id)
 */
class Member extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "system_user";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = \Model\System\User::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->getFullname();

        return $res;
    }

    /**
     *
     * @return \DibiFluent  user that could possibly be members
     */
    public static function getPossibleUsers() {
        return \dibi::select(
                                "u.id AS id, CONCAT(u.name, ' ', u.surname) AS name")
                        ->from(":t:system_user AS u")
                        ->leftJoin(":t:app_backer AS b")->on("b.user_id = u.id")
                        ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
                        ->where("u.active = 1")
                        ->where("(u.registration IS NULL OR b.active = 0)")
                        ->where("m.user_id IS NULL")
                        ->orderBy("name");
    }

}
