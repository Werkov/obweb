<?php

namespace Model\Org;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table org_informationValues
 * @hasOne(name = Race, referencedEntity = \Model\Org\Race, column = race_id)
 * @hasOne(name = Information, referencedEntity = \Model\Org\Information, column = information_id)
 */
class InformationValues extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "org_race";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->race_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->race_id;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
$row = Race::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->event_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
