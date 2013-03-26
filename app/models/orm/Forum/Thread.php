<?php

namespace Model\Forum;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table forum_thread
 * @hasMany(name = Posts, referencedEntity = \Model\Forum\Post, column = thread_id)
 * @hasOne(name = Topic, referencedEntity = \Model\Forum\Topic, column = topic_id)
 */
class Thread extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "forum_thread";
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find(array('url' => $params['id']));        

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->Topic->url);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Topic::find(array('url' => $params['parent']));        

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo3($params = array()) {
        $row = self::find(array('url' => $params['id']));        

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("id" => $row->Topic->url);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function delete() {
        foreach ($this->Posts->fetchAll() as $post) {
            $post->delete();
        }
        parent::delete();
    }

}
