<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\ApproveTime;
use App\Model\Database\Entity\PermissionItem;

class ApproveTimePresenter extends BasePresenter
{
    /**
     * ACL name='Správa doby schvalování'
     * ACL rejection='Nemáte přístup ke správě doby schvalování.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním doby schvalování'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getApproveTimeRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Doba schvalování nebyla nalezena.', 'error');
                $this->redirect('ApproveTime:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem doby schvalování'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ApproveTime::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ApproveTime:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ApproveTime:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        //$this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit doby schvalování'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ApproveTime::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dobu schvalování', 'success'], ['Nepodařilo se uložit dobu schvalování!', 'error']);
        $form->setRedirect('ApproveTime:');
        return $form;
    }
}