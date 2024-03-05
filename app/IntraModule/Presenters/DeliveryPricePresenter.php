<?php

namespace App\IntraModule\Presenters;

use App\Components\UblabooTable\Model\ACLGrid;
use App\Model\Database\Entity;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;

class DeliveryPricePresenter extends BasePresenter
{
    /**
     * ACL name='Ceny dopravy'
     * ACL rejection='Nemáte přístup k cenám dopravy.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, Entity\PermissionItem::TYPE_PRESENTER);
    }
    
    /**
    * ACL name='Zobrazení stránky s úpravou / přidáním cen dopravy'
    */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getDeliveryPriceRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Cena dopravy nebyla nalezena.', 'error');
                $this->redirect('DeliveryPrice:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem cen dopravy'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Entity\DeliveryPrice::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'DeliveryPrice:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Skill:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit cen dopravy'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Entity\DeliveryPrice::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit cenu dopravy', 'success'], ['Nepodařilo se uložit cenu dopravy!', 'error']);
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
            $this->redirect('DeliveryPrice:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('DeliveryPrice:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('DeliveryPrice:edit');
        } else {
            $this->redirect('DeliveryPrice:edit', ['id' => $entity->id]);
        }
    }
}