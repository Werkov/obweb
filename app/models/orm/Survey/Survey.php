<?php

namespace Model\Survey;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table survey_survey
 * @hasMany(name = Answers, referencedEntity = \Model\Survey\Answer, column = survey_id)
 * @hasMany(name = Survey2users, referencedEntity = \Model\Survey\Survey2user, column = survey_id)
 */
class Survey extends \Navigation\Record implements \Nette\Security\IResource {

    /**
     *
     * @var int  number of votes that have been already posted
     */
    private $votes = null;

    public function getVotes() {
        if ($this->votes == null && $this->getState() == self::STATE_EXISTING) {
            $this->votes = \dibi::fetchSingle("SELECT COUNT(*) FROM :t:survey_survey2user WHERE survey_id = %i", $this->id);
        }

        return $this->votes;
    }

    public function getResourceId() {
        return "survey_survey";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array();
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->question;

        return $res;
    }

}
