<?php
namespace ACL;
/**
 * Description of ACLModel
 *
 * @author Michal 
 */
class Model {

   public static function getRoles() {
      return \dibi::fetchAll("SELECT [r1.name] AS [name], [r2.name] AS [parent_name]
			FROM [:t:system_role] AS [r1]
			LEFT JOIN [:t:system_role] AS [r2] ON [r1.parent_id] = [r2.id]");
   }

   public static function getResources() {
      return \dibi::fetchAll("SELECT [name], [assertion] FROM [:t:system_resource]");
   }

   public static function getRules() {
      return \dibi::fetchAll("SELECT [a.allowed] AS allowed, [a.assertion] AS assertion, [r.name] AS role, [p.name] AS privilege, [re.name] AS resource
			FROM [:t:system_acl] AS [a]
			LEFT JOIN [:t:system_role] AS [r] ON [a.role_id] = [r.id]
			LEFT JOIN [:t:system_privilege] AS [p] ON [a.privilege_id] = [p.id]
			LEFT JOIN [:t:system_resource] AS [re] ON [a.resource_id] = [re.id]");
   }   

}

