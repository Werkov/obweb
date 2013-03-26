<?php

namespace OOB;

class CommentViewer extends AbstractViewer {

   //<editor-fold desc="Variables">

//</editor-fold>
   //<editor-fold desc="Getters & setters">


//</editor-fold>
   //<editor-fold desc="Rendering">

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/viewer.latte");
      $template->registerHelperLoader(callback("\OOB\Helpers::loader"));
      return $template;
   }

//</editor-fold>
}

?>
