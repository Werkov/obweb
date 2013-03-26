<?php

namespace FeedUtils;

   class VEvent {
      const DATETIME = "Ymd\\THis\\Z";
      const DATE = "Ymd";

      public function __construct($values) {
	 foreach ($values as $key => $value) {
	    $this->$key = $value;
	 }
      }

      /**
       * @var DateTime
       */
      public $dtstamp;
      /**
       *
       * @var string
       */
      public $uid;
      /**
       * @var DateTime
       */
      public $dtstart;
      /**
       * dtstart precision, true if datetime, false if date
       * @var boolean 
       */
      public $dtstart_tm;
      /**
       * @var DateTime
       */
      public $dtend;
      /**
       * dtstart precision, true if datetime, false if date
       * @var boolean
       */
      public $dtend_tm;
      /**
       * @var string
       */
      public $location;
      /**
       * @var string
       */
      public $url;
      /**
       * @var string
       */
      public $summary;

      /*public function getStart() {
	 if ($this->dtstart_tm)
	    return $this->dtstart->format(self::DATETIME);
	 else
	    return ";VALUE=DATE:".$this->dtstart->format(self::DATE);
      }

      public function getEnd() {
	 if ($this->dtstart_tm)
	    return $this->dtend->format(self::DATETIME);
	 else
	    return ";VALUE=DATE:".$this->dtstart->format(self::DATE);
      }

      public function getStamp() {
	 return $this->dtstamp->format(self::DATETIME);
      }*/

      public function getSortingKey() {
	 if ($this->dtstart)
	    return $this->dtstart->getTimestamp();
	 else
	    return time();
      }

   }

