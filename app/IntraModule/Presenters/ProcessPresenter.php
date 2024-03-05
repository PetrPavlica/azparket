<?php

namespace App\IntraModule\Presenters;

use App\Model\Facade\Cron;
use Nette\Forms\Controls\SelectBox;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Facade\Process;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;

class ProcessPresenter extends BasePresenter
{
    /** @var Process @inject */
    public $processFac;

    /** @var Cron @inject */
    public Cron $cronFac;

    /** @var IPDFPrinterFactory @inject */
    public $IPrintFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    /** @persistent */
    public $slug;

    protected function createComponentPrint()
    {
        return $this->IPrintFactory->create();
    }

    /**
     * ACL name='Obchodní případy - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        $this->sess = $this->session->getSection('workflow');

        $id = $this->getParameter('id');
        $slug = $this->getParameter('slug');
        $this->slug = $slug;
        $processState = null;
        if ($slug) {
            $processState = $this->em->getProcessStateRepository()->findOneBy(['slug' => $slug]);
        }

        if (!$processState && !$id && $this->getView() != 'default') {
            $this->flashMessage("Nepodařilo se určit stav procesu - neexistující stav obchodního případu", 'warning');
            $this->redirect('Homepage:default');
        }

        $this->sess->processState = $processState;
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->processState = $this->sess->processState;
    }

    public function renderEdit($id)
    {
        if ($id) {
            $process = $this->em->getProcessRepository()->find($id);
            if (!$process) {
                $this->flashMessage('Nepodařilo se najít daný obchodní případ!', 'warning');
                $this->redirect('Process:default');
            }
            $this->template->process = $process;
            $this->template->entity = $process;

            $this->sess->processState = $process->processState;
            $this->template->processState = $this->sess->processState;
            $arr = $this->ed->get($process);

            $this['form']->setDefaults($arr);
            $customerValue = $this->processFac->getSpecificCustomer($process->customer);
            if ($customerValue)
                $this['form']->setAutocmp('customer', $customerValue);

            // Data for table Basket Form
            if (!isset($this->sess->formValues['id']) || $this->sess->formValues['id'] != $id) {
                $this->sess->formValues = $this->processFac->getDataFromProcess($process);

                if ($process->customer)
                    $this->sess->formValues['idCustomer'] = $process->customer->id;
            }

            $this->template->previousOP = $this->em->getProcessRepository()->findOneBySpecific(['id !' => $id, 'createdAt <' => $process->createdAt, 'processState' => $process->processState], ['createdAt' => 'DESC']);
            $this->template->nextOP = $this->em->getProcessRepository()->findOneBySpecific(['id !' => $id, 'createdAt >' => $process->createdAt, 'processState' => $process->processState], ['createdAt' => 'ASC']);
        } else {
            if (isset($this->sess->formValues['id']) && $this->sess->formValues['id'] != "") {
                unset($this->sess->formValues);
            } elseif (isset($this->sess->formValues['processState']) && $this->sess->formValues['processState'] != $this->sess->processState->id) {
                unset($this->sess->formValues);
            }
        }

        // Plnění dat pro speciální formulář s meziukládáním
        if (!isset($this->sess->formValues)) {
            $this->sess->formValues = [];
            $this->sess->formValues = $this->processFac->getEmptyArrayFormProces(null, 0, false);
            $this->sess->formValues['dealer'] = $this->user->id;
            $this->sess->formValues['foundedDate'] = date_format(new DateTime(), "d. m. Y");
            $this->sess->formValues['sendDate'] = date_format(new DateTime('+1 Weekday'), "d. m. Y");
            $this->sess->formValues['receiptDate'] = date_format(new DateTime(), "d. m. Y");
            $this->sess->formValues['idCustomer'] = "";
            $this->sess->formValues['currency'] = "1";
        }

        if (!empty($this->sess->formValues['customer'])) {
            $this->sess->formValues['customerData'] = $this->em->getCustomerRepository()->find($this->sess->formValues['customer']);
        }

        if (!empty($this->sess->formValues['idCustomer'])) {
            $this->sess->formValues['customerData'] = $this->em->getCustomerRepository()->find($this->sess->formValues['idCustomer']);
        }

        foreach ($this['form']->getComponents() as $k => $c) {
            if (isset($this->sess->formValues[$k]) && empty($this->sess->formValues[$k]) && $c instanceof SelectBox) {
                unset($this->sess->formValues[$k]);
            }
        }

        $this['form']->setDefaults($this->sess->formValues);
        if (isset($this->sess->formValues['textcustomer']))
            $this['form']->setAutocmp('customer', $this->sess->formValues['textcustomer']);

        $ItemTypes = [];
        $result = $this->em->getItemTypeRepository()->findAll();
        if ($result) {
            foreach ($result as $s) {
                $ItemTypes[$s->id] = $s->name;
            }
        }

        $this->template->itemTypes = $ItemTypes;

        $this->template->formValues = $this->sess->formValues;
    }

    /**
     * ACL name='Tabulka s přehledem procesů'
     */
    public function createComponentTable()
    {
        $findBy = [];
        if (isset($this->sess->processState)) {
            $findBy = ['processState' => $this->sess->processState->id];
        }

        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Process::class, get_class(), __FUNCTION__, 'default', $findBy);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Process:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $grid->getColumn('customer')->setRenderer(function ($item) {
            if ($item->customer) {
                return $item->customer->name . ' ' . $item->customer->surname;
            }

            return null;
        });

        $presenter = $this;
        $prevState = $grid->addActionCallback('prevstate', '');
        if ($prevState) {
            $prevState->setIcon('arrow-left')
                ->setTitle('Přepnout do předchozího stavu OP')
                ->setClass('btn btn-xs btn-link')
                ->onClick[] = function ($id) use ($presenter) {
                $presenter->swapState($id, -1);
            };
        }
        $nextState = $grid->addActionCallback('nextstate', '');
        if ($nextState) {
            $nextState->setIcon('arrow-right')
                ->setTitle('Přepnout do dalšího stavu OP')
                ->setClass('btn btn-xs btn-link')
                ->onClick[] = function ($id) use ($presenter) {
                $presenter->swapState($id, 1);
            };
        }

        $grid->allowRowsAction('prevstate', function ($item) {
            return $item->processState->order > 1;
        });

        $grid->allowRowsAction('nextstate', function ($item) {
            return $item->processState->id != 4;
        });

        $edit = $grid->addAction('edit', '', 'Process:edit');
        if ($edit)
            $edit->setIcon('edit')
                ->setTitle('Úprava')
                ->setClass('btn btn-link');

        $this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    public function swapState($id, $step)
    {
        $res = $this->processFac->swapProcessState($id, $step, $this->user->id);
        if ($res) {
            $this->flashMessage('Stav OP byl změněn', 'success');
        } else {
            $this->flashMessage('Stav se nepodařilo přepnout.', 'warning');
        }
        $this->redirect('this');
    }

    /**
     * ACL name='Formulář pro přidání/edit procesu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Process::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit OP', 'success'], ['Nepodařilo se uložit OP!', 'warning']);
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
            $this->redirect('Process:default', ['slug' => $this->sess->processState->slug]);
        }

        if (isset($values2['addItem'])) {
            $someEmpty = false;
            foreach ($values2['newItem'] as $k => $v) {
                if ($k == 'description') {
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
                $this->flashMessage('Vyberte aspoň jednu možnost z nabízených možností vzorku.', 'warning');
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
            $process = $this->formGenerator->processForm($form, $values, true);

            if (!$process) {
                return;
            }

            if (!$process->processState) {
                $this->processFac->manageSwapProcessState(1, $process, $this->user->id, null);
            }

            if ($process->bpNumber == "") {
                $process = $this->processFac->generateNumberBp($process);
            }

            // ukládám položky:
            $this->processFac->saveItems($process, $this->sess->formValues);

            //$this->cronFac->synchronizeProcessInOracle($process);

            if (isset($values2['sendBack'])) { // Uložit a zpět
                unset($this->sess->formValues);
                $this->redirect('Process:default', ['slug' => $this->sess->processState->slug]);
            } else if (isset($values2['send']) || isset($values2['acceptToStock'])) { //Uložit
                unset($this->sess->formValues);
                $this->redirect('Process:edit', ['id' => $process->id, 'slug' => $process ? $process->processState->slug : $this->sess->processState->slug]);
            } else if (isset($values2['sendNew'])) {
                unset($this->sess->formValues);
                $this->redirect('Process:edit');
            } else {
                $this->redirect('Process:edit', ['id' => $process->id, 'slug' => $process ? $process->processState->slug : $this->sess->processState->slug]);
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
}
