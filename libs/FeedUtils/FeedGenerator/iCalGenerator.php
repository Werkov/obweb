<?php

namespace FeedUtils;

/**
 * iCalendar format generator
 * @see http://tools.ietf.org/html/rfc5545
 */
class iCalGenerator extends AbstractGenerator {

//<editor-fold desc="Variables">
//</editor-fold>
//<editor-fold desc="Getters & setters">
//</editor-fold>
//<editor-fold desc="Rendering">
    protected function createTemplate($class = null) {
        $template = parent::createTemplate($class)->setFile(__DIR__ . "/templates/ical.latte");
        $template->registerHelper("ical", function($text) {
                    return $text; // Nette does escaping

                    $esc = preg_replace('/([,;\\\\])/', '\\\\$1', $text); //escape ,;\ with \
                    return preg_replace("/\\n/", '\n', $esc);
                });
        $template->registerHelper("icalDate", function($date, $time = false) {
                    if ($time) {
                        return $date->format(":Ymd\\THis\\Z");
                    } else {
                        return ";VALUE=DATE:" . $date->format("Ymd");
                    }
                    $esc = preg_replace('/([,;\\\\])/', '\\\\$1', $text); //escape ,;\ with \
                    return preg_replace("/\\n/", '\n', $esc);
                });
        return $template;
    }

    public function render() {
        return parent::render();
        ob_start(function($output) {
                    $lines = preg_split("/\\n/", $output);
                    $ret = "";
                    $max = 75;
                    $nl = "\r\n";
                    $indent = " ";
                    foreach ($lines as $line) {
                        $i = 0;
                        while (mb_strlen($line) > $max) {
                            if ($i == 0) {
                                $ret .= mb_substr($line, 0, $max) . $nl;
                            } else {
                                $ret .= " " . mb_substr($line, 0, $max) . $nl;
                            }
                            $line = mb_substr($line, $max);
                            ++$i;
                        }
                        if ($i > 0)
                            $ret .= $indent . $line . $nl;
                        else
                            $ret .= $line . $nl;
                    }

                    return $ret;
                });
        parent::render();
        ob_end_flush();
    }

    protected function getItems() {
        $this->aggregator->setOffset(0);
        $this->aggregator->setLimit(0); //get all items

        return $this->aggregator->getItems();
    }

//</editor-fold>
}

