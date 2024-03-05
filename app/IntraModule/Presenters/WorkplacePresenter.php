<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Workplace;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;

class WorkplacePresenter extends BasePresenter
{
    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Pracoviště - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getWorkplaceRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se najít dané pracoviště!', 'warning');
                $this->redirect('Workplace:default');
            }
            $this->template->entity = $entity;

            // znemožnění výběru aktuálně upravovaného pracoviště do nadřazených
            $opts = $this['form']->getComponent('superiorWorkplaces')->getItems();
            if (isset($opts[$id]))
                unset($opts[$id]);
            $this['form']->getComponent('superiorWorkplaces')->setItems($opts);
            $this['form']->getComponent('subordinateWorkplaces')->setItems($opts);

            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Tabulka s přehledem pracovišť'
     */
    public function createComponentTable()
    {

        $grid = $this->gridGen->generateGridByAnnotation(Workplace::class, get_class(), __FUNCTION__, 'default');

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Workplace:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $edit = $grid->addAction('edit', '', 'Workplace:edit');
        if ($edit)
            $edit->setIcon('edit')
                ->setTitle('Úprava')
                ->setClass('btn btn-link');

        $this->gridGen->addButtonDeleteCallback();
        
        $grid->getColumn('id')
            ->setDefaultHide(false);

        // id to 1st column
        $colsRef = $grid->getColumns();
        $cols[] = 'id';
        unset($colsRef['id']);
        foreach ($colsRef as $key => $col) {
            $cols[] = $key;
        }
        $grid->setColumnsOrder($cols);

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit pracoviště'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Workplace::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit pracoviště', 'success'], ['Nepodařilo se uložit pracoviště!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];

        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        // Pokud byl form odeslán ajaxově, tak jej meziuložím a překreslím, ale neposílám ještě do db
        if ($this->isAjax()) {
            $this->redrawControl('owf-form');
        } else {
            // ukládám formulář - při klasickém postu pomocí automatického save
            $entity = $this->formGenerator->processForm($form, $values, true);

            if (!$entity) {
                return;
            }

            if (isset($values2['sendBack'])) { // Uložit a zpět
                $this->redirect('Workplace:default');
            } else if (isset($values2['send'])) { //Uložit
                $this->redirect('Workplace:edit', ['id' => $entity->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('Workplace:edit');
            } else {
                $this->redirect('Workplace:edit', ['id' => $entity->id]);
            }
        }
    }
}
