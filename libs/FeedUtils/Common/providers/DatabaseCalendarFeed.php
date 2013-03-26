<?php

namespace FeedUtils;

   /**
    * Component showing aggregated feeds in one.
    */
   class DatabaseCalendarFeed extends \Gridito\DibiFluentModel implements IFeedProvider {

      /**
       *
       * @var bool
       */
      protected $withTime;
      /**
       *
       * @var callback|null 
       */
      protected $linkCallback;

      public function __construct($fluent, $withTime = false) {

	 parent::__construct($fluent, '\FeedUtils\vEvent');
	 
	 $this->setOffset(0); //can't set no offset on DibiFluent
	 $this->setLimit(10000000); //can't set no limit on DibiFluent
	 $this->withTime = $withTime;
      }

      //<editor-fold desc="Getters & setters">
      public function getWithTime() {
	 return $this->withTime;
      }

      public function setWithTime($withTime) {
	 $this->withTime = $withTime;
      }

      public function getLinkCallback() {
	 return $this->linkCallback;
      }

      public function setLinkCallback($linkCallback) {
	 $this->linkCallback = $linkCallback;
      }

//</editor-fold>


      public function getItems() {
	 $items = parent::getItems();

	 foreach ($items as $item) {
	    $item->dtstart_tm = $this->withTime;
	    $item->end_tm = $this->withTime;
	    if ($this->linkCallback !== null) {
	       $item->url = call_user_func($this->linkCallback, $item->url);
	    }
	 }
	 return $items;
      }

   }

