<?php

namespace Model\Doc;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table doc_filetype
 * @hasMany(name = Files, referencedEntity = \Model\Doc\File, column = filetype_id)
 */
class Filetype extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "doc_filetype";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

}
