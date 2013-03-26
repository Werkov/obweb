<?php

namespace Navigation;

/**
 *
 * @author Michal Koutny
 */
abstract class BasicNode extends \Nette\Object {

   /**
    * @var BasicNode
    */
   protected $parent;
   /**
    *
    * @var array of BasicNode
    */
   protected $children = array();
   /**
    * @var Navigation
    */
   protected $navigation;
   /**
    * @var string
    */
   protected $defaultLabel;
   /**
    * @var string Label of the appended node in path when given node is leaf.
    */
   protected $leafLabel = "";
   /**
    * @var string action of the node
    */
   protected $action = "";
   /**
    *
    * @var array of array of mixed Parameters of the action
    */
   protected $parameters = array();
   /**
    * @var bool|callback Visibility of given node
    */
   protected $visible = true;
   /**
    * @var mixed Universal data
    */
   protected $tag = null;
   /**
    *
    * @var bool Whether to display in menu
    */
   protected $menu = true;

   // <editor-fold desc="Getters and setters">
   abstract public function getPathNodes($last = false);

   public function getChildren() {
      return $this->children;
   }

   public function getAction() {
      return $this->action;
   }

   public function setAction($value) {
      $this->action = $value;
      return $this;
   }

   public function getLeafLabel() {
      return $this->leafLabel;
   }

   public function setLeafLabel($value) {
      $this->leafLabel = $value;
      return $this;
   }

   public function getVisibile() {
      return $this->visible;
   }

   public function setVisible($value) {
      $this->visible = $value;
      return $this;
   }

   public function getTag() {
      return $this->tag;
   }

   public function setTag($value) {
      $this->tag = $value;
      return $this;
   }

   public function getParent() {
      return $this->parent;
   }

   public function setParent($value) {
      $this->parent = $value;
      return $this;
   }

   public function setNavigation($value) {
      $this->navigation = $value;
      return $this;
   }

   public function getMenu() {
      return $this->menu;
   }

   public function setMenu($menu) {
      $this->menu = $menu;
      return $this;
   }

   public function getParameters() {
      return $this->parameters;
   }

   public function setParameters($parameters) {
      $this->parameters = $parameters;
      return $this;
   }

   public function getHierarchyDelimiter() {
      return false;
   }

   // </editor-fold>

   public function __construct($text, $action = "") {
      $this->defaultLabel = $text;
      $this->action = $action;
   }

   /**
    *
    * @param BasicNode $node
    * @return BasicNode
    */
   public function addChild(BasicNode $node) {
      $this->children[] = $node;
      $node->parent = $this;
      $node->navigation = $this->navigation;
      if ($node->getAction() != "")
	 $this->navigation->nodes[$node->getAction()] = $node;

      return $node;
   }

}
