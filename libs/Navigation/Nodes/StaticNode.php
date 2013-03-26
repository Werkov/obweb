<?php

namespace Navigation;

/**
 *
 * @author Michal Koutny
 */
class StaticNode extends BasicNode {

   // <editor-fold desc="Fields">
   /**
    * @var bool Whether params are inherited from parent node.
    */
   protected $inheritParams = false;
   /**
    *
    * @var array of string Translation table for parameter inheritance. Translation direction is from child to parent.
    */
   protected $translationTable = array();
   /**
    *
    * @var bool Delimiter in hierarchy when creating uncles menu. Static nodes
    * 					without action are hierarchyDelimiters by default.
    */
   protected $hierarchyDelimiter = false;

   // </editor-fold>
   // <editor-fold desc="Getters and setters">
   public function getInheritParams() {
      return $this->inheritParams;
   }

   public function setInheritParams($inheritParams) {
      $this->inheritParams = $inheritParams;
      return $this;
   }

   public function getTranslationTable() {
      return $this->translationTable;
   }

   public function setTranslationTable($translationTable) {
      $this->translationTable = $translationTable;
      return $this;
   }

   public function getHierarchyDelimiter() {
      return $this->hierarchyDelimiter;
   }

   // </editor-fold>
   // <editor-fold desc="Constructor">
   public function __construct($text, $action = "", $hierarchyDelimiter = false) {
      $this->defaultLabel = $text;
      $this->action = $action;
      if ($action == "") {
	 $this->hierarchyDelimiter = true;
      }
      $this->hierarchyDelimiter = $hierarchyDelimiter;
   }

   // </editor-fold>
   // <editor-fold desc="Menu nodes">
   public function getPathNodes($last = false) {
      $res = array();

      if ($last && $this->leafLabel != "") {
	 $res[] = new PathNode($this->leafLabel);
      }
      //propagate params upwards
      if ($this->inheritParams && isset($this->navigation->nodeData[$this->action])) {
	 $params = $this->navigation->nodeData[$this->action];
	 $this->navigation->nodeData[$this->parent->getAction()] = $this->translateChildToParent($params);
      } else {
	 $params = $this->parameters;
      }

      $res[] = new PathNode($this->defaultLabel, $this->action, $params, "", $this->tag);



      return $res;
   }

   public function getMenuNode() {

      if ($this->inheritParams && isset($this->navigation->nodeDataMenu[$this->getParent()->getAction()])) {
	 $params = $this->translateParentToChild($this->navigation->nodeDataMenu[$this->getParent()->getAction()]);
      } else {
	 $params = $this->parameters;
      }
      return new MenuNode($this->defaultLabel, $this->action, $params, "", $this->tag, $this->visible);
   }

// </editor-fold>
   // <editor-fold desc="Inner methods">
   protected function translateChildToParent($params) {
      if (count($this->translationTable) === 0)
	 return $params;
      $res = array();
      foreach ($params as $key => $value) {
	 if (\array_key_exists($key, $this->translationTable)) {
	    $res[$this->translationTable[$key]] = $value;
	 } else {
	    $res[$key] = $value;
	 }
      }
      return $res;
   }

   protected function translateParentToChild($params) {
      if (count($this->translationTable) === 0)
	 return $params;
      $res = array();
      $table = array_flip($this->translationTable);
      foreach ($params as $key => $value) {
	 if (\array_key_exists($key, $this->translationTable)) {
	    $res[$table[$key]] = $value;
	 } else {
	    $res[$key] = $value;
	 }
      }
      return $res;
   }

   // </editor-fold>
}
