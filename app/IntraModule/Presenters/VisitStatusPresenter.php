<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class VisitStatusPresenter extends BasePresenter
{
    /**
     * ACL name='Správa stavů výjezdů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getVisitStatusRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('VisitStatus:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem stavů výjezdu'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\VisitStatus::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'VisitStatus:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'VisitStatus:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit stavů výjezdu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\VisitStatus::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit stav výjezdu', 'success'], ['Nepodařilo se uložit stav výjezdu!', 'warning']);
        $form->setRedirect('VisitStatus:default');
        return $form;
    }

}
