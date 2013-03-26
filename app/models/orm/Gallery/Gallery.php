<?php

namespace Model\Gallery;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table gallery_gallery
 * @hasOne(name = Directory, referencedEntity = \Model\Gallery\Directory, column = directory_id)
 * @hasMany(name = Photos, referencedEntity = \Model\Gallery\Photo, column = gallery_id)
 */
class Gallery extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "gallery_gallery";
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->directory_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {

        $row = Directory::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function delete() {
        //delete photos
        foreach ($this->Photos->fetchAll() as $photo) {
            $photo->delete();
        }

        parent::delete();
    }

}
