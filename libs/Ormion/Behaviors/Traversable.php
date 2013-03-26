<?php

namespace Ormion\Behavior;

use Ormion\IRecord;

/**
 * Provides behavior for traversable trees.
 * Only for insertion/deletion.
 * You need to correctly set parent field,
 * the rest is automatic.
 * Record is insered at the end of 
 * the list of parent's children.
 *
 * @author Michal Koutny
 */
class Traversable extends \Nette\Object implements IBehavior {

   /** @var string */
   private $orderColumn; //not implemented
   /** @var string */
   private $groupColumn;
   /**
    * Name of the field where parent_id is saved
    * @var string 
    */
   private $parentField;
   /**
    * @var string
    */
   private $depthColumn;
   /**
    * @var string
    */
   private $lft;
   /**
    * @var rgt
    */
   private $rgt;

   /**
    * Constructor
    */
   public function __construct($parentField = "parent", $groupColumn = null, $depthColumn = "depth", $lft = "lft", $rgt = "rgt") {
      //$this->orderColumn = $orderColumn;
      $this->groupColumn = $groupColumn;
      $this->parentField = $parentField;
      $this->depthColumn = $depthColumn;
      $this->lft = $lft;
      $this->rgt = $rgt;
   }

   /**
    * Setup behavior
    * @param IRecord record
    */
   public function setUp(IRecord $record) {
      $record->onBeforeInsert[] = array($this, "setBeforeInsert");
      $record->onBeforeDelete[] = array($this, "fixBeforeDelete");
      //$record->onBeforeUpdate[] = array($this, "fixBeforeUpdate"); //not supported
   }

   /**
    * Set order before insert
    * @param IRecord record
    */
   public function setBeforeInsert(IRecord $record) {
      if ($record->{$this->parentField} === null) {
	 $collection = $record->findAll();
	 if (isset($this->groupColumn)) {
	    $type = $record->getConfig()->getType($this->groupColumn);
	    $collection->where("%n = %$type", $this->groupColumn, $record->{$this->groupColumn});
	 }
	 $fl = $collection->toFluent();
	 $fl->select(false)->select("MAX(%n) AS m", $this->rgt);
	 $max = $fl->fetchSingle("m");

	 $record->{$this->lft} = $max + 1;
	 $record->{$this->rgt} = $max + 2;
	 $record->{$this->depthColumn} = 0;
      } else {
	 $parent = $record->find($record->{$this->parentField});
	 $record->{$this->lft} = $parent->{$this->rgt};
	 $record->{$this->rgt} = $parent->{$this->rgt} + 1;
	 $record->{$this->depthColumn} = $parent->{$this->depthColumn} + 1;
      }

      \dibi::begin();
//shift following nodes
      $fluent = $record->getMapper()->getDb()
	      ->update($record->getMapper()->getTable(), array(
		  $this->lft . "%sql" => array("%n + 2", $this->lft),
		  $this->rgt . "%sql" => array("%n + 2", $this->rgt),
	      ))
	      ->where("%n >= %i AND %n >= %i", $this->lft, $record->{$this->lft}, $this->rgt, $record->{$this->lft});

      if (isset($this->groupColumn)) {
	 $type = $record->getConfig()->getType($this->groupColumn);
	 $fluent->where("%n = %$type", $this->groupColumn, $record->{$this->groupColumn});
      }

      $fluent->execute();

      //half shift path to root
      $fluent = $record->getMapper()->getDb()
	      ->update($record->getMapper()->getTable(), array(
		  $this->rgt . "%sql" => array("%n + 2", $this->rgt),
	      ))
	      ->where("%n < %i AND %n >= %i", $this->lft, $record->{$this->lft}, $this->rgt, $record->{$this->lft});

      if (isset($this->groupColumn)) {
	 $type = $record->getConfig()->getType($this->groupColumn);
	 $fluent->where("%n = %$type", $this->groupColumn, $record->{$this->groupColumn});
      }

      $fluent->execute();
      \dibi::commit();
   }

   /**
    * Fix order before delete
    * @param IRecord record
    */
   public function fixBeforeDelete(IRecord $record) {
      $columns[] = $this->rgt;
      if (isset($this->groupColumn)) {
	 $columns[] = $this->groupColumn;
      }

      $record->lazyLoadValues($columns);

      \dibi::begin();
//shift following nodes
      $fluent = $record->getMapper()->getDb()
	      ->update($record->getMapper()->getTable(), array(
		  $this->lft . "%sql" => array("%n - 2", $this->lft),
		  $this->rgt . "%sql" => array("%n - 2", $this->rgt),
	      ))
	      ->where("%n >= %i AND %n >= %i", $this->lft, $record->{$this->lft}, $this->rgt, $record->{$this->lft});

      if (isset($this->groupColumn)) {
	 $type = $record->getConfig()->getType($this->groupColumn);
	 $fluent->where("%n = %$type", $this->groupColumn, $record->{$this->groupColumn});
      }

      $fluent->execute();

      //half shift path to root
      $fluent = $record->getMapper()->getDb()
	      ->update($record->getMapper()->getTable(), array(
		  $this->rgt . "%sql" => array("%n - 2", $this->rgt),
	      ))
	      ->where("%n < %i AND %n > %i", $this->lft, $record->{$this->lft}, $this->rgt, $record->{$this->lft});

      if (isset($this->groupColumn)) {
	 $type = $record->getConfig()->getType($this->groupColumn);
	 $fluent->where("%n = %$type", $this->groupColumn, $record->{$this->groupColumn});
      }

      $fluent->execute();
      \dibi::commit();
   }

   /**
    * Fix order before update
    * @param IRecord record
    */
   public function fixBeforeUpdate(IRecord $record) {
      throw new \Nette\NotImplementedException();
   }

}