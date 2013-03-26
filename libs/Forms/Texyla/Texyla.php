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
class Texyla extends TextArea {

    private $texyConfiguration = 'texyPublic';

    public function getTexyConfiguration() {
        return $this->texyConfiguration;
    }

    public function setTexyConfiguration($texyConfiguration) {
        $this->texyConfiguration = $texyConfiguration;
    }

    /**
     * @param  string  label
     * @param  int  width of the control
     * @param  int  maximum number of characters the user may enter
     */
    public function __construct($label, $cols = NULL, $rows = NULL) {
        $cols = $cols ? $cols : 80;
        $rows = $rows ? $rows : 10;
        parent::__construct($label, $cols, $rows);
    }

    /**
     * Returns control's value.
     * @return mixed 
     */
    public function getValue() {
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
        $control->class = 'texyla';
        $control->data('texy-cfg', $this->getTexyConfiguration());


        return $control;
    }

}

