<?php

namespace FeedUtils;

   /**
    * RSS 2.0 feed component
    */
   class RSSGenerator extends AbstractGenerator {

//<editor-fold desc="Variables">
      /**
       * @var string
       */
      protected $language = "cs";
      /**
       * Email
       * @var string
       */
      protected $editor;
      /**
       * Email
       * @var string
       */
      protected $webmaster;
      /**
       * @var ImageInfo
       */
      protected $image;

//</editor-fold>
//<editor-fold desc="Getters & setters">
      public function getLanguage() {
	 return $this->language;
      }

      public function setLanguage($language) {
	 $this->language = $language;
      }

      public function getEditor() {
	 return $this->editor;
      }

      public function setEditor($editor) {
	 $this->editor = $editor;
      }

      public function getWebmaster() {
	 return $this->webmaster;
      }

      public function setWebmaster($webmaster) {
	 $this->webmaster = $webmaster;
      }

      public function getImage() {
	 return $this->image;
      }

      public function setImage($image) {
	 $this->image = $image;
      }

//</editor-fold>
//<editor-fold desc="Rendering">
      /**
       *
       * @return \Nette\Templating\Template
       */
      protected function createTemplate($class = null) {
	 return parent::createTemplate($class)->setFile(__DIR__ . "/templates/rss.latte");
      }

      public function render() {
	 $this->template->language = $this->language;
	 $this->template->editor = $this->editor;
	 $this->template->webmaster = $this->webmaster;
	 $this->template->image = $this->image;
	 parent::render();
      }

      protected function getItems() {
	 $items = parent::getItems();
	 if (count($items) > 0) {
	    $this->template->lastDate = $items[0]->datetime;
	 }
	 return $items;
      }

//</editor-fold>
   }

