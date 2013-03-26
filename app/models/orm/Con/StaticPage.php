<?php

namespace Model\Con;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table con_staticPage
 * @hasOne(name = Parent, referencedEntity = \Model\Con\StaticPage, column = parent_id)
 * @hasMany(name = StaticPages, referencedEntity = \Model\Con\StaticPage, column = parent_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = lastModUser)
 */
class StaticPage extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "con_staticPage";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    protected function init() {
        $this->addBehavior(new \Model\CoolUrlBehavior());
        $this->addBehavior(new \Ormion\Behavior\Texy('content', 'content_src'));
    }

    public static function menuParentInfo($params = array()) {
//      $row = \dibi::fetch("SELECT c.name, p.url FROM [:t:con_staticPage] AS c " .
//		      "LEFT JOIN [:t:con_staticPage] AS p ON p.id = c.parent_id " .
//		      "WHERE c.url = %s", $params["id"]);
        $row = self::find(array('url' => $params['id']));
        if (!$row) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = "O nás";
        } else if (!$row->Parent) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;
        } else {
            $res[\Navigation\Navigation::PINFO_PARAMS] = array("id" => $row->Parent->url);
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name . "w";
        }

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        if (!isset($params["parent"]))
            $params["parent"] = null;

        $row = \dibi::fetch("SELECT name, parent_id FROM [:t:con_staticPage] WHERE %and", array("id" => $params["parent"]));

        if (!$row) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
        } else {
            $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name . "e";
        }



        return $res;
    }

    public static function menuParentInfo3($params = array()) {
        if (!isset($params["parent"]))
            $params["parent"] = null;

        $row = \dibi::fetch("SELECT name, parent_id FROM [:t:con_staticPage] WHERE %and", array("id" => $params["parent"]));

        if (!$row) {
            $res[\Navigation\Navigation::PINFO_PARAMS] = null;
        } else {
            $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
            $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name . "a";
        }



        return $res;
    }

    public static function menuExpand($params = array()) {
        if ($params == null) {
            $data = new \stdClass();
            $data->text = "Statické stránky";
            $data->parent = null;
            return array($data);
        } else {
            return array();
        }
    }

    public static function menuExpand2($params = array()) {
        if ($params == null) {
            $data = new \stdClass();
            $data->text = "O nás";
            $data->parent = null;
            return array($data);
        } else {
            return array();
        }
    }

}
