<?php

namespace Model\Doc;

use Nette\Security\Permission;

/**
 * @generator MScaffolder
 *
 * @table doc_file
 * @hasOne(name = Filetype, referencedEntity = \Model\Doc\Filetype, column = filetype_id)
 * @hasOne(name = Directory, referencedEntity = \Model\Doc\Directory, column = directory_id)
 * @hasOne(name = User, referencedEntity = \Model\System\User, column = author_id)
 */
class File extends \Navigation\Record implements \Nette\Security\IResource {

    public function getResourceId() {
        return "doc_file";
    }

    public static function assertion(Permission $acl, $role, $resource, $privilege) {
        
    }

    public static function menuParentInfo($params = array()) {
        $row = self::find($params['id']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->directory_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public static function menuParentInfo2($params = array()) {
        $row = Directory::find($params['parent']);

        $res[\Navigation\Navigation::PINFO_PARAMS] = array("parent" => $row->parent_id);
        $res[\Navigation\Navigation::PINFO_THIS][\Navigation\Navigation::PINFO_TEXT] = $row->name;

        return $res;
    }

    public function saveWithFile(\Nette\Http\FileUpload $file) {
        $update = $this->getState() == self::STATE_EXISTING;
        $oldFile = "";
        if ($update) {
            $oldFile = $this->getMapper()->find($this->getPrimary(), true)->file;
        }



        $conf = \Nette\Environment::getConfig("documents");
        $path = \WWW_DIR . '/' . $conf['path'];

        try {
            $type = $this->getFiletype($file);

            if (!$type) {
                throw new \Nette\Application\ApplicationException("Uploaded file is not supported.");
            }

            $this->Filetype = $type;
            $this->save(); //to get ID



            $this->file = md5($this->getPrimary() . $file->name) . "." . $type->extension;
            $this->size = $file->getSize();


            $file->move($path . '/' . $this->file);

            $this->save(); //save filename
            //delete old file
            if ($oldFile != "" && \file_exists($path . '/' . $oldFile)) {
                \unlink($path . '/' . $oldFile);
            }
        } catch (\Exception $exc) {
            if (!$update)
                $this->delete(); //delete incomplete record
            throw $exc;
        }
    }

    private function getFiletype(\Nette\Http\FileUpload $file) {
        $ext = \preg_split("/\./", $file->name);
        $ext = $ext[count($ext) - 1];

        return Filetype::find(array("extension" => $ext));
    }

    public function delete() {
        $conf = \Nette\Environment::getConfig("documents");
        $path = \WWW_DIR . '/' . $conf['path'];

        if (\file_exists($path . '/' . $this->file))
            unlink($path . '/' . $this->file);
        parent::delete();
    }

}
