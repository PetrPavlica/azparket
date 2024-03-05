<?php

declare(strict_types=1);

namespace App\Components\Reservation;


use Nette;
use Nette\Http\Session;
use Nette\Application\UI;
use Nette\Application\UI\Form;
use Nette\Http\SessionSection;
use App\Model\Utils\TimeHelper;
use App\Model\DoctrineFormGenerator;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Components\MailSender\MailSender;
use App\Model\Database\Entity\Reservation;
use App\Model\Facade\Reservation as ReservationFac;
use App\Components\FormRenderer\IFormRendererFactory;

class ReservationControl extends UI\Control
{

    /** @var EntityManager */
    private $em;

    /** @var EntityData @inject */
    public $ed;

    /** @var Session @inject */
    public $session;

    /** @var ReservationFac @inject */
    public $reservationFac;
    
    /** @var TimeHelper @inject */
    public $timeHelper;

    /** @var DoctrineFormGenerator @inject */
    public DoctrineFormGenerator $formGenerator;

    /** @var SessionSection */
    public $sess;

    /** @var IFormRendererFactory */
    public $formRenderFactory;
    
    /** @var MailSender */
    public $mailSender;

    private $adminMode = false;

    /**
     * reservationItemId, [admin], [date]
     * @var array
     */
    private $cParams;

    private $days = [
      'Pondělí',
      'Úterý',
      'Středa',
      'Čtvrtek',
      'Pátek',
      'Sobota',
      'Neděle'  
    ];

    public function __construct(
        EntityManager $em,
        EntityData $ed,
        IFormRendererFactory $formRenderer,
        Session $session,
        MailSender $mailSender,
        TimeHelper $timeHelper,
        DoctrineFormGenerator $formGenerator,
        ReservationFac $reservationFac,
        $cParams
    ) {
        $this->em = $em;
        $this->ed = $ed;
        $this->formRenderFactory = $formRenderer;
        $this->session = $session;
        $this->sess = $this->session->getSection('front');
        $this->mailSender = $mailSender;
        $this->timeHelper = $timeHelper;
        $this->formGenerator = $formGenerator;
        $this->cParams = $cParams;
        $this->reservationFac = $reservationFac;
        if (isset($cParams['admin'])) {
            $this->adminMode = $cParams['admin'];
        }
    }

    /**
     * Render component for rendering form in specific style
     */
    public function createComponentRenderer()
    {
        return $this->formRenderFactory->create();
    }

    public function render()
    {
        $t = $this->template;
        $t->setFile(__DIR__ . '/templates/default.latte');
        $t->form = $this['form'];

        $t->adminMode = $this->adminMode;
        $t->days = $this->days;
        $t->timeHelper = $this->timeHelper;

        // prepare sess
        $this->checkSess();

        // date range
        $date = new \DateTime($this->getSessValue('date'));

        $dateFrom = (clone $date)->modify('last monday');
        $dateTo = (clone $date)->modify('sunday')->setTime(23, 59, 59);
        $t->dateStr = $dateFrom->format('j. n. Y') . ' - ' .  $dateTo->format('j. n. Y');


        // reservation days array

        $ri = $this->em->getReservationItemRepository()->find($this->cParams['reservationItemId']);
        if (!$ri || !$ri->reservablePeriod) {
            return;
        }
        $t->riDayRanges = $riDayRanges = [
            [TimeHelper::timeStrToMinutes($ri->timeMondayFrom), TimeHelper::timeStrToMinutes($ri->timeMondayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeTuesdayFrom), TimeHelper::timeStrToMinutes($ri->timeTuesdayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeWednesdayFrom), TimeHelper::timeStrToMinutes($ri->timeWednesdayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeThursdayFrom), TimeHelper::timeStrToMinutes($ri->timeThursdayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeFridayFrom), TimeHelper::timeStrToMinutes($ri->timeFridayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeSaturdayFrom), TimeHelper::timeStrToMinutes($ri->timeSaturdayTo)],
            [TimeHelper::timeStrToMinutes($ri->timeSundayFrom), TimeHelper::timeStrToMinutes($ri->timeSundayTo)]
        ];


        // prepare date values for grid render

        $timeFromMin = 1439;
        $timeToMax = 0;
        $dayFrom = -1;
        $dayTo = 4;
        foreach ($riDayRanges as $key => $rid) {
            // starting day
            if (!$rid[0] || !$rid[1]) {
                continue;
            } elseif ($dayFrom === -1) {
                $dayFrom = $key;
            }
            // end day
            if ($key > 4) {
                $dayTo = $key;
            }
            // min max day times
            if ($rid[0] < $timeFromMin) {
                $timeFromMin = $rid[0];
            }
            if ($rid[1] > $timeToMax) {
                $timeToMax = $rid[1];
            }
        }
        if ($dayFrom < 4) {
            $dayFrom = 0;
        }
        if ($dayTo > 4) {
            $dayTo = 6;
        }
        $timeFromDiff = $timeFromMin % $ri->reservablePeriod;
        $timeFromMin = $timeFromMin; //+ ($ri->reservablePeriod - $timeFromDiff);
        $timeToMax = $timeToMax - $timeToMax % $ri->reservablePeriod;


        // prepare occupied reservations for the week

        $rs = $this->em->getReservationRepository()->createQueryBuilder('r')
            ->where('r.dateFrom >= :dateFrom AND r.dateTo <= :dateTo AND (r.canceled = 0 OR r.canceled IS NULL) AND r.reservationItem = :riId')
            ->orderBy('r.dateFrom')
            ->setParameters(['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'riId' => $this->cParams['reservationItemId']])
            ->getQuery()->getResult();
        $rsArr = [
            0 => [],
            1 => [],
            2 => [],
            3 => [],
            4 => [],
            5 => [],
            6 => []
        ];
        foreach ($rs as $r) {
            $rsArr[$r->dateFrom->format('w') - 1][] = [
                'minutesFrom' => TimeHelper::timeStrToMinutes($r->dateFrom->format('H:i')),
                'minutesTo' => TimeHelper::timeStrToMinutes($r->dateTo->format('H:i')),
                'reservation' => $r
            ];
        }

        // pass to template

        $t->ri = $ri;
        $t->timeFromDiff = $timeFromDiff;
        $t->rColumns = ($timeToMax - $timeFromMin) / $ri->reservablePeriod;
        $t->timeFromMin = $timeFromMin;
        $t->timeToMax = $timeToMax;
        $t->riDayRanges = $riDayRanges;
        $t->dayFrom = $dayFrom;
        $t->dayTo = $dayTo;
        $t->dateFrom = $dateFrom;
        $t->now = new \DateTime();
        $t->rsArr = $rsArr;

        $t->render();
    }

    public function renderScripts()
    {
        $t = $this->template;
        $t->setFile(__DIR__ . '/templates/scripts.latte');
        $t->adminMode = $this->adminMode;
        $t->render();
    }

    public function renderModal()
    {
        $t = $this->template;
        $t->setFile(__DIR__ . '/templates/modal.latte');
        $t->adminMode = $this->adminMode;
        $t->render();
    }

    public function createComponentForm()
    {
        $presenter = $this->getPresenter();
        $form = new Nette\Application\UI\Form;
            
        $form->addText('name', 'Jméno')
            ->setRequired('Toto pole je povinné')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('surname', 'Příjmení')
            ->setRequired('Toto pole je povinné')
            ->setHtmlAttribute('class', 'form-control');
        $form->addEmail('email', 'E-mail')
            ->setRequired('Toto pole je povinné')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('phone', 'Telefon')
            ->addRule(
                Form::PATTERN,
                'Tel.č. nevyhovělo validaci! Může a nemusí obsahovat předvolbu a části čísla lze dělit mezerou či pomlčkou. např: +420 123 456 789',
                '^((\+[0-9]{1,3}|[(][0-9]{1,3}[)])[ -])?[^- ][- 0-9]{3,16}[^- ]$')
            ->setRequired('Toto pole je povinné')
            ->setHtmlAttribute('class', 'form-control');
        
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
            ->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
        
        $form->addText('timeFrom', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné')
            ->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');

        $form->addText('timeTo', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired('Toto pole je povinné')
            ->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');

        if ($this->adminMode) {
            $form->addCheckbox('gdpr', 'Souhlasím se zpracováním osobních údajů')
                ->setRequired('Musíte souhlasit se zpracováním osobních údajů')
                ->setHtmlAttribute('class', 'form-control');
        }

        $form->addHidden('reservationItem', $this->cParams['reservationItemId']);
        $form->addHidden('customer');
        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');


        if (!$this->adminMode && $customer = $this->getPresenter()->customer) {
            $form->getComponent('name')
                ->setDefaultValue($customer->name)
                ->setHtmlAttribute('class', 'form-control');
            $form->getComponent('surname')
                ->setDefaultValue($customer->surname)
                ->setHtmlAttribute('class', 'form-control');
            $form->getComponent('email')
                ->setDefaultValue($customer->email)
                ->setHtmlAttribute('class', 'form-control');
            $form->getComponent('phone')
                ->setDefaultValue($customer->phone)
                ->setHtmlAttribute('class', 'form-control');
            $form->getComponent('customer')
                ->setDefaultValue($this->getPresenter()->customer->id)
                ->setHtmlAttribute('class', 'form-control');
        }

        /*if (isset($this->cParams['confId'])) {
            $confId = $this->cParams['confId'];
            $form->addHidden('confId', $confId);
            
            if (isset($presenter->sess->confData) && isset($presenter->sess->confData)) {
                $form->getComponent('company')->setValue($presenter->sess->confData['company']);
                $form->getComponent('ico')->setValue($presenter->sess->confData['ico']);
                $form->getComponent('name')->setValue($presenter->sess->confData['name']);
                $form->getComponent('surname')->setValue($presenter->sess->confData['surname']);
                $form->getComponent('email')->setValue($presenter->sess->confData['email']);
                $form->getComponent('phone')->setValue($presenter->sess->confData['phone']);
                $form->getComponent('company')->setValue($presenter->sess->confData['company']);
                $form->getComponent('installCity')->setValue($presenter->sess->confData['installCity']);
                $form->getComponent('installZip')->setValue($presenter->sess->confData['installZip']);
                $control = $form->getComponent('message')->setValue($presenter->sess->confData['message']);
                if ($presenter->sess->confData[$confId]['salesman']) {
                    $control->setRequired('Toto pole je povinné');
                }
            }
        }*/

        $form->onError[] = function($form) {
            $this->presenter->flashMessage($form->errors[0], 'error');
            bdump($form, 'error');
            $cmps = [];
            foreach ($form->getComponents() as $c) {
                $cmps[$c->getName()] = $c->getValue();
            }
            bdump($cmps, 'values');
        };
        $form->onSuccess[] = [$this, 'successReservationForm'];

        return $form;
    }

    /**
     * ACL name='Formulář pro přidání/edit rezervace'
     */
    public function createComponentReservationAdminModalForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Reservation::class, $this->getPresenter()->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit rezervaci', 'success'], ['Nepodařilo se uložit rezervaci!', 'error']);
        
        $presenter = $this->getPresenter();
            
        $form->addText('name', 'Jméno')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('surname', 'Příjmení')
            ->setHtmlAttribute('class', 'form-control');
        $form->addEmail('email', 'E-mail')
            ->setHtmlAttribute('class', 'form-control');
        $form->addText('phone', 'Telefon')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(
                Form::PATTERN,
                'Tel.č. nevyhovělo validaci! Může a nemusí obsahovat předvolbu a části čísla lze dělit mezerou či pomlčkou. např: +420 123 456 789',
                '^((\+[0-9]{1,3}|[(][0-9]{1,3}[)])[ -])?[^- ][- 0-9]{3,16}[^- ]$');
        
        $form->addText('date', 'Datum')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('data-provide', 'datepicker')
            ->setHtmlAttribute('data-date-orientation', 'bottom')
            ->setHtmlAttribute('data-date-format', 'd. m. yyyy')
            ->setHtmlAttribute('data-date-today-highlight', 'true')
            ->setHtmlAttribute('data-date-autoclose', 'true')
            ->setHtmlAttribute('data-date-language', 'cs')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule(UI\Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
        
        $form->addText('timeFrom', 'Od')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');

        $form->addText('timeTo', 'Do')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule(UI\Form::PATTERN, 'Čas musí být ve formátu "15:20"', '(^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9])');

        $form->addCheckbox('newCustomer', 'Nový zákazník')
            ->setHtmlAttribute('class', 'form-control');
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


        $form->onError[] = function($form) {
            $this->presenter->flashMessage($form->errors[0], 'error');
            bdump($form, 'error');
            $cmps = [];
            foreach ($form->getComponents() as $c) {
                $cmps[$c->getName()] = $c->getValue();
            }
            bdump($cmps, 'values');
        };
        $form->onSuccess[] = [$this, 'successReservationForm'];

        return $form;
    }

    public function successReservationForm($form, $values)
    {
        $presenter = $this->getPresenter();
        $values2 = $presenter->getRequest()->getPost();

        $messages = [];
        $reservation = $this->reservationFac->processReservationForm($values, $presenter->user->getId(), $this->adminMode, $messages);


        if ($reservation) {

            if ($this->adminMode) {
                $presenter->flashMessage('Rezervace byla uložena', 'success');
            } else {
                $presenter->flashMessage('Rezervaci se podařilo úspěšně odeslat. Děkujeme.', 'success');
            }
                 
            if ($presenter->isAjax()) {
                $presenter->payload->close = 1;
                $this->redraw();
            } else {
                if ($this->adminMode) {
                    $presenter->redirect('this', ['openTab' => '#ri-tab-' . $reservation->reservationItem->id]);
                } else {
                    $presenter->redirect('Page:default', ['id' => 7, 'openTab' => '#ri-tab-' . $values->reservationItem]);
                }
            }
    
        } elseif (!$messages && !$adminMode) {
            $presenter->flashMessage('Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error');
        }

        foreach ($messages as $m) {
            $this->flashMessage($m[0], $m[1]);
        }


        //$presenter->flashMessage('Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error');

    }

    public function handleCancelReservation($id)
    {
        $presenter = $this->getPresenter();
        if (!$id) {
            $id =  $presenter->getParameter('id');
        }
        $reservation = $this->em->getReservationRepository()->find($id);

        if (!$reservation) {
            $this->flashMessage('Rezervaci se nepodařilo nalézt', 'error');
            return;
        }
        $reservationItem = $reservation->reservationItem;

        if ($reservation = $this->reservationFac->cancelReservation($id, $presenter->user->getId(), $this->adminMode)) {
            $presenter->flashMessage('Rezervace byla zrušena', 'success');
            if ($presenter->isAjax()) {
                $presenter->payload->close = 1;
                $this->redraw();
            } else {
                $presenter->redirect('this', ['openTab' => '#ri-tab-' . $reservationItem->id]);
            }
        } else {
            $presenter->flashMessage('Nepodařilo se zrušit rezervaci', 'error');
        }
    }

    public function handleModalFormEdit() {
        $presenter = $this->getPresenter();
        $values = $presenter->getRequest()->getPost();
        if (isset($values['modal']) && isset($values['id'])) {
            switch ($values['modal']) {
                case 'reservationAdmin':
                    $entity = $this->em->getReservationRepository()->find($values['id']);
                    if ($entity) {
                        $this->template->reservation = $entity;

                        $arr = $this->ed->get($entity);

                        $arr['date'] = ($entity->dateFrom ? $entity->dateFrom->format('j. n. Y') : '');
                        $arr['timeFrom'] = ($entity->dateFrom ? $entity->dateFrom->format('H:i') : '');
                        $arr['timeTo'] = ($entity->dateFrom ? $entity->dateTo->format('H:i') : '');

                        if ($entity->customer) {
                            $this['reservationAdminModalForm']->setAutocmp('customer', $this->em->getCustomerRepository()->getSpecificCustomer($entity->customer));
                        }
                    }
                    break;
                default:
                    return;
            }
            if (!$entity) {
                return;
            }
            //$arr = $this->ed->get($entity);
            $this[$values['modal'] . 'ModalForm']->setDefaults($arr);
            $this->redrawModal($values['modal']);
        }
    }

    public function handleModalFormReset($defValues = []) {
        $presenter = $this->getPresenter();
        $values = $presenter->getRequest()->getPost();
        if (isset($values['modal']) && in_array($values['modal'], ['reservationAdminModal'])) {
            $this->redrawModal($values['modal']);
        }
    }

    public function redraw()
    {
        $presenter = $this->getPresenter();
        $presenter->redrawControl('reservation');
        $presenter->redrawControl('reservationItem-' . $this->cParams['reservationItemId']);
    }

    public function redrawModal($name)
    {
        $presenter = $this->getPresenter();
        $presenter->redrawControl('reservationModal');
        $presenter->redrawControl($name . 'Modal');
        $this->redrawControl($name . 'Modal');
    }

    public function getForm()
    {
        return $this['form'];
    }

    public function handleNextWeek()
    {
        $this->saveSessValue('date', (new \DateTime($this->getSessValue('date')))->modify('+1 week')->format('Y-m-d'));
        $this->redraw();
    }

    public function handlePreviousWeek()
    {
        $this->saveSessValue('date', (new \DateTime($this->getSessValue('date')))->modify('-1 week')->format('Y-m-d'));
        $this->redraw();
    }

    public function handleChangeDate($date)
    {
        if (!$date) {
            $date = $this->getPresenter()->getRequest()->getPost()['date'];
        }
        $date = \DateTime::createFromFormat('j. n. Y', $date);
        $this->saveSessValue('date', $date->format('Y-m-d'));
        $this->redraw();
    }

    private function saveSessValue($key, $value)
    {
        $this->checkSess();
        $this->sess->reservationItems[$this->cParams['reservationItemId']][$key] = $value;
    }

    private function getSessValue($key)
    {
        $this->checkSess();
        return $this->sess->reservationItems[$this->cParams['reservationItemId']][$key];
    }

    private function checkSess()
    {
        if (!isset($this->sess->reservationItems)) {
            $this->sess->reservationItems = [];
        }
        if (!array_key_exists($this->cParams['reservationItemId'], $this->sess->reservationItems)) {
            $this->sess->reservationItems[$this->cParams['reservationItemId']] = [
                'date' => date('Y-m-d')
            ];
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomers($term)
    {
        $presenter = $this->getPresenter();
        $values = $presenter->getParameters();
        if (!isset($values['term'])) {
            return;
        }
        $term = $values['term'];
        $data = isset($values['data']) ? $values['data'] : null;
        $result = $this->em->getCustomerRepository()->getDataAutocompleteCustomers($term, $data);
        $presenter->payload->autoComplete = json_encode($result);
        $presenter->sendPayload();
    }

}
