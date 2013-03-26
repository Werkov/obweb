1) Add the line:

	Nette\Forms\Controls\CheckboxList::register();

   to app/bootstrap.php to register checkbox control.

2) Example of usage:

	protected function createComponentForm()
	{
		$form = new Nette\Application\UI\Form;

		$items = array(1 => 'foo', 2 => 'bar');
		$form->addCheckboxList('demo', 'Choices', $items)
			->addRule('checked', 'Check something!');

		return $form;
	}