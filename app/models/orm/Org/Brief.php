<?php

namespace Model\Org;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table org_brief
 * @hasOne(name = Event, referencedEntity = \Model\Org\Event, column = event_id)
 * @hasOne(name = Author, referencedEntity = \Model\System\User, column = author_id)
 */
class Brief extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "org_brief";
    }
    protected function init() {
        $this->addBehavior(new \Ormion\Behavior\Texy('text', 'text_src'));
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        $event = $acl->getQueriedResource()->Event;
        $author = isset($acl->getQueriedResource()->author_id) ? $acl->getQueriedResource()->Author : null;
        return ($author && $author->id == $acl->getQueriedRole()->getIdentity()->getId())
                || $event->isEventAdmin($acl->getQueriedRole()->getIdentity());
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->event_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Event::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array('parent' => $params['parent']);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
