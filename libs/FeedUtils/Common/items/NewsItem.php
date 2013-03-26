<?php

namespace FeedUtils;

   /**
    * Container class for single item.
    */
   class NewsItem implements IFeedItem {

      public function __construct($values = null) {
	 if($values === null){
             return;
         }
	 foreach ($values as $key => $value) {
	    $this->$key = $value;
	 }
      }

      public function getSortingKey() {
	 if($this->datetime)
	    return $this->datetime->getTimestamp();
	 else
	    return time();
      }
      
      public function parseAuthor(){
          if(preg_match('/([^\(]+)\s*\((.*)\)/', $this->author, $matches)){
              return $matches[2];
          }else{
              return $this->author;
          }
      }

      /**
       * Unique id
       * @var string
       */
      public $id;
      /**
       * Is $id a permalink?
       * @var bool
       */
      public $isPermalink;
      /**
       * Required
       * @var string
       */
      public $name;
      /**
       * @var string
       */
      public $author;
      /**
       * @var string
       */
      public $desc;
      /**
       * Required
       * @var DateTime
       */
      public $datetime;
      /**
       * @var string
       */
      public $url;
      /**
       * @var SourceInfo
       */
      public $sourceInfo;


   }

