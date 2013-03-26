<?php

namespace Model\Doc;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table doc_directory
 * @hasOne(name = Parent, referencedEntity = \Model\Doc\Directory, column = parent_id)
 * @hasMany(name = Directorys, referencedEntity = \Model\Doc\Directory, column = parent_id)
 * @hasMany(name = Files, referencedEntity = \Model\Doc\File, column = directory_id)
 */
class Directory extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "doc_directory";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
    }

    public static function menuExpand($params = array()) {
        if ($params == null) {
            $data = new \stdClass();
            $data->text = "Dokumenty";
            $data->parent = null;
            return array($data);
        } else {
            return array();
        }

        //return \dibi::fetchAll("SELECT name AS [text], id AS [parent] FROM [:t:doc_directory] WHERE %and", array("parent_id" => $params["parent"]));
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);

        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        if (!isset($params["parent"]))
            $params["parent"] = null;

        //$row = \dibi::fetch("SELECT name, parent_id FROM [:t:doc_directory] WHERE %and", array("id" => $params["parent"]));
        $row = Directory::find($params['parent']);


        if (!$row) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
        } else {
            $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;
        }

        return $res;
    }

    public static function menuParentInfo3($params = array()) {
        if (!isset($params["parent"]))
            $params["parent"] = null;

        $row = Directory::find(array('url' => $params["parent"]));


        if ($row->parent_id == null || !$row) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;
        } else {
            $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->Parent->url);
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;
        }

        return $res;
    }

    public function delete() {
        foreach ($this->Directorys->fetchAll() as $subdir) {
            $subdir->delete();
        }
        foreach ($this->Files->fetchAll() as $file) {
            $file->delete();
        }
        parent::delete();
    }

}
