<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Skill;
use App\Model\Database\Entity\PermissionItem;

class SkillPresenter extends BasePresenter
{
    /**
     * ACL name='Správa dovedností zaměstnanců'
     * ACL rejection='Nemáte přístup ke správě dovedností zaměstnanců.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním dovednosti zaměstnanců'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getSkillRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Dovednost nebyla nalezena.', 'error');
                $this->redirect('Skill:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem dovedností'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Skill::class, get_class(), __FUNCTION__);

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
     * ACL name='Formulář pro přidání/edit dovednost zaměstnance'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Skill::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dovednost', 'success'], ['Nepodařilo se uložit dovednost!', 'error']);
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
            $this->redirect('Skill:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Skill:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Skill:edit');
        } else {
            $this->redirect('Skill:edit', ['id' => $entity->id]);
        }
    }
}