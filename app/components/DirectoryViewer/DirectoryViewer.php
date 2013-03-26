<?php

namespace OOB;

/**
 * Class for generic directory/file listing
 */
class DirectoryViewer extends AbstractViewer {

   //<editor-fold desc="Variables">
   protected $directoryAction;
   /**
    * @var string 
    */
   protected $fileAction;
   /**
    *
    * @var int
    */
   protected $columnsCount = 6;

//</editor-fold>
   //<editor-fold desc="Getters & setters">

   public function getDirectoryAction() {
      return $this->directoryAction;
   }

   public function setDirectoryAction($directoryAction) {
      $this->directoryAction = $directoryAction;
   }

   public function getFileAction() {
      return $this->fileAction;
   }

   public function setFileAction($fileAction) {
      $this->fileAction = $fileAction;
   }
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
      //$template->registerHelper('thumb', 'LayoutHelpers::thumb');
      return $template;
   }

   public function render() {
      $this->prepareData();
      $this->template->fileAction = $this->fileAction;
      $this->template->directoryAction = $this->directoryAction;
      //$config = \Nette\Environment::getConfig("gallery");
      //$this->template->path = $config["path"]; //w/out trailing slash

      $this->template->render();
   }

//</editor-fold>
}

?>
