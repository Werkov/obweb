<?php

//require_once LIBS_DIR . '/Nette/Forms/Controls/TextInput.php';

namespace OOB;

use Nette\Forms\Controls\TextArea;
use Nette\Forms\Form;

/**
 * Wisiwyg form control.
 *
 * @author     Michal Koutny
 */
class Wysiwyg extends TextArea {

   /**
    * @param  string  label
    * @param  int  width of the control
    * @param  int  maximum number of characters the user may enter
    */
   public function __construct($label, $cols = NULL, $rows = NULL) {
      parent::__construct($label, $cols, $rows);
   }

   /**
    * Returns control's value.
    * @return mixed 
    */
   public function getValue() {
      //TODO: HTMLPurifier here!
      return $this->value;
   }

   /**
    * Sets control's value.
    * @param  string
    * @return void
    */
   public function setValue($value) {
      parent::setValue($value);
   }

   /**
    * Generates control's HTML element.
    * @return Html
    */
   public function getControl() {
      $control = parent::getControl();
      $control->class = 'tinymce';

      return $control;
   }

}

class WysiwygHelper {

   public static function addWysiwyg(Form $_this, $name, $label, $cols = NULL, $rows = NULL) {
      static $used = array();
      if (!isset($used[$_this->getName()])) {
	 $used[$_this->getName()] = true;
	 $_this->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
      }

      return $_this[$name] = new Wysiwyg($label, $cols, $rows);
   }

}

// To add method to the form class:
//FormContainer::extensionMethod('FormContainer::addRequestButton', array('WysiwygHelper', 'addWysiwyg'));


