<?php

namespace App\IntraModule\Presenters;

use App\Components\UblabooTable\Model\ACLGrid;
use App\Model\Database\Entity;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;

class VatPresenter extends BasePresenter
{
    /**
     * ACL name='DPH presenter'
     * ACL rejection='Nemáte přístup k DPH.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, Entity\PermissionItem::TYPE_PRESENTER);
    }
    
    /**
    * ACL name='Zobrazení stránky s úpravou / přidáním DPH'
    */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getVatRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('DPH nebylo nalezeno.', 'error');
                $this->redirect('Vat:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem DPH'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Entity\Vat::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Vat:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Skill:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit DPH'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Entity\Vat::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit DPH', 'success'], ['Nepodařilo se uložit DPH!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];
        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        
        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }
        
        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Vat:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Vat:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Vat:edit');
        } else {
            $this->redirect('Vat:edit', ['id' => $entity->id]);
        }
    }
}