<?php

namespace FeedUtils;

   /**
    * Body for feed generating component
    */
   abstract class AbstractGenerator extends \Nette\Application\UI\Control {

//<editor-fold desc="Variables">
      /**
       * @var string
       */
      protected $title;
      /**
       * @var string
       */
      protected $URL;
      /**
       * @var string
       */
      protected $description;
      /**
       * No. of items in the feed.
       * @var int
       */
      protected $itemsCount = 20;
      /**
       * @var AbstractFeedAggregator
       */
      protected $aggregator;

      //</editor-fold>
//<editor-fold desc="Public API">

      public function __construct($parent, $name, $aggregator, $title, $URL, $description) {
	 parent::__construct($parent, $name);

	 $this->aggregator = $aggregator;
	 $this->title = $title;
	 $this->URL = $URL;
	 $this->description = $description;
      }

      /**
       * Set count of the items in the feed.
       * @param int items
       * @return RSSGenerator
       */
      public function setItemsCount($itemsCount) {
	 $this->itemsCount = $itemsCount;
	 return $this;
      }

      /**
       * @return int
       */
      public function getItemsCount() {
	 return $this->itemsCount;
      }

//</editor-fold>
//<editor-fold desc="Rendering">

      /**
       * Create template
       * @return Template
       */
      protected function createTemplate($class = null) {
	 return parent::createTemplate($class);
      }

      public function render() {
	 $this->template->title = $this->title;
	 $this->template->URL = $this->URL;
	 $this->template->feedURL = \Nette\Environment::getHttpRequest()->getUrl()->absoluteUrl;
	 $this->template->description = $this->description;
	 $this->template->date = new \DateTime();


	 $this->template->items = $this->getItems();

	

	 $this->template->render();
      }

      protected function getItems() {
	 $this->aggregator->setOffset(0);
	 $this->aggregator->setLimit($this->itemsCount);
	 
	 return $this->aggregator->getItems();
      }

//</editor-fold>
   }

