<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\SkillType;
use App\Model\Database\Entity\Skill;
use App\Model\Database\Entity\PermissionItem;

class SkillTypePresenter extends BasePresenter
{
    /**
     * ACL name='Správa typů dovedností'
     * ACL rejection='Nemáte přístup ke správě typů dovedností.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním typu dovednosti'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getSkillTypeRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Typ dovednosti nebyl nalezen.', 'error');
                $this->redirect('SkillType:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem typů dovedností'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(SkillType::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'SkillType:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'SkillType:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem dovedností daného'
     */
    public function createComponentTableSkills()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Skill::class, get_class(), __FUNCTION__, 'default', ['type' => $this->params['id']]);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Skill:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Skill:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit typu dovednosti'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(SkillType::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit typ dovednosti', 'success'], ['Nepodařilo se uložit typ dovednosti!', 'error']);
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
            $this->redirect('SkillType:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('SkillType:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('SkillType:edit');
        } else {
            $this->redirect('SkillType:edit', ['id' => $entity->id]);
        }
    }
}