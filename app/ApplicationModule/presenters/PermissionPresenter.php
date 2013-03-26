<?php

namespace ApplicationModule;

use \Model\System\User;

/**
 * @generator MScaffolder
 */
final class PermissionPresenter extends \AuthenticatedPresenter {

    // <editor-fold defaultstate="collapsed" desc="Components">





    protected function createComponentGrid($name) {

        $grid = new \Gridito\EGrid($this, $name);

        User::findAll()->fetchAll(); // preload cache
        // model
        $grid->setModel(new \Gridito\DibiFluentModel(\dibi::select("u.*, u.id AS uid, CONCAT(u.surname, u.name, u.registration) AS [sort]")
                                ->from(":t:system_user AS u")
                                ->leftJoin(":t:app_member AS m")->on("m.user_id = u.id")
                                ->leftJoin(":t:app_backer AS b")->on("b.user_id = u.id")
                                ->where("u.active = 1")
                                ->and("(m.active = 1 OR b.active = 1)"), //active member or backer
                        '\Model\System\User'
        ));

        $grid->getModel()->setPrimaryKey("u.id");
        $grid->getModel()->setSorting('sort', 'ASC');

        // columns
        $grid->addColumn("sort", "Uživatel")
                ->setSortable(true)
                ->setRenderer(function($row, $column) {
                            echo $row->getFullName();
                        });


        $f = new \OOB\MultipleTextSelect(new \Model\App\AllowedModel(null));
        $f->setUnknownMode(\OOB\MultipleTextSelect::N_IGNORE)
                ->addRule(\Nette\Forms\Form::VALID, "Jména nejsou správně.")
                ->setSize(50);
        $grid->addColumn("uid", "Přihlašovatelé")
                ->setSortable(false)
                ->setRenderer(function($row, $column) {
                            $first = true;
                            foreach ($row->Appliers as $applier) {
                                echo ($first ? '' : ', ') . $applier->getFullName();
                                $first = false;
                            }
                        })
                ->setControl($f)
                ->setField('uid')
                ->setValueSetter(function($control, $record) {
                            $control->setValue($record->Appliers->fetchColumn('id'));
                        });
        // buttons

        $grid->setShowAdd(false);
        $pres = $this;

        $grid->setSubmitCallback(function($form) use($pres) {
                    $values = $form->getValues();
                    $user = User::find($values["editId"]);
                    if ($user) {
                        $self = $user->id;
                        $user->Appliers = array_map(function ($id) {
                                    return User::create(array("id" => $id));
                                }, array_filter($values["uid"], function($item) use ($self) {
                                            return $item != $self;
                                        }));
                        $user->save();
                        $pres->flashMessage($user->getFullname() . " – přihlašovatelé upraveni.");
                    }
                });

        //settings
        $grid->setItemsPerPage(\RecordPresenter::IPP);

        return $grid;
    }

    public function actionDefault() {
        $testUser = User::create();
        if (!$this->getUser()->isAllowed($testUser, 'changeAppliers')) {
            throw new Nette\Application\BadRequestException(\RecordPresenter::ttUnauthorizedAccess($testUser, 'changeAppliers'), 403);
        }
    }

    // </editor-fold>
}

