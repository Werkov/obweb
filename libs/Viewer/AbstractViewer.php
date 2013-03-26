<?php

namespace OOB;

abstract class AbstractViewer extends \Nette\Application\UI\Control {

   //<editor-fold desc="Variables">
   /**
    * @var array of pairs(\DibiFluent, className|null)
    */
   protected $fluents;
   /**
    * @var \VisualPaginator
    */
   protected $paginator;

//</editor-fold>
   //<editor-fold desc="Getters & setters">
   public function getItemsPerPage() {
      return $this->paginator->getPaginator()->getItemsPerPage();
   }

   public function setItemsPerPage($itemsPerPage) {
      $this->paginator->getPaginator()->setItemsPerPage($itemsPerPage);
   }

   /**
    *
    * @param string $name
    * @param \DibiFluent $fluent 
    */
   public function addFluent($name, $fluent, $class = null) {
      $this->fluents[$name] = array("fluent" => $fluent, "class" => $class);
   }
   
   public function getAjaxClass() {
      return $this->paginator->getAjaxClass();
   }

   public function setAjaxClass($ajaxClass) {
      $this->paginator->setAjaxClass($ajaxClass);
   }



//</editor-fold>
//<editor-fold desc="Constructor">
   public function __construct($parent = NULL, $name = NULL) {
      parent::__construct($parent, $name);
      $this->fluents = array();
      $this->paginator = new \VisualPaginator($this, "paginator");
      $this->paginator->getPaginator()->setItemsPerPage(1000);
   }

//</editor-fold>
   //<editor-fold desc="Rendering">
   public function getPaginator() {
      return $this->paginator;
   }

   public function render() {
      $this->prepareData();

      $this->template->render();
   }

   protected function prepareData() {
      $sum = 0;
      $cumsum = array();

      //total sum and cumulative sums
      foreach ($this->fluents as $pair) {
	 $sum += $pair["fluent"]->count();
	 $cumsum[] = $sum;
      }

      //set total count, offsets and template varibles
      $this->paginator->getPaginator()->setItemCount($sum);
      $offset = $this->paginator->getPaginator()->getOffset();
      $limit = $this->paginator->getPaginator()->getLength();
      $i = -1;
      $cumsum[-1] = 0; //sum before first fluent
      foreach ($this->fluents as $name => $pair) {
	 ++$i;
	 $fluent = $pair["fluent"];

	 $res = $fluent->execute();
	 if ($pair["class"] !== null) {
	    $res->setRowClass($pair["class"]);
	 }
	 $this->template->$name = $res->fetchAll(max(0, $offset - $cumsum[$i - 1]), min($cumsum[$i] - $cumsum[$i - 1], $limit));
      }
   }

//</editor-fold>
}

?>
