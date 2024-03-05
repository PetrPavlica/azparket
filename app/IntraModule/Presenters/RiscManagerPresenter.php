<?php

namespace App\IntraModule\Presenters;

use Nette\Forms\Controls\SelectBox;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Facade\ManagedRisc;
use App\Model\Database\Entity\ManagedRisc as ManagedRiscEntity;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;

class RiscManagerPresenter extends BasePresenter
{
    /** @var ManagedRisc @inject */
    public $managedRiscFac;

    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Řízení rizik - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        $id = $this->getParameter('id');
    }

    public function beforeRender()
    {
        parent::beforeRender();
    }

    public function renderEdit($id)
    {
        
        if ($id) {
            $risc = $this->em->getManagedRiscRepository()->find($id);
            if (!$risc) {
                $this->flashMessage('Nepodařilo se najít daný risk!', 'warning');
                $this->redirect('RiscManager:default');
            }
            $this->template->risc = $this->template->entity = $risc;

            $arr = $this->ed->get($risc);
            $this['form']->setDefaults($arr);

            // Data for table of revaluations
            if (!isset($this->sess->formValues['id']) || $this->sess->formValues['id'] != $id) {
                $this->sess->formValues = $this->managedRiscFac->getDataFromManagedRisc($risc);
            }

        } else {
            if (isset($this->sess->formValues['id']) && $this->sess->formValues['id'] != "") {
                unset($this->sess->formValues);
            }
        }

        // Plnění dat pro speciální formulář s meziukládáním
        if (!isset($this->sess->formValues)) {
            $this->sess->formValues = [];
        }

        //$this['form']->setDefaults($this->sess->formValues);

        $this->template->formValues = $this->sess->formValues;
    }

    /**
     * ACL name='Tabulka s přehledem rizik'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ManagedRiscEntity::class, get_class(), __FUNCTION__, 'default');

        $grid = $this->gridGen->setClicableRows($grid, $this, 'RiscManager:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $edit = $grid->addAction('edit', '', 'RiscManager:edit');
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
     * ACL name='Formulář pro přidání/edit risku'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ManagedRiscEntity::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit riziko', 'success'], ['Nepodařilo se uložit riziko!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'processFormSuccess'];

        $form->addHidden('changedInput');

        $form->addSubmit('ajax', '')
            ->setHtmlAttribute('class', 'ajax')
            ->setHtmlAttribute('hidden', 'true');

        return $form;
    }

    public function processFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        // Při stisku tlačíka Zahodit změny - nic neukládám a smažu session.
        if (isset($values2['sendRemoveAll'])) {
            unset($this->sess->formValues);
            $this->redirect('RiscManager:default');
        }

        if (isset($values2['addItem'])) {
            $someEmpty = false;
            foreach ($values2['newItem'] as $k => $v) {
                if ($k != 'revaluationDate') {
                    continue;
                }
                if (empty($v)) {
                    $someEmpty = true;
                    break;
                }
            }
            if (!$someEmpty) {
                if (!isset($values2['items'])) {
                    $values2['items'] = [];
                }
                $values2['items'][] = $values2['newItem'];
                unset($values2['newItem']);
            } else {
                $this->flashMessage('Pro přidání přehodnocení je nutné vyplnit alespoň datum.', 'warning');
            }
        }

        // Pokud bylo zmáčknuto smazat řádek
        if (isset($values2['removeItem'])) {
            unset($values2['items'][$values2['removeItem']]);
            unset($values2['items'][$values2['removeItem']]);
            if (count($values2['items']) == 0) {
                unset($values2['items']);
            }
        }

        $this->sess->formValues = $values2;

        // Pokud byl form odeslán ajaxově, tak jej meziuložím a překreslím, ale neposílám ještě do db
        if ($this->isAjax()) {
            $this->redrawControl('owf-form');
        } else {
            // ukládám formulář - při klasickém postu pomocí automatického save
            $risc = $this->formGenerator->processForm($form, $values, true);

            if (!$risc) {
                return;
            }
            
            // ukládám přehodnocení:
            $this->managedRiscFac->saveItems($risc, $this->sess->formValues);

            if (isset($values2['sendBack'])) { // Uložit a zpět
                unset($this->sess->formValues);
                $this->redirect('RiscManager:default');
            } else if (isset($values2['send'])) { //Uložit
                $this->redirect('RiscManager:edit', ['id' => $risc->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('RiscManager:edit');
            } else {
                $this->redirect('RiscManager:edit', ['id' => $risc->id]);
            }
        }
    }
}
