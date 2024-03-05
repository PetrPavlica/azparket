<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\ProductionLine;
use App\Model\Database\Entity\PermissionItem;

class ProductionLinePresenter extends BasePresenter
{
    /**
     * ACL name='Správa výrobních linek'
     * ACL rejection='Nemáte přístup ke správě výrobních linek.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním výrobní linky'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getProductionLineRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Výrobní linka nebyla nalezena.', 'error');
                $this->redirect('ProductionLine:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem výrobních linek'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ProductionLine::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ProductionLine:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ProductionLine:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit výrobní linky'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ProductionLine::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit výrobní linku', 'success'], ['Nepodařilo se uložit výrobní linku!', 'error']);
        $form->setRedirect('ProductionLine:');
        return $form;
    }
}