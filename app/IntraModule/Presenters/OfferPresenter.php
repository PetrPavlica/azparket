<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\PermissionItem;
use App\Model\Database\Entity\OfferPart;
use App\Model\Facade\Offer;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Nette\Utils\DateTime;
use App\Model\ACLForm;
use Nette\Application\UI\Form;
use App\Model\Utils\GoogleMaps;

class OfferPresenter extends BasePresenter
{

    /** @var Offer @inject */
    public $offerFac;

    /** @var GoogleMaps @inject */
    public $googleMaps;

    /**
     * ACL name='Správa nabídek'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }
    
    public function renderDefault() {
        if (isset($this->params['openTab'])) {
            $this->template->openTab = $this->params['openTab'];
        } else {
            $this->template->openTab = null;
        }
    }

    public function renderEdit($id)
    {
        if ($id) {
            $entity = $this->em->getOfferRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Offer:');
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['form']->setDefaults($arr);

            $customerValue = $this->processFac->getSpecificCustomer($entity->customer);
            if ($customerValue) {
                $this['form']->setAutocmp('customer', $customerValue);
            }

            if ($entity->salesman) {
                $this['form']->setAutocmp('salesman', $entity->salesman->name . ', (' . $entity->salesman->email . ')');
            }

            if ($entity->inquiry) {
                $this['form']->setAutocmp('inquiry', $entity->inquiry->id . ', ' . $entity->inquiry->configurator->name);
            }
            
            $parts = $this->em->getOfferPartRepository()->findBy(['offer' => $id], ['order' => 'ASC']);
            $this->template->parts = $parts;

            $this->template->sendOfferModalForm = $this['sendOfferModalForm'];

        } else {
            if (isset($this->params['inquiryId'])) {
                $inquiry = $this->em->getInquiryRepository()->find($this->params['inquiryId']);
                if ($inquiry) {
                    $this['form']->getComponent('inquiry')->setValue($inquiry->id);
                    $this['form']->setAutocmp('inquiry', $inquiry->id . ', ' . $inquiry->configurator->name);
                    $this['form']->getComponent('customer')->setValue($inquiry->customer->id);
                    $this['form']->setAutocmp('customer', ($inquiry->customer->company ? $inquiry->customer->company . ', ' : '') . $inquiry->customer->name . ' ' . $inquiry->customer->surname);

                } else {
                    $this->flashMessage('Poptávka nenalezena', 'warning');
                }

            } else if (isset($this->params['customer'])) {
                $this['form']->getComponent('customer')->setValue($this->params['customer']);
            }
            

            $this['form']->setDefaults(['offerNo' => $this->offerFac->getNextOfferNo()]);

        }

        $templates = $this->em->getOfferPartTemplateRepository()->findBy(['type' => 1], ['order' => 'ASC']);;
        $this->template->templates = $templates;

    }

    /**
     * ACL name='Tabulka s přehledem nabídek'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Offer::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Offer:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $this->gridGen->addEditAction('edit', 'Upravit', 'Offer:edit');

        $this->gridGen->addButtonDeleteCallback('delete', 'Smazat', function($itemId) /*use ($presenter)*/ {
            $offer = $this->em->getOfferRepository()->find($itemId);
            if (!$offer) {
                return;
            }
            $file = Offer::OFFER_PATH . Offer::OFFER_PREFIX . $itemId . '.pdf';
            if (file_exists($file)) {
                @unlink($file);
            }
            $this->gridGen->deleteRow($itemId);
        });
        //     * GRID replacement=#['0' > 'Rozpracováno'|'1' > 'Odesláno'|'2' > 'Schváleno', '3' > 'Neschváleno']
        /*$grid->getColumn('state')
            ->setCaret(false)
            ->addOption(0, 'Rozpracováno')
                ->setIcon('question')
                ->setClass('btn-secondary')
                //->setConfirmation(new StringConfirmation('Do you really want set status as Online?'))
                ->endOption()
            ->addOption(1, 'Odesláno')
                ->setIcon('envelope')
                ->setClass('btn-lg btn-primary')
                ->endOption()
            ->addOption(2, 'Schváleno')
                ->setIcon('check')
                ->setClass('btn-lg btn-success')
                ->endOption()
            ->addOption(3, 'Neschváleno')
                ->setIcon('times')
                ->setClass('btn-lg btn-danger')
                ->endOption()
            ->onChange[] = [$this, 'statusChange'];*/

        

        return $grid;
    }

    public function statusChange($id, $newState)
    {
        $entity = $this->em->getOfferRepository()->find($id);
        if ($entity) {
            $entity->setState($newState);
            $this->em->flush();
        }
        if ($this->isAjax()) {
            $this['table']->redrawItem($id);
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit nabídky'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Offer::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit nabídku', 'success'], ['Nepodařilo se uložit nabídku!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'offerFormSuccess'];
        return $form;
    }

    public function offerFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        if (!$values->id) {
            $values->originator = $this->user->id;
        }
        $entity = $this->formGenerator->processForm($form, $values, true);

        // smth weird in background not saving zeros & idk why, thus got to workaround
        if (is_numeric($values->price)) {
            $entity->setPrice($values->price);
        }
        if (is_numeric($values->priceDelivery)) {
            $entity->setPriceDelivery($values->priceDelivery);
        }
        if (is_numeric($values->priceInstall)) {
            $entity->setpriceInstall($values->priceInstall);
        }
        if (is_numeric($values->priceCrane)) {
            $entity->setPriceCrane($values->priceCrane);
        }
        $this->em->flush();

        if (!$entity) {
            return;
        }

        if (isset($values2['calcPrice'])) {
            $this->offerFac->calcPrice($entity->id);
        } else if (isset($values2['calcPriceDelivery'])) {
            $this->offerFac->calcPriceDelivery($entity);
        } else if (isset($values2['calcPriceInstall'])) {
            $this->offerFac->calcPriceInstall($entity);
        }

        if ($this->isAjax()) {
            $this->redrawControl('owf-form');
        } else {

            if (isset($values2['sendBack'])) { // Uložit a zpět
                $this->redirect('Offer:default');
            } else if (isset($values2['send'])) { //Uložit
                $this->redirect('Offer:edit', ['id' => $entity->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('Offer:edit');
            } else {
                $this->redirect('Offer:edit', ['id' => $entity->id]);
            }
        }
    }

    /**
     * ACL name='Tabulka s přehledem šablon komponent nabídek'
     */
    public function createComponentTableTemplate()
    {
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\OfferPartTemplate::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Offer:editTemplate');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);
        $this->gridGen->addEditAction('edit', 'Upravit', 'Offer:editTemplate');

        $gridGen = $this->gridGen;
        $this->gridGen->addButtonDeleteCallback('delete', 'Smazat', function($itemId) use ($gridGen, $grid)  {
            $entity = $this->em->getOfferPartTemplateRepository()->find($itemId);
            if ($entity) {
                if ($entity->isDefault) {
                    $this->flashMessage('Odstranění zakázáno', 'warning');
                } else {
                    $gridGen->deleteRow($itemId);
                    $this->flashMessage('Odstraněno', 'success');
                }
            }
        });
        return $grid;
    }

    public function renderEditTemplate($id)
    {
        if ($id) {
            $entity = $this->em->getOfferPartTemplateRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Nepodařilo se nalézt uvedený záznam!', 'error');
                $this->redirect('Offer:', ['openTab' => '#templates']);
            }
            $this->template->entity = $entity;
            $arr = $this->ed->get($entity);
            $this['formTemplate']->setDefaults($arr);
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit šablony nabídky'
     */
    public function createComponentFormTemplate()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\OfferPartTemplate::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit šablonu', 'success'], ['Nepodařilo se uložit šablonu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'templateFormSuccess'];
        return $form;
    }

    public function templateFormSuccess($form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        if (isset($values2->parts)) {

        }

        if ($this->isAjax()) {
            $this->redrawControl('owf-form');
        } else {
            $entity = $this->formGenerator->processForm($form, $values, true);
            if (isset($values2['sendBack'])) {
                $this->redirect('Offer:default', ['openTab' => '#templates']);
            } else if (isset($values2['send'])) {
                $this->redirect('Offer:editTemplate', ['id' => $entity->id]);
            } else if (isset($values2['sendNew'])) {
                $this->redirect('Offer:editTemplate');
            } else {
                $this->redirect('Offer:editTemplate', ['id' => $entity->id]);
            }
        }
    }

    public function actionPrintOffer($id)
    {
        $offer = $this->em->getOfferRepository()->find($id);
        if (!$offer) {
            return;
        }
        $date = new \DateTime();

        // add later (!empty($customer->company)$customer->name.'_'.$customer->surname.
        $outputName = $this->offerFac::OFFER_PREFIX . $offer->id . '.pdf';
        $file = $this->pdfPrinter->handlePrintOffer($offer, $outputName, $this->user->getId(), $date, 'F');
        $fsize = filesize($file);

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $outputName . '"');
        //header('Content-Transfer-Encoding: binary');
        //header('Accept-Ranges: bytes');
        header("Cache-control: private");
        header("Content-length: $fsize");

        if ($fsize < 66060288) { // 63 MB
            @readfile($file);
        } else {
            $handle = fopen($file, "rb");
            if ($handle) {
                while(!feof($handle)) {
                    $buffer = fread($handle, 1048576); // 1 MB
                    echo $buffer;
                    ob_flush();
                    flush();
                }
            }
            fclose($handle);
        }

        $this->terminate();
    }

    function handleAddPart()
    {
        $data = $this->request->getPost();
        $part = new OfferPart();
        $offer = $this->em->getOfferRepository()->find($this->params['id']);
        $template = $this->em->getOfferPartTemplateRepository()->find($data['template']);

        if ($offer && $template) {
            $part->offer = $offer;
            $part->name = $template->name;
            $part->order = count($offer->parts) + 1;
            $part->price = $template->price;
            $part->content = $template->content;
            $part->template = $template;
            $part->isAfterPricing = $template->isAfterPricing;

            $this->em->persist($part);
            $this->em->flush();
        }
        $this->redrawControl('owf-form');
        $this->redrawControl('parts-snippet');
    }

    function handleRemovePart()
    {
        $data = $this->request->getPost();

        $part = $this->em->getOfferPartRepository()->findOneBy(['id' => $data['id'], 'offer' => $this->params['id']]);
        if ($part) {
            $order = $part->order;
            $this->em->remove($part);
            $this->em->flush();

            $qb = $this->em->getOfferPartRepository()->createQueryBuilder('p');
            $qb->select('p')
                ->where('p.order > :order AND p.offer = :offer')
                ->orderBy('p.order', 'ASC')
                ->setParameters(['order' => $order, 'offer' => $this->params['id']]);
            $nextParts = $qb->getQuery()->getResult();

            foreach ($nextParts as $p) {
                $p->order = $order++;
            }
            $this->em->flush();
        }
        
        $this->redrawControl('owf-form');
        $this->redrawControl('parts-snippet');
    }

    function handleChangePartOrder()
    {
        $data = $this->request->getPost();

        $part = $this->em->getOfferPartRepository()->findOneBy(['id' => $data['id'], 'offer' => $this->params['id']]);
        if ($part) {
            if ($data['toOrder'] != $part->order) {
                $qb = $this->em->getOfferPartRepository()->createQueryBuilder('p');
                $qb->select('p')
                    ->where('p.offer = :offer')
                    ->orderBy('p.order', 'ASC')
                    ->setParameter('offer', $this->params['id']);

                if ($data['toOrder'] > $part->order) {
                    $qb->andWhere('p.order > :orderMin AND p.order <= :orderMax')
                        ->setParameter('orderMin', $part->order)
                        ->setParameter('orderMax', $data['toOrder']);
                    $parts = $qb->getQuery()->getResult();
                    foreach ($parts as $p) {
                        $p->order -=  1; 
                    }
                } else {
                    $qb->andWhere('p.order >= :orderMin AND p.order < :orderMax')
                        ->setParameter('orderMin', $data['toOrder'])
                        ->setParameter('orderMax', $part->order);
                    $parts = $qb->getQuery()->getResult();
                    foreach ($parts as $p) {
                        $p->order +=  1; 
                    }
                }

                $qb = $this->em->getOfferPartRepository()->createQueryBuilder('p');
                    $qb->select('Count(p.id)')
                    ->where('p.offer = :offer')
                    ->setParameter('offer', $this->params['id']);
                $count = $qb->getQuery()->getSingleScalarResult();
                if ($data['toOrder'] > $count) {
                    $part->order = $count;
                } else {
                    $part->order = $data['toOrder'];
                }
                
                $this->em->flush();
            }
        }
        
        $this->redrawControl('owf-form');
        $this->redrawControl('parts-snippet');
    }

    function handleSavePart()
    {
        $data = $this->request->getPost();
        $part = $this->em->getOfferPartRepository()->findOneBy(['id' => $data['id'], 'offer' => $this->params['id']]);
        if ($part) {
            if (isset($data['name'])) {
                $part->name = $data['name'];
            }
            if (isset($data['price'])) {
                if (empty($data['price'])) {
                    $part->price = null;
                } else {
                    $part->price = $data['price'];
                }
            }
            if (isset($data['content'])) {
                $part->content = $data['content'];
            }
            if (isset($data['isChapter'])) {
                $part->isChapter = $data['isChapter'];
            }
            if (isset($data['pageBreak'])) {
                $part->pageBreak = $data['pageBreak'];
            }
            if (isset($data['isAfterPricing'])) {
                $part->isAfterPricing = $data['isAfterPricing'];
            }
            
            $this->em->flush();
        }
    }

    /**
     * ACL name='Formulář pro odeslání nabídky'
     */
    public function createComponentSendOfferModalForm()
    {
        $offer = $this->em->getOfferRepository()->find($this->params['id']);

        $webSettings = $this->baseFrontFac->getWebSettings($this->locale);
        
        $form = new ACLForm();
        $form->addHidden('id')
            ->setValue($this->params['id']);
        $input = $form->addEmail('emailTo', 'Příjemce')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(true);
        if ($offer->customer) {
            $input->setDefaultValue($offer->customer->email);
        }
        $input = $form->addEmail('emailCopy', 'Kopie')
            ->setHtmlAttribute('class', 'form-control');
        if ($offer->salesman) {
            $input->setDefaultValue($offer->salesman->email);
        }
        $form->addText('subject', 'Předmět')
            ->setHtmlAttribute('class', 'form-control')
            ->setRequired(true)
            ->setDefaultValue($webSettings['default_offer_email_subject']);
            
        $form->addTextArea('text', 'Zpráva', null, 8)
            ->setHtmlAttribute('class', 'ckeditor')
            ->setDefaultValue($webSettings['default_offer_email']);


        $form->onSuccess[] = function(Form $form, $values): void {

            if ($this->offerFac->prepareAndSendOffer($values->id, $values->emailTo, $values->emailCopy, $values->subject, $values->text, $this->locale)) {
                $this->flashMessage('Nabídka byla odeslána', 'success');
                $entity = $this->em->getOfferRepository()->find($values->id);
                $entity->setSendDate(new DateTime());
                $this->em->flush();
            } else {
                $this->flashMessage('Nabídku se nepodařilo odeslat', 'error');
            }
        };

        return $form;
    }

    /**
     * ACL name='Formulář pro edit produktů'
     */
    public function createComponentProductModalForm() {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\OfferProduct::class, $this->user, $this, __FUNCTION__);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'productModalFormSuccess'];
        return $form;
    }

    public function productModalFormSuccess($form, $values) {
        $values2 = $this->request->getPost();

        if (!$values->product || !$values->count) {
            $this->flashMessage('Vyplňte všecna pole pro přidání produktu!', 'error');
            $this->payload->productSuccessFailed =  1;
            $this->sendPayload();
            return;
        }

        $res = $this->offerFac->addOfferProduct($this->getParameter('id'), $values->product, $values->id, $values->price, $values->count);
        
        if (!$res) {
            $this->flashMessage('Produkt se nepodařilo uložit!', 'warning');
        } else {
            $this->flashMessage('Produkt se podařilo uložit.', 'success');
        }

        if (isset($values2['sendProductNew'])) {
            $this['productModalForm']->setValues([], true);
            $this->redrawControl('productModalParent');
            $this->redrawControl('productModal');
        }
        if($this->isAjax()) {
            $this->redrawControl('offer-product');
        } else {
            $this->redirect('this');
        }
    }

    public function actionTestMaps()
    {
        $resCoords0 = $this->googleMaps->geocodeToLatLangArr(['street' => 'Stříbrná 851', 'city' => 'Bystřice nad Pernštejnem', 'zip' => '593 01']);
        //$resCoords1 = $this->googleMaps->geocode(['city' => $inquiry->installCity, 'zip' => $inquiry->installZip]);
        $resCoords1 = $this->googleMaps->geocodeToLatLangArr(['city' => 'Jihlava', 'zip' => '586 01']);
        bdump($resCoords0);
        bdump($resCoords1);
        $res = $this->googleMaps->distanceValueAndTime(['origins' => $resCoords0, 'destinations' => $resCoords1]);
        bdump($res);
    }

    public function handleCheckProduct()
    {
        $values = $this->request->getPost();
        if($values['productId']) {
            $entity = $this->em->getOfferProductRepository()->find($values['productId']);
            $arr = $this->ed->get($entity);
            $this['productModalForm']->setAutocmp('product', $entity->product->klic_polozky . ': ' . $entity->product->nazev_polozky);
            $this['productModalForm']->setDefaults($arr);
            $this->template->modalProduct = $entity;
        }

        $this->redrawControl('productModalParent');
        $this->redrawControl('productModal');
    }

    public function handleRemoveProduct()
    {
        $values = $this->request->getPost();
        if (isset($values['productId'])) {
            $entity = $this->em->getOfferProductRepository()->find($values['productId']);
            if ($entity) {
                $this->em->remove($entity);
                $this->em->flush();
                $this->flashMessage('Produkt se podařilo odstranit.', 'success');
            } else {
                $this->flashMessage('Produkt se nepodařilo odstranit!', 'warning');
            }
        } else {
            $this->flashMessage('Produkt se nepodařilo odstranit!', 'warning');
        }

        $this->redrawControl('offer-product');
    }

    /**
     * Get products for autocomplete
     * @param string $term
     */
    public function handleGetProducts($term)
    {
        if (!$term) {
            $term = $_GET['term'];
        }
        $result = $this->em->getProductRepository()->getDataAutocompleteProducts($term);
        $this->getPresenter()->payload->autoComplete = json_encode($result);
        $this->getPresenter()->sendPayload();
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

    /**
     * Get salesmans for autocomplete
     * @param string $term
     */
    public function handleGetSalesmans($term)
    {
        $result = $this->em->getUserRepository()->getDataAutocompleteUsers($term, 'isSalesman = 1');
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    /**
     * Get inquiries for autocomplete
     * @param string $term
     */
    public function handleGetInquiries($term)
    {
        $result = $this->em->getInquiryRepository()->getDataAutocompleteInquiries($term);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    /**
     * Get task processes for autocomplete
     * @param string $term
     */
    public function handleGetTaskProcesses($term)
    {
        $data = isset($_GET['data']) ? $_GET['data'] : null;
        $result = $this->em->getTaskRepository()->getDataAutocompleteTaskProcesses($term, $data);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }
}
