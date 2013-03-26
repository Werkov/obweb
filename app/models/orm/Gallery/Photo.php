<?php

namespace Model\Gallery;

use Nette\Security\Permission;
use Nette\Image;

/**
 * @generator MScaffolder
 *
 * @table gallery_photo
 * @hasOne(name = Gallery, referencedEntity = \Model\Gallery\Gallery, column = gallery_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = author_id)
 */
class Photo extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "gallery_photo";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        if (isset($acl->getQueriedResource()->author_id)) {
            return $acl->getQueriedResource()->author_id == $acl->getQueriedRole()->getIdentity()->id;
        } else {
            return true;
        }
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->gallery_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->file;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Gallery::find(array('url' => $params['parent']));

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->Directory->url);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo3($params = array()) {
        $row = Gallery::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->directory_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function saveWithFile(\Nette\Http\FileUpload $file) {
        $this->save(); //to get ID

        $conf = \Nette\Environment::getConfig("gallery");

        $this->file = md5($this->getPrimary() . $file->name) . ".jpg";

        try {
            if (!$file->isImage()) {
                throw new \Nette\InvalidStateException("Uploaded file is not an image.");
            }

            //resize and save file
            $img = Image::fromFile($file->getTemporaryFile());
            $img->resize($conf["maxSize"], $conf["maxSize"], Image::FIT);
            if (!$img->save(\WWW_DIR . '/' . $conf["path"] . '/' . $this->file)) {
                throw new \Nette\IOException("Can't save file '" . $file->name . "'");
            }

            $this->save(); //save filename
        } catch (\Exception $exc) {
            $this->delete();
            throw $exc;
        }
    }

    public function delete() {
        $conf = \Nette\Environment::getConfig("gallery");
        unlink(\WWW_DIR . '/' . $conf["path"] . '/' . $this->file);
        parent::delete();
    }

    public function getThumbnailUrl($size, $absolute = false) {
        $config = \Nette\Environment::getConfig("gallery");
        $path = $config["path"]; //w/out trailing slash
        $baseUrl = \Nette\Environment::getConfig('baseUrl');
        return $baseUrl . '/' . \LayoutHelpers::thumb($path . '/' . $this->file, $size);
    }

}
