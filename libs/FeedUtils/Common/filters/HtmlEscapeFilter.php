<?php

namespace FeedUtils{
   /***
    * Escapes all dangerous characters from description tag.
    */
   class HtmlEscapeFilter implements IFeedItemFilter {

      public function filter(IFeedItem $item)
      {	 
	 $item->desc = \htmlspecialchars($item->desc);
	 return $item;
      }
   }
}