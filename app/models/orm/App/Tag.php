<?php

namespace Model\App;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table app_tag
 * @hasMany(name = Races, referencedEntity = \Model\App\Race, column = tag_id)
 */
class Tag extends \Navigation\Record implements \Nette\Security\IResource {

   public function getResourceId() {
      return "app_tag";
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
