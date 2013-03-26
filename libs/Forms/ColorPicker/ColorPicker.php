<?php


namespace OOB;


use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

/**
 * ColorPicker input control.
 *
 * @author     Michal Koutny
  */
class ColorPicker extends TextInput
{

	/**
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  maximum number of characters the user may enter
	 */
	public function __construct($label, $cols = NULL, $maxLenght = NULL)
	{
		parent::__construct($label, $cols, $maxLenght);
	}

	/**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{		
		$control = parent::getControl();
		$control->class = 'colpicker';
		
		return $control;
	}

}

class ColorPickerHelper {

	public static function addColorPicker(Form $_this, $name, $label, $cols = NULL, $maxLength = 6)
	{
		return $_this[$name] = new ColorPicker($label, $cols, $maxLength);
	}

}