<?php

use Ormion\IMapper;
use Ormion\IRecord;

/**
 * Has many association
 *
 * @author Jan Marek
 * @license MIT
 */
class HasManyAnnotation extends Ormion\Association\BaseAssociation {

   /** @var string */
   protected $referencedEntity;
   /** @var string */
   protected $column;
   /** @var IMapper */
   protected $mapper;



   public function setMapper(IMapper $mapper) {
      $this->mapper = $mapper;
   }

   public function setReferenced(IRecord $record, $data) {
      if ($record->getState() === IRecord::STATE_EXISTING) {
	 foreach ($data as $item) {
	    $item[$this->column] = $record->getPrimary();
	 }
      }
   }

   public function retrieveReferenced(IRecord $record) {
      if ($record->getState() === IRecord::STATE_NEW) {
	 return array();
      }

      $cls = $this->referencedEntity;

      return $cls::findAll(array(
	  $this->column => $record->getPrimary()
      ));
   }

   public function saveReferenced(IRecord $record, $data) {
      $this->setReferenced($record, $data);
      $cls = $this->referencedEntity;
      $pks = array(); //PK values

      foreach ($data as $item) {
	 $item->save(null, true);
	 $pks[] = $item->getPrimary();
	 //$pks[] = (is_array($pr = $item->getPrimary()) ? $pr[$cls::getConfig()->getPrimaryColumn()] : $pr); //take only first key
      }

      $q = $this->mapper->getDb()
		      ->delete($cls::getMapper()->getTable())
		      ->where(array(
			  $this->column => $record->getPrimary()
		      ));

      if (!empty($pks)) {
	 $pk = $cls::getConfig()->getPrimaryColumns(); //PK names
	 if (count($pk) <= 1) {
	    $q->and("%n not in %in", $pk, $pks);
	 } else {
	    foreach ($pks as $pkvalue) {
	       $q->and("NOT (%and)", array_combine($pk, $pkvalue)); //'not in' rewritten via and/or
	    }
	 }
      }

      $q->execute();
   }

}