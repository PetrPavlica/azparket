<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionGroup;
use Nette;
use App\Model\Database\Entity\PermissionItem;
use Doctrine\ORM\Tools\SchemaTool;

class GroupPresenter extends BasePresenter
{
    /**
     * ACL name='Správa skupin rolí'
     * ACL rejection='Nemáte přístup k správě skupin rolí.'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání skupin rolí uživatelů'
     */
    public function renderEdit($id) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($id) {
            $entity = $this->em->getPermissionGroupRepository()->find($id);
            if (!$entity) {
                $this->redirect('Group:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem rolí'
     */
    public function createComponentTable() {
        $grid = $this->gridGen->generateGridByAnnotation(PermissionGroup::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Group:edit');
        $action = $grid->addAction('editMode', 'Oprávnění', 'editMode!');
        if ($action)
            $action->setIcon('cogs')
                    ->setTitle('Oprávnění')
                    ->setClass('btn btn-xs btn-dark');
        $grid->allowRowsAction('editMode', function($item) {
            if ($item->id == 1) {
                return false;
            } else {
                return true;
            }
        });

        $action = $grid->addAction('edit', '', 'Group:edit');
        if ($action)
            $action->setIcon('edit')
                    ->setTitle('Úprava')
                    ->setClass('btn btn-xs btn-info');
        $this->gridGen->addButtonDeleteCallback();
        $grid->allowRowsMultiAction('multiAction', 'delete', function($item) {
            if ($item->id == 1) {
                return false;
            } else {
                return true;
            }
        });
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/editaci skupin rolí'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation(PermissionGroup::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit skupinu rolí', 'success'], ['Nepodařilo se uložit skupinu rolí!', 'warning']);
        $form->setRedirect('Group:default');
        return $form;
    }

    /**
     * ACL name='Doctrine update schematu akce'
     */
    public function handleSchemaUpdate()
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_METHOD);

        try {
            $cacheDriver = new \Doctrine\Common\Cache\ArrayCache();
            $deleted = $cacheDriver->deleteAll();
            $schemaTool = new SchemaTool($this->em);
            $this->em->getMetadataFactory()->setCacheDriver(null);
            $metadatas = $this->em->getMetadataFactory()->getAllMetadata();
            $updateSchemaSql = $schemaTool->getUpdateSchemaSql($metadatas, false);
            foreach ($updateSchemaSql as $s) {
                echo "<p>";
                echo $s . ';';
                echo "<p>";
            }
            die;
        } catch (\Exception $i) {
            $this->flashMessage('Chyba:' . $i->getMessage(), 'error');
            \Tracy\Debugger::log($i);
        }
        $this->flashMessage('Ok', 'success');
    }

    /**
     * ACL name='Smazání mapovaných elementů akce'
     */
    public function handleDeleteMapping($id) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_METHOD);

        try {
            $q = $this->em->createQuery('DELETE FROM App\Model\Database\Entity\PermissionItem');
            $numDeleted = $q->execute();
        } catch (\Exception $i) {
            $this->flashMessage('Chyba:' . $i->getMessage(), 'error');
            \Tracy\Debugger::log($i);
        }
        $this->flashMessage('Ok. Smazáno záznamů: ' . $numDeleted, 'success');
        $this->redirect('this');
    }

}
