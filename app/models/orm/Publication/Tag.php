<?php

namespace Model\Publication;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table public_tag
 * @manyToMany(name = Articles, referencedEntity = \\Model\\Publication\\Article, connectingTable = public_article2tag, referencedKey = article_id, localKey = tag_id)
 */
class Tag extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "publication_tag";
    }
    
    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior('name'));
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = Tag::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

}
