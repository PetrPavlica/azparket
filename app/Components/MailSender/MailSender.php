<?php

namespace App\Components\MailSender;

use App\Components\PDFPrinter\PDFPrinterControl;
use App\Model\Database\Entity\Process;
use App\Model\Facade\BaseFront;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Model\Facade\Offer as OfferFac;
use Contributte\Translation\Translator;
use Nette;
use Nette\Application\UI;
use Nette\Mail\Message;
use Nette\Application\LinkGenerator;
use Nette\Mail\SendException;
use Nette\Utils\AssertionException;
use Tracy\Debugger;

class MailSender extends UI\Control
{
    /** @var UI\TemplateFactory */
    public $templateFactory;

    /** @var LinkGenerator */
    public $linkGenerator;

    /** @var BaseFront */
    public $frontFac;

    /** @var string */
    public $dir;

    /** @var string */
    public $baseUri;

    /** @var boolean */
    public $production;

    /** @var Nette\Mail\Mailer */
    private $mailer;

    /** @var bool */
    private $checkMailer = false;

    /** @var EntityManager */
    private $em;

    /** @var Translator */
    private $trans;

    /** @var PDFPrinterControl */
    public $pdfPrinter;

    /** @var EntityData */
    public $ed;

    public function __construct(UI\TemplateFactory $templateFactory, LinkGenerator $linkGenerator,
        Translator $trans, PDFPrinterControl $pdfPrinter, EntityData $entityData, EntityManager $em, Nette\Mail\Mailer $mailer,
        BaseFront $frontFac)
    {
        $this->templateFactory = $templateFactory;
        $this->linkGenerator = $linkGenerator;
        $this->trans = $trans;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->frontFac = $frontFac;
        $this->pdfPrinter = $pdfPrinter;
        $this->ed = $entityData;
    }

    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    public function setProduction($production)
    {
        $this->production = $production;
    }

    private function createMailer()
    {
        if ($_SERVER['SERVER_NAME'] == 'localhost') {
            $validSmtp = true;
            $find = 'smtp_';
            $smtpArr = [
                'smtp_host' => true,
                'smtp_username' => true,
                'smtp_password' => true,
                'smtp_port' => false,
                'smtp_secure' => false
            ];
            $smtpValues = [];
            $settings = $this->em->getSettingRepository()->findBy(['code' => $smtpArr]);
            if ($settings) {
                foreach ($settings as $s) {
                    $smtpValues[str_replace($find, '', $s->code)] = $s->value;
                }
            }
            $smtpConfig = [];
            foreach ($smtpArr as $k => $v) {
                $key = str_replace($find, '', $k);
                if ($v && (!isset($smtpValues[$key]) || empty(trim($smtpValues[$key])))) {
                    $validSmtp = false;
                    break;
                }
                if (isset($smtpValues[$key])) {
                    $smtpConfig[$key] = $smtpValues[$key];
                }
            }
            if ($validSmtp) {
                $this->mailer = new Nette\Mail\SmtpMailer($smtpConfig);
            }
            return $this->mailer;
        } else {
            return new Nette\Mail\SendmailMailer();
        }
    }

    public function sendTest()
    {
        try {
            $mail = new Message();
            $mail->setHtmlBody('Testovací e-mail');
            $emailSender = $this->em->getSettingRepository()->findOneBy(['code' => 'email_sender_mask']);
            if (!$emailSender) {
                throw new \Exception('Unable load "email_sender_mask" from setting');
            }
            $mail->setFrom($emailSender->value);
            $mail->setSubject($this->trans->translate('Testovací e-mail DKIM'));

            $mailer = $this->createMailer();

            $mail->addTo('david@webrex.eu', 'David Šilhavý');

            $mailer->send($mail);

            return true;
        } catch (\Exception $ex) {
            Debugger::log(sprintf('[sendTest] Unable send e-mail - %s', $ex->getMessage()), 'warning');
        }

        return false;
    }

    public function sendCreationTask($idTask)
    {
        $template = $this->templateFactory->createTemplate();
        $template->task = $task = $this->em->getTaskRepository()->find($idTask);

        $emailSender = $this->em->getSettingRepository()->findOneBy(['code' => 'email_sender_mask']);
        if (!$emailSender) {
            throw new \Exception('Unable load "email_sender_mask" from setting');
        }

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/creationTask.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($emailSender->value)
            ->addTo($task->assigned->email);

        $mail->setSubject($this->trans->translate('Vytvoření nového úkolu.'));

        $mailer = $this->createMailer();
        $mailer->send($mail);
    }

    public function sendAbsence($absence, $isResolution = false, $title = '') {
        if (is_numeric($absence)) {
            $absence = $this->em->getAbsenceRepository()->find($absence);
        }

        if (!$absence) {
            return false;
        }

        $emailSender = $this->em->getSettingRepository()->findOneBy(['code' => 'email_sender_mask']);
        if (!$emailSender) {
            throw new \Exception('Unable load "email_sender_mask" from setting');
        }

        $template = $this->templateFactory->createTemplate();
        $template->absence = $absence;
        $template->title = $title;
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        if ($isResolution) {
            $template->setFile(__DIR__ . '/templates/absenceResolution.latte');
        } else {
            $template->setFile(__DIR__ . '/templates/absenceNew.latte');
        }

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($emailSender->value);

        if ($absence->user) {
            $email = $absence->user->email;
        } else {
            return false;
        }

        if (!$email) {
            return false;
        }

        if ($isResolution) {
            try {
                $mail->addTo($email);
                $mail->addCc($this->em->getSettingRepository()->findOneBy(['code' => 'email_for_absence'])->value);
            } catch (AssertionException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            }
            $tit = $title.' podané absence';
        } else {
            try {
                $mail->addTo($this->em->getSettingRepository()->findOneBy(['code' => 'email_for_absence'])->value);
                $mail->addCc($email);
            } catch (AssertionException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            }
            $tit = $title.' nové absence';
        }

        $mail->setSubject($this->trans->translate($tit));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (SendException $e) {
            Debugger::log($e);
            return false;
        }
        return true;
    }

    public function sendAbsenceSuccessInfo($absence) {
        if (is_numeric($absence)) {
            $absence = $this->em->getAbsenceRepository()->find($absence);
        }

        if (!$absence) {
            return false;
        }

        $emailSender = $this->em->getSettingRepository()->findOneBy(['code' => 'email_sender_mask']);
        if (!$emailSender) {
            throw new \Exception('Unable load "email_sender_mask" from setting');
        }

        $template = $this->templateFactory->createTemplate();
        $template->absence = $absence;
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/absenceSuccessInfo.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        $mail->setFrom($emailSender->value);

        try {
            $mail->addTo($this->em->getSettingRepository()->findOneBy(['code' => 'email_for_success_absence_info'])->value);
            if ($absence->userDelegate) {
                $mail->addTo($absence->userDelegate->email);
            }
        } catch (AssertionException $e) {
            Debugger::log($e, 'MailSender');
            return false;
        }

        $mail->setSubject($this->trans->translate('Informace ohledně absence'));
        $mailer = $this->createMailer();
        try {
            $mailer->send($mail);
        } catch (SendException $e) {
            Debugger::log($e);
            return false;
        }
        return true;
    }

    public function sendVisitDocs($form, $originator, $msg = 'výjezdu', $trafficId = null, $forInvoicing = false)
    {
        $user = $this->em->getUserRepository()->find($originator);
        $copyEmail = $this->em->getSettingRepository()->findOneBy(['code' => 'email_for_copy_docs'])->value;
        $invoicingEmail = $this->em->getSettingRepository()->findOneBy(['code' => 'email_for_invoicing_docs'])->value;

        $template = $this->templateFactory->createTemplate();
        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/visitDocs.latte');

        $template->traffic = $traffic = $this->em->getTrafficRepository()->find($trafficId);
        $template->msg = $msg;
        $template->noteForm = isset($form['note']) ? $form['note'] : '';

        $emailSender = $this->em->getSettingRepository()->findOneBy(['code' => 'email_sender_mask']);
        if (!$emailSender) {
            throw new \Exception('Unable load "email_sender_mask" from setting');
        }

        $emails = [];
        //pro fakturaci nastavit email z fakturace jinak z formuláře
        if ($forInvoicing) {
            $emails[] = $invoicingEmail;
        } else {
            foreach ($form as $k => $f) {
                $st = substr($k, 0, 5);
                if ($st == 'email') {
                    if ($f) {
                        $emails[] = $f;
                    }
                }
            }
        }
        if (count($emails) && isset($form['docIds']) && count($form['docIds'])) {
            $mail = new Message();
            $mail->setHtmlBody($template);
            $mail->setFrom($emailSender->value);
            //přidání kopií
            try {
                $mail->addCc($user->email);
                if ($user->email != $copyEmail) {
                    $mail->addCc($copyEmail);
                }
            } catch (AssertionException $e) {
                Debugger::log($e, 'MailSender');
                return false;
            }
            //přidání příjemců
            foreach ($emails as $email) {
                try {
                    $mail->addTo($email);
                } catch (AssertionException $e) {
                    Debugger::log($e, 'MailSender');
                }
            }
            //přidání dokumentů
            foreach ($form['docIds'] as $docId) {
                $doc = $this->em->getVisitDocumentRepository()->find($docId);
                if ($doc && file_exists($doc->document)) {
                    $file = substr($doc->document, strrpos($doc->document, '/') + 1);
                    $type = substr($file, strrpos($file, '.') + 1);
                    //$fileName = $file;
                    $fileName = $doc->name;
                    $mail->addAttachment($fileName, file_get_contents($doc->document), $type);
                }
            }
            $mail->setSubject($this->trans->translate('Dokumenty k '.$msg));

            $mailer = $this->createMailer();
            try {
                $mailer->send($mail);
            } catch (SendException $e) {
                Debugger::log($e);
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function sendInquiry($inquiry, $locale = 'cs', $user = false)
    {
        $template = $this->templateFactory->createTemplate();

        if (is_numeric($inquiry)) {
            $inquiry = $this->em->getInquiryRepository()->find($inquiry);
        }
        if (!$inquiry) {
            return false;
        }

        if (!$inquiry->customer) {
            return false;
        }
        
        $template->products = $this->em->getInquiryProductRepository()->findBy(['inquiry' => $inquiry->id]);
        $template->inquiry = $inquiry;
        $template->customer = $customer = $inquiry->customer;
        $template->settings = $settings = $this->frontFac->getSettings();
        $template->webSettings = $webSettings = $this->frontFac->getWebSettings($locale);
        $template->user = $user;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/inquiry.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        if (!$user) {
            if ($this->production) {
                $domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
                $mail->setFrom('webmailer@'.$domain, $customer->name . ' ' . $customer->surname)
                    ->addTo($settings['email_sender_mask'])
                    ->addReplyTo($customer->email);
            } else {
                $mail->setFrom($settings['email_sender_mask'])
                    ->addTo($customer->email);
            }
        } else {
            $mail->setFrom($settings['email_sender_mask'])
                ->addTo($customer->email);
        }
        $mail->setSubject($webSettings['default_inquiry_email_subject']);

        $mailer = $this->createMailer();

        try {
            $mailer->send($mail);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function sendReservation($reservation, $locale = 'cs', $user = false)
    {
        $template = $this->templateFactory->createTemplate();

        if (is_numeric($reservation)) {
            $reservation = $this->em->getInquiryRepository()->find($reservation);
        }
        if (!$reservation) {
            return false;
        }

        if (!$reservation->customer) {
            return false;
        }
        
        $template->reservation = $reservation;
        $template->customer = $customer = $reservation->customer;
        $template->settings = $settings = $this->frontFac->getSettings();
        $template->webSettings = $webSettings = $this->frontFac->getWebSettings($locale);
        $template->user = $user;

        $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
        $template->setFile(__DIR__ . '/templates/reservation.latte');

        $mail = new Message();
        $mail->setHtmlBody($template);
        if (!$user) {
            if ($this->production) {
                $domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
                $mail->setFrom('webmailer@'.$domain, $customer->name . ' ' . $customer->surname)
                    ->addTo($settings['email_sender_mask'])
                    ->addReplyTo($customer->email);
            } else {
                $mail->setFrom($settings['email_sender_mask'])
                    ->addTo($customer->email);
            }
        } else {
            $mail->setFrom($settings['email_sender_mask'])
                ->addTo($customer->email);
        }
        $mail->setSubject($webSettings['default_reservation_email_subject']);

        $mailer = $this->createMailer();

        try {
            $mailer->send($mail);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    
    public function sendOffer($offer, $emailTo, $emailCopy, $subject, $text = '', $locale = 'cs')
    {
        if (is_numeric($offer)) {
            $offer = $this->em->getOfferRepository()->find($offer);
        }
        if (!$offer) {
            return false;
        }

        if ($offer && $emailTo && $subject) {

            $template = $this->templateFactory->createTemplate();

            $webSettings = $this->frontFac->getWebSettings($locale);
            $template->webSettings = $webSettings;
            $settings = $this->frontFac->getSettings($locale);
            $template->settings = $settings;

            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->setFile(__DIR__ . '/templates/offer.latte');
            if ($text) {
                $template->text = nl2br(trim($text));
            } else {
                $template->text = $webSettings['default_offer_email'];
            }
            $template->offer = $offer;

            if (!isset($settings['email_sender_mask'])) {
                throw new \Exception('Unable to load "email_sender_mask" from setting');
            }
            
            $mail = new Message();
            $mail->setHtmlBody($template);
            $mail->setFrom($settings['email_sender_mask']);
            //přidání kopií
            if ($emailCopy) {
                try {
                    $mail->addCc($emailCopy);
                } catch (AssertionException $e) {
                    Debugger::log($e, 'MailSender');
                    return false;
                }
            }
            //přidání příjemců
            try {
                $mail->addTo($emailTo);
            } catch (AssertionException $e) {
                Debugger::log($e, 'MailSender');
            }
            //přidání dokumentů
            $file = OfferFac:: OFFER_PATH .  $offer->id . '/' . OfferFac::OFFER_PREFIX . $offer->id . '.pdf';
            if (file_exists($file)) {
                $mail->addAttachment('Nabidka', file_get_contents($file), 'pdf');
            } else {
                throw new \Exception('Offer file not found');
            }
            $mail->setSubject($subject);

            $mailer = $this->createMailer();
            try {
                $mailer->send($mail);
            } catch (SendException $e) {
                Debugger::log($e);
                return false;
            }

            return true;
        } else {
            throw new \Exception('Missing parameters required for offer to be send');
        }
    }
    
    public function sendCustomerCreation($customer, $passwordText = null)
    {
        try {
            $template = $this->templateFactory->createTemplate();

            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->setFile(__DIR__ . '/templates/customerCreation.latte');
            $template->customer = $customer;
            $template->passwordText = $passwordText;
            $template->settings = $settings = $this->em->getSettingRepository()->getSettings();

            $mail = new Message();
            $mail->setHtmlBody($template);
            if (!array_key_exists('email_sender_mask', $settings)) {
                throw new \Exception('Unable load "email_sender_mask" from setting');
            }
            $mail->setFrom($settings['email_sender_mask']);
            $mail->setSubject($this->trans->translate('Vytvoření účtu v zákaznickém portálu SVÚ Jihlava'));
            $mail->addTo($customer->email, $customer->fullname);

            $mailer = $this->createMailer();
            $mailer->send($mail);

            $templateCC = $this->templateFactory->createTemplate();
            $templateCC->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $templateCC->setFile(__DIR__ . '/templates/customerCreationAdmin.latte');
            $templateCC->customer = $customer;
            $templateCC->passwordText = $passwordText;
            $templateCC->settings = $settings = $this->em->getSettingRepository()->getSettings();

            $mailCC = new Message();
            $mailCC->setHtmlBody($templateCC);
            if (!array_key_exists('email_sender_mask', $settings)) {
                throw new \Exception('Unable load "email_sender_mask" from setting');
            }
            $mailCC->setFrom($settings['email_sender_mask']);
            $mailCC->setSubject($this->trans->translate('Upozornění – registrace nového zákazníka v ZP'));
            $mailCC->addTo('portal@svujihlava.cz');

            $users = $this->em->getUserRepository()->findBy(['customerCreateNotify' => true]);
            if ($users) {
                foreach ($users as $user) {
                    $mailCC->addTo($user->getEmail());
                }
            }
            $mailer->send($mailCC);

            return true;
        } catch (\Exception $ex) {
            Debugger::log(sprintf('[%s] Unable send e-mails - %s', __FUNCTION__, $ex->getMessage()), 'warning');
        }

        return false;
    }

    public function sendCustomerPasswordRecovery($customer)
    {
        try {
            $template = $this->templateFactory->createTemplate();

            $template->getLatte()->addProvider('uiControl', $this->linkGenerator);
            $template->setFile(__DIR__ . '/templates/customerPasswordRecovery.latte');
            $template->customer = $customer;
            $template->settings = $settings = $this->em->getSettingRepository()->getSettings();

            $mail = new Message();
            $mail->setHtmlBody($template);
            if (!array_key_exists('email_sender_mask', $settings)) {
                throw new \Exception('Unable load "email_sender_mask" from setting');
            }
            $mail->setFrom($settings['email_sender_mask']);
            $mail->setSubject($this->trans->translate('Obnova hesla v Zákaznickém portálu SVÚ Jihlava'));
            $mail->addTo($customer->email, $customer->fullname);

            $mailer = $this->createMailer();
            $mailer->send($mail);

            return true;
        } catch (\Exception $ex) {
            Debugger::log(sprintf('[%s] Unable send e-mails - %s', __FUNCTION__, $ex->getMessage()), 'warning');
        }

        return false;
    }

}