<?php
namespace Model\Survey;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table survey_survey2user
 * @hasOne(name = Survey, referencedEntity = \Model\Survey\Survey, column = survey_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = user_id)
 * @hasOne(name = Answer, referencedEntity = \Model\Survey\Answer, column = answer_id)
 */
class Survey2user extends \Navigation\Record implements \Nette\Security\IResource {

	public function getResourceId()
	{
		return "survey_survey2user";
	}

	public static function assertion(Permission $acl, $role, $resource, $privilege)
	{
			}


	public static function menuParentInfo($params = array())
	{
		$row = \dibi::fetch("SELECT survey_id, survey_id FROM [:t:survey_survey2user] WHERE survey_id = %i", $params["id"]);

		$res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->survey_id);
		$res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->survey_id;

		return $res;
	}
	public static function menuParentInfo2($params = array())
	{
		$row = \dibi::fetch("SELECT id FROM [:t:survey_survey] WHERE id = %i", $params["parent"]);

		$res[\Navigation\Navigation::PINFO_PARAMS] = array();
		$res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->id;

		return $res;
	}

}
