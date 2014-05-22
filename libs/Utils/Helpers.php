<?php

namespace OOB;

/**
 *
 * @author michal
 */
class Helpers {

    public static function loader($helper) {
        $callback = callback(__CLASS__, $helper);
        if ($callback->isCallable()) {
            return $callback;
        }
    }

    public static function mail($address, $link = false) {
        $obfs = preg_replace("/@/", " na ", $address);
        if ($link) {
            return \Nette\Utils\Html::el("a")->href("mailto:" . $address)->setText($obfs);
        } else {
            return $obfs;
        }
    }

    /**
     * @author dgx
     * @param type $time
     * @return type 
     */
    public static function timeInWords(\DateTime $time, $timeTitle = true) {


        $delta = time() - $time->format('U');

        if ($delta < 0) {
            $delta = round(abs($delta) / 60);

            if ($delta == 0)
                $show = 'za okamžik';
            else if ($delta == 1)
                $show = 'za minutu';
            else if ($delta < 45)
                $show = 'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
            else if ($delta < 90)
                $show = 'za hodinu';
            else if ($delta < 1440)
                $show = 'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
            else if ($delta < 2880)
                $show = 'zítra';
            else if ($delta < 10800) //one week
                $show = 'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
            else
                $show = self::mydate($time, false);
        }else {

            $delta = round($delta / 60);
            if ($delta == 0)
                $show = 'před okamžikem';
            else if ($delta == 1)
                $show = 'před minutou';
            else if ($delta < 45)
                $show = "před $delta minutami";
            else if ($delta < 90)
                $show = 'před hodinou';
            else if ($delta < 1440)
                $show = 'před ' . round($delta / 60) . ' hodinami';
            else if ($delta < 2880 && $time->format('d') == date('d', time() - 84000))
                $show = 'včera ' . $time->format("H:i");
            else
                $show = self::mydate($time, false);
        }

        if ($timeTitle) {
            return '<span class="timeago" title="' . $time->format(\DateTime::ISO8601) . '">' . $show . '</span>';
        } else {
            return $show;
        }
    }

    /**
     * Plural: three forms, special cases for 1 and 2, 3, 4.
     * (Slavic family: Slovak, Czech)
     * @param  int
     * @return mixed
     */
    private static function plural($n) {
        $args = func_get_args();
        return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
    }

    public static function currency($value) {
        return str_replace(" ", "\xc2\xa0", number_format($value, 0, "", " ")) . "\xc2\xa0Kč";
    }

    public static function mydate(\DateTime $date, $timeTitle = true) {
        if ($date->format('Y') == date('Y')) {
            $show = $date->format("j. n.");
        } else {
            $show = $date->format("j. n. Y");
        }

        if ($timeTitle) {
            return '<span title="' . $date->format("j.n.Y H:i:s") . '">' . $show . '</span>';
        } else {
            return $show;
        }
    }

    public static function iconRaceStatus($status) {
        switch ($status) {
            case 0:
                $show = "Editační režim";
                break;
            case 1:
                $show = "Probíhá přihlašování";
                break;
            case 2:
                $show = "Přihlašování skončilo";
                break;
            default:
                return "Neznámý";
        }

        return '<span class="ui-icon ui-icon-race-status-' . $status . '" title="' . $show . '"></span>';
    }

    public static function iconAppliactionStatus($status, \DateTime $deadline = null, $sex = 'M') {
        switch ($status) {
            case 0:
                $show = "Nepřihlášen";
                break;
            case 1:
                $show = "Přihlášen";
                break;
            default:
                return "Neznámý";
        }

        if ($sex == 'F') {
            $show .= 'a';
        }

        if ($deadline) {
            $show .= ', uzávěrka ' . self::timeInWords($deadline, false);
        }

        return '<span class="ui-icon ui-icon-application-status-' . $status . '" title="' . $show . '"></span>';
    }

}

?>
