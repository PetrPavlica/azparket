<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Reservation;
use App\Model\Database\Entity\ReservationItem;
use App\Model\Database\Entity\PermissionItem;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Components\Reservation\IReservationControlFactory;
use App\Model\Utils\TimeHelper;
use App\Model\Facade\Reservation as ReservationFac;

class ReservationPresenter extends BasePresenter
{

    /** @var IReservationControlFactory @inject */
    public $reservationControlFac;

    /** @var ReservationFac @inject */
    public $reservationFac;

    /** @var TimeHelper @inject */
    public $timeHelper;

    /**
     * ACL name='Správa rezervací'
     * ACL rejection='Nemáte přístup ke správě rezervací.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Přehled rezervací a rezervačních položek'
     */
    public function renderDefault($openTab)
    {
        $this->template->openTab = $openTab;
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním rezervace'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getReservationRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Rezervace nebyla nalezena.', 'error');
                $this->redirect('Reservation:');
            }
            $arr = $this->ed->get($entity);
            $arr['date'] = ($entity->dateFrom ? $entity->dateFrom->format('j. n. Y') : '');
            $arr['timeFrom'] = ($entity->dateFrom ? $entity->dateFrom->format('H:i') : '');
            $arr['timeTo'] = ($entity->dateFrom ? $entity->dateTo->format('H:i') : '');

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;

            if ($entity->customer) {
                $this['form']->setAutocmp('customer', $this->em->getCustomerRepository()->getSpecificCustomer($entity->customer));
                bdump($this->em->getCustomerRepository()->getSpecificCustomer($entity->customer));
            }
        }
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním rezervovatelné položky'
     */
    public function renderEditItem($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getReservationItemRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Rezervovatelná položka nebyla nalezena.', 'error');
                $this->redirect('Reservation:', ['openTab' => 'item']);
            }
            $arr = $this->ed->get($entity);

            $this['itemForm']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Zobrazení stránky administrací rezervací'
     */
    public function renderAdmin($openTab = '')
    {
        if (!isset($this->sess->reservationItems)) {
            $this->sess->reservationItems = [];
        }
        // reservation page
        $reservationItems = $this->em->getReservationItemRepository()->findBy(['active' => 1]);
        $this->template->reservationItems = $reservationItems;
        $this->template->timeHelper = $this->timeHelper;
        $this->template->openTab = $openTab;
    }

    /**
     * ACL name='Tabulka s přehledem rezervací'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Reservation::class, get_class(), __FUNCTION__);

        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Reservation:edit');

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Reservation:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();


        return $grid;
    }

    /**
     * ACL name='Tabulka s přehledem rezervací'
     */
    public function createComponentItemTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(ReservationItem::class, get_class(), __FUNCTION__);

        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Reservation:editItem');

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Reservation:editItem');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit rezervace'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Reservation::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit rezervaci', 'success'], ['Nepodařilo se uložit rezervaci!', 'error']);

        $that = $this;

        $form->addText('date', 'Datum')
            ->setRequired('Toto pole je povinné')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('data-provide', 'datepicker')
            ->setHtmlAttribute('data-date-orientation', 'bottom')
            ->setHtmlAttribute('data-date-format', 'd. m. yyyy')
            ->setHtmlAttribute('data-date-today-highlight', 'true')
            ->setHtmlAttribute('data-date-autoclose', 'true')
            ->setHtmlAttribute('data-date-language', 'cs')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
    
        $form->addText('timeFrom', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');

        $form->addText('timeTo', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');
        
        $form->addCheckbox('repeat', 'Opakovat')
            ->setHtmlAttribute('class', 'form-control');

        $form->addText('repeatDateTo', 'Opakovat do')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('data-provide', 'datepicker')
            ->setHtmlAttribute('data-date-orientation', 'bottom')
            ->setHtmlAttribute('data-date-format', 'd. m. yyyy')
            ->setHtmlAttribute('data-date-today-highlight', 'true')
            ->setHtmlAttribute('data-date-autoclose', 'true')
            ->setHtmlAttribute('data-date-language', 'cs')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
        
        $form->addSelect('repeatBy', 'Opakovat po', ['day' => 'dny', 'month' => 'měsíce', 'year' => 'roky'])
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('repeatByValue', 'Interval')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(Form::INTEGER, 'Zadaný interval musí být celé číslo.');

        $form->onSuccess[] = [$this, 'reservationFormSuccess'];
        
        return $form;
    }

    public function reservationFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        $messages = [];
        $reservation = $this->reservationFac->processReservationForm($values, $this->user->getId(), true, $messages);

        bdump($reservation);

        if ($reservation) {
            $this->flashMessage('Rezervace byla uložena', 'success');
        } elseif (!$messages) {
            $this->flashMessage('Při ukládání došlo k chybě', 'error');
        }

        foreach ($messages as $m) {
            $this->flashMessage($m[0], $m[1]);
        }

        if (isset($values2['sendBack'])) {
            $this->redirect('Reservation:default');
        } else if (isset($values2['send'])) {
            $this->redirect('Reservation:edit', ['id' => $reservation->id]);
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit položky rezervace'
     */
    public function createComponentItemForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(ReservationItem::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit rezervovatelnou položku', 'success'], ['Nepodařilo se uložit rezervovatelnou položku!', 'error']);
        //$form->setRedirect('Reservation:');

        $that = $this;
        $form->onSuccess[] = function(Form $form, $values) use($that): void {
            $values2 = $this->request->getPost();

            $entity = $this->formGenerator->processForm($form, $values, true);
            if ($entity) {

            } else {
                return;
            }
            bdump($entity);

            if (isset($values2['sendBack'])) {
                $that->redirect('Reservation:default');
            } else if (isset($values['send'])) {
                $this->redirect('Reservation:editItem', ['id' => $entity->id]);
            }
        };
        return $form;
    }
    
    protected function createComponentReservation(): Multiplier
    {
        return new Multiplier(function ($riId) {
            return $this->reservationControlFac->create(['reservationItemId' => $riId, 'admin' => true]);
        });
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $data = isset($_GET['data']) ? $_GET['data'] : null;
        $result = $this->em->getCustomerRepository()->getDataAutocompleteCustomers($term, $data);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

}