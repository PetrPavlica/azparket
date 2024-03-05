<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Document;
use App\Model\Database\Entity\PermissionItem;
use Nette\Application\UI\Form;

class DocumentsPresenter extends BasePresenter
{
    /**
     * ACL name='Správa dokumentů'
     * ACL rejection='Nemáte přístup ke správě dokumentů.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového dokumentu'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getDocumentRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Dokument nebyl nalezen.', 'error');
                $this->redirect('Documents:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem dokumentů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Document::class, get_class(), __FUNCTION__);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Documents:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Documents:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit dokumentu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Document::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit dokument', 'success'], ['Nepodařilo se uložit dokument!', 'error']);
        $form->setRedirect('Documents:');

        $form->onSuccess[] = function(Form $form, $values): void {
            $values2 = $this->request->getPost();

            if (empty($values->id)) {
                $values['user'] = $this->em->getUserRepository()->find($this->getUser()->getId());
            }

            $entity = $this->formGenerator->processForm($form, $values, true);

            if (isset($values2['send'])) {
                $this->redirect('Documents:');
            } else {
                $this->redirect('Documents:edit', ['id' => $entity->id]);
            }
        };

        return $form;
    }
}