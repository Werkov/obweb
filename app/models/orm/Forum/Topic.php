<?php

namespace Model\Forum;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table forum_topic
 * @hasMany(name = Threads, referencedEntity = \Model\Forum\Thread, column = topic_id)
 */
class Topic extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "forum_topic";
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }


    public function delete() {
        foreach ($this->Threads->fetchAll() as $thread) {
            $thread->delete();
        }

        parent::delete();
    }

}
