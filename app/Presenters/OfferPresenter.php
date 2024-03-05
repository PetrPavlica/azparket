<?php

namespace App\Presenters;

use App\Model\Database\Utils\AnnotationParser;
use App\Model\Facade\Offer;
use Nette\Application\UI\Form;

class OfferPresenter extends BasePresenter
{
    /** @var Offer @inject */
    public Offer $offerFac;

    public function startup()
    {
        parent::startup();
    }

    public function renderDefault()
    {

    }

    public function renderAccept($acceptCode)
    {
        if (!$acceptCode) {
            $this->redirect('Offer:');
        }
        //$offer = $this->em->getOfferRepository()->findOneBy(['acceptCode' => $acceptCode]);
        $offer = $this->em->getOfferRepository()->createQueryBuilder('c')
            ->where("c.acceptCode = :code AND (c.acceptDate IS NULL OR NOW() < DATE_ADD(c.acceptDate, 3, 'DAY'))")
            ->setParameters(['code' =>  $acceptCode])
            ->getQuery()->getOneOrNullResult();
        if (!$offer) {
            $this->flashMessage('Kód pro potvrzení k nabídky již není platný! Prosíme kontaktujte obchodníka', 'error');
            $this->redirect('Offer:');
        }

        $this['offerAcceptForm']->getComponent('id')->setValue($offer->id);
        $this['offerAcceptForm']->getComponent('acceptCode')->setValue($offer->acceptCode);
        $this['offerAcceptForm']->getComponent('customer')->setValue($offer->customer->id);
        $this['offerAcceptForm']->getComponent('company')->setValue($offer->customer->company);
        $this['offerAcceptForm']->getComponent('ico')->setValue($offer->customer->idNo);
        $this['offerAcceptForm']->getComponent('name')->setValue($offer->customer->name);
        $this['offerAcceptForm']->getComponent('surname')->setValue($offer->customer->surname);
        $this['offerAcceptForm']->getComponent('email')->setValue($offer->customer->email);
        $this['offerAcceptForm']->getComponent('phone')->setValue($offer->customer->phone);
        $this['offerAcceptForm']->getComponent('installCity')->setValue($offer->installCity);
        $this['offerAcceptForm']->getComponent('installZip')->setValue($offer->installZip);
        
        $this->template->offerPath = $this->offerFac::OFFER_PATH . $offer->id . '/' . $this->offerFac::OFFER_PREFIX . $offer->id . '.pdf';

       $this->template->offer = $offer;
    }

    public function createComponentOfferAcceptForm()
    {
        $form = new Form();
        
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
        $form->addText('installCity', 'Obec místa instalace poptávaných výrobků')
            ->setRequired('Toto pole je povinné');
        $form->addText('installZip', 'PSČ místa instalace poptávaných výrobků')
            ->setRequired('Toto pole je povinné');

        foreach ($form->getComponents() as $comp) {
            $comp->setHtmlAttribute('class', 'form-control');
        }

        $form->addHidden('customer');
            
        $form->addCheckbox('agree', 'Souhlasím se zpracování osobních údajů za účelem nákupu')
            ->setRequired('Musíte potvrdit před objednáním')
            ->setHtmlAttribute('class', 'checkbox-lg form-control-lg');

        $form->addHidden('id');
        $form->addHidden('acceptCode');

        $form->addSubmit('send', 'Potvrzuji')
            ->setHtmlAttribute('class', 'btn btn-primary btn-lg');
            
        //$form->addSubmit('cancel', 'Odmítnout')
        //    ->setHtmlAttribute('class', 'btn btn-outline-danger btn-lg');


        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };
        $form->onValidate[] = function(Form $form) {
            $values = $this->request->getPost();
            if (isset($values['save']) || isset($values['send'])) {

            }
        };
        $form->onSuccess[] = function(Form $form, $values) {
            $values2 = $this->getRequest()->getPost();

            $this->sess->formValues = $values2;

            if (isset($values2['send'])) {

                $res = $this->offerFac->acceptOffer($values);
                if ($res) {
                    $this->flashMessage('Nabídka byla úspěšně schválena.', 'success');
                } else {
                    $this->flashMessage('Omlouváme se, při zpracování došlo k chybě! Prosíme kontaktujte obchodníka', 'error');
                }
            }

            if ($this->isAjax()) {

            } else {
                $this->redirect('this');
            }
        };

        return $form;
    }

    public function handleDismissOffer($acceptCode)
    {
        $res = $this->offerFac->dismissOffer($acceptCode);
        if ($res) {
            $this->flashMessage('Nabídka byla úspěšně zamítnuta.', 'info');
        } else {
            $this->flashMessage('Omlouváme se, při zpracování došlo k chybě! Prosíme kontaktujte obchodníka', 'error');
        }
        $this->redirect('this');
    }

    /*public function actionDownloadOffer()
    {
        $offer = $this->em->getOfferRepository()->createQueryBuilder('c')
            ->where("c.signCode = :code AND (c.realSignedDate IS NULL OR NOW() < DATE_ADD(c.realSignedDate, 3, 'DAY'))")
            ->setParameters(['code' =>  $this->getParameter('signCode')])
            ->getQuery()->getOneOrNullResult();
        if (!$offer) {
            return;
        }

        $fileName = $this->offerFac::OFFER_PREFIX . $offer->id . '.pdf';
        $file = $this->offerFac::OFFER_PATH . $offer->id . '/' . $fileName;

        $fsize = filesize($file);

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $file . '"');
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
    }*/
}