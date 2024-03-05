<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Components\Article\IArticleControlFactory;
use App\Components\MailSender\MailSender;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\UblabooTable\Model\DoctrineGridGenerator;
use App\Model\CustomerAuthenticator;
use App\Model\Database\Entity\Customer;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Model\DoctrineDetailGenerator;
use App\Model\Utils\Ares\Ares;
use App\Model\Utils\Ares\AresException;
use App\Model\Facade\BaseFront;
use App\Model\DatabaseResource\DatabaseResource;
//use App\Model\DatabaseTranslator;
//use App\Model\TranslationLoader;
use Contributte\Translation\Translator;
use Ublaboo\ImageStorage\ImageStorage;
use Nette;
use Nette\Application\Helpers;
use Nette\Caching\Cache;
use Nette\Http\SessionSection;
use Nette\Database\Context;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var SessionSection */
    public $sess;

    /** @var DoctrineGridGenerator @inject */
    public DoctrineGridGenerator $gridGen;

    /** @var DoctrineDetailGenerator @inject */
    public DoctrineDetailGenerator $detailGen;

    // /** @var DatabaseTranslator @inject */
    // public $translator;

    // /** @var TranslationLoader @inject */
    // public $translationLoader;

    /** @var Translator @inject */
    public Translator $translator;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var EntityManager @inject */
    public $em;

    /** @var EntityData @inject */
    public $ed;
    
    /** @var Context @inject */
    public $db;

    /** @var Nette\Caching\IStorage @inject */
    public $storage;

    /** @var Nette\Caching\Cache */
    public $cache;

    /** @var Customer */
    public $customer;

    /** @var CustomerAuthenticator @inject */
    public $customerAuthenticator;

    /** @var IArticleControlFactory @inject */
    public $articleFac;

    /** @var IPDFPrinterFactory @inject */
    public $IPDFPrinterFactory;
    
    /** @var array|string[] */
    public array $viewsForNotLoggedUsers = ['login', 'register', 'passwordRecovery', 'newPassword'/*, 'registerSuccess'*/];

    /** @var ImageStorage @inject */
    public $imageStorage;

    /** @var BaseFront @inject */
    public $facade;

    /** @var integer */
    public $menuID = null;

    /** @var boolean */
    public $production;

    /** @var array */
    public $langs;

    /** @var string */
    public $locale;
    
    /** @var integer */
    public $productsMainMenu = 10;

    /**
     * Formats view template file names.
     * @return array<int, string>
     */
    public function formatTemplateFiles(): array
    {
        [, $presenter] = Helpers::splitName($this->getName());
        $dir = dirname(static::getReflection()->getFileName());
        if (stripos($dir, 'App\Presenters\Custom') !== false) {
            $dir = is_dir("$dir/templates/$presenter/") && (
            file_exists("$dir/templates/$presenter/$this->view.latte")
            ||
            file_exists("$dir/templates/$presenter.$this->view.latte")
            ) ? $dir : dirname($dir);
        } else {
            $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        }

        return [
            "$dir/templates/$presenter/$this->view.latte",
            "$dir/templates/$presenter.$this->view.latte",
        ];
    }

    public function checkRequirements($element): void
    {
        $this->getUser()->getStorage()->setNamespace('frontend');
        parent::checkRequirements($element);
    }

    public function startup()
    {
        parent::startup();

        if ($this->getUser()->isLoggedIn()) {
            $this->customer = $this->em->getCustomerRepository()->find($this->getUser()->getId());
        }
        
        $this->sess = $this->session->getSection('front');
        $this->cache = new Nette\Caching\Cache($this->storage);
        $this->production = false;//isset($this->context->parameters['production']) ? $this->context->parameters['production'] : false;
        
        $this->langs = $this->em->getLanguageRepository()->findBy([], ['orderCode' => 'ASC']);
        if (!$this->getParameter('locale')) {
            $this->params['locale'] = 'cs';
        }
        $locale =  $this->getParameter('locale');
        $this->locale = $locale; 
        $this->translator->setLocale($locale);
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout(__DIR__.'/templates/@layout.latte');

        $this->template->production = $this->production;
        $this->template->sess = $this->sess;
        $this->template->settings = $this->facade->getSettings();
        $this->template->webSettings = $this->facade->getWebSettings($this->locale);
        $this->template->imageStorage = $this->imageStorage;
        $this->template->homepage = false;
        //$this->template->productsMenu = $this->productsMainMenu;
        $this->template->mainMenu = $this->facade->getMainMenuUl($this->locale);
        $this->template->locale = $this->locale;
        $this->template->langs = $this->langs;
        $this->template->footerMenu = $this->facade->getFooterMenu($this->locale);
        $this->template->activeMenuArr = $this->facade->getActiveMenu($this->locale);
        $lang = $this->em->getLanguageRepository()->findOneBy(['code' => $this->locale]);
        //$this->template->guideHome = $this->em->getMenuLanguageRepository()->findBy(['showOnHomepage' => 1, 'lang' => $lang],['menu.orderPage' => 'ASC']);
        $qb = $this->em->getMenuLanguageRepository()->createQueryBuilder('ml')
            ->select('ml')
            ->leftJoin('ml.menu', 'm')
            ->where('ml.showOnHomepage = 1 AND ml.lang = :lang')
            ->orderBy('m.orderPage', 'ASC')
            ->setParameters(['lang' => $lang->id]);
        $this->template->guideHome = $qb->getQuery()->getResult();
        $this->template->aMenu = str_replace('Intra:', '', $this->getName());
        $this->template->aSubMenu = $this->getRequest()->getParameter('slug');
        $this->template->aSubView = $this->getView();
        /*$this->template->reservationMenu = $this->em->getMenuLanguageRepository()->createQueryBuilder('m')
            ->leftJoin("m.lang","l")->where("m.menu=7 and l.code=:locale")->setParameters(["locale" => $this->locale])->getQuery()->getSingleResult();*/
        $this->template->customer = $this->customer;
    }

    public function afterRender()
    {
        parent::afterRender();
        if ($this->isAjax() && $this->hasFlashSession()) {
            $this->redrawControl('flashess');
        }
    }

    protected function createComponentArticle()
    {
        return $this->articleFac->create();
    }

    public function handleLogout()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout(true);
            $this->redirect('this');
        }
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomersAres($term)
    {
        try {
            $maxRecords = 10;
            $records = [];
            if (intval($term) > 0) {
                $records[] = $this->ares->findByIdentificationNumber($term); // instance of AresRecord
            } else {
                $records = $this->ares->findByName($term);
            }

            $arr = [];

            foreach ($records as $record) {
                if (!$record) {
                    continue;
                }
                $arr[] = [
                    ($record->getCompanyName() . ' (IČ: ' . $record->getCompanyId() . '), <br /> ' . $record->getStreetWithNumbers() . '<br />' . $record->getZip().' '.$record->getTown()),
                    [
                        $record->getCompanyName(),
                        $record->getCompanyId(),
                        $record->getTaxId(),
                        $record->getStreetWithNumbers(),
                        $record->getTown(),
                        $record->getZip(),
                    ],
                    '1'
                ];
            }

            $this->payload->autoComplete = json_encode($arr);
        } catch (AresException $ex) {
            $this->payload->autoComplete = json_encode([]);
        }
        $this->sendPayload();
    }
}
