<?php

declare(strict_types=1);

namespace App\Components\Forms\InquiryForm;


use Nette\Application\UI\Form;
use App\Components\FormRenderer\IFormRendererFactory;
use App\Model\Facade\Configurator as ConfFac;
use App\Model\Facade\Offer as OfferFac;
use Nette;
use Nette\Application\UI;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use App\Components\MailSender\MailSender;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Control that can collect basic inquiry data or also it can be used
 * w/ the configurator where it also processes it's values 
 */
class InquiryFormControl extends UI\Control
{

    /** @var EntityManager */
    private $em;

    /** @var EntityData */
    public $ed;

    /** @var Session */
    public $session;

    /** @var SessionSection */
    public $sess;
    
    /** @var ConfFac */
    public $confFac;

    /** @var OfferFac */
    public $offerFac;

    /** @var IFormRendererFactory */
    public $formRenderFactory;
    
    /** @var MailSender */
    public $mailSender;

    /**
     * @var array
     */
    private $cParams;

    public function __construct(
        EntityManager $em,
        EntityData $ed,
        IFormRendererFactory $formRenderer,
        Session $session,
        ConfFac $confFac,
        OfferFac $offerFac,
        MailSender $mailSender,
        $cParams
    ) {
        $this->em = $em;
        $this->ed = $ed;
        $this->formRenderFactory = $formRenderer;
        $this->session = $session;
        $this->sess = $this->session->getSection('front');
        $this->confFac = $confFac;
        $this->offerFac = $offerFac;
        $this->mailSender = $mailSender;
        $this->cParams = $cParams;
    }

    /**
     * Render component for rendering form in specific style
     */
    public function createComponentRenderer()
    {
        return $this->formRenderFactory->create();
    }

    public function render($confProducts)
    {
        $t = $this->template;
        $t->confProducts = $confProducts;
        $t->setFile(__DIR__ . '/../templates/form.latte');
        $t->form = $this['form'];

        if (isset($this->cParams['hideButtons'])) {
            $t->hideButtons = $this->cParams['hideButtons'];
        }

        if (isset($this->cParams['confId'])) {
            $t->confId = $this->cParams['confId'];
            $t->conf = $this->em->getConfiguratorRepository()->find($t->confId);
            $t->confData = $this->sess->confData;
            $t->currConfData = $this->sess->confData[$t->confId];

            // solve configurator count to know what to display
        }

        $t->render();
    }

    public function createComponentForm()
    {
        $presenter = $this->getPresenter();
        $form = new Nette\Application\UI\Form;
            
        $form->addText('company', 'Firma');
        $form->addText('ico', 'IČO');
        $form->addText('name', 'Jméno')
            ->setRequired('Toto pole je povinné');
        $form->addText('surname', 'Příjmení')
            ->setRequired('Toto pole je povinné');
        $form->addEmail('email', 'E-mail')
            ->setRequired('Toto pole je povinné');
        $form->addText('phone', 'Telefon')
            ->addRule(
                Form::PATTERN,
                'Tel.č. nevyhovělo validaci! Může a nemusí obsahovat předvolbu a části čísla lze dělit mezerou či pomlčkou. např: +420 123 456 789',
                '^((\+[0-9]{1,3}|[(][0-9]{1,3}[)])[ -])?[^- ][- 0-9]{3,16}[^- ]$')
            ->setRequired('Toto pole je povinné');
        $form->addTextArea('message', 'Poptávka')
            ->setHtmlAttribute('placeholder', 'Poptávka...')
            ->setHtmlAttribute('rows', '5');
        $form->addText('installCity', 'Obec místa instalace poptávaných výrobků')
            ->setRequired('Toto pole je povinné');
        $form->addText('installZip', 'PSČ místa instalace poptávaných výrobků')
            ->setRequired('Toto pole je povinné');
        $form->addCheckbox('familyHouse', 'Montáž k rodinnému domu');

        foreach ($form->getComponents() as $comp) {
            $comp->setHtmlAttribute('class', 'form-control');
        }

        if (isset($this->cParams['productId'])) {
            $productId = $this->cParams['productId'];
            $form->addHidden('productId', $productId);
        }

        if (isset($this->cParams['confId'])) {
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
                $form->getComponent('familyHouse')->setValue($presenter->sess->confData['familyHouse']);
            }
        } else {
            $form->getComponent('message')->setRequired('Toto pole je povinné');
        }

        $form->onSuccess[] = [$this, 'successInquiryForm'];

        return $form;
    }

    public function successInquiryForm($form, $values)
    {
        //$values = $this->request->getPost();
        $presenter = $this->getPresenter();


        // prepare inquiry
        $inquiry = new \App\Model\Database\Entity\Inquiry();
        $inquiry->setMessage($values->message);
        $inquiry->setInstallCity($values->installCity);
        $inquiry->setInstallZip($values->installZip);
        $inquiry->setForFamilyHouse($values->familyHouse);
        $this->em->persist($inquiry);
        //$this->em->flush();
        $productColl = new ArrayCollection();
        
        // create customer
        $customer = $this->em->getCustomerRepository()->createQueryBuilder('c')
            ->where('c.email = :email AND (c.name  = :name AND c.surname = :surname AND (c.company = :company OR c.idNo = :ico))')
            ->setParameters(['email' => $values->email, 'name' => $values->name, 'surname' => $values->surname, 'company' => $values->company, 'ico' => $values->ico])
            ->setMaxResults(1)
            ->getQuery()->getResult();

        if (!$customer) {
            $customer = new \App\Model\Database\Entity\Customer();
            $customer->setName($values->name);
            $customer->setSurname($values->surname);
            $customer->setFullname($values->name . ' ' . $values->surname);
            $customer->setEmail($values->email);
            $customer->setPhone($values->phone);
            if ($values->company) {
                $customer->setCompany($values->company);
            }
            if ($values->ico) {
                $customer->setIdNo($values->idNo);
            }
            $customer->setActive(1);
            $customer->setCreatedByInquiry(1);

            $this->em->persist($customer);
            //$this->em->flush();
        } else {
            $customer = $customer[0];
        }
        

        // set cutomer to inq
        $inquiry->setCustomer($customer);
        $inquiry->setCustomerAuto(1);
        
        if (isset($values->confId)) {
            // process configurator if set

            bdump($this->sess->confData[$values->confId]);

            $conf = $this->em->getConfiguratorRepository()->find($values->confId);
            if (!$conf || !isset($this->sess->confData[$conf->id])) {
                $this->presenter->flashMessage('Konfigurátor se nepodařilo odeslat', 'error');
                return;
            }

            if (!isset($values->message) && $this->sess->confData[$conf->id]['salesman']) {
                $this->presenter->flashMessage('Vyplňte zprávu!', 'error');
                return;
            }

            // add configurator to inq
            $inquiry->setConfigurator($conf);
            $inquiry->setNeedsSalesman($this->sess->confData[$conf->id]['salesman'] ? 1 : 0);

            // set input values
            foreach ($this->sess->confData[$conf->id]['inputs'] as $inputId => $inputArr) {
                $inputEnt = $this->em->getConfiguratorInputRepository()->find($inputId);
                if (!$inputEnt) {
                    continue;
                }
                $iv = new \App\Model\Database\Entity\InquiryValue();
                $iv->setName($inputEnt->name);
                $valueNode = $this->em->getConfiguratorNodeRepository()->find($this->sess->confData[$conf->id]['inputs'][$inputId]['value']);
                if (!$valueNode) {
                    continue;
                }
                $iv->setValue($valueNode->value);
                $iv->setInquiry($inquiry);

                $this->em->persist($iv);
            }
            //$this->em->flush();


            // set products
            foreach ($this->sess->confData[$conf->id]['products'] as $key => $product) {
                $productEnt = $this->em->getProductRepository()->find($product['id']);
                if (!$productEnt) {
                    continue;
                }
                $ip = new \App\Model\Database\Entity\InquiryProduct();
                $ip->setInquiry($inquiry);
                $ip->setProduct($productEnt);
                $ip->setCount($product['count']);
                $ip->setPrice($productEnt->evid_cena_pol);
                $ip->setKlic_polozky($productEnt->klic_polozky);

                $this->em->persist($ip);
                $productColl->add($ip);
            }
            

        } else {
            // else process basic inquiry

            $conf = null;
            $inquiry->setNeedsSalesman(1);
        }
        
        // add product if specified (in prod. detail)
        if (isset($values->productId)) {

            // set products
            $productEnt = $this->em->getProductRepository()->find($values->productId);
            if ($productEnt) {
                $ip = new \App\Model\Database\Entity\InquiryProduct();
                $ip->setInquiry($inquiry);
                $ip->setProduct($productEnt);

                $this->em->persist($ip);
                $productColl->add($ip);
            } else {
                $presenter->flashMessage('Produkt se nepodařilo přidat jako součást poptávky', 'error');
            }
        }

        $inquiry->setProducts($productColl);

        bdump($inquiry);
        try {
            $this->em->flush();
        } catch(\Exception $e) {
            $this->presenter->flashMessage('Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error');
            return;
        }
        
        // if not for salesman => register autosend offer
        if (!$inquiry->needsSalesman) {
            $this->offerFac->createOfferFromInquiry($inquiry, 1);
        }
        
        $this->mailSender->setProduction(false);
        if ($this->mailSender->sendInquiry($inquiry->id, $presenter->locale, false)) {
            $this->presenter->flashMessage('Poptávku se podařilo úspěšně odeslat. Děkujeme.', 'success');
            if (isset($values->confId)) {
                $this->handleConfiguratorReset($values->confId);
            }
        } else {
            $this->presenter->flashMessage('Omlouváme se, ale při odesílání došlo k chybě. Opakujte proces nebo nás kontaktujte jiným způsobem viz. menu Kontakty', 'error');
        }

    }

    public function handleConfiguratorUpdate($inputId, $nextNodeId, $confId)
    {
        $presenter = $this->getPresenter();
        $inputId = $presenter->getParameter('inputId');
        $nextNodeId = $presenter->getParameter('nextNodeId');
        $confId = $presenter->getParameter('confId');

        if (!isset($inputId) || !isset($nextNodeId) || !isset($confId)) {
            return;
        }
        if ($nextNodeId === '') {
            $presenter->flashMessage('Musíte vybrat položku', 'warning');
        }
        
        $nextNode = $this->confFac->updateConfigurator($inputId, $nextNodeId, $confId);

        if (!$nextNode) {
            //$this->flashMessage('Při konfigurování došlo k chybě. Opakujte vyplnění, obnovte okno nebo napište poptávku níže.', 'error');
        }

        if ($presenter->isAjax()) {
            $presenter->redrawControl('configurators');
            $presenter->redrawControl('configurator-' . $confId);
        }
    }

    public function handleConfiguratorReset($confId)
    {
        $presenter = $this->getPresenter();

        $conf = $this->em->getConfiguratorRepository()->find($confId);
        if (!$conf) {
            return;
        }

        $this->confFac->prepareConfData($conf);
        if ($presenter->isAjax()) {
            $presenter->redrawControl('configurators');
            $presenter->redrawControl('configurator-' . $confId);
        }
    }

    public function handleConfiguratorForceSalesman($confId) {
        $presenter = $this->getPresenter();

        if (!isset($confId) || !isset($this->sess->confData[$confId])) {
            return;
        }
        $this->sess->confData[$confId]['salesman']++;
        if ($presenter->isAjax()) {
            $presenter->redrawControl('configurators');
            $presenter->redrawControl('configurator-' . $confId);
        }
    }

    public function getForm()
    {
        return $this['form'];
    }
}
