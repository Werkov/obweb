<?php

namespace Model\Publication;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table public_article
 * @manyToMany(name = Tags, referencedEntity = \Model\Publication\Tag, connectingTable = public_article2tag, referencedKey = tag_id, localKey = article_id)
 * @hasOne(name = Author, referencedEntity = \Model\System\User, column = author_id)
 * @hasMany(name = Comments, referencedEntity = \Model\Publication\Comment, column = article_id)
 */
class Article extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "publication_article";
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior('title'));
        $this->addBehavior(new \Ormion\Behavior\Texy('perex', 'perex_src'));
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
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->title;

        return $res;
    }

    public static function menuParentInfoUrl($params = array()) {
        $row = self::find(array('url' => $params['id']));

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->title;

        return $res;
    }

}
