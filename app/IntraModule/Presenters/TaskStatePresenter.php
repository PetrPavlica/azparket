<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class TaskStatePresenter extends BasePresenter
{
    /**
     * ACL name='Správa stavů úkolů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getTaskStateRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('TaskState:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem stavů úkolů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\TaskState::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'TaskState:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'TaskState:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit stavu úkolu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\TaskState::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit stav úkolu', 'success'], ['Nepodařilo se uložit stav úkolu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'taskStateFormSuccess'];
        return $form;
    }

    public function taskStateFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('TaskState:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('TaskState:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('TaskState:edit');
        } else {
            $this->redirect('TaskState:edit', ['id' => $entity->id]);
        }
    }

}
