<?php
namespace Model\Transport;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table transport_realization
 * @hasOne(name = Demand, referencedEntity = \Model\Transport\Demand, column = demand_id)
 * @hasOne(name = Supply, referencedEntity = \Model\Transport\Supply, column = supply_id)
 */
class Realization extends \Navigation\Record implements \Nette\Security\IResource {

	public function getResourceId()
	{
		return "transport_realization";
	}

	public static function assertion(Permission $acl, $role, $resource, $privilege)
	{
			}


	public static function menuParentInfo($params = array())
	{
		$row = \dibi::fetch("SELECT demand_id FROM [:t:transport_realization] WHERE demand_id = %i", $params["id"]);

		$res[\Navigation\Navigation::PINFO_PARAMS] = array();
		$res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->demand_id;

		return $res;
	}

}
