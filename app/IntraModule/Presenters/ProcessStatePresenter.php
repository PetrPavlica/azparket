<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\ProcessState;
use App\Model\Database\Entity\PermissionItem;

class ProcessStatePresenter extends BasePresenter
{
    /**
     * ACL name='Správa stavů procesu obchodního případu'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getProcessStateRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se najít uvedený záznam!', 'error');
                $this->redirect('ProcessState:');
            }
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);

            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem stavů procesu obch. případu'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ProcessState::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'ProcessState:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $action = $grid->addAction('edit', '', 'ProcessState:edit');
        if ($action)
            $action->setIcon('pencil')
                ->setTitle('Úprava')
                ->setClass('btn btn-xs btn-link');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit stavů procesu obch. případu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ProcessState::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit stav obchodních případů', 'success'], ['Nepodařilo se uložit stav obchodních případů!', 'warning']);
        $form->setRedirect('ProcessState:');
        return $form;
    }

}