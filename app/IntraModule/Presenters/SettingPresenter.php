<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use Nette\Caching\Cache;

class SettingPresenter extends BasePresenter
{
    /**
     * ACL name='Správa nastavení'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getSettingRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Setting:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem nastavení'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Setting::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Setting:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Setting:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        if ($this->user->getIdentity()->getData()[ 'group' ] == 1) {
            $this->gridGen->addButtonDeleteCallback();
        } 
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit nastavení'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Setting::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se nastavení', 'success'], ['Nepodařilo se nastavení!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'settingFormSuccess'];
        return $form;
    }

    public function settingFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $entity = $this->formGenerator->processForm($form, $values, true);

        if (!$entity) {
            return;
        }

        // Clean cache
        $this->cache->clean([
            Cache::TAGS => ["settings"],
        ]);

        if (isset($values2['sendBack'])) { // Uložit a zpět
            $this->redirect('Setting:default');
        } else if (isset($values2['send'])) { //Uložit
            $this->redirect('Setting:edit', ['id' => $entity->id]);
        } else if (isset($values2['sendNew'])) {
            $this->redirect('Setting:edit');
        } else {
            $this->redirect('Setting:edit', ['id' => $entity->id]);
        }
    }

}
