<?php

namespace App\Presenters;

use Nette;
use Nette\Utils\Paginator;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use App\Model\Facade\Offer as OfferFac;
use App\Model\Facade\Configurator as ConfiguratorFac;
use App\Components\Forms\InquiryForm\IInquiryFormControlFactory;
use App\Components\Reservation\IReservationControlFactory;
use App\Model\Utils\TimeHelper;

class PagePresenter extends BasePresenter
{

    /** @var IInquiryFormControlFactory @inject */
    public $inquiryFormFac;

    /** @var IReservationControlFactory @inject */
    public $reservationFac;

    /** @var OfferFac @inject */
    public $offerFac;
    
    /** @var ConfiguratorFac @inject */
    public $confFac;

    /** @var TimeHelper @inject */
    public $timeHelper;
    
    /** @var integer */
    private $limit = 20;

    /** @var array */
    private $pagerMenu = [];

    private $eventDates = [];

    public function renderDefault($id, $page = 1,$eventPage = 1)
    {
        $menu = $this->facade->getMenu($id, $this->locale);
        if ($menu) {
            $this->template->menu = $menu;
            $this->menuID = $id;
            list($menuIds, $menuArr) = $this->facade->getStructureMenu($menu->id, $this->locale);
            /*if (in_array($this->productsMainMenu, $menuIds) && $this->productsMainMenu != $menu->id) {
                $this->template->products = $this->facade->getProducts($menuIds, $this->locale);
            }*/
            if ($menu->show_signpost || in_array($this->productsMainMenu, $menuIds)) {
                $this->template->parentMenu = $parentMenu = $this->facade->getParentMenu($menu->id, $this->locale);
                $this->template->guideMenu = $menu;
                if (count($parentMenu) == 0 && in_array(6, $menuIds)) {
                    $this->template->parentMenu = $this->facade->getParentMenu($menuArr[0]->id, $this->locale);
                    $this->template->guideMenu = $menuArr[0];
                }
            }
            $this->template->banners = $this->facade->getBanners($this->locale, 'submain');
            $this->template->events = $this->facade->getArticlesEvents($menu, $this->locale, null, $this->limit, 'date', true);
            if ($id == 10) {
                // event big calendar page

                if (!$this->eventDates) {
                    $this->eventDates[0] = new \DateTime('today');
                }
                $endDate = new \DateTime();
                $endDate->modify('+12 months');
                $endDate->modify('last day of this month');
                $this->eventDates[1] = $endDate;
                $this->template->eventStartDate = $this->eventDates[0];
                $order = ['date' => 'ASC'];
                $limit = 24;

                $eventCount = count($this->facade->getArticlesEventsAndNews(0, $this->locale, null, null, [], true, $this->eventDates));

                if ($eventCount) {
                    $eventPaginator = new Paginator();
                    $eventPaginator->setItemCount($eventCount);
                    $eventPaginator->setItemsPerPage($limit);
                    $eventPaginator->setPage($eventPage);
                    $this->template->paginator = $eventPaginator;
                    $events = $this->facade->getArticlesEventsAndNews(0, $this->locale, $eventPaginator->getOffset(), $limit, $order, true, $this->eventDates);
                } else {
                    $events = [];
                }
                
                $eventIDs = [];
                foreach ($events as $e) {
                    $eventIDs[$e->id] = $e['type'];
                }
                $this->template->articlesTop = $eventIDs;
                
            } else if ($id == 111) {
                // event list
                $order = ['date' => 'DESC'];
                $limit = $this->limit;

                $eventCount = count($this->facade->getArticlesEvents(0, $this->locale, null, null, [], true, $this->eventDates));

                if ($eventCount) {
                    $eventPaginator = new Paginator();
                    $eventPaginator->setItemCount($eventCount);
                    $eventPaginator->setItemsPerPage($limit);
                    $eventPaginator->setPage($eventPage);
                    $this->template->paginator = $eventPaginator;
                    $events = $this->facade->getArticlesEvents(0, $this->locale, $eventPaginator->getOffset(), $limit, $order, true, $this->eventDates);
                } else {
                    $events = [];
                }
                
                $eventIDs = [];
                foreach ($events as $e) {
                    $eventIDs[$e->id] = 'event';
                }
                $this->template->articlesTop = $eventIDs;
            } else if ($id == 28) { // 34, 80, 81, 112, 37, 82, 83, 74, 84, 85, 75, 47
                // news
                $order = 'date';
                $newCount = count($this->facade->getArticlesNews($menu, $this->locale, null, null));
                
                if ($newCount) {
                    $newPaginator = new Paginator();
                    $newPaginator->setItemCount($newCount);
                    $newPaginator->setItemsPerPage($this->limit);
                    $newPaginator->setPage($page);
                    $this->template->paginator = $newPaginator;
                    $news = $this->facade->getArticlesNews($menu, $this->locale, $newPaginator->getOffset(), $this->limit, $order, true);
                } else {
                    $news = [];
                }
                
                $newsIDs = [];
                foreach ($news as $e) {
                    $newsIDs[$e->id] = 'new';
                }
                $this->template->articlesTop = $newsIDs;

            } else { 
                $articlesTop = $this->facade->getArticlesTopIDS($menu);
                $this->template->articlesBottom = $this->facade->getArticlesBottomIDS($menu);
                
                $this->template->articlesBottom = $this->facade->getArticlesBottomIDS($menu);
                /*$this->template->articlesTop = $this->facade->getArticlesTop($menu, $this->locale);
                $this->template->articlesBottom = $this->facade->getArticlesBottom($menu, $this->locale);*/
                $paginator = new Paginator();
                $paginator->setItemCount(count($articlesTop));
                $paginator->setItemsPerPage($this->limit);
                $paginator->setPage($page);
                $this->template->paginator = $paginator;
                $this->template->pagerMenu = $this->pagerMenu;
                if (in_array($menu->id, $this->pagerMenu)) {
                    $this->template->articlesTop = $this->facade->getArticlesTopIDS($menu, $paginator->getOffset(), $this->limit);
                } else {
                    $this->template->articlesTop = $articlesTop;
                }
            }
            $this->template->pagerMenu = $this->pagerMenu;
                
            // callendar date range arr
            $callendarRange = [
                (new \DateTime('-3 months'))->modify('first day of this month'),
                (new \DateTime('+12 months'))->modify('last day of this month')
            ];
            $eventsCalendar = $this->facade->getArticlesEvents(0, $this->locale, null, null, [], true, $callendarRange);
            $eventsCalendarArr = [];
            foreach ($eventsCalendar as $e) {
                $eventsCalendarArr[$e->date_start->format('Y-m-d')] = [
                    'title' => $e->name,
                    'id' => $e->id,
                    'slug' => \Nette\Utils\Strings::webalize($e->name)
                ];
            }
            $this->template->eventsCalendar = json_encode($eventsCalendarArr);

            $newsCalendar = $this->facade->getArticlesNews(0, $this->locale, null, null, null, true, $callendarRange, true);
            $newsCalendarArr = [];
            foreach ($newsCalendar as $e) {
                $newsCalendarArr[$e->date_start->format('Y-m-d')] = [
                    'title' => $e->name,
                    'id' => $e->id,
                    'slug' => \Nette\Utils\Strings::webalize($e->name)
                ];
            }
            $this->template->newsCalendar = json_encode($newsCalendarArr);
        } else {
            $this->flashMessage('Hledaná stránka neexistuje!', 'warning');
            $this->redirect('Homepage:default');
        }
    }

    public function createComponentFilterForm()
    {
        $form = new Nette\Application\UI\Form();
        $form->addSubmit('sendFilter')->setAttribute('class', 'ajax')->setAttribute('style', "display: none");

        $form->onSuccess[] = [$this, 'filterSucc'];

        return $form;
    }

    public function filterSucc($form, $values)
    {
        $values2 = $this->request->getPost();
        if (!isset($this->sess->filtersVal)) {
            $this->sess->filtersVal = [];
        }
        $this->sess->filtersVal = array_replace($this->sess->filtersVal, $values2);

        if ($this->isAjax()) {
            $this->redrawControl('filters');
            $this->redrawControl('list');
        }
    }

    public function handleShowAll()
    {
        $this->showAll = true;
        if ($this->isAjax()) {
            $this->redrawControl('filters');
            $this->redrawControl('list');
        }
    }

    public function createComponentContactForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addHidden('type');
        $form->addText('name', 'Jméno a přijmení')
            ->setRequired('Toto pole je povinné.');
        $form->addText('address', 'Adresa')
            ->setRequired('Toto pole je povinné.');
        $form->addText('phone', 'Telefon')
            ->setRequired('Toto pole je povinné.');
        $form->addEmail('email', 'E-mail')
            ->setRequired('Toto pole je povinné.');
        $form->addTextArea('inquiry', 'Dotaz')
            ->setAttribute('style', 'resize: vertical')
            ->setRequired('Toto pole je povinné.');
        $form->addUpload('file', 'Přiložený soubor');
        $form->addCheckbox('gdpr', ' Souhlasím se zpracováním osobních údajů v souladu se zákonem o ochranně osobních údajů (tzv. GDPR)')
            ->setRequired('Toto pole je povinné.');
        $form->addSubmit('send', 'Odeslat');
        $form->addButton('clear', 'Smazat')->setHtmlAttribute('type', 'reset');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onSuccess[] = [$this, 'successContactForm'];

        return $form;
    }

    public function successContactForm(Form $form, $values)
    {
        $this->mailSender->setProduction(false);
        $this->mailSender->sendContactUs($values, $this->locale, false);
        $this->mailSender->sendContactUs($values, $this->locale, true);
        $this->flashMessage('Zpráva byla zaslána', 'success');
        $this->redirect('this');
    }

    public function createComponentReservationForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addText('name', 'Označení akce')
            ->setRequired('Toto pole je povinné.');
        $form->addText('bookerName', 'Jméno a přijmení')
            ->setRequired('Toto pole je povinné.');
        $form->addText('address', 'Adresa')
            ->setRequired('Toto pole je povinné.');
        $form->addText('phone', 'Telefon')
            ->setRequired('Toto pole je povinné.');
        $form->addEmail('email', 'E-mail')
            ->setRequired('Toto pole je povinné.');
        $form->addTextArea('inquiry', 'Dotaz')
            ->setAttribute('style', 'resize: vertical')
            ->setRequired('Toto pole je povinné.');
        $form->addUpload('file', 'Přiložený soubor');
        $form->addCheckbox('gdpr', ' Souhlasím se zpracováním osobních údajů v souladu se zákonem o ochranně osobních údajů (tzv. GDPR)')
            ->setRequired('Toto pole je povinné.');
        $form->addText('dateFrom', 'Datum od')
            ->setHtmlAttribute('data-provide', 'datepicker')
            ->setHtmlAttribute('data-date-orientation', 'bottom')
            ->setHtmlAttribute('data-date-format', 'd. m. yyyy')
            ->setHtmlAttribute('data-date-today-highlight', 'true')
            ->setHtmlAttribute('data-date-autoclose', 'true')
            ->setHtmlAttribute('data-date-language', 'cs')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setRequired('Toto pole je povinné.')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
        $form->addText('dateTo', 'Datum do')
            ->setHtmlAttribute('data-provide', 'datepicker')
            ->setHtmlAttribute('data-date-orientation', 'bottom')
            ->setHtmlAttribute('data-date-format', 'd. m. yyyy')
            ->setHtmlAttribute('data-date-today-highlight', 'true')
            ->setHtmlAttribute('data-date-autoclose', 'true')
            ->setHtmlAttribute('data-date-language', 'cs')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setRequired('Toto pole je povinné.')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})');
        $form->addSubmit('send', 'Odeslat');
        $form->addButton('clear', 'Smazat')->setHtmlAttribute('type', 'reset');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onSuccess[] = [$this, 'successReservationForm'];

        return $form;
    }

    public function successReservationForm(Form $form, $values)
    {
        $this->mailSender->setProduction(false);
        $this->mailSender->sendHallReservation($values, $this->locale, false);
        $this->mailSender->sendHallReservation($values, $this->locale, true);
        $this->flashMessage('Rezervace byla zaslána', 'success');
        $this->redirect('this');
    }

    // commented bcs old / unused

    /*public function createComponentInquiryForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addText('company', 'Firma')->setAttribute('placeholder', 'Firma')
            ->setRequired('Toto pole je povinné.');
        $form->addText('contactPerson', 'Kontaktní osoba')->setAttribute('placeholder', 'Kontaktní osoba')
            ->setRequired('Toto pole je povinné.');
        $form->addText('address', 'Adresa')->setAttribute('placeholder', 'Adresa')
            ->setRequired('Toto pole je povinné.');
        $form->addText('phone', 'Telefon')->setAttribute('placeholder', 'Telefon')
            ->setRequired('Toto pole je povinné.');
        $form->addEmail('email', 'E-mail')->setAttribute('placeholder', 'E-mail')
            ->setRequired('Toto pole je povinné.');
        $form->addTextArea('inquiry', 'Dotaz')->setAttribute('placeholder', 'Text poptávky')
            ->setAttribute('style', 'resize: vertical')->setAttribute('rows', '10')
            ->setRequired('Toto pole je povinné.');
        $form->addSubmit('send', 'Odeslat');
        $form->addButton('clear', 'Smazat')->setHtmlAttribute('type', 'reset');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onSuccess[] = [$this, 'successInquiry'];

        return $form;
    }

    public function successInquiry(Form $form, $values)
    {
        $this->mailSender->sendInquiry($values, false);
        $this->mailSender->sendInquiry($values, true);
        $this->flashMessage('Děkujeme za zájem o nás.<br>Na Váš dotaz odpovíme co nejdříve.', 'info');
        $this->redirect('this');
    }*/

    /*public function createComponentQuestionForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addText('name', 'Jméno')->setAttribute('placeholder', 'Jméno')
            ->setRequired('Toto pole je povinné.');
        $form->addText('lastName', 'Příjmení')->setAttribute('placeholder', 'Příjmení')
            ->setRequired('Toto pole je povinné.');
        $form->addText('phone', 'Telefon')->setAttribute('placeholder', 'Telefon')
            ->setRequired('Toto pole je povinné.');
        $form->addEmail('email', 'E-mail')->setAttribute('placeholder', 'E-mail')
            ->setRequired('Toto pole je povinné.');
        $form->addTextArea('inquiry', 'Dotaz')->setAttribute('placeholder', 'Text dotazu')
            ->setAttribute('style', 'resize: vertical')->setAttribute('rows', '5')
            ->setRequired('Toto pole je povinné.');
        $form->addSubmit('send', 'Odeslat');
        $form->addButton('clear', 'Smazat')->setHtmlAttribute('type', 'reset');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onSuccess[] = [$this, 'successQuestion'];

        return $form;
    }

    public function successQuestion(Form $form, $values)
    {
        $this->mailSender->sendQuestion($values, false);
        $this->mailSender->sendQuestion($values, true);
        $this->flashMessage('Děkujeme za Váš zájem.<br>Na Váš dotaz odpovíme co nejdříve.', 'info');
        $this->redirect('this');
    }*/

    protected function createComponentInquiryForm(): Multiplier
    {
        return new Multiplier(function ($confId) {
            return $this->inquiryFormFac->create(['confId' => $confId]);
        });
    }

    public function handleGetEvent() {
        $value = $this->getRequest()->getPost();
        if (isset($value['str'])) {
            $value = $value['str'];
            $date = new \DateTime($value);
            if ($date) {
                $this->eventDates = [$date, clone $date]; 
            }
        }
        $this->redrawControl('eventsWrap');
        $this->redrawControl('events');
    }

}
