<?php

namespace FeedUtils;

   /**
    * Component showing aggregated feeds in one list.
    */
   abstract class FeedReader extends \Nette\Application\UI\Control {

//<editor-fold desc="Variables">
      /** @var int */
      protected $itemsPerPage = 12;
      /**
       * @var bool
       * @persistent
       */
      public $isLoaded = 0;
      /**
       * Zero based page number
       * @var int
       * @persistent
       */
      public $page = 0;
      /**
       * Enables AJAX delayed loading
       * @var boolean
       */
      protected $lazyLoad = true;
      /** @var string */
      protected $ajaxClass = "ajax";
      /**
       *
       * @var AbstractFeedAggregator
       */
      protected $aggregator;

      //</editor-fold>
//<editor-fold desc="Public API">

      public function __construct($parent = null, $name = null, $aggregator = null) {
	 parent::__construct($parent, $name);

	 $this->aggregator = $aggregator;
         
      }

      /**
       * Set items per page
       * @param int items per page
       * @return FeedReader
       */
      public function setItemsPerPage($itemsPerPage) {
	 $this->itemsPerPage = $itemsPerPage;
      }

      public function getItemsPerPage() {
	 return $this->itemsPerPage;
      }

      /**
       * Get ajax class
       * @return string
       */
      public function getAjaxClass() {
	 return $this->ajaxClass;
      }

      /**
       * Set ajax class
       * @param string ajax class
       * @return Grid
       */
      public function setAjaxClass($ajaxClass) {
	 $this->ajaxClass = $ajaxClass;
	 return $this;
      }

      public function getLazyLoad() {
	 return $this->lazyLoad;
      }

      public function setLazyLoad($lazyLoad) {
	 $this->lazyLoad = $lazyLoad;
      }
      
      public function getAggregator() {
          return $this->aggregator;
      }

      public function setAggregator($aggregator) {
          $this->aggregator = $aggregator;
      }



//</editor-fold>
//<editor-fold desc="Utils">
//</editor-fold>
//<editor-fold desc="Signals">

      /**
       * Handle change page signal
       * @param int page
       */
      public function handleChangePage($page) {
	 if ($this->presenter->isAjax()) {
	    $this->invalidateControl();
	 }
      }

      /**
       * Handle (sub)request for load.
       * @param int page
       */
      public function handleLoad() {
	 if ($this->presenter->isAjax()) {
	    $this->invalidateControl();
	 }
      }

//</editor-fold>
//<editor-fold desc="Rendering">

      /**
       * Create template
       * @return Template
       */
      protected function createTemplate($class = null) {
	 return parent::createTemplate($class);
	 /* if ($this->isLoaded) {
	   return parent::createTemplate($class)->setFile(__DIR__ . "/templates/list.latte");
	   } else {
	   return parent::createTemplate($class)->setFile(__DIR__ . "/templates/unloaded.latte");
	   } */
      }

      public function render() {
	 if (!$this->lazyLoad || $this->isLoaded) {
	    $this->aggregator->setOffset($this->page * $this->itemsPerPage);
	    $this->aggregator->setLimit($this->itemsPerPage);

	    $this->template->itemsPerPage = $this->itemsPerPage;
	    $this->template->items = $this->aggregator->getItems();
	 }


	 $this->template->render();
      }

//</editor-fold>
   }

