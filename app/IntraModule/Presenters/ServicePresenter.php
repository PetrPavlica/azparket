<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use Doctrine\Common\Collections\Criteria;

class ServicePresenter extends BasePresenter
{
    /**
     * ACL name='Správa služeb'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getServiceRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Service:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem služeb'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Service::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Service:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Service:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        $grid->setDefaultSort(['dateService' => 'DESC']);
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit služeb'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Service::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit službu', 'success'], ['Nepodařilo se uložit službu!', 'warning']);
        $form->onValidate[] = function($form) {
            $values = $form->getValues();
            if ($values->dateService != '') { // Kontrola jedinečnosti Data
                $dateService = date_create_immutable_from_format('j. n. Y', $values->dateService);
                $criteriaStart = new Criteria();
                $criteriaStart->where(Criteria::expr()->notIn('id', [$values->id]));
                $criteriaStart->andWhere(Criteria::expr()->eq('dateService', $dateService));
                $service = $this->em->getServiceRepository()->matching($criteriaStart)->getValues();
                if ($service) {
                    $form->addError('Pozor! Službu se nepodařilo uložit. Pod tímto datem máte již naplánovanou službu!');
                }
            }
        };
        $form->setRedirect('Service:default');
        return $form;
    }

}
