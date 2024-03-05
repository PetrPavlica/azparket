<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\TaskComment;
use App\Model\Database\Entity\TaskDocument;
use App\Model\Facade\Task;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Form;

class TaskPresenter extends BasePresenter
{
    /** @var Task @inject */
    public $taskFac;

    /**
     * ACL name='Správa úkolů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení úkolů - nástěnka'
     */
    public function renderDefault()
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        $taskStates = [];
        $statesDashboard = $this->em->getTaskStateRepository()->findBy(['active' => 1, 'forDashboard' => 1],['orderType' => 'ASC']);

        foreach ($statesDashboard as $state) {
            $taskStates[$state->id]['state'] = $state;
            $query = $this->em->createQueryBuilder()
                ->select('t')
                ->from(\App\Model\Database\Entity\Task::class, 't', 't.id')
                ->where('t.taskState = '.$state->id);
            if (!in_array($this->usrGrp, [1])) {
                $query->andWhere('t.assigned = ' . $this->user->getId());
            }
            $query->orderBy('t.priority', 'DESC');
            $query = $query->getQuery();
            $taskStates[$state->id]['tasks'] = $query->getResult();
        }
        $this->template->taskStates = $taskStates;
        $this->template->statesDashboard = $statesDashboard; //zobrazované stavy ukolů
    }

    public function renderEdit($id)
    {
        $taskStatesItem = [];
        $states = $this->em->getTaskStateRepository()->findBy(['active' => 1], ["orderType"=>"ASC"]);
        foreach ($states as $state) {
            $taskStatesItem[$state->id] = $state->name;
        }
        $this['form']->getComponent('taskState')->items = $taskStatesItem;

        $assignedItems = [];
        $assigneds = $this->em->getUserRepository()->findBy(['isBlocked' => 0]);

        foreach ($assigneds as $user) {
            $assignedItems[$user->id] = $user->name;
        }
        $this[ 'form' ]->getComponent('assigned')->items = $assignedItems;

        if ($id) {
            $entity = $this->em->getTaskRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('TaskState:');
            }
            if (!in_array($this->usrGrp, [1])) {
                if ($entity->originator->id != $this->user->getId()) {
                    $this->redirect('Task:default');
                }
                /*if ($entity->assigned->id != $this->user->getId()) {
                    $this->redirect('Task:default');
                }*/
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);

            if ($entity->taskState) {
                $arr['state'] = $entity->taskState->id;
                $taskStatesItem[$entity->taskState->id] = $entity->taskState->name;
            }
            $this[ 'form' ]->getComponent('taskState')->items = $taskStatesItem;

            if ($entity->assigned) {
                $arr['assigned'] = $entity->assigned->id;
                $assignedItems[$entity->assigned->id] = $entity->assigned->name;
            }
            $this[ 'form' ]->getComponent('assigned')->items = $assignedItems;

            $this['form']->setDefaults($arr);
        } else {
            $this['form']->setDefaults(['originator' => $this->user->getId()]);
        }
    }

    /**
     * ACL name='Zobrazení úkolů - náhled'
     */
    public function renderView($id) {
        if (!$id) {
            $this->redirect('Task:default');
        }
        $entity = $this->em->getTaskRepository()->find($id);
        if (!$entity) {
            $this->redirect('Task:default');
        }
        $this->template->entity = $entity;
        $this->template->taskDocs = $this->em->getTaskDocumentRepository()->findBy(['task' => $entity]);
        $this->template->taskComm = $this->em->getTaskCommentRepository()->findBy(['task' => $entity]);
        $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
    }

    /**
     * ACL name='Zobrazení úkolů - tabulka'
     */
    public function renderTable() {
        $query = $this->em->createQueryBuilder()
            ->select('t')
            ->from(\App\Model\Database\Entity\Task::class, 't', 't.id')
            ->join(\App\Model\Database\Entity\TaskState::class, 'ts', 'WITH', 't.taskState = ts.id')
            ->leftJoin(\App\Model\Database\Entity\User::class, 'uass', 'WITH', 't.assigned = uass.id')
            ->andWhere('ts.active = 1');
        if (!in_array($this->usrGrp, [1])) {
            $query->andWhere('t.assigned = ' . $this->user->getId());;
        }
        $query->orderBy('t.priority', 'DESC');

        $this['table']->setDataSource($query);
        $this['table']->setDefaultSort(['foundedDate' => 'DESC']);
    }

    /**
     * ACL name='Tabulka s přehledem všech úkolů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Task::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Task:view');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $groupId = $this->usrGrp;
        $usrId = $this->user->getId();
        $multiAction = $grid->getAction('multiAction');

        $multiAction->addAction('view', 'Náhled', 'Task:view');
        $action = $multiAction->getAction('view');
        if ($action)
            $action->setIcon('eye')
                ->setTitle('Náhled')
                ->setClass('dropdown-item datagrid-multiaction-dropdown-item text-primary');

        $multiAction->addAction('edit', 'Upravit', 'Task:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $grid->allowRowsMultiAction('multiAction', 'edit', function($item) use ($groupId, $usrId) {
            if (in_array($groupId, [1])) {
                return true;
            } else {
                if ($item->originator && $usrId == $item->originator->id) {
                    return true;
                } else {
                    return false;
                }
            }
        });

        $this->gridGen->addButtonDeleteCallback();
        $grid->allowRowsMultiAction('multiAction', 'delete', function($item) use ($groupId) {
            if (in_array($groupId, [1])) {
                return true;
            } else {
                return false;
            }
        });
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit stavu úkolu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Task::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit úkol', 'success'], ['Nepodařilo se uložit úkol!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'taskFormSuccess'];
        return $form;
    }

    public function taskFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        if(!$values->taskState) {
            $this->flashMessage('Prosím vyberte stav!', 'warning');
            return;
        }

        if(!$values->closeToDate) {
            $this->flashMessage('Prosím vyplňte Uzavřít do!', 'warning');
            return;
        }
        $date = new \DateTime();
        $oldTask = null;
        $new = false;
        if (!$values->id) {
            $values->foundedDate = $date->format('d. m. Y');
            $new = true;
        } else {
            $oldTask = $this->em->getTaskRepository()->find($values->id);
            $oldTask = $this->ed->get($oldTask);
            $values->foundedDate = $oldTask['foundedDate'];
        }
        $values->lastEdited = $this->user->getId();
        $values->lastEditedDate = $date->format('d. m. Y');

        $task = $this->formGenerator->processForm($form, $values, true);

        if (!$task) {
            return;
        }

        if ($oldTask && $oldTask['taskState'] != $values->taskState->id) {
            $oldState = $this->em->getTaskStateRepository()->find($oldTask['taskState']);
            $newState = $task->taskState;
            $task->setInStateDate(new \DateTime());
            $this->em->flush($task);
            $this->taskFac->logTaskText($task, $this->user->getId(),
                'Změna stavu úkolu.', $oldState->name, $newState->name);
        } elseif (is_null($oldTask)) {
            $newState = $task->taskState;
            $this->taskFac->logTaskText($task, $this->user->getId(),
                'Založen nový úkol.', '', $newState->name);
        }

        //log změn
        if ($oldTask) {
            if ($oldTask['assigned'] != $values->assigned->id) {
                $old = $this->em->getUserRepository()->find($oldTask['assigned']);
                $this->taskFac->logTaskText($task, $this->user->getId(),
                    'Změna přiřazení úkolu.', $old->name, $values->assigned->name);
            }
            if ($oldTask['name'] != $values->name) {
                $this->taskFac->logTaskText($task, $this->user->getId(),
                    'Změna názvu úkolu.', $oldTask['name'], $values->name);
            }
            if ($oldTask['description'] != $values->description) {
                $this->taskFac->logTaskText($task, $this->user->getId(),
                    'Změna popisu úkolu.', $oldTask['description'], $values->description);
            }
            /*if ($values->foundedDate) {
                $foundedDate = date_create_from_format('j. n. Y', $values->foundedDate);
                if ($oldTask['foundedDate'] != $foundedDate->format('j. n. Y')) {
                    $this->taskFac->logTaskText($task, $this->user->getId(),
                        'Změna datumu založení úkolu.', $oldTask['foundedDate'], $foundedDate->format('d. m. Y'));
                }
            }*/
            $closeToDate = date_create_from_format('j. n. Y', $values->closeToDate);
            if ($oldTask['closeToDate'] != $closeToDate->format('j. n. Y')) {
                $this->taskFac->logTaskText($task, $this->user->getId(),
                    'Změna datumu ukončení úkolu.', $oldTask['closeToDate'], $closeToDate->format('j. n. Y'));
            }
            if ($oldTask['priority'] != $values->priority) {
                $this->taskFac->logTaskText($task, $this->user->getId(),
                    'Změna priority úkolu.', ($oldTask['priority'] == 1 ? 'ANO' : 'NE'), ($values->priority == 1 ? 'ANO' : 'NE'));
            }
        }

        if ($new) {
            //$this->mailSender->sendCreationTask($task->id); //TODO: odeslat mail o založení úkolu - povolit
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Task:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Task:edit', ['id' => $task->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Task:edit');
        } else {
            $this->redirect('Task:edit', ['id' => $task->id]);
        }
    }

    public function handleChangeTaskState($id, $state) {
        $res = $this->taskFac->swapStateFast($id, $state, $this->user->getId());
        if ($res) {
            $this->flashMessage('Stav byl změněn.', 'success');
        } else {
            $this->flashMessage('Stav se nepovedlo změnit!', 'success');
        }
        if($this->isAjax()){
            $this->redrawControl("dcards");
        }else {
            $this->redirect('this');
        }
    }

    public function handleChangeTaskStateDraggable() {
        $values = $this->request->getPost();
        if (isset($values['id']) && $values['id'] && isset($values['state']) && $values['state']) {
            $this->handleChangeTaskState($values['id'], $values['state']);
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit dokumentu úkolu'
     */
    public function createComponentDocModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(TaskDocument::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dokument', 'success'], ['Nepodařilo se uložit dokument!', 'error']);
        $form->isRedirect = false;

        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $that->em->getUserRepository()->find($that->getUser()->getId());
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setTask($that->em->getTaskRepository()->find($values2['task']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('docsTable');
            } else {
                $that->redirect('Task:view',  ['id' => $values2['task'], 'openTab' => '#docs']);
            }
        };

        return $form;
    }

    public function handleCheckTaskDoc() {
        $values = $this->request->getPost();
        if($values['doc']) {
            $entity = $this->em->getTaskDocumentRepository()->find($values['doc']);
            $arr = $this->ed->get($entity);
            $this['docModalForm']->setDefaults($arr);
            $this->template->modalDoc = $entity;
        }
        $this->redrawControl('docModal');
    }

    public function handleRemoveTaskDoc() {
        $values = $this->request->getPost();
        $entity = $this->em->getTaskDocumentRepository()->find($values['doc']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('docsTable');
    }

    /**
     * ACL name='Formulář pro přidání/edit komentáře úkolu'
     */
    public function createComponentCommModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(TaskComment::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit komentář', 'success'], ['Nepodařilo se uložit komentář!', 'error']);
        $form->isRedirect = false;

        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $that->em->getUserRepository()->find($that->getUser()->getId());
            }

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setTask($that->em->getTaskRepository()->find($values2['task']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('commTable');
            } else {
                $that->redirect('Task:view',  ['id' => $values2['task'], 'openTab' => '#comm']);
            }
        };

        return $form;
    }

    public function handleCheckTaskComm() {
        $values = $this->request->getPost();
        if($values['comm']) {
            $entity = $this->em->getTaskCommentRepository()->find($values['comm']);
            $arr = $this->ed->get($entity);
            $this['commModalForm']->setDefaults($arr);
            $this->template->modalDoc = $entity;
        }
        $this->redrawControl('commModal');
    }

}
