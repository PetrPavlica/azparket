<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\ItemType;
use App\Model\Database\Entity\PermissionItem;

class ItemTypePresenter extends BasePresenter
{
    /**
     * ACL name='Správa typů položek'
     * ACL rejection='Nemáte přístup ke správě typů položek.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním typu položky'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getItemTypeRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Typ položky nebyl nalezen.', 'error');
                $this->redirect('ItemType:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem typů položkek'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ItemType::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ItemType:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ItemType:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit typu položky'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ItemType::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit typ položky', 'success'], ['Nepodařilo se uložit typ položky!', 'error']);
        $form->setRedirect('ItemType:');
        return $form;
    }
}