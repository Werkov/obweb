<?php

namespace OOB;

class ThreadViewer extends AbstractViewer {

   //<editor-fold desc="Variables">

//</editor-fold>
   //<editor-fold desc="Getters & setters">


//</editor-fold>
   //<editor-fold desc="Rendering">

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/viewer.latte");
      $template->registerHelperLoader("\OOB\Helpers::loader");
      return $template;
   }

//</editor-fold>
}

?>
