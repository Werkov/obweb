<?php

namespace Model\Survey;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table survey_answer
 * @hasOne(name = Survey, referencedEntity = \Model\Survey\Survey, column = survey_id)
 * @hasMany(name = Survey2users, referencedEntity = \Model\Survey\Survey2user, column = answer_id)
 */
class Answer extends \Navigation\Record implements \Nette\Security\IResource {

   public function getResourceId() {
      return "survey_survey";
   }

   public static function assertion(Permission $acl, $role, $resource, $privilege) {

   }

   public static function menuParentInfo($params = array()) {
      $row = \dibi::fetch("SELECT id, survey_id FROM [:t:survey_answer] WHERE id = %i", $params["id"]);

      $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->survey_id);
      $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

      return $res;
   }

   public static function menuParentInfo2($params = array()) {
      $row = \dibi::fetch("SELECT question FROM [:t:survey_survey] WHERE id = %i", $params["parent"]);

      $res[\Navigation\Navigation::PINFO_PARAMS] = array();
      $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->question;

      return $res;
   }

}
