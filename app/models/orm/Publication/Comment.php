<?php

namespace Model\Publication;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table public_comment
 * @hasOne(name = Article, referencedEntity = \\Model\\Publication\\Article, column = article_id)
 */
class Comment extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "public_comment";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->article_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Article::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->title;

        return $res;
    }

}
