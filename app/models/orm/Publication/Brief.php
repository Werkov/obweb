<?php

namespace Model\Publication;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table public_brief
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = author_id)
 */
class Brief extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "publication_brief";
    }
    protected function init() {
        $this->addBehavior(new \Ormion\Behavior\Texy('text', 'text_src'));
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        if (isset($acl->getQueriedResource()->author_id)) {
            return $acl->getQueriedResource()->author_id == $acl->getQueriedRole()->getIdentity()->id;
        } else {
            return true;
        }
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

}
