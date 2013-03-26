<?php

namespace OOB;

use Model\Org\Race;

class OrgRaceInfo extends \Nette\Application\UI\Control {
    const TYPE_DETAILS = 'detailsOrder';
    const TYPE_INSTRUCTIONS = 'instructionsOrder';

    /**
     * @var \Model\Org\Race
     */
    private $race;
    private $type;

    public function getRace() {
        return $this->race;
    }

    public function setRace($race) {
        $this->race = $race;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/info.latte");
        $template->registerHelperLoader('\OOB\Helpers::loader');
        return $template;
    }

    public function render() {
        \Model\Org\Information::findAll()->fetchAll(); // preload
        $this->template->values = $this->getRace()->InformationValues->where('%n IS NOT NULL', $this->getType())->orderBy($this->getType());
        $this->template->render();
    }

}