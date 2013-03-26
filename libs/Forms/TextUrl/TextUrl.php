<?php

namespace OOB;

use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * ColorPicker input control.
 *
 * @author     Michal Koutny
 */
class TextUrl extends TextInput {

    /**
     * @param  string  label
     * @param  int  width of the control
     * @param  int  maximum number of characters the user may enter
     */
    public function __construct($label = NULL, $cols = NULL, $maxLenght = NULL) {
        parent::__construct($label, $cols, $maxLenght);
    }

    /**
     * Generates control's HTML element.
     * @return Html
     */
    public function getControl() {
        $control = parent::getControl();

        return $control;
    }
    public function getValue() {
        if(preg_match('#^([a-z]+:)?//?#i', $this->value)){
            return $this->value;
        }else{
            return $this->value ? 'http://' . $this->value : null;
        }
    }

    /**
     * Adds addCheckboxList() method to Nette\Forms\Container
     */
    public static function register() {
        \Nette\Forms\Container::extensionMethod('addTextUrl', function (\Nette\Forms\Container $_this, $name, $label, array $items = NULL) {
                    return $_this[$name] = new TextUrl($label, $items);
                });
    }

}