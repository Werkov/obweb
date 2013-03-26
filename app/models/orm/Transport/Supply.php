<?php

namespace Model\Transport;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table transport_supply
 * @hasMany(name = Messages, referencedEntity = \Model\Transport\Message, column = supply_id)
 * @hasMany(name = Realizations, referencedEntity = \Model\Transport\Realization, column = supply_id)
 * @hasOne(name = Event, referencedEntity = \Model\Transport\Event, column = event_id)
 * @hasOne(name = Car, referencedEntity = \Model\Transport\Car, column = car_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = driver_id)
 */
class Supply extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "transport_supply";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->event_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Event::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
