<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class AbsencePresenter extends BasePresenter
{
    /**
     * ACL name='Správa absencí'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderDefault($slug = null) {
        $qb = $this[ 'table' ]->getDataSource();
        if($slug) {
            $this->template->absenceState = $absenceState = $this->em->getAbsenceStateRepository()->find($slug);
            if (!$absenceState) {
                $this->redirect('Absence:');
            }
            $qb->filterOne(['state' => $slug]);

            $this['table']->removeFilter('state');
            $this['table']->removeColumn('state');

            /* všem kromě role admin zobrazit podle nastavení pro všechny */
            if (!in_array($this->usrGrp, [1])) {
                if ($absenceState && !$absenceState->forAll) {
                    $qb->filterOne(['user' => $this->user->getId()]);
                }
            }
        } else {
            /* bez stavu pro techniky všechny schválené absence */
            if (!in_array($this->usrGrp, [1])) {
                $qb->filterOne(['state' => '4']);
            }
        }
        $this[ 'table' ]->setDataSource($qb);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getAbsenceRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Absence:');
            }
            /* role kromě admina nesmějí editovat cizí absence */
            if (!in_array($this->usrGrp, [1])) {
                if ($entity->user && $entity->state && $entity->state->allowEditTech) {
                    if ($this->user->getId() != $entity->user->id) {
                        $this->redirect('Absence:default', ['slug' => 1]);
                    }
                } else {
                    $this->redirect('Absence:default', ['slug' => 1]);
                }
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);

            if ($entity->dateStart && $entity->user) {
                $dStart = $entity->dateStart;
                if (!$entity->dateEnd) {
                    $dEnd = $entity->dateStart;
                } else {
                    $dEnd = $entity->dateEnd;
                }
                $services = [];
                if ($entity->user->group->id == 2 && isset($entity->user->workersUsr[0]) && $entity->user->workersUsr[0]) {
                    $services = $this->db->query('SELECT * FROM service s WHERE s.worker_id = ? AND s.date_service BETWEEN ? AND ?', $entity->user->workersUsr[0]->id, $dStart->format('Y-m-d') . ' 00:00:00', $dEnd->format('Y-m-d') . ' 23:59:59')->fetchAll();
                }
                if (count($services)) {
                    $this->template->needDelegateService = true;
                }
            }
        } else {
            //když je nový tak předvyplním z přihlášeného uživatele
            $user = $this->em->getUserRepository()->find($this->user->getId());
            $this[ 'form' ]->setDefaults(['user' => $user->id]);
        }
    }

    /**
     * ACL name='Náhled absence'
     */
    public function renderView($id, $back) {
        if (!$id) {
            $this->redirect('Absence:default');
        }
        $absence = $this->em->getAbsenceRepository()->find($id);
        if (!$absence) {
            $this->redirect('Absence:default');
        }
        $this->template->absence = $absence;
    }

    /**
     * ACL name='Tabulka s přehledem absencí'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Absence::class, get_class(), __FUNCTION__);
        //$grid = $this->gridGen->setClicableRows($grid, $this, 'Absence:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $groupId = $this->usrGrp;
        $usrId = $this->user->getId();
        $multiAction = $grid->getAction('multiAction');

        /* view button */
        $multiAction->addAction('view', 'Náhled', 'Absence:view');
        $action = $multiAction->getAction('view');
        if ($action) {
            $action->setIcon('eye')
                ->setTitle('Náhled')
                ->setClass('dropdown-item datagrid-multiaction-dropdown-item text-primary');
        }

        /* edit button */
        $multiAction->addAction('edit', 'Upravit', 'Absence:edit');
        $action = $multiAction->getAction('edit');
        if ($action) {
            $action->setIcon('edit')
                ->setTitle('Úprava');
        }
        $grid->allowRowsMultiAction('multiAction', 'edit', function($item) use ($groupId, $usrId) {
            if (in_array($groupId, [1])) {
                return true;
            } else {
                if ($item->user && $usrId == $item->user->id) {
                    if ($item->state) {
                        return $item->state->allowEditTech;
                    } else {
                        return false;
                    }
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

        /* allow clickable */
        foreach ($grid->getColumns() as $col) {
            if (get_class($col) == 'Ublaboo\DataGrid\Column\ColumnLink' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnStatus' || get_class($col) == 'Ublaboo\DataGrid\Column\ColumnCallback') {
                continue;
            }
            // Add class clikable for enable click to column.
            if ($col != null && strpos($col->getTemplate(), 'column_file.latte') === false) {
                $col = $col->addCellAttributes(['class' => 'clickable']);
            }
        }
        $grid->setRowCallback(function ($item, $tr) use ($groupId, $usrId){
            if (in_array($groupId, [1])) {
                $target = 'Absence:edit';
            } else {
                if ($item->user && $usrId == $item->user->id) {
                    if ($item->state && $item->state->allowEditTech) {
                        $target = 'Absence:edit';
                    } else {
                        $target = 'Absence:view';
                    }
                } else {
                    $target = 'Absence:view';
                }
            }
            $tr->addAttribute('data-click-to', $this->link($target, ['id' => $item->id]));
        });

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit absence'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Absence::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit absenci', 'success'], ['Nepodařilo se uložit absenci!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'successAbsenceForm'];
        return $form;
    }

    public function successAbsenceForm($form, $values)
    {
        $values2 = $this->request->getPost();

        if (!$values2['dateStart']) {
            $this->flashMessage('Vyplně Datum začátku!', 'warning');
            return;
        }
        if ($this->usrGrp == 2) {
            if (isset($values2['needDelegateService']) && !$values2['userDelegate']) {
                $this->flashMessage('Vyplně zástup!', 'warning');
                return;
            }
            if ($values2['user'] == $values2['userDelegate']) {
                $this->flashMessage('Zástup nelze vyplnit na sebe!', 'warning');
                return;
            }
        }
        if (!$values->id) {
            $values->foundedDate = new \DateTime();
            $values->createdUser = $this->em->getUserRepository()->find($this->user->id);
            $values->state = $this->em->getAbsenceStateRepository()->find(1);
        }
        $absence = $this->formGenerator->processForm($form, $values, true);

        if (!$absence) {
            return;
        }

        if (isset($values2['sendToConfirm'])) {
            /* absence je podaná */
            $absence->setState($this->em->getAbsenceStateRepository()->find(2));
            $this->em->flush($absence);
            $res = $this->mailSender->sendAbsence($absence, false, 'Podání');
            if ($res) {
                $this->flashMessage('Odeslání proběhlo v pořádku.', 'success');
            } else {
                $this->flashMessage('Při odeslání emailu nastala chyba!', 'warning');
                $absence->setState($this->em->getAbsenceStateRepository()->find(1));
                $this->em->flush($absence);
                $this->redirect('this');
            }
            $this->redirect('Absence:default', ['slug' => 2]);
        } elseif (isset($values2['sendApprove'])) {
            /* absence je schválená */
            $absence->setState($this->em->getAbsenceStateRepository()->find(4));
            $this->em->flush($absence);
            $res = $this->mailSender->sendAbsence($absence, true, 'Schválení');
            if ($res) {
                $this->flashMessage('Odeslání proběhlo v pořádku.', 'success');
            } else {
                $this->flashMessage('Při odeslání emailu nastala chyba!', 'warning');
                $absence->setState($this->em->getAbsenceStateRepository()->find(2));
                $this->em->flush($absence);
                $this->redirect('this');
            }

            //odeslání info mailu
            $res = $this->mailSender->sendAbsenceSuccessInfo($absence);
            if ($res) {
                $this->flashMessage('Odeslání informačního emailu proběhlo v pořádku.', 'success');
            } else {
                $this->flashMessage('Při odeslání informačního emailu nastala chyba!', 'warning');
            }

            $this->redirect('Absence:default', ['slug' => 2]);
        } elseif (isset($values2[ 'sendReject' ])) {
            /* absence je zamítnutá */
            $absence->setState($this->em->getAbsenceStateRepository()->find(3));
            $this->em->flush($absence);
            $res = $this->mailSender->sendAbsence($absence, true, 'Zamítnutí');
            if ($res) {
                $this->flashMessage('Odeslání proběhlo v pořádku.', 'success');
            } else {
                $this->flashMessage('Při odeslání emailu nastala chyba!', 'warning');
                $absence->setState($this->em->getAbsenceStateRepository()->find(2));
                $this->em->flush($absence);
                $this->redirect('this');
            }
            $this->redirect('Absence:default', ['slug' => 2]);
        } elseif (isset($values2[ 'sendBack' ])) {
            $this->redirect('Absence:default');
        } elseif (isset($values2[ 'sendNew' ])) {
            $this->redirect('Absence:edit');
        } else {
            $this->redirect('Absence:edit', $absence->id);
        }
    }

    public function handleCheckServiceOnAbsence($dateStart, $dateEnd) {
        $dStart = null;
        $dEnd = null;
        if ($dateStart) {
            $pom = explode('.', $dateStart);
            if (isset($pom[0]) && $pom[0] && isset($pom[1]) && $pom[1] && isset($pom[2]) && $pom[2]) {
                $dStart = new \DateTime(trim($pom[2]).'-'.trim($pom[1]).'-'.trim($pom[0]));
            }
        }
        if ($dateEnd) {
            $dateEnd = trim($dateEnd);
            $pom = explode('.', $dateEnd);
            if (isset($pom[0]) && $pom[0] && isset($pom[1]) && $pom[1] && isset($pom[2]) && $pom[2]) {
                $dEnd = new \DateTime(trim($pom[2]).'-'.trim($pom[1]).'-'.trim($pom[0]));
            }
        }
        $worker = $this->em->getWorkerRepository()->findOneBy(['user' => $this->user->getId()]);
        if ($dStart && $worker) {
            if (!$dEnd) {
                $dEnd = $dStart;
            }
            $services = $this->db->query('SELECT * FROM service s WHERE s.worker_id = ? AND s.date_service BETWEEN ? AND ?', $worker->id ,$dStart->format('Y-m-d').' 00:00:00', $dEnd->format('Y-m-d').' 23:59:59')->fetchAll();
            if (count($services)) {
                $this->template->needDelegateService = true;
            }
        }
        $this->redrawControl('absenceService');
        $this->redrawControl('absenceForm');
    }

}
