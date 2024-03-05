<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Worker;
use App\Model\Database\Entity\WorkerPosition;
use App\Model\Database\Entity\PermissionItem;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Application\UI\Form;

class WorkerPositionPresenter extends BasePresenter
{
    /**
     * ACL name='Správa pozic zaměstnanců'
     * ACL rejection='Nemáte přístup ke správě pozic zaměstnanců.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním pozice zaměstnanců'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getWorkerPositionRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Pracovní pozice nebyla nalezena.', 'error');
                $this->redirect('WorkerPosition:');
            }
            $arr = $this->ed->get($entity);

            // znemožnění výběru aktuálně upravované pozice do nadřazených
            $opts = $this['form']->getComponent('superiorPositions')->getItems();
            if (isset($opts[$id]))
                unset($opts[$id]);
            $this['form']->getComponent('superiorPositions')->setItems($opts);
            $this['form']->getComponent('subordinatePositions')->setItems($opts);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
            
            $this->template->openTab = ($this->getParameter('openTab') !== null) ? $this->getParameter('openTab') : null;
        } else {
            $this->template->openTab = null;
        }
    }

    /**
     * ACL name='Tabulka s přehledem pozic zaměstnanců'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(WorkerPosition::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'WorkerPosition:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        // render jména
        $grid->getColumn('workers')->setRenderer(function ($item) {
            $str = '';
            foreach ($item->workers as $wp) {
                $str .= $wp->name . ' ' . $wp->surname . ', ';
            }
            $str = substr($str, 0, -2);
            return $str;
        });

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'WorkerPosition:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem zaměstnanců dané pozice'
     */
    public function createComponentTableWorkers()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Worker::class, get_class(), __FUNCTION__, 'default');

        if ($this->getParameter('id') !== null) {
            $qb = $this->em->getWorkerRepository()->createQueryBuilder('w');
            $qb->select('w')
                ->join('w.workerPosition', 'wpos')
                ->where('wpos.id = :id');
            $qb->setParameters(['id' => $this->getParameter('id')]);
            $grid->setDataSource($qb);
        } else {
            $grid->setDataSource([]);
            return $grid;
        }

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Worker:edit', 'id', (($this->getParameter('id') !== null) ? ['backWPosID' => $this->getParameter('id')] : 0));

        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Worker:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');


                //
                //
                // FIX
                //
        $action = $multiAction->addActionCallback('delete_from_position', 'Odstranit z pozice', function($itemId) {
            $worker = $this->em->getWorkerRepository()->find($itemId);
            if ($worker && $worker->workerPosition->id == $this->getParameter('id')) {
                try {
                    $worker->setWorkerPosition(null);
                    $this->em->persist($worker);
                    $this->em->flush();
                } catch (\Exception $e) {
                    $this->flashMessage('Zaměstnance nebylo možné z pozice odstranit!', 'error');
                    $this->redirect('this', ['openTab' => '#workers']);
                    return;
                }
                $this->flashMessage('Pozice zaměstnance byla odebrána', 'info');     
            } else {
                $this->flashMessage('Zaměstnance nebylo možné z pozice odstranit!', 'error');
            }
            $this->redirect('this', ['openTab' => '#workers']);
        });
        if ($action) {
            $action->setIcon('times')
                ->setTitle('Odstranit z pozice')
                ->setConfirmation(new StringConfirmation('Opravdu chcete odstranit zaměstnance z dané pozice?'))
                ->setClass('text-danger dropdown-item');
        }


        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit pozice zaměstnance'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(WorkerPosition::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit pracovní pozici', 'success'], ['Nepodařilo se uložit pracovní pozici!', 'error']);
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
            $this->redirect('WorkerPosition:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('WorkerPosition:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('WorkerPosition:edit');
        } else {
            $this->redirect('WorkerPosition:edit', ['id' => $entity->id]);
        }
    }

    
    /**
     * ACL name='Formulář pro přidání zaměstnanců do pozice'
     */
    public function createComponentWorkerModalForm()
    {
        $that = $this;

        $workers = $this->em->getWorkerRepository()->findBy(['active' => 1]);
        $workersArr = [];
        foreach ($workers as $w) {
            $workersArr[$w->id] = $w->name . ' ' . $w->surname . ' (' . $w->personalId . ')';
        }

        $form = new Form();
        $form->addSelect('worker', '', $workersArr)
            ->setPrompt('Zvolte zaměstnance')
            ->setHtmlAttribute('class', 'fomr-control selectpicker')
            ->setHtmlAttribute('data-live-search', 'true');

        $form->onSuccess[] = function(Form $form, $values) use($that): void {

            $values2 = $this->request->getPost();
            if(isset($values2['position']) && $values2['position'] && $values->worker) {
                $entity = $this->em->getWorkerPositionRepository()->find($values2['position']);
                $worker = $this->em->getWorkerRepository()->find($values->worker);

                if ($entity && $worker) {
                    if ($worker->workerPosition) {
                        $that->flashMessage('Tento zaměstnanec již je v pracovní pozici!', 'error');
                        $that->redirect('WorkerPosition:edit',  ['id' => $values2['position'], 'openTab' => '#workers']);
                    } else {
                        $worker->setWorkerPosition($entity);
                        $this->em->persist($entity);
                        $this->em->flush();
                    }
                } else {
                    $err = true;
                }
            } else {
                $err = true;
            }
            if (isset($err)) {
                $that->flashMessage('Došlo k chybě při přiřazování zaměstnance', 'error');
            }

            $that->template->openTab = '#workers';
            if ($that->isAjax()) {
                $that->redrawControl('tableWorkers');
            } else {
                $that->redirect('WorkerPosition:edit',  ['id' => $values2['position'], 'openTab' => '#workers']);
            }
        };

        return $form;
    }
}