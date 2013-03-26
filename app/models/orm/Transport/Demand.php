<?php

namespace Model\Transport;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table transport_demand
 * @hasOne(name = Event, referencedEntity = \Model\Transport\Event, column = event_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = customer_id)
 * @hasMany(name = Realizations, referencedEntity = \Model\Transport\Realization, column = demand_id)
 */
class Demand extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "transport_demand";
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
