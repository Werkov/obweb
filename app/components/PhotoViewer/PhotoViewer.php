<?php

namespace OOB;

use Model\Gallery\Gallery;
use Model\Gallery\Directory;
use Model\Gallery\Photo;

class PhotoViewer extends AbstractViewer {

   //<editor-fold desc="Variables">
   /**
    *
    * @var int
    */
   protected $columnsCount = 4;

//</editor-fold>
   //<editor-fold desc="Getters & setters">
   
   public function getColumnsCount() {
      return $this->columnsCount;
   }

   public function setColumnsCount($columnsCount) {
      $this->columnsCount = $columnsCount;
   }




//</editor-fold>
   //<editor-fold desc="Rendering">

   protected function createTemplate($class = null) {
      $template = parent::createTemplate($class)->setFile(__DIR__ . "/viewer.latte");
      $template->registerHelper('thumb', 'LayoutHelpers::thumb');
      return $template;
   }

   public function render() {
      $this->prepareData();
      $config = \Nette\Environment::getConfig("gallery");
      $this->template->path = $config["path"]; //w/out trailing slash
      
      $this->template->render();
   }

//</editor-fold>
}

?>
