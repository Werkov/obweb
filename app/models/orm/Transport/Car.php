<?php

namespace Model\Transport;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table transport_car
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = owner_id)
 * @hasMany(name = Supplys, referencedEntity = \Model\Transport\Supply, column = car_id)
 */
class Car extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "transport_car";
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
