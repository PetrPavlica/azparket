<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ShiftBonus;
use App\Model\Database\Entity\ShiftBonusGroup;
use App\Model\Database\Entity\ShiftBonusTemplate;
use App\Model\Database\Entity\Worker;
use App\Model\Database\Entity\WorkerNote;
use App\Model\Database\Entity\SkillInWorker;
use Nette\Application\UI\Form;

class ShiftBonusGroupPresenter extends BasePresenter
{
    /**
     * ACL name='Správa šablon směn'
     * ACL rejection='Nemáte přístup ke šablon směn.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového šablon směn'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getShiftBonusGroupRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Šablona nebyla nalezena.', 'error');
                $this->redirect('ShiftBonusGroup:');
            } // :o:  close();
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

            $shiftBonuses = $this->em->getShiftBonusTemplateRepository()->findBy(['shiftBonusGroup' => $entity]);
            $this->template->shiftBonuses = $shiftBonuses;
        }

        $this->template->shiftTrans = ['A'=>'A', 'B'=>'B', 'C'=>'C', 'D'=>'D'];
        $this->template->dayTrans = [1=>'Pondělí', 2=>'Úterý', 3=>'Středa', 4=>'Čtvrtek', 5=>'Pátek', 6=>'Sobota', 7=>'Neděle'];
        $this->template->nameTrans = [1=>'Ranní', 2=>'Noční'];
        $this->template->lineTrans = [1=>'KTL', 2=>'ZN'];
    }

    /**
     * ACL name='Tabulka s přehledem šablon směn'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ShiftBonusGroup::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ShiftBonusGroup:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'ShiftBonusGroup:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit šablon směn'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ShiftBonusGroup::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit šablonu', 'success'], ['Nepodařilo se uložit šablonu!', 'error']);
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
            $this->redirect('ShiftBonusGroup:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('ShiftBonusGroup:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('ShiftBonusGroup:edit');
        } else {
            $this->redirect('ShiftBonusGroup:edit', ['id' => $entity->id]);
        }
    }

    public function createComponentPlanModalForm()
    {
        $that = $this;

        $form = new Form();
        $form->onSuccess[] = function(Form $form, $values) use($that): void {

            $values2 = $this->request->getPost();
            if(isset($values2['bonus']) && $values2['bonus']) {
                $entity = $this->em->getShiftBonusTemplateRepository()->find($values2['bonus']);
            } else {
                $entity = new ShiftBonusTemplate();
            }

            if(isset($values2['shiftBonusGroup']) && $values2['shiftBonusGroup']) {
                $shiftBonusGroup = $this->em->getShiftBonusGroupRepository()->find($values2['shiftBonusGroup']);

                $entity->setShiftBonusGroup($shiftBonusGroup);
                $entity->setShift($values2['shift']);
                $entity->setName($values2['name']);
                $entity->setProductionLine($values2['line']);
                $entity->setDayOfWeek($values2['day']);
                $entity->setDateEnd(NULL);
                if($values2['dateEnd']) {
                    $testDate = date_create_from_format('d. m. Y', $values2['dateEnd']);
                    if($testDate) {
                        $entity->setDateEnd($testDate);
                    }
                }

                $this->em->persist($entity);
                $this->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('bonusTable');
            } else {
                $that->redirect('ShiftBonusGroup:edit',  ['id' => $values2['shiftBonusGroup']]);
            }
        };

        return $form;
    }

    public function handleCheckShiftBonus() {
        $values = $this->request->getPost();
        if($values['bonus']) {
            $entity = $this->em->getShiftBonusTemplateRepository()->find($values['bonus']);
            $this->template->modalBonus = $entity;
        }
        $this->redrawControl('planModal');
    }

    public function handleRemoveShiftBonus() {
        $values = $this->request->getPost();
        $entity = $this->em->getShiftBonusTemplateRepository()->find($values['bonus']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('bonusTable');
    }

}