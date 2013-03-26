<?php

namespace Navigation; //TODO move to different namespace

/**
 *
 * @author Michal
 */

abstract class Record extends \Ormion\Record {

    public static function menuParentById($params = array()) {
        $row = static::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentByUrl($params = array()) {
        $row = static::find(array('url' => $params['parent']));

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuInfoById($params = array()) {
        $row = static::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuInfoByUrl($params = array()) {
        $row = static::find(array('url' => $params['id']));

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function save($oldHash = null, $asReference = false) {
        if ($this instanceof \Nette\Security\IResource) {
            \Nette\Environment::getCache()->clean(array(
                \Nette\Caching\Cache::TAGS => array($this->getResourceId()),
            ));
        }
        return parent::save($oldHash, $asReference);
    }

    public function delete() {
        if ($this instanceof \Nette\Security\IResource) {
            \Nette\Environment::getCache()->clean(array(
                \Nette\Caching\Cache::TAGS => array($this->getResourceId()),
            ));
        }
        return parent::delete();
    }

}

