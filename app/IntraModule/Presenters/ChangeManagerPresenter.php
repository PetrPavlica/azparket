<?php

namespace App\IntraModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\ManagedChangeStep;
use App\Model\Facade\Process;
use App\Model\Facade\ChangeManager;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;

class ChangeManagerPresenter extends BasePresenter
{
    /** @var Process @inject */
    public $processFac;

    /** @var ChangeManager @inject */
    public $changeFac;

    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Řízení změn - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        //$this->sess = $this->session->getSection('workflow');

        $id = $this->getParameter('id');
    }

    public function beforeRender()
    {
        parent::beforeRender();
    }

    public function renderEdit($id)
    {
        if ($id) {
            $change = $this->em->getManagedChangeRepository()->find($id);
            if (!$change) {
                $this->flashMessage('Nepodařilo se najít danou změnu!', 'warning');
                $this->redirect('ChangeManager:default');
            }
            $this->template->change = $change;
            //$this->template->entity = $process;

            $arr = $this->ed->get($change);
            $this['form']->setDefaults($arr);

            $customerValue = $this->processFac->getSpecificCustomer($change->customer);
            if ($customerValue)
                $this['form']->setAutocmp('customer', $customerValue);

            if ($change->parentChange)
                $this['form']->setAutocmp('parentChange', $change->parentChange->id);

        } else {
            $this['form']->setDefaults([
                'dateCreatedAt' => date_format(new DateTime(), "d. m. Y"),
                'originator' => $this->user->id
            ]);
        }

    }

    /**
     * ACL name='Tabulka s přehledem zmněn'
     */
    public function createComponentTable()
    {

        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\ManagedChange::class, get_class(), __FUNCTION__, 'default');

        $grid = $this->gridGen->setClicableRows($grid, $this, 'ChangeManager:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $grid->getColumn('customer')->setRenderer(function ($item) {
            if ($item->customer) {
                return $item->customer->name . ' ' . $item->customer->surname;
            }
            return null;
        });

        $edit = $grid->addAction('edit', '', 'ChangeManager:edit');
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
     * ACL name='Formulář pro přidání/edit změny'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\ManagedChange::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit změnu', 'success'], ['Nepodařilo se uložit změnu!', 'warning']);
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
            $this->redirect('ChangeManager:default');
        }

        // Pokud byl form odeslán ajaxově, tak jej meziuložím a překreslím, ale neposílám ještě do db
        if ($this->isAjax()) {
            $this->redrawControl('owf-form');
        } else {
            // ukládám formulář - při klasickém postu pomocí automatického save
            $change = $this->formGenerator->processForm($form, $values, true);

            if (!$change) {
                return;
            }

            // Při stisku tlačíka Schválit - uložit.
            if (isset($values2['approve'])) {
                $user = $this->em->getUserRepository()->find($this->user->getId());
                $this->changeFac->approveChangeManager($change, $user);

                $this->redirect('ChangeManager:edit', ['id' => $change->id]);
            }

            if (isset($values2['sendBack'])) { // Uložit a zpět
                unset($this->sess->formValues);
                $this->redirect('ChangeManager:default');
            } else if (isset($values2['send']) || isset($values2['acceptToStock'])) { //Uložit
                $this->redirect('ChangeManager:edit', ['id' => $change->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('ChangeManager:edit');
            } else {
                $this->redirect('ChangeManager:edit', ['id' => $change->id]);
            }
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $data = isset($_GET['data']) ? $_GET['data'] : null;
        $result = $this->processFac->getDataAutocompleteCustomers($term, $data);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleGetCustomerPreload($customerId)
    {
        $customer = $this->processFac->getCustomer()->find($customerId);
        $priceLvl = isset($customer->priceLevel) ? $customer->priceLevel->id : null;
        $this->payload->data = json_encode([$priceLvl, $customer->currency->id]);
        $this->sendPayload();
    }

    /**
     * Get managed changes for autocomplete
     * @param string $term
     */
    public function handleGetManagedChanges($term)
    {
        $changes = $this->em->getManagedChangeRepository()->findBy(['id' => $term], ['dateCreatedAt' => 'DESC'], 20);
        $result = [];
        foreach ($changes as $change) {
            $result[] = 'ID: '
                . $change->id . ", "
                . date_format($change->dateCreatedAt, "d. m. Y");
        }
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    /**
     * ACL name='Formulář pro přidání/edit kruků k dosažení změny'
     */
    public function createComponentChangeManageStepModalForm()
    {
        $that = $this;

        $form = $this->formGenerator->generateFormByAnnotation(ManagedChangeStep::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit krok', 'success'], ['Nepodařilo se uložit krok!', 'error']);
        $form->isRedirect = false;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $entity = $that->formGenerator->processForm($form, $values, true);
            if($entity) {
                $entity->setManagedChange($that->em->getManagedChangeRepository()->find($values2['change']));
                $that->em->flush();
            }

            if ($that->isAjax()) {
                $that->redrawControl('changeManageStepTable');
            } else {
                $that->redirect('ChangeManager:edit',  ['id' => $values2['change']]);
            }
        };

        return $form;
    }

    public function handleCheckManageChangeStep() {
        $values = $this->request->getPost();
        if($values['changeStep']) {
            $entity = $this->em->getManagedChangeStepRepository()->find($values['changeStep']);
            $arr = $this->ed->get($entity);
            $this['changeManageStepModalForm']->setDefaults($arr);
            $this->template->modalManageChangeStep = $entity;
        }
        $this->redrawControl('changeManageStepModal');
    }

    public function handleRemoveManageChangeStep() {
        $values = $this->request->getPost();
        $entity = $this->em->getManagedChangeStepRepository()->find($values['changeStep']);
        $this->em->remove($entity);
        $this->em->flush();

        $this->redrawControl('changeManageStepTable');
    }
}
