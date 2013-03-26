<?php

namespace Navigation;

/**
 *
 * @author Michal Koutny
 */
class CyclicNode extends BasicNode {
   /**
    * Class name of the model. Model must have static method menuItems which
    * accepts array (or null) of action parametres and returns resultset
    * of DibiRow with field 'text' for menu label and other fields containg
    * action parameters.
    *
    * Model must have static method menuParentInfo
    * that accepts array of action parameters and returns associative array
    * with two fields:
    * - Navigation::PINFO_THIS - array of information for current node - only
    *                            Navigation::PINFO_TEXT is supported so far
    *
    * - Navigation::PINFO_PARAMS - parameters for parent node action
    * */

   /**
    * @var string
    */
   protected $expander;
   /**
    *
    * @var string
    */
   protected $parentizer;

   // <editor-fold desc="Getters and setters">
   public function getExpander() {
      return $this->expander;
   }

   public function setExpander($expander) {
      $this->expander = $expander;
      return $this;
   }

   public function getParentizer() {
      return $this->parentizer;
   }

   public function setParentizer($parentizer) {
      $this->parentizer = $parentizer;
      return $this;
   }

// </editor-fold>

   public function getPathNodes($last = false) {
      $res = array();
      if ($last && $this->leafLabel != "") {
	 $res[] = new PathNode($this->leafLabel);
      }
      if (isset($this->navigation->nodeData[$this->action])) {
	 $data = $this->navigation->nodeData[$this->action];
	 $i = 0;
	 while ($data !== null && $i < 30) {

	    $parentInfo = \call_user_func($this->parentizer, $data);
	    $label = \array_key_exists(Navigation::PINFO_THIS, $parentInfo) ? $parentInfo[Navigation::PINFO_THIS][Navigation::PINFO_TEXT] : $this->defaultLabel;
	    $res[] = new PathNode($label, $this->action, $data, $this->defaultLabel);
	    $this->navigation->nodeData[$this->parent->getAction()] = $parentInfo[Navigation::PINFO_PARAMS];

	    //$res[] = new PathNode($data["text"], $this->navigation->getPresenter()->link($this->action, $data["parameters"]), $this->defaultLabel);
	    //$rawData = \call_user_func($this->model . "::menuParent", $data);
	    //format obtained data
	    $data = $parentInfo[Navigation::PINFO_PARAMS];
	    $i++;
	 }
      } else {
	 $res[] = new PathNode($this->defaultLabel, "");
      }



      return $res;
   }

   /**
    *
    * @return array of array[MenuNode, data] children nodes and parameters for them
    */
   public function expand() {
      //model vrací popisky a parametry jako DibiRow s pole 'text' se použije jako popiska, ostatní jako parametry akce

      $nodes = array();
      //get possible texts and their parameters for given set of parameters
      $params = isset($this->navigation->nodeDataMenu[$this->action]) ? $this->navigation->nodeDataMenu[$this->action] : null;
      $expanded = \call_user_func($this->expander, $params);
      foreach ($expanded as $row) {
	 $parameters = array();
	 foreach ($row as $key => $value) {
	    if ($key == "text")
	       continue;
	    $parameters[$key] = $value;
	 }

	 $nodes[] = new MenuNode($row->text, $this->action, $parameters, $this->defaultLabel, $this->tag, $this->visible);
      }

      return $nodes;
   }

     public function getMenuNode() {

      if ($this->inheritParams && isset($this->navigation->nodeDataMenu[$this->getParent()->getAction()])) {
	 $params = $this->translateParentToChild($this->navigation->nodeDataMenu[$this->getParent()->getAction()]);
      } else {
	 $params = $this->parameters;
      }
      return new MenuNode($this->defaultLabel, $this->action, $params, "", $this->tag, $this->visible);
   }

}

