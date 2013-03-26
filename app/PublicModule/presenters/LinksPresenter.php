<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Link\Link;
use Model\Link\Category;

final class LinksPresenter extends PublicPresenter {

   public function renderDefault() {
      $this->template->categories = Category::findAll();
   }   

}