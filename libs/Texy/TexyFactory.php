<?php

/**
 * Description of TexyFactory
 *
 * @author michal
 */
class TexyFactory {

    /**
     * Keys beginning with '__' are considered as member objects.
     * @param stdClass $parameters
     * @return Texy 
     */
    public static function create($parameters = null) {
        $texy = new Texy();
        \Nette\Diagnostics\Debugger::barDump($parameters);
        if ($parameters !== null)
            self::setValuesToObject($texy, $parameters);
        return $texy;
    }

    private static function setValuesToObject($object, $values) {
        foreach ($values as $key => $value) {
            if (strpos($key, '__') === 0) {
                $pure = substr($key, 2);
                self::setValuesToObject($object->$pure, $value);
            } else {
                if (is_array($object->{$key})) {
                    foreach ($value as $k => $v) {
                        $object->{$key}[$k] = $v;
                    }
                } else {
                    $object->{$key} = $value;
                }
            }
        }
    }

}

