<?php

/**
 * Description of HomepagePresenter
 *
 * @author michal
 */

namespace OrganizationModule;

use Model\Org\Race;

final class InfoPresenter extends SitePresenter {

    /**
     * @var \Model\Org\Race
     */
    private $race;

    public function actionDetails($race) {
        $this->getComponent('raceInfo')->setType(\OOB\OrgRaceInfo::TYPE_DETAILS);
    }

    public function actionInstructions($race) {
        $this->getComponent('raceInfo')->setType(\OOB\OrgRaceInfo::TYPE_INSTRUCTIONS);
    }


    protected function createComponentRaceInfo($name) {
        $info = new \OOB\OrgRaceInfo($this, $name);
        $info->setRace($this->currentRace);        
    }

}