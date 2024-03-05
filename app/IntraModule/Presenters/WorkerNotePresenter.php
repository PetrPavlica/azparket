<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\WorkerNote;
use App\Model\Database\Entity\PermissionItem;

class WorkerNotePresenter extends BasePresenter
{
    /**
     * Worker's ID to return to
     * @persistent
     */
    public $backWID;

    /**
     * ACL name='Správa poznámek zaměstnanců'
     * ACL rejection='Nemáte přístup ke správě poznámek zaměstnanců.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním poznámek zaměstnanců'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getWorkerNoteRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Poznámka nebyla nalezena.', 'error');
                $this->redirect('WorkerNote:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

        } else if ($this->getParameter('backWID') !== null) {
            $this['form']->setDefaults(['worker' => $this->getParameter('backWID')]);
        }

        if ($this->getParameter('backWID') !== null) {
            $this->template->backWID = $this->getParameter('backWID', '');
        }
    }

    /**
     * ACL name='Tabulka s přehledem poznámek'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(WorkerNote::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'WorkerNote:edit');

        // render jména
        $grid->getColumn('worker')->setRenderer(function ($item) {
            return $item->worker->name . ' ' . $item->worker->surname;
        });
        
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'WorkerNote:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit poznámka zaměstnance'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(WorkerNote::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit poznámku', 'success'], ['Nepodařilo se uložit poznámku!', 'error']);
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
            if ($this->getParameter('backWID') !== null) {
                $this->redirect('Worker:edit', ['id' => $this->getParameter('backWID'), 'openTab' => '#notes']);
            } else {
                $this->redirect('WorkerNote:default');
            }
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('WorkerNote:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('WorkerNote:edit');
        } else {
            $this->redirect('WorkerNote:edit', ['id' => $entity->id]);
        }
    }
}