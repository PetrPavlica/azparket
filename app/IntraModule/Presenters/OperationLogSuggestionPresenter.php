<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\OperationLogSuggestion;
use App\Model\Database\Entity\PermissionItem;

class OperationLogSuggestionPresenter extends BasePresenter
{
    /**
     * ACL name='Správa námětů a připomínek'
     * ACL rejection='Nemáte přístup ke správě námětů a připomínek.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním námětů a připomínek'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getOperationLogSuggestionRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Připomínka nebyla nalezena.', 'error');
                $this->redirect('OperationLogSuggestion:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem námětů a připomínek'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(OperationLogSuggestion::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'OperationLogSuggestion:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'OperationLogSuggestion:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        //$this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit námětů a připomínek'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(OperationLogSuggestion::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dobu schvalování', 'success'], ['Nepodařilo se uložit dobu schvalování!', 'error']);
        $form->setRedirect('OperationLogSuggestion:');
        return $form;
    }
}