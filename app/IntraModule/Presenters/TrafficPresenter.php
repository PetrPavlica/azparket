<?php

namespace App\IntraModule\Presenters;

use App\Model\ACLForm;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Utils\SQLHelper;

class TrafficPresenter extends BasePresenter
{
    /** @var SQLHelper */
    private $SQLHelper;

    /**
     * ACL name='Správa provozoven'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        $this->SQLHelper = new SQLHelper();
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getTrafficRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Traffic:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
        $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
    }

    public function renderChangeWorker() {
        $workers = $this->em->getWorkerRepository()->findAll();
        $workersItems = [];
        $workersItems2 = [];
        foreach ($workers as $worker) {
            $workersItems[$worker->id] = $worker->name;
            if ($worker->active) {
                $workersItems2[$worker->id] = $worker->name;
            }
        }
        $this[ 'changeWorkerForm' ]->getComponent('worker')->items = $workersItems;
        $this[ 'changeWorkerForm' ]->getComponent('workerSubstitute')->items = $workersItems2;
    }

    /**
     * ACL name='Tabulka s přehledem provozoven'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Traffic::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Traffic:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Traffic:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem výjezdů'
     */
    public function createComponentVisitTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Visit::class, get_class(), __FUNCTION__, 'default', ['traffic' => $this->params['id']]);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Visit:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        $grid->getColumn('materialNeedBuy')->setRenderer(function($item) {
            $arr = [];
            if ($item->materialNeedBuy) {
                foreach ($item->materialNeedBuy as $mnb) {
                    $arr[] = $mnb->name;
                }
            }
            return implode(', ', $arr);
        });
        $grid->getColumn('materialNeedBuy')->setSortableCallback(function($qb, $value) {
            $qb->leftJoin('\App\Model\Database\Entity\MaterialNeedBuy', 'mnb2', 'WITH', 'a.id = mnb2.visit');
            foreach ($value as $k => $v) {
                $qb->orderBy('mnb2.name', $v);
            }
        });
        $grid->getFilter('materialNeedBuy')->setCondition(function($qb, $value) {
            $search = $this->SQLHelper->termToLike($value, 'mnb', ['name']);
            $qb->leftJoin('\App\Model\Database\Entity\MaterialNeedBuy', 'mnb', 'WITH', 'a.id = mnb.visit');
            $qb->andWhere($search);
        });

        $multiAction->addAction('edit', 'Upravit', 'Visit:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit provozovny'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Traffic::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit provozovnu', 'success'], ['Nepodařilo se uložit provozovnu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'trafficFormSuccess'];
        return $form;
    }

    public function trafficFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Traffic:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Traffic:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Traffic:edit');
        } else {
            $this->redirect('Traffic:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro změnu techniků'
     */
    public function createComponentChangeWorkerForm()
    {
        $form = new ACLForm();
        $form->addSelect('worker', 'Technik původní')->setHtmlAttribute('class', 'form-control');
        $form->addSelect('workerSubstitute', 'Technik nový')->setHtmlAttribute('class', 'form-control');
        $form->setMessages(['Podařilo se změnit techniky', 'success'], ['Nepodařilo se změnit techniky!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'successChangeWorkerForm'];
        return $form;
    }

    public function successChangeWorkerForm($form, $values) {
        $values2 = $this->request->getPost();
        $this->em->getTrafficRepository()->changeCaregivers($values2['worker'], $values2['workerSubstitute']);
        $this->redirect('Traffic:default');
    }

    public function handleGetCustomers($term)
    {
        $result = $this->em->getCustomerRepository()->getDataAutocompleteCustomers($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetCustomersOrdered($term)
    {
        $result = $this->em->getCustomerOrderedRepository()->getDataAutocompleteCustomerOrdered($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

}
