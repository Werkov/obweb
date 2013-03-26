<?php

namespace Model\Gallery;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table gallery_directory
 * @hasMany(name = Gallerys, referencedEntity = \Model\Gallery\Gallery, column = directory_id)
 */
class Directory extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "gallery_directory";
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function delete() {
        //delete galleries
        foreach ($this->Gallerys->fetchAll() as $gallery) {
            $gallery->delete();
        }

        parent::delete();
    }

}
