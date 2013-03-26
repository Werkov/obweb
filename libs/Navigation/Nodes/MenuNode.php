<?php

namespace Navigation;

/**
 * Description of MenuNode
 *
 * @author Michal
 */
//TODO: Refaktorovat třídu, aby obsahovala i action a aprametry odděleně, odkazy předgenerovat ale taky vedle a tím přidat možnost isCurrent

class MenuNode extends PathNode {

   /**
    * @var array of MenuNode
    */
   protected $children = array();
   /**
    * @var bool|callback visibility in menu
    */
   protected $visible = true;
   /**
    * @var array of array of mixed parameters given to action for this menu item
    */
   protected $parameters = array();

   public function __construct($text, $action = "", $parametres = array(), $title = "", $tag = null, $visible = true) {
      parent::__construct($text, $action, $parametres, $title, $tag);
      $this->visible = $visible;
   }

   public function addChild(MenuNode $child) {
      $this->children[] = $child;
   }

   public function getChildren() {
      return $this->children;
   }

   /**
    *
    * @return bool
    */
   public function isVisible() {
      return is_bool($this->visible) ? $this->visible : call_user_func($this->visible);
   }

   /**
    *
    * @return bool   Action and params have to be same as those of current action.
    */
   public function isCurrent() {
      $pres = \Nette\Environment::getApplication()->getPresenter();
      if($this->action == $pres->getAction())
      {
	 foreach($this->parameters as $name => $value)
	 {
	    if($pres->getParam($name) !== $value)
		    return true;
	 }
      }
      return false;
   }

}

