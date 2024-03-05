<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity;

class LanguagePresenter extends BasePresenter
{
    /**
     * ACL name='Správa jazyků'
     * ACL rejection='Nemáte přístup k správě jazyků.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, Entity\PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání jazyka'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($id) {
            $entity = $this->em->getLanguageRepository()->find($id);
            $this['form']->setDefaults($this->ed->get($entity));
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem jazyků'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Entity\Language::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Language:edit');
        
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Language:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/editaci jazyka'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Entity\Language::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit jazyk.', 'success'],
            ['Nepodařilo se uložit jazyk!', 'warning']);
        $form->setRedirect('Language:');
        return $form;
    }

}
