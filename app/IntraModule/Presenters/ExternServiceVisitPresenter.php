<?php

namespace App\IntraModule\Presenters;

use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\Machine;
use App\Model\Database\Entity\ExternServiceVisit;
use App\Model\Database\Entity\MachineInExternServiceVisit;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Application\UI\Form;

class ExternServiceVisitPresenter extends BasePresenter
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
     * ACL name='Externí servis strojů - sekce'
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
     * ACL name='Zobrazení návštěv servisu strojů'
     */
    public function renderDefault() {
        $this->template->scriptsForCalendar = true;
        $this->template->openTab = $this->openTab;
        $this->openTab = null;
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getExternServiceVisitRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se najít danou návštěvu servisu!', 'warning');
                $this->redirect('ExternServiceVisit:default');
            }
            $this->template->entity = $entity;

            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
            
        }

        $this->template->openTab = $this->openTab;
    }

    /**
     * ACL name='Tabulka s přehledem náštěv servisu'
     */
    public function createComponentTableServiceVisit()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ExternServiceVisit::class, get_class(), __FUNCTION__, 'default');

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ExternServiceVisit:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $edit = $grid->addAction('edit', '', 'ExternServiceVisit:edit');
        if ($edit)
            $edit->setIcon('edit')
                ->setTitle('Úprava')
                ->setClass('btn btn-link');

        $this->gridGen->addButtonDeleteCallback();
        
        $grid->getColumn('machines')->setRenderer(function ($item) {
            $str = '';
            foreach ($item->machines as $mis) {
                $str .= $mis->machine->name . ', ';
            }
            if (strlen($str) > 2)
                $str = substr($str, 0, -2);
            return $str;
        });

        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem strojů v návštěvě servisu'
     */
    public function createComponentTableMachines()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Machine::class, get_class(), __FUNCTION__, 'default');

        $qb = $this->em->getMachineRepository()->createQueryBuilder('m')
            ->select('m')
            ->join('m.externServiceVisits', 'mis')
            ->join('mis.externServiceVisit', 's')
            ->where('s.id = :id')
            ->setParameters(['id' => $this->params['id']]);

        $grid->setDataSource($qb);

        $params = ($this->getParameter('id') !== null) ? ['backExSrvVsID' => $this->getParameter('id')] : [];
        $params['openTab'] = '#serviceVisits';

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Machine:edit', 'id', $params);
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Machine:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        if ($this->getParameter('id') !== null) {
            $action = $multiAction->addActionCallback('delete_from_service', 'Odstranit ze servisu', function($itemId) {
                $mis = $this->em->getMachineInExternServiceVisitRepository()->findOneBy(['externServiceVisit' => $this->getParameter('id'), 'machine' => $itemId]);
                if ($mis) {
                    try {
                        $this->em->remove($mis);
                        $this->em->flush();
                    } catch (\Exception $e) {
                        $this->flashMessage('Stroj nebylo možné odstranit ze servisu!', 'error');
                        $this->redirect('this', ['openTab' => '#machines']);
                        return;
                    }
                    $this->flashMessage('Stroj byl odebrán ze servisu', 'info');     
                } else {
                    $this->flashMessage('Stroj nebylo možné odstranit ze servisu!', 'error');
                }
                $this->redirect('this', ['openTab' => '#machines']);
            });
            if ($action) {
                $action->setIcon('times')
                    ->setTitle('Odstranit ze servisu')
                    ->setConfirmation(new StringConfirmation('Opravdu chcete odstranit stroj z daného servisu?'))
                    ->setClass('text-danger dropdown-item');
            }
        }


        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit návštěvy servisu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ExternServiceVisit::class, $this->user, $this, __FUNCTION__);
        $form->getComponent('repeatPeriod')->addRule($form::MIN, 'Číslo musí být kladné či nula', 0);
        $form->setMessages(['Podařilo se uložit návštěvu servisu', 'success'], ['Nepodařilo se uložit návštěvu servisu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];
        
        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        if (isset($values2['openTab'])) {
            $this->openTab = $values2['openTab'];
        }

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendAddMachine'])) {
            $this->redirect('ExternServiceVisit:edit', ['id' => $entity->id, 'openTab' => '#machines']);
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('ExternServiceVisit:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('ExternServiceVisit:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('ExternServiceVisit:edit');
        } else {
            $this->redirect('ExternServiceVisit:edit', ['id' => $entity->id]);
        }
    }

    /**
     * ACL name='Formulář pro přidání stroje do servisu'
     */
    public function createComponentMachineModalForm()
    {
        $that = $this;

        $sqb = $this->em->getMachineInExternServiceVisitRepository()->createQueryBuilder('mis');
        $sqb->select('sm.id')
            ->join('mis.machine', 'sm')
            ->where('mis.externServiceVisit = :service');

        $qb = $this->em->getMachineRepository()->createQueryBuilder('m');
        $qb->where('m.active = 1')
            ->andWhere(
                $qb->expr()->notIn(
                    'm.id',
                    $sqb->getDQL()
                ))
            ->setParameter('service', $this->params['id']);
        $machines = $qb->getQuery()->getResult();
        $machinesArr = [];
        foreach ($machines as $m) {
            $machinesArr[$m->id] = $m->name . ($m->regId ? ' (' . $m->regId . ')' : '');
        }

        $form = new Form();
        $form->addSelect('machine', '', $machinesArr)
            ->setPrompt('Zvolte stroj')
            ->setHtmlAttribute('class', 'fomr-control selectpicker')
            ->setHtmlAttribute('data-live-search', 'true');

        $form->onSuccess[] = function(Form $form, $values) use($that): void {

            $values2 = $this->request->getPost();
            if(isset($values2['serviceVisit']) && $values2['serviceVisit'] && $values->machine) {
                $entity = $this->em->getExternServiceVisitRepository()->find($values2['serviceVisit']);
                $machine = $this->em->getMachineRepository()->find($values->machine);

                if ($entity && $machine) {
                    $mis = new MachineInExternServiceVisit();
                    $mis->setMachine($machine);
                    $mis->setExternServiceVisit($entity);
                    $this->em->persist($mis);
                    $this->em->flush();
                } else {
                    $err = true;
                }
            } else {
                $err = true;
            }
            if (isset($err)) {
                $that->flashMessage('Došlo k chybě při přiřazování stroje', 'error');
                return;
            }

            $that->template->openTab = '#machines';
            if ($that->isAjax()) {
                $that->redrawControl('tableMachines');
            } else {
                $that->redirect('ExternServiceVisit:edit',  ['id' => $values2['serviceVisit'], 'openTab' => '#machines']);
            }
        };

        return $form;
    }
}
