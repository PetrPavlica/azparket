<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;

class MaterialPresenter extends BasePresenter
{
    /**
     * ACL name='Správa materiálů'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getMaterialRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Material:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem materiálů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Material::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Material:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');

        $grid->getColumn('stock')->setRenderer(function($item) {
            return $item->getStockName();
        });

        $multiAction->addAction('edit', 'Upravit', 'Material:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit materiálu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Material::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit materiál', 'success'], ['Nepodařilo se uložit materiál!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'materialFormSuccess'];
        return $form;
    }

    public function materialFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Material:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Material:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Material:edit');
        } else {
            $this->redirect('Material:edit', ['id' => $entity->id]);
        }
    }

}
