<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use Doctrine\Common\Collections\Criteria;

class CustomerOrderedPresenter extends BasePresenter
{

    /** @persistent */
    public $backUrl;

    /** @persistent */
    public $backId;

    /** @var integer  */
    public $dealer;

    /** @var array  */
    public $filter;

    /**
     * ACL name='Správa objednavatelů - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
        $this->sess = $this->session->getSection('workflow');
    }

    /**
     * ACL name='Přidávání/edit objednavatele'
     */
    public function renderEdit($id, $backUrl, $backId) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_METHOD);

        if ($backUrl) {
            $this->backUrl = $backUrl;
            $this->backId = $backId;
            $this->template->backUrl = $this->link($backUrl, ['id' => $backId]);
        }
        if ($id) {
            $customer = $this->em->getCustomerOrderedRepository()->find($id);
            $arr = $this->ed->get($customer);
            $this['form']->setDefaults($arr);

            $this->template->customerId = $customer->id;
            $this->template->entity = $customer;
        }
    }

    /**
     * ACL name='Tabulka přehled zákazníků'
     */
    public function createComponentTable() {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\CustomerOrdered::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'CustomerOrdered:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'CustomerOrdered:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit zákazníků'
     */
    public function createComponentForm() {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\CustomerOrdered::class, $this->user, $this, __FUNCTION__);
        $form->addText('searchAres', 'Vyhledat dle názvu firmy nebo IČ')
            ->setHtmlAttribute('title', 'Vyhledat dle názvu firmy nebo IČ')
            ->setHtmlAttribute('placeholder', 'Vyhledat dle názvu firmy nebo IČ')
            ->setHtmlAttribute('class', 'autocomplete-input form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('data-toggle', 'completer')
            ->setHtmlAttribute('data-preload', 'false')
            ->setHtmlAttribute('data-suggest', 'true')
            ->setHtmlAttribute('data-minlen', '3');
        $form->setMessages(['Podařilo se uložit kontakt', 'success'], ['Nepodařilo se uložit kontakt!', 'warning']);
        $form->isRedirect = false;
        $form->onError[] = function($form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        /*$form->onValidate[] = function($form) {
            $values = $form->getValues();
            if ($values->idNo != '') { // Kontrola jedinečnosti IČ
                $criteriaStart = new Criteria();
                $criteriaStart->where(Criteria::expr()->notIn('id', [$values->id]));
                $criteriaStart->andWhere(Criteria::expr()->eq('idNo', $values->idNo));
                $customer = $this->em->getCustomerRepository()->matching($criteriaStart)->getValues();
                if ($customer) {
                    $form->addError('Pozor! Objednavatele se nepodařilo uložit. Pod tímto IČ máte již zaevidovaného jiného objednavatele!');
                }
            }
        };*/
        $form->onSuccess[] = [$this, 'successCustomerOrderedForm'];
        return $form;
    }

    public function successCustomerOrderedForm($form, $values) {
        $values2 = $this->getRequest()->getPost();

        if ($values->id) {
            $customer = $this->em->getCustomerOrderedRepository()->find($values->id);
            if ($customer) {
                if ($customer->customerState && $customer->customerState->id != $values->customerState) {
                    $values->dateChangeState = new \DateTime();
                }
            }
        }

        $values['fullname'] = $values['name'].' '.$values['surname'];

        $customer = $this->formGenerator->processForm($form, $values, true);

        // Uložit a zpět
        if (isset($values2['sendBack'])) {
            if ($this->backUrl)
                $this->redirect($this->backUrl, $this->backId);
            else
                $this->redirect('CustomerOrdered:default');
        }
        // Uložit
        if (isset($values2['send'])) {
            $this->redirect('CustomerOrdered:edit', ['id' => $customer->id]);
        }

        // Uložit a nový
        if (isset($values2['sendNew'])) {
            if ($this->backUrl) {
                $this->redirect('this');
            } else {
                $this->redirect('CustomerOrdered:edit');
            }
        }
    }
}
