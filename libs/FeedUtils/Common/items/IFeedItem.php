<?php

namespace FeedUtils;

   /**
    * General feed items for feeds need only to have a key for sorting.
    */
   interface IFeedItem {
      /**
       * @return mixed type with comparison operator
       */
      public function getSortingKey();
   }

