<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Facade\BaseFront;

class HomepagePresenter extends BasePresenter
{
    
    /** @var BaseFront @inject */
    public $facade;

	public function renderDefault()
	{
        $this->template->homepage = true;
        $mainMenu = $this->facade->getMainMenu($this->locale);
        if ($mainMenu) {
            $articles = $this->facade->getArticlesDefaultIDS($mainMenu);
            //$newsMenu = $this->em->getMenuRepository()->find(9);
            $news = $this->facade->getArticlesNews(null, $this->locale, null, 12, 'date', true);
            $templates = $this->facade->getArticlesTemplates($mainMenu, $this->locale);
            $primaryEvents = $this->facade->getArticlesEventsPrimary(null, $this->locale, 3);
            $primaryEventsIds = [];
            foreach ($primaryEvents as $pe) {
                $primaryEventsIds[] = $pe->id;
            }
            $events = array_merge($primaryEvents, $this->facade->getArticlesEvents(null, $this->locale, null,  3, ['date' => 'ASC'], true, [new \DateTime('today')], $primaryEventsIds));
            if (count($events) < 3)  {
                $events = array_merge($events, $this->facade->getArticlesEvents(null, $this->locale, null,  3 - count($events), ['date' => 'DESC'], true, [null, new \DateTime('yesterday')], $primaryEventsIds));
            }
            //$products = $this->facade->getMainProducts($mainMenu, $this->locale);
        } else {
            $articles = [];
            $news = [];
            $templates = [];
            //$products = [];
        }
        $this->template->articles = $articles;
        $this->template->news = $news;
        $this->template->templates = $templates;
        $this->template->events = $events;
        //$this->template->products = $products;
        $this->template->banners = $this->facade->getBanners($this->locale, 'main');
        $this->template->bannerPartners = $this->facade->getBannersPartners($this->locale);
    }
    
    public function renderSitemap()
    {
        $this->template->menu = $this->facade->getMenuForSitemap($this->locale);
        $this->template->locale = $this->locale;
    }

    public function createComponentInquiryForm()
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->addText('name', 'Jméno')->setAttribute('placeholder', 'Jméno')
            ->setRequired();
        $form->addText('address', 'Adresa')->setAttribute('placeholder', 'Adresa')
            ->setRequired();
        $form->addText('phone', 'Telefon')->setAttribute('placeholder', 'Telefon')
            ->setRequired();
        $form->addEmail('email', 'E-mail')->setAttribute('placeholder', 'E-mail')
            ->setRequired();
        $form->addTextArea('inquiry', 'Text poptávky')->setAttribute('placeholder', 'Text poptávky')
            ->setAttribute('style', 'resize: vertical')->setAttribute('rows', '10')
            ->setRequired();
        $form->addSubmit('send', 'Odeslat');
        $form->addButton('clear', 'Smazat')->setHtmlAttribute('type', 'reset');

        $form->onSuccess[] = [$this, 'successInquiry'];

        return $form;
    }

    public function successInquiry(Form $form, $values)
    {
        $this->mailSender->sendInquiry($values, false);
        $this->mailSender->sendInquiry($values, true);
        $this->flashMessage('Děkujeme za zájem o nás.<br>Na Váš dotaz odpovíme co nejdříve.', 'info');
        $this->redirect('this');
    }
}
