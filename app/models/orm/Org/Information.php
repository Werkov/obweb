<?php

namespace Model\Org;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table org_information
 * @hasMany(name = InformationValuess, referencedEntity = \Model\Org\InformationValues, column = information_id)
 */
class Information extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "org_information";
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
