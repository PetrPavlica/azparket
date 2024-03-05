<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Field;
use App\Model\Database\Entity\PermissionItem;

class FieldsPresenter extends BasePresenter
{
    /**
     * ACL name='Správa oborů'
     * ACL rejection='Nemáte přístup ke správě oborů.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového oboru'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getFieldRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Obor nebyl nalezen.', 'error');
                $this->redirect('Fields:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem oborů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Field::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Fields:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Fields:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit oboru'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Field::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit obor', 'success'], ['Nepodařilo se uložit obor!', 'error']);
        $form->setRedirect('Fields:');
        return $form;
    }
}