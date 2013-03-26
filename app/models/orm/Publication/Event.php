<?php

namespace Model\Publication;

use Nette\Security\Permission;

/**
 *
 * @table public_event
 */
class Event extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "publication_event";
    }


    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

}
