<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\Machine;
use Nette\Application\UI\Form;

class MachinePresenter extends BasePresenter
{
    /**
     * ExternServiceVisit's ID
     * @persistent
     */
    public $backExSrvVsID;

    /**
     * ACL name='Správa strojů'
     * ACL rejection='Nemáte přístup ke správě strojů.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového stroje'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getMachineRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Stroj nebyl nalezen.', 'error');
                $this->redirect('Machine:');
            } // :o:  close();
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

            $qb = $this->em->getMachineInExternServiceVisitRepository()->createQueryBuilder('mis')
                ->select('mis')
                ->join('mis.externServiceVisit', 's')
                ->join('mis.machine', 'm')
                ->where('m.id = :id')
                ->setParameters(['id' => $this->params['id']]);
            $serviceVisits = $qb->getQuery()->getResult();
            $this->template->serviceVisits = $serviceVisits;

            $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
        } else {
            $this->template->openTab = null;
        }

        if ($this->getParameter('backExSrvVsID') !== null) {
            $this->template->backExSrvVsID = $this->getParameter('backExSrvVsID');
        }
    }

    /**
     * ACL name='Tabulka s přehledem strojů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Machine::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Machine:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Machine:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit stroje'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Machine::class, $this->user, $this, __FUNCTION__);
        $form->addText('ph1', 'Data 1')->setHtmlAttribute('class', 'form-control');
        $form->addText('ph2', 'Data 2')->setHtmlAttribute('class', 'form-control');
        $form->addText('ph3', 'Data 3')->setHtmlAttribute('class', 'form-control');
        $form->addText('ph4', 'Data 4')->setHtmlAttribute('class', 'form-control');


        $form->setMessages(['Podařilo se uložit stroj', 'success'], ['Nepodařilo se uložit stroj!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];

        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        $oldEntity = null;
        if ($values->id) {
            $oldEntity = $this->em->getMachineRepository()->find($values->id);
        }
       
        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            if ($this->getParameter('extSrvVisID') !== null) {
                $this->redirect('ExtenServiceVisit:edit', ['id' => $this->getParameter('extSrvVisID'), 'openTab' => '#machines']);
            } else {
                $this->redirect('Machine:default');
            }
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Machine:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Machine:edit');
        } else {
            $this->redirect('Machine:edit', ['id' => $entity->id]);
        }
    }

    public function handleSaveServiceVisit() {
        $values = $this->request->getPost();
        if($values['id'] && $values['result']) {
            $entity = $this->em->getMachineInExternServiceVisitRepository()->find($values['id']);
            if ($entity) {
                $entity->setResult($values['result']);
                $entity->setResultDesc(isset($values['resultDesc']) ? $values['resultDesc'] : '');
                $this->em->persist($entity);
                $this->em->flush();

                $this->flashMessage('Výsledek servisu byl uložen', 'success');
            } else {
                $this->flashMessage('Došlo k chybě při hledání návštěvy servisu', 'error');
            }
        }
        $this->redrawControl('tableServiceVisit');
    }

    public function handleRemoveServiceVisit() {
        $values = $this->request->getPost();
        $entity = $this->em->getMachineInExternServiceVisitRepository()->find($values['id']);
        if ($entity) {
            $this->em->remove($entity);
            $this->em->flush();
            $this->flashMessage('Stroj byl z návštěvy servisu odstraněn', 'success');
        } else {
            $this->flashMessage('Nastala chyba při odstraňování stroje z servisu', 'success');
        }
        $this->redrawControl('tableServiceVisit');
    }
}