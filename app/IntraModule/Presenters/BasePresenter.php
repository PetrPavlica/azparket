<?php
declare(strict_types=1);

namespace App\IntraModule\Presenters;

use App\Components\ACLHtml\IACLHtmlControlFactory;
use App\Components\FormRenderer\IFormRendererFactory;
use App\Components\MailSender\MailSender;
use App\Components\PDFPrinter\IPDFPrinterFactory;
use App\Components\PDFPrinter\PDFPrinterControl;
use App\Components\UblabooTable\Model\DoctrineGridGenerator;
use App\Model\ACLMapper;
use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\PermissionRule;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Model\DoctrineFormGenerator;
use App\Model\Facade\BaseIntra;
use App\Model\Facade\BaseFront;
use App\Model\Facade\Process;
use Contributte\Translation\Translator;
use Defr\Ares;
use Nette;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Http\SessionSection;
use Nette\Database\Context;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{    
    /** @var DoctrineGridGenerator @inject */
    public DoctrineGridGenerator $gridGen;

    /** @var DoctrineFormGenerator @inject */
    public DoctrineFormGenerator $formGenerator;

    /** @var Translator @inject */
    public Translator $translator;

    /** @var Storage @inject */
    public Storage $storage;

    /** @var SessionSection */
    public SessionSection $sess;

    /** @var SessionSection */
    public SessionSection $baseSess;

    /** @var Cache */
    public Cache $cache;

    /** @var EntityManager @inject */
    public $em;

    /** @var EntityData @inject */
    public $ed;
    
    /** @var Context @inject */
    public $db;

    /** @var ACLMapper @inject */
    public $acl;

    /** @var IACLHtmlControlFactory @inject */
    public $IACLControlFactory;

    /** @var IPDFPrinterFactory @inject */
    public $IPDFPrinterFactory;

    /** @var PDFPrinterControl @inject */
    public $pdfPrinter;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var Process @inject */
    public $processFac;

    /** @var BaseIntra @inject */
    public $baseIntraFac;

    /** @var BaseFront @inject */
    public $baseFrontFac;

    /** @var IFormRendererFactory @inject */
    public $formRenderFactory;

    public $usrGrp;

    public $locale;

    /** @var array */
    public $langs;

    protected function createComponentAcl()
    {
        return $this->IACLControlFactory->create();
    }

    public function createComponentRenderer()
    {
        return $this->formRenderFactory->create();
    }

    protected function createComponentPDFPrinter()
    {
        return $this->IPDFPrinterFactory->create();
    }

    public function checkRequirements($element): void
    {
        $this->getUser()->getStorage()->setNamespace('backend');
        parent::checkRequirements($element);
    }

    protected function startup()
    {
        parent::startup();
        if (!$this->getUser()->isLoggedIn() && (
                $this->getPresenter()->getName() != 'Intra:Sign' ||
                ($this->getPresenter()->getName() != 'Intra:Sign' && $this->getPresenter()->getAction() != 'default')
            )
        ) {
            $this->redirect('Sign:');
        }
        $this->sess = $this->session->getSection('backend');
        $this->baseSess = $this->session->getSection('base');
        $this->cache = new Cache($this->storage);
        $this->usrGrp = $this->user->identity ? $this->user->identity->data[ 'group' ] : 0;

        $this->langs = $this->em->getLanguageRepository()->findBy([], ['orderCode' => 'ASC']);
        if ($this->langs) {
            //$this->translator->addLoader('database', $this->translationLoader);
            //$this->translator->addResource('database', DatabaseResource::class, $this->locale, 'messages');
            if (!isset($this->locale) || empty($this->locale)) {
                $this->translator->setLocale('cs');
                $this->locale = 'cs';
            }
        }

        /** only in editMode prepare data for sidebar permision form */
        if (isset($this->baseSess->editMode) && $this->baseSess->editMode != false) {
            $this->template->editMode = true;
            $this->template->group = $group = $this->em->getPermissionGroupRepository()->find($this->baseSess->editMode);
            $this[ 'permissionRule' ]->setDefaults($group->getRules());
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->locale = $this->locale;
        $this->template->langs = $this->langs;

        $this->template->sess = $this->sess;
        $this->template->aMenu = str_replace('Intra:', '', $this->getName());
        $this->template->aSubMenu = $this->getRequest()->getParameter('slug');
        $this->template->aSubView = $this->getView();
        $this->template->visitStates = $this->em->getVisitStateRepository()->findBy(['active' => true], ['stateOrder' => 'ASC', 'name' => 'ASC']);
        $this->template->visitStateCounts = $this->baseIntraFac->getVisitStateCount($this->user->getId());
        $this->template->visitProcessStates = $this->em->getVisitProcessStateRepository()->findBy(['active' => true], ['stateOrder' => 'ASC', 'name' => 'ASC']);
        $this->template->visitProcessStateCounts = $this->baseIntraFac->getVisitProcessStateCount($this->user->getId());
        $this->template->absenceStates = $this->em->getAbsenceStateRepository()->findBy([], ['stateOrder' => 'ASC', 'name' => 'ASC']);
        $this->template->absenceStateCounts = $this->baseIntraFac->getAbsenceStateCount($this->user->getId());
        $this->template->approveStates = $this->em->getApproveStateRepository()->findBy(['active' => true], ['order' => 'ASC', 'name' => 'ASC']);
        $this->template->processStates = $this->em->getProcessStateRepository()->findBy(['active' => true], ['order' => 'ASC', 'name' => 'ASC']);
        $this->template->processStateCounts = $this->processFac->getStateCount();
        $createProcessSlug = null;
        $this->template->usrGrp = $this->user->identity ? $this->user->identity->data[ 'group' ] : 0;
        $this->template->usrId = $this->user->identity ? $this->user->identity->data[ 'id' ] : 0;
        $usrMenu = '{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1,"10":1,"11":1,"12":1,"13":1,"14":1,"15":1,"16":1,"17":1,"18":1,"19":1,"20":1}';
        if($this->user->identity) {
            $entity = $this->em->getUserRepository()->find($this->user->getId());
            if($entity->menu) {
                $usrMenu = $entity->menu;
            }
        }
        $this->template->usrMenu = json_decode($usrMenu, true);
        $processState = $this->em->getProcessStateRepository()->findOneBy(['active' => true], ['order' => 'ASC']);
        if ($processState) {
            $createProcessSlug = $processState->slug;
        }
        $this->template->createProcessSlug = $createProcessSlug;
    }

    public function afterRender()
    {
        parent::afterRender();
        if ($this->isAjax() && $this->hasFlashSession()) {
            $this->redrawControl('flashess');
        }
    }

    public function handleLogout(): void
    {
        if ($this->getUser()->isLoggedIn()) {
            unset($this->baseSess->editMode);
            $this->getUser()->logout(true);
            $this->redirect('Sign:');
        }
    }

    public function createComponentPermissionRule() {
        //IF editMode is turn off - dont create this form
        if (!$this->baseSess->editMode)
            return NULL;

        $form = new Form;

        $presenter = get_class($this);
        $presenter = str_replace('\\', '_', $presenter);
        $presenter = str_replace('App_IntraModule_Presenters_', '', $presenter);
        $items = $this->em->createQueryBuilder('i')
            ->select('i')
            ->from(PermissionItem::class, 'i')
            ->where('i.name LIKE :presenter')
            ->orWhere('i.type = :menu')
            ->orWhere('i.type = :global')
            ->setParameters([
                'presenter' => $presenter . '%',
                'menu' => PermissionItem::TYPE_MENU,
                'global' => PermissionItem::TYPE_GLOBAL_ELEMENT
            ])
            ->getQuery()
            ->getResult();

        foreach ($items as $item) {
            switch ($item->type) {
                case 'presenter':
                case 'form' :
                case 'action' :
                    $form->addSelect($item->name, $item->caption, [
                        'all' => 'Vše',
                        'read' => 'Vše pro čtení',
                        'show' => 'Vlastní nastavení'
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setHtmlAttribute('acl-type', $item->type);
                    break;
                case 'form-element' :
                    $form->addSelect($item->name, $item->caption, [
                        'write' => 'Zobrazen pro zápis',
                        'read' => 'Zobrazen pro čtení',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setHtmlAttribute('acl-type', $item->type);
                    break;
                case 'element' :
                case 'global-element' :
                    $form->addSelect($item->name, $item->caption, [
                        'show' => 'Zobrazit',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setHtmlAttribute('acl-type', $item->type);
                    break;
                case 'menu' :
                case 'method' :
                    $form->addSelect($item->name, $item->caption, [
                        'show' => 'Zpřístupnit',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setHtmlAttribute('acl-type', $item->type);
                    break;
                default:
                    break;
            }
        }
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = function($form, $values) {
            $group = $this->em->getPermissionGroupRepository()->find($this->user->identity->group);
            foreach ($values as $name => $value) {
                $rule = $this->em->getPermissionRuleRepository()->findOneBy([
                    'item' => $name,
                    'group' => $group->id
                ]);

                if ($rule) { /* Check if rule exist */
                    if ($value) {
                        $rule->setAction($value);
                        $this->em->flush($rule);
                    } else { /* if item type is NULL - remove this rule */
                        $this->em->remove($rule);
                        $this->em->flush($rule);
                    }
                } else { /* if item not exist - create new */
                    if ($value) {
                        $item = $this->em->getPermissionItemRepository()->findOneBy(['name' => $name]);
                        /* in inserted item is menu - you must insert read rule for presenter */
                        if ($item->type == PermissionItem::TYPE_MENU) {
                            $presenterName = explode('__', $name)[0];
                            $presenterRule = new PermissionRule();
                            $presenterRule->setGroup($group);
                            $presenterRule->setItem($presenterName);
                            $presenterRule->setAction(PermissionRule::ACTION_ALL);
                            $this->em->persist($presenterRule);
                        }
                        $rule = new PermissionRule();
                        $rule->setGroup($group);
                        $rule->setItem($name);
                        $rule->setAction($value);
                        $this->em->persist($rule);
                        $this->em->flush($rule);
                    }
                }
            }
            $this->user->identity->roles = $group->getRules();
        };
        return $form;
    }

    public function handleEditMode($id) {
        if (is_numeric($id) && $id != 0 && $id != 1) {
            $this->baseSess->editMode = $id;

            /* Swap entity roles */
            $this->sess->oldGroup = $this->user->identity->data[ 'group' ];
            $this->sess->oldRoles = $this->user->identity->roles;
            // swap entity permision
            $this->user->identity->group = (integer)$id;
            $group = $this->em->getPermissionGroupRepository()->find($id);
            $this->user->identity->roles = $group->getRules();

            $this->flashMessage("Editační mód byl zapnut", 'notice');
        } else {
            $this->baseSess->editMode = false;
            if ($id == 1)
                $this->flashMessage("Editační mód nelze zapnout pro administratorskou roli!", 'warning');
            else
                $this->flashMessage("Editační mód byl vypnut", 'notice');

            /* Return entity roles and group if isset */
            if (isset($this->sess->oldGroup)) {
                $this->user->identity->group = $this->sess->oldGroup;
                unset($this->sess->oldGroup);
            }
            if (isset($this->sess->oldRoles)) {
                $this->user->identity->roles = $this->sess->oldRoles;
                unset($this->sess->oldRoles);
            }
        }
        $this->redirect('this');
    }

    /**
     * Get customers for autocomplete
     * @param string $term
     */
    public function handleGetCustomersAres($term)
    {
        try {
            $ares = new Ares();
            $records = [];
            if (intval($term) > 0) {
                $records[] = $ares->findByIdentificationNumber($term); // instance of AresRecord
            } else {
                try {
                    $companies = $ares->findByName($term);
                    if ($companies) {
                        foreach ($companies as $c) {
                            $records[] = $ares->findByIdentificationNumber(intval($c->getCompanyId()));
                        }
                    }
                } catch (\Exception $ex) {

                }
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
        } catch (Ares\AresException $ex) {
            $this->payload->autoComplete = json_encode([]);
        }
        $this->sendPayload();
    }

    public function handleDataSource($dataSource)
    {
        $term = $this->request->getParameters()['term'];
        $dataSource = 'handle' . ucfirst($dataSource);

        return $this->$dataSource($term);
    }

    public function handleMenuToggle(): void
    {
        $values = $this->request->getPost();
        if($values['item']) {
            $item = intval($values['item']);
            $usrMenu = '{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1,"10":1,"11":1,"12":1,"13":1,"14":1,"15":1,"16":1,"17":1,"18":1,"19":1,"20":1}';
            $entity = $this->em->getUserRepository()->find($this->user->getId());
            if($entity->menu) {
                $usrMenu = $entity->menu;
            }
            $usrMenu = json_decode($usrMenu, true);
            $usrMenu[$item] = $usrMenu[$item] ? 0 : 1;
            $entity->setMenu(json_encode($usrMenu));
            $this->em->flush($entity);
        }
    }

    public function actionTestApi()
    {
        //$url = 'https://www74.webrex.eu/db-jimky/api/v2/openapi?token=N1qHiec9VnuEYsn4pVX9';
        //$url = 'https://www74.webrex.eu/db-jimky/api/v1/product?key=?token=N1qHiec9VnuEYsn4pVX9';
        //$url = 'localhost/db-jimky/api/v1/offer?new=0&token=N1qHiec9VnuEYsn4pVX9';
        $url = 'localhost/db-jimky/api/v2/offer/8?token=N1qHiec9VnuEYsn4pVX9';
        //$url = 'localhost/db-jimky/api/v1/openapi?token=N1qHiec9VnuEYsn4pVX9';

        $curl = curl_init();
        //$qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data",
                "cache-control: no-cache"
            ),
        )); //  ..
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }
        echo($response);
        $this->terminate();
        //$this->setLayout('default');
    }

    public function actionTestApiUpdate()
    {
        //$url = 'https://www74.webrex.eu/db-jimky/api/v1/openapi?token=N1qHiec9VnuEYsn4pVX9';
        //$url = 'https://www74.webrex.eu/db-jimky/api/v1/product?key=?token=N1qHiec9VnuEYsn4pVX9';
        //$url = 'localhost/db-jimky/api/v1/offer?new=0&token=N1qHiec9VnuEYsn4pVX9';
        $url = 'localhost/db-jimky/api/v2/offer/4?new=1&token=N1qHiec9VnuEYsn4pVX9';

        $curl = curl_init();
        //$qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data",
                "cache-control: no-cache"
            ),
        )); //  ..
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }
        echo($response);
        $this->terminate();
        //$this->setLayout('default');
    }

    public function actionTestApiProduct()
    {
        $url = 'localhost/db-jimky/api/v2/product/1799?token=N1qHiec9VnuEYsn4pVX9';

        $curl = curl_init();
        //$qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data",
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }
        echo($response);
        $this->terminate();
        //$this->setLayout('default');
    }

    public function actionTestApiUpdateProduct()
    {
        //$url = 'localhost/db-jimky/api/v2/product/1799?token=N1qHiec9VnuEYsn4pVX9';
        $url = 'https://www74.webrex.eu/db-jimky/api/v2/product/1799?token=N1qHiec9VnuEYsn4pVX9';

        $data = [
            'atribut2' => 'test'
        ];

        $curl = curl_init();
        //$qParams = http_build_query(array('page' => $failSafePage, 'limit' => 1000));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: multipart/form-data",
                "cache-control: no-cache"
            ),
            CURLOPT_POSTFIELDS => json_encode($data, JSON_PRESERVE_ZERO_FRACTION)
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        $status = $info['http_code'];
        $data = null;
        if ($status == 200) {
            $data = json_decode($response, true);
        } else if ($status == 0) {
            $this->flashMessage('API není momentálně dostupná.', 'warning');
        } else {
            $this->flashMessage('Odpověď z API není správná.', 'error');
        }
        bdump($response);
        echo($response);
        $this->terminate();
        //$this->setLayout('default');
    }

    public function actionTestSetAtribute2() {


        $map = [
            'atribut2' => 'atribut2'
        ];

        $ent = $this->em->getProductRepository()->findOneBy(['klic_polozky' => 1799]);
        
        $ent->{$map['atribut2']} = 'tt';
        
        //$ent->setAtribut2('t');
        $this->em->flush();
    }


}
