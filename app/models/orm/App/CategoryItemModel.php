<?php

namespace Model\App;

class CategoryItemModel implements \OOB\Forms\IItemsModel {

    /**
     * @var array
     */
    private $idToName = null;

    /**
     * @var array
     */
    private $nameToId = null;

    public function __construct() {
        $this->idToName = \dibi::select("id, name")->from(":t:app_category")->orderBy("name")->fetchPairs("id", "name");
        $this->nameToId = \array_flip($this->idToName);
    }

    public function GetAllItems() {
        return $this->idToName;
    }

    public function IdToName($id) {
        if (\array_key_exists($id, $this->idToName))
            return $this->idToName[$id];
        else
            return "no name";
    }

    public function NameToId($name, $insert = false) {
        $name = \Nette\Utils\Strings::upper($name);
        if (\array_key_exists($name, $this->nameToId)) {
            return $this->nameToId[$name];
        } else if ($insert == true) {
            $safeName = str_replace(array("\r", "\n"), ' ', $name);
            if (\dibi::query("INSERT INTO :t:app_category", array("name" => $safeName))) {
                $id = \dibi::insertId();
                $this->nameToId[$name] = $id;
                return $id;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}
