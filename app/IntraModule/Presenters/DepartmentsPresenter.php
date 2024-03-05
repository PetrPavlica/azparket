<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Department;
use App\Model\Database\Entity\PermissionItem;

class DepartmentsPresenter extends BasePresenter
{
    /**
     * ACL name='Správa oddělení'
     * ACL rejection='Nemáte přístup ke správě oddělení.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového oddělení'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getDepartmentRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Oddělení nebylo nalezeno.', 'error');
                $this->redirect('Departments:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem oddělení'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Department::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Departments:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Departments:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit oddělení'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Department::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit oddělení', 'success'], ['Nepodařilo se uložit oddělení!', 'error']);
        $form->setRedirect('Departments:');
        return $form;
    }
}