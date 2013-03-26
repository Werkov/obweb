<?php

namespace Model\Forum;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table forum_post
 * @hasOne(name = Thread, referencedEntity = \Model\Forum\Thread, column = thread_id)
 */
class Post extends \Navigation\Record implements \Nette\Security\IResource {

    public $parent;

    public function getResourceId() {
        return "forum_post";
    }

    protected function init() {
        parent::init();

        $b = new \Ormion\Behavior\Traversable("parent", "thread_id", "depth", "lft", "rgt");
        $this->addBehavior($b);
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->thread_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->datetime->format("Y-m-d H:i:s");

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Thread::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->topic_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
