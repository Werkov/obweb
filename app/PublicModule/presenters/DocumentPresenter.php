<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Doc\Directory;
use Model\Doc\File;
use Model\Doc\Filetype;

final class DocumentPresenter extends PublicPresenter {

    public function renderDefault() {
        $fl = \dibi::select("f.name, f.size, f.filetype_id, f.id")
                ->from(":t:doc_file AS f")
                ->leftJoin(":t:doc_directory AS d")->on("d.id =f.directory_id")
                ->orderBy("f.published DESC");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("d.public = 1");
        }



        $this->getComponent("fileViewer")->addFluent("files", $fl, '\Model\Doc\File');
        $this->getComponent("fileViewer")->setItemsPerPage(10);

        $fl = \dibi::select("d.id, d.name, d.url")
                ->from(":t:doc_directory AS d")
                ->where("d.parent_id IS NULL")
                ->orderBy("d.name");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("d.public = 1");
        }



        $this->getComponent("directoryViewer")->addFluent("directories", $fl, '\Model\Doc\Directory');
    }

    public function renderDirectory($parent) {
        $directory = Directory::find(array('url' => $parent));
        if (!$directory) {
            throw new \Nette\Application\BadRequestException("Neexistující složka galerií.", 404);
        }
        if (!$directory->public && !$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }

        //load directories
        $fl = \dibi::select("d.id, d.name, d.url")
                ->from(":t:doc_directory AS d")
                ->where("d.parent_id = %i", $directory->id)
                ->orderBy("d.name ASC");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("d.public = 1");
        }


        $this->getComponent("directoryViewer")->addFluent("directories", $fl, '\Model\Doc\Directory');
        $this->template->directory = $directory;

        //load files
        $fl = \dibi::select("f.name, f.size, f.filetype_id, f.id")
                ->from(":t:doc_file AS f")
                ->where("f.directory_id = %i", $directory->id)
                ->orderBy("f.name ASC");

        /* if(!$this->getUser()->isLoggedIn()){
          $fl->where("d.public = 1");
          } */


        $this->getComponent("directoryViewer")->addFluent("files", $fl, '\Model\Doc\File');
    }

    public function actionDownload($id) {
        $file = File::find($id);
        if (!$file) {
            throw new \Nette\Application\BadRequestException("Neexistující soubor.", 404);
        }
        if (!$file->Directory->public && !$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }

        $filename = $file->name . "." . $file->Filetype->extension;

        $config = \Nette\Environment::getConfig("documents");
        $path = $config["path"]; //w/out trailing slash
        $source = WWW_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $file->file;



        $downloader = \FileDownload::getInstance();
        $downloader->setSourceFile($source);
        $downloader->setTransferFileName($filename);
        $downloader->download();
    }

    /*     * ******************* components ****************************** */

    protected function createComponentDirectoryViewer($name) {
        $viewer = new \OOB\DirectoryViewer($this, $name);
        $viewer->setFileAction(":Public:Document:download");
        $viewer->setDirectoryAction(":Public:Document:directory");
        return $viewer;
    }

    protected function createComponentFileViewer($name) {
        $viewer = new \OOB\DirectoryViewer($this, $name);
        $viewer->setFileAction(":Public:Document:download");
        $viewer->setDirectoryAction(":Public:Document:directory");
        return $viewer;
    }

}