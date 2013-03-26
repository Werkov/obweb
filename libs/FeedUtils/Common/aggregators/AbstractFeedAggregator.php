<?php

namespace FeedUtils;

   /**
    * Merges multiple feeds togehther.
    * It is generic, it can handle general items that implements
    * IFeedItem.
    */
   abstract class AbstractFeedAggregator implements \IteratorAggregate {

      /**
       * @var array of IFeedProvider
       */
      protected $providers;
      /**
       * @var array of IFeedItemFilter
       */
      protected $filters;
      /**
       * @var int
       */
      protected $offset;
      /**
       * Zero means everything
       * @var int
       */
      protected $limit;
      /**
       * @var array of IFeedItem merged sorted items from feeds
       */
      protected $items;

      /**
       *
       * @var IFeedItemFilter
       */
      protected $defaultFilter;

      public function __construct() {
	 $this->providers = array();
	 $this->items = null;
	 $this->offset = 0;
	 $this->limit = 0;
	 $this->defaultFilter = new HtmlEscapeFilter();
      }

      public function getItems() {
	 if ($this->items === null) {
	    $this->merge();
	 }

	 return $this->items;
      }

      public function setOffset($offset) {
	 $this->offset = $offset;
      }

      public function setLimit($limit) {
	 $this->limit = $limit;
      }

      public function addProvider(IFeedProvider $provider, $filter = null) {
	 $this->providers[] = $provider;
	 $this->filters[] = $filter;
      }

      protected function merge() {
	 $iterators = array();
	 foreach ($this->providers as $provider) {
	    $tmp = $provider->getIterator();
	    $iterators[] = $tmp;
	    $tmp->rewind();
	 }

	 $this->items = array();

	 $position = 0;
	 $finalPosition = $this->offset + ($this->limit == 0 ? 1000000 : $this->limit);


	 while ($position < $finalPosition) {
//find maximum of all iterators
	    $max = 0;
	    $maxIt = null; //iterator that gives maximal value
	    $paxP = null; //index of provider that gives maximal value
	    $isEnd = true;
	    $p = -1;
	    foreach ($iterators as $iterator) {
	       ++$p;
	       if (!$iterator->valid()) {
		  continue; //we do not test ended iterators}
	       }
	       $isEnd = false; //we have found a running iterator
	       $tmpItem = $iterator->current();

	       if ($tmpItem->getSortingKey() > $max) {
		  $max = $tmpItem->getSortingKey();
		  $maxIt = $iterator;
		  $maxP = $p;
	       }
	    }
	    if ($isEnd) {
	       break;
	    }

//append item to result and move iterator
	    if ($position >= $this->offset) {
	       if ($this->filters[$maxP] !== null) {
		  $this->items[] = $this->filters[$maxP]->filter($maxIt->current());
	       } else {
		  $this->items[] = $maxIt->current();
	       }
	    }
	    $maxIt->next();
	    $position += 1;
	 }
	 
      }


      public function getIterator() {
	 return new \ArrayIterator($this->getItems());
      }

   }

