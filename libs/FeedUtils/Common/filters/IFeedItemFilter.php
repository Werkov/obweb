<?php

namespace FeedUtils;

   /**
    * Checks content of feed item and modifies it.
    */
   interface IFeedItemFilter {
      /**
       * @param IFeedItem $item
       * @return IFeedItem
       */
      public function filter(IFeedItem $item);
   }


