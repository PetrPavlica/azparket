<?php

namespace App\IntraModule\Presenters;

use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\Worker;
use App\Model\Database\Entity\WorkerTender;
use App\Model\Database\Entity\WorkerInWorkerTender;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Application\UI\Form;

class WorkerTenderPresenter extends BasePresenter
{
    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;
    
    /** @persistent */
    public $openTab;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Školení zaměstnanců - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    
        $this->openTab = $this->getParameter('openTab');
    }

    public function beforeRender()
    {
        parent::beforeRender();
    }

    /**
     * ACL name='Zobrazení default stránky'
     */
    public function renderDefault() {
        $this->template->scriptsForCalendar = true;
        $this->template->openTab = $this->openTab;
        $this->openTab = null;
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getWorkerTenderRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se najít dané školení!', 'warning');
                $this->redirect('WorkerTender:default');
            }
            $this->template->entity = $entity;

            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
            
        }

        // filtering skills
        $qb = $this->em->createQueryBuilder();
        $qb->select('s')
            ->from(\App\Model\Database\Entity\Skill::class, 's', 's.id')
            //->where('s.tenderableIntern = 1 OR s.tenderableExtern = 1 ')
            ->andWhere('s.isTenderable = 1')
            ->orderBy('s.name', 'ASC');
        ;
        $opts = $qb->getQuery()->getResult();
        foreach ($opts as $optKey => $opt) {
            $opts[$optKey] = $opt->name;
        }
        $this['form']->getComponent('skills')->setItems($opts);

        $this->template->openTab = $this->openTab;
    }

    /**
     * ACL name='Tabulka s přehledem školení'
     */
    public function createComponentTableTender()
    {
        $grid = $this->gridGen->generateGridByAnnotation(WorkerTender::class, get_class(), __FUNCTION__, 'default');

        $grid = $this->gridGen->setClicableRows($grid, $this, 'WorkerTender:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $edit = $grid->addAction('edit', '', 'WorkerTender:edit');
        if ($edit)
            $edit->setIcon('edit')
                ->setTitle('Úprava')
                ->setClass('btn btn-link');

        $this->gridGen->addButtonDeleteCallback();
        
        $grid->getColumn('workers')->setRenderer(function ($item) {
            $str = '';
            foreach ($item->workers as $wip) {
                $str .= $wip->worker->name . ' ' . $wip->worker->surname . ', ';
            }
            if (strlen($str) > 2)
                $str = substr($str, 0, -2);
            return $str;
        });

        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem zaměstnanců daného řízení'
     */
    public function createComponentTableWorkers()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Worker::class, get_class(), __FUNCTION__, 'default');

        $qb = $this->em->getWorkerRepository()->createQueryBuilder('w');
        $qb->select('w')
            ->join('w.workerTenders', 'wiwt')
            ->join('wiwt.tender', 'wt')
            ->where('wt.id = :id')
            ->setParameters(['id' => $this->params['id']]);

        $grid->setDataSource($qb);

        $params = ($this->getParameter('id') !== null) ? ['backWTendID' => $this->getParameter('id')] : [];
        $params['openTab'] = '#tenders';

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Worker:edit', 'id', $params);
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Worker:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        if ($this->getParameter('id') !== null) {
            $action = $multiAction->addActionCallback('delete_from_tender', 'Odstranit ze školení', function($itemId) {
                $wit = $this->em->getWorkerInWorkerTenderRepository()->findOneBy(['tender' => $this->getParameter('id'), 'worker' => $itemId]);
                if ($wit) {
                    try {
                        $this->em->remove($wit);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        $this->flashMessage('Zaměstnanece nebylo možné odstranit ze školení!', 'error');
                        $this->redirect('this', ['openTab' => '#workers']);
                        return;
                    }
                    $this->flashMessage('Zaměstnanec byl odebrán ze školení', 'info');     
                } else {
                    $this->flashMessage('Zaměstnance nebylo možné odstranit ze školení!', 'error');
                }
                $this->redirect('this', ['openTab' => '#workers']);
            });
            if ($action) {
                $action->setIcon('times')
                    ->setTitle('Odstranit ze školení')
                    ->setConfirmation(new StringConfirmation('Opravdu chcete odstranit zaměstnance z daného školení?'))
                    ->setClass('text-danger dropdown-item');
            }
        }


        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit řízení'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(WorkerTender::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit školení', 'success'], ['Nepodařilo se uložit školení!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];
        
        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        if (isset($values2['opneTab'])) {
            $this->openTab = $values2['opneTab'];
        }

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendAddWorker'])) { //Přiřadit zaměstnance podle dovedností - všichni kdo mají dovednost
            if ($entity->skills) {
                foreach ($entity->skills as $siwt) {
                    if ($siwt->skill->workers) {
                        foreach ($siwt->skill->workers as $siworker) {
                            $wot = $this->em->getWorkerInWorkerTenderRepository()->findBy(['worker' => $siworker->worker, 'tender' => $entity]);
                            if (!$wot) {
                                $workerInTender = new WorkerInWorkerTender();
                                $workerInTender->setWorker($siworker->worker);
                                $workerInTender->setTender($entity);
                                $this->em->persist($workerInTender);
                                $this->em->flush();
                            }
                        }
                    }
                }
                $this->flashMessage('Zaměstnance se podařilo přiřadit', 'info');
                $this->redirect('WorkerTender:edit', ['id' => $entity->id, 'openTab' => '#workers']);
            }
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('WorkerTender:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('WorkerTender:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('WorkerTender:edit');
        } else {
            $this->redirect('WorkerTender:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro přidání zaměstnanců do školení'
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
            if(isset($values2['tender']) && $values2['tender'] && $values->worker) {
                $entity = $this->em->getWorkerTenderRepository()->find($values2['tender']);
                $worker = $this->em->getWorkerRepository()->find($values->worker);

                if ($entity && $worker) {
                    $wiwt = new WorkerInWorkerTender();
                    $wiwt->setWorker($worker);
                    $wiwt->setTender($entity);
                    $this->em->persist($wiwt);
                    $this->em->flush();
                } else {
                    $err = true;
                }
            } else {
                $err = true;
            }
            if (isset($err)) {
                $that->flashMessage('Došlo k chybě při přiřazování zaměstnance', 'error');
                return;
            }

            $that->template->openTab = '#workers';
            if ($that->isAjax()) {
                $that->redrawControl('tableWorkers');
            } else {
                $that->redirect('WorkerTender:edit',  ['id' => $values2['tender'], 'openTab' => '#workers']);
            }
        };

        return $form;
    }
}
