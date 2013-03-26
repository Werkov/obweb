<?php

namespace OOB;

/**
 * Retrieve resultset with N rows with N/windowSize queries.
 * 
 * @author michal
 */
class SmartSession extends \Nette\Http\Session {

    private $filteredMasks;

    public function addFilterMask($mask) {
        $this->filteredMasks[] = $mask;
    }

    public function start() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            foreach ($this->filteredMasks as $mask) {
                if (preg_match($mask, $_SERVER['HTTP_USER_AGENT'])) {
                    return;
                }
            }
        }
        
        parent::start();
        $max=ini_get('session.gc_maxlifetime');
    }

}

?>
