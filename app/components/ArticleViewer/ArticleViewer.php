<?php

namespace OOB;

class ArticleViewer extends AbstractViewer {

   //<editor-fold desc="Rendering">

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/viewer.latte");
      $template->registerHelperLoader(callback("\OOB\Helpers::loader"));
      return $template;
   }

//</editor-fold>
}

?>
