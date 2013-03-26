<?php

namespace Model;

/**
 * Description of RecencyLog
 *
 * @author michal
 */
class FrecencyLog {
    const INF = 1e9;

    private $frequencyGranularity = 1;
    private $recencyGranularity = 10; //900;

    public function getFrequencyGranularity() {
        return $this->frequencyGranularity;
    }

    public function setFrequencyGranularity($frequencyGranularity) {
        $this->frequencyGranularity = $frequencyGranularity;
    }

    public function getRecencyGranularity() {
        return $this->recencyGranularity;
    }

    public function setRecencyGranularity($recencyGranularity) {
        $this->recencyGranularity = $recencyGranularity;
    }

    /**
     * Log access to current action with parameters
     * @param \Nette\Application\UI\Presenter $presenter current presetner
     */
    public function log(\Nette\Application\UI\Presenter $presenter) {
        $sess = $this->getSession();
        $action = $presenter->getAction(true);
        $parameters = array();
        foreach ($presenter->getRequest()->getParameters() as $key => $value) {
            if (($key == 'id' || $key == 'url' || $key == 'parent' || $key == 'old')
                    && $value !== null) {
                $parameters[$key] = $value;
            }
        }
        if (count($parameters) == 0) {
            $parameters = null;
        }
        $key = $this->getKey($action, $parameters);
        if (!array_key_exists($key, $sess->items)) {
            $sess->items[$key] = (object) array(
                        'action' => $action,
                        'parameters' => $parameters
            );
            $sess->frequency[$key] = 0;
        }
        $sess->frequency[$key] += 1;
        $sess->lastVisit[$key] = time();

        \Nette\Diagnostics\Debugger::barDump($key, 'log');
    }

    /**
     * Return recency of given action and parameters.
     *   frecency = frequency / recency
     * 
     * @param string $action
     * @param array $parameters 
     */
    public function getFrecency($action, $parameters) {
        $key = $this->getKey($action, $parameters);
        \Nette\Diagnostics\Debugger::barDump($key, 'get');
        $sess = $this->getSession();
        if (!array_key_exists($key, $sess->items)) {
            return 0;
        }
        $frequency = (int) ($sess->frequency[$key] / $this->frequencyGranularity);
        $recency = (int) ((time() - $sess->lastVisit[$key]) / $this->recencyGranularity); // 15 minutes granularity
        if ($recency == 0) {
            return self::INF;
        } else {
            return $frequency / $recency;
        }
    }

    /**
     * @return array of array with actions and their parameters
     */
    public function getAll() {
        return array_values($this->getSession()->items);
    }

    private $sess = null;

    /**
     * @return \Nette\Http\SessionSection 
     */
    private function getSession() {
        if ($this->sess === null) {
            $this->sess = \Nette\Environment::getSession('recency_log');
            if (!isset($this->sess->lastVisit)) {
                $this->sess->lastVisit = array();
            }
            if (!isset($this->sess->frequency)) {
                $this->sess->frequency = array();
            }
            if (!isset($this->sess->items)) {
                $this->sess->items = array();
            }
        }
        return $this->sess;
    }

    private function getKey($action, $parameters) {
        return $action . serialize($parameters);
    }

    public function clean() {
        $this->getSession()->remove();
        $this->sess = null;
    }

}

?>
