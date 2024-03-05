<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\ApproveNorm;
use App\Model\Database\Entity\PermissionItem;

class ApproveNormPresenter extends BasePresenter
{
    /**
     * ACL name='Správa norem'
     * ACL rejection='Nemáte přístup ke správě norem.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním normy'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getApproveNormRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Norma nebyla nalezena.', 'error');
                $this->redirect('ApproveNorm:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem normy'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ApproveNorm::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ApproveNorm:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ApproveNorm:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        //$this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit normy'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ApproveNorm::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit normu', 'success'], ['Nepodařilo se uložit normu!', 'error']);
        $form->setRedirect('ApproveNorm:');
        return $form;
    }
}