<?php

namespace FeedUtils;

   /**
    * Component showing aggregated feeds in one.
    */
   class DatabaseNewsfeed extends \Gridito\DibiFluentModel implements IFeedProvider {

      /**
       *
       * @var SourceInfo
       */
      protected $sourceInfo;
      /**
       *
       * @var boolean
       */
      protected $isPermalink;
      /**
       *
       * @var callback|null 
       */
      protected $formatCallback;



      public function __construct($sourceInfo, $fluent) {
	 $this->sourceInfo = $sourceInfo;
	 $this->isPermalink = false;

	 parent::__construct($fluent, "\FeedUtils\NewsItem");

	 $this->setOffset(0);
	 $this->setLimit(20);
      }

//<editor-fold desc="Getters & setters">

      public function getSourceInfo() {
	 return $this->sourceInfo;
      }

      public function setSourceInfo($sourceInfo) {
	 $this->sourceInfo = $sourceInfo;
      }

      public function getIsPermalink() {
	 return $this->isPermalink;
      }

      public function setIsPermalink($isPermalink) {
	 $this->isPermalink = $isPermalink;
      }

      public function getFormatCallback() {
	 return $this->formatCallback;
      }

      public function setFormatCallback($formatCallback) {
	 $this->formatCallback = $formatCallback;
      }



//</editor-fold>

      public function getItems() {
	 $items = parent::getItems();

	 foreach ($items as $item) {
	    $item->sourceInfo = $this->sourceInfo;
	    $item->isPermalink = $this->isPermalink;
	    if ($this->formatCallback !== null) {
	       $item = call_user_func($this->formatCallback, $item);       
	    }	    
	 }
	 return $items;
      }

   }

