<?php

namespace FeedUtils;

/**
 * Component showing aggregated feeds in one list.
 */
class CalendarReader extends FeedReader {

    /**
     * Create template
     * @return Template
     */
    protected function createTemplate($class = null) {

        if (!$this->lazyLoad || $this->isLoaded) {
            return parent::createTemplate($class)->setFile(__DIR__ . "/templates/Calendar/list.latte");
        } else {
            return parent::createTemplate($class)->setFile(__DIR__ . "/templates/Calendar/unloaded.latte");
        }
    }

    function render() {
        if (!$this->lazyLoad || $this->isLoaded) {


            $day = 86400;
            $week = 604800; //24*3600*7
            $month = 2678400; //24*3600*31
            $now = strtotime('midnight today');

            // lower bound is inclusive, upper exclusive
            $periods = array(
                "Dnes" => array($now, $now + $day),
                "Následujících sedm dní" => array($now + $day, $now + $week),
                "Následující měsíc" => array($now + $week, $now + $month),
                "Minulých sedm dní" => array($now - $week, $now),
            );

            $tmp = array_values($periods);
            $min = $tmp[3][0]; //minimal time in calendar

            $this->template->periods = array();
            $keys = array();
            foreach (array_keys($periods) as $key) {
                $period = new \stdClass();
                $period->name = $key;
                $period->items = array();
                $keys[$key] = $period;
                $this->template->periods[] = $period;
            }

            foreach ($this->aggregator->getItems() as $item) {
                if ($item->dtstart->getTimestamp() < $min)
                    break; //items are sorted from newest, so we don't need to iterate further on

                foreach ($periods as $key => $interval) {
                    if ($item->dtstart->getTimestamp() >= $interval[0] && $item->dtstart->getTimestamp() < $interval[1]) {
                        $keys[$key]->items[] = $item;
                    }
                }
            }

            //sort ascendetnaly
            foreach ($keys as $key) {
                usort($key->items, function($a, $b) {
                            return $a->dtstart->getTimestamp() - $b->dtstart->getTimestamp();
                        });
            }
        }




        $this->template->render();
    }

}

//</editor-fold>
