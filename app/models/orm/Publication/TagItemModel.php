<?php

namespace Model\Publication;

use Model\Publication\Tag;

class TagItemModel implements \OOB\Forms\IItemsModel {

    /**
     * @var array
     */
    private $idToName = null;

    /**
     * @var array
     */
    private $nameToId = null;

    public function __construct() {
        $this->idToName = \dibi::select("id, name")->from(":t:public_tag")->orderBy("name")->fetchPairs("id", "name");
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
        //$name = \Nette\Utils\Strings::upper($name);
        if (\array_key_exists($name, $this->nameToId)) {
            return $this->nameToId[$name];
        } else if ($insert == true) {
            $tag = Tag::find(array('name' => $name));
            if (!$tag) {
                $tag = Tag::create(array('name' => $name));
                try {
                    if ($tag->save()) {
                        $id = $tag->id;
                        $this->nameToId[$name] = $id;
                        return $id;
                    } else {
                        return null;
                    }
                } catch (\ModelException $exc) {
                    return null;
                }
            }else{
                return $tag->id;
            }
        } else {
            return null;
        }
    }

}
