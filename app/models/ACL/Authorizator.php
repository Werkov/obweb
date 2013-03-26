<?php
namespace ACL;
/**
 * <bshit>ACL based of database tables.</bshit>
 *
 * @author Michal
 */
class Authorizator extends \Nette\Security\Permission {

   public function __construct() {
      $assertions = array();

      foreach (Model::getRoles() as $role)
	 $this->addRole($role->name, $role->parent_name);

      foreach (Model::getResources() as $resource) {
	 $this->addResource($resource->name);
	 if ($resource->assertion != null) {
	    $assertions[$resource->name] = $resource->assertion;
	 }
      }

      foreach (Model::getRules() as $rule) {
	 if ($rule->assertion && $rule->resource !== null)
	    $assertion = $assertions[$rule->resource];
	 else
	    $assertion = null;
	 $this->{$rule->allowed == 'Y' ? 'allow' : 'deny'}($rule->role, $rule->resource, $rule->privilege, $assertion);
      }
   }

   public static function createService() {
      $cache = \Nette\Environment::getCache("ACL");
      if (isset($cache["authorizator"])) {
	 return $cache["authorizator"];
      } else {
	 return $cache["authorizator"] = new self();
      }
   }

   /*public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL) {
      return true;      
   }*/

}
