<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ProductionSetting;

class ProductionSettingPresenter extends BasePresenter
{
    /**
     * ACL name='Nastavení plánování výroby'
     * ACL rejection='Nemáte přístup k nastavení plánování výroby.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním nastavení výroby'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getProductionSettingRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nastavení nebylo nalezeno.', 'error');
                $this->redirect('Product:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem nastavení výroby'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ProductionSetting::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ProductionSetting:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ProductionSetting:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        //$this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit nastavení výroby'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ProductionSetting::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit nastavení', 'success'], ['Nepodařilo se uložit nastavení!', 'error']);
        $form->setRedirect('Product:');
        return $form;
    }
}