<?php

/**
 *
 * @author michal
 */

namespace PublicModule;

use Model\Gallery\Gallery;
use Model\Gallery\Directory;
use Model\Gallery\Photo;

final class GalleryPresenter extends PublicPresenter {

    public function renderDefault() {
        $fl = \dibi::select("g.id, g.name, g.desc, g.published, COUNT(p.id) AS photos")
                ->from(":t:gallery_gallery AS g")
                ->leftJoin(":t:gallery_photo AS p")->on("g.id = p.gallery_id")
                ->groupBy("g.id")
                ->having("photos > 0")
                ->orderBy("g.published DESC");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("g.public = 1");
        }


        $this->getComponent("galleryViewer")->addFluent("galleries", $fl, '\Model\Gallery\Gallery');
        $this->getComponent("galleryViewer")->setItemsPerPage(6);

        $fl = \dibi::select("d.id, d.name, COUNT(g.id) AS galleries")
                ->from(":t:gallery_directory AS d")
                ->leftJoin(":t:gallery_gallery AS g")->on("d.id = g.directory_id")
                ->groupBy("d.id")
                ->having("galleries > 0")
                ->orderBy("d.name");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("g.public = 1");
        }


        $this->getComponent("directoryViewer")->addFluent("directories", $fl, '\Model\Gallery\Directory');
        $this->getComponent("directoryViewer")->setDirectoryAction(":Public:Gallery:directory");
    }

    public function renderDirectory($parent) {
        $directory = Directory::find(array('url' => $parent));
        if (!$directory) {
            throw new \Nette\Application\BadRequestException("Neexistující složka galerií.", 404);
        }

        $fl = \dibi::select("g.id, g.name, g.url, g.desc, g.published, COUNT(p.id) AS photos")
                ->from(":t:gallery_gallery AS g")
                ->leftJoin(":t:gallery_photo AS p")->on("g.id = p.gallery_id")
                ->groupBy("g.id")
                ->where("g.directory_id = %i", $directory->id)
                ->having("photos > 0")
                ->orderBy("g.published DESC");

        if (!$this->getUser()->isLoggedIn()) {
            $fl->where("g.public = 1");
        }

        //$fl->limit(3);      
        $this->getComponent("galleryViewer")->addFluent("galleries", $fl, '\Model\Gallery\Gallery');
        $this->template->directory = $directory;
    }

    public function renderGallery($parent) {
        $gallery = Gallery::find(array('url' => $parent));
        if (!$gallery) {
            throw new \Nette\Application\BadRequestException("Neexistující složka galerií.", 404);
        }
        if (!$gallery->public && !$this->getUser()->isLoggedIn()) {
            $this->loginRedirect();
        }

        $fl = \dibi::select("p.id, p.file, p.desc")
                ->from(":t:gallery_photo AS p")
                ->where("p.gallery_id = %i", $gallery->id)
                ->orderBy("p.published ASC");


        //$fl->limit(3);


        $this->getComponent("photoViewer")->addFluent("photos", $fl, '\Model\Gallery\Photo');
        $this->getComponent("photoViewer")->setItemsPerPage(40);
        $this->template->gallery = $gallery;
    }

    /*     * ******************* components ****************************** */

    protected function createComponentPhotoViewer($name) {
        $viewer = new \OOB\PhotoViewer($this, $name);

        return $viewer;
    }

    protected function createComponentGalleryViewer($name) {
        $viewer = new \OOB\GalleryViewer($this, $name);
        return $viewer;
    }

    protected function createComponentDirectoryViewer($name) {
        $viewer = new \OOB\DirectoryViewer($this, $name);
        return $viewer;
    }

}