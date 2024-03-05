<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\VacationType;
use App\Model\Database\Entity\PermissionItem;

class VacationTypePresenter extends BasePresenter
{
    /**
     * ACL name='Správa důvodů absencí'
     * ACL rejection='Nemáte přístup ke správě důvodů absencí.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním důvodů absence'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getVacationTypeRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Důvod absence nebyl nalezen.', 'error');
                $this->redirect('VacationType:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem důvodů absencí'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(VacationType::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'VacationType:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'VacationType:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit důvodů absence'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(VacationType::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit důvod absence', 'success'], ['Nepodařilo se uložit důvod absence!', 'error']);
        $form->setRedirect('WorkerPosition:');
        return $form;
    }
}