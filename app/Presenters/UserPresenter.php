<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Database\Entity\CustomerNotifyEmail;
use App\Model\Facade\Customer;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\IUserStorage;

class UserPresenter extends BasePresenter
{
    /** @persistent */
    public string $backlink = '';

    /** @var Customer @inject */
    public Customer $customerFac;

    public function startup()
    {
        parent::startup();
        ini_set('default_socket_timeout', '15');
    }

    public function beforeRender()
    {
        parent::beforeRender();
        if (in_array($this->getView(), $this->viewsForNotLoggedUsers)) {
            if ($this->user->isLoggedIn()) {
                $this->redirect('Homepage:');
            }
            //$this->setLayout(__DIR__ . '/templates/@login.latte');
        } else if (!$this->user->isLoggedIn() && !in_array($this->getView(), $this->viewsForNotLoggedUsers)) {
            $this->redirect('User:login');
        }
    }

    public function renderDefault()
    {
        if (!$this->customer) {
            $this->redirect('Homepage:');
        }
        $arr = $this->ed->get($this->customer);
        $this->template->customer = $this->customer;

        $existNotifications = [];
        if ($this->customer->notifications) {
            foreach ($this->customer->notifications as $n) {
                $existNotifications[] = $n->processState->getId();
                if ($n->active) {
                    $arr['notifications'][] = $n->processState->getId();
                }
            }
        }
        $types = [];
        if ($this->customer->types) {
            foreach ($this->customer->types as $item) {
                $types[] = $item->type->getId();
            }
        }
        $this->template->customerTypes = $types;

        /*$states = $this->em->getProcessStateRepository()->findPairs('id', 'notification', ['active' => true, 'notification' => true], ['order' => 'ASC']);
        if ($states) {
            foreach ($states as $k => $v) {
                if (!in_array($k, $existNotifications) && !isset($arr['notifications'][$k]) && $v) {
                    $arr['notifications'][] = $k;
                }
            }
        }*/

        $this->sess->userProfile = $arr;

        $this['profileForm']->setDefaults($arr);
    }

    public function renderNewPassword($email, $hash)
    {
        $res = $this->customerFac->checkPasswordHash($email, $hash);
        if (!$res) {
            $this->flashMessage('Odkaz není platný. Obnovte heslo znovu.', 'error');
            $this->redirect('User:login');
        }

        $this['newPasswordForm']->setDefaults([
            'email' => $email,
            'hash' => $hash
        ]);
    }

    /*public function renderRegisterSuccess()
    {
        if (!$this->sess->registerSuccess) {
            $this->redirect('User:login');
        }
        unset($this->sess->registerSuccess);
    }*/

    public function renderReservation()
    {
        $this->template->reservations = $this->em->getReservationRepository()->findBy(['customer' => $this->user->getId()], ['dateFrom' => 'DESC']);
    }

    public function handleCancelReservation($id)
    {
        $reservation = $this->em->getReservationRepository()->find($id);
        if (!$reservation) {
            $this->flashMessage('Rezervaci se nepodařilo nalézt', 'error');
        }

        $reservation->setCanceled(true);
        $this->em->flush();
        $this->flashMessage('Rezervace byla zrušená', 'succes');

        if ($this->isAjax()) {
            $this->redrawControl('reservations');
        } else {
            $this->redirect('User:reservation');
        }
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Prosím vyplňte své uživatelské jméno')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autofocus', 'true');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Prosím vyplňte své heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addCheckbox('remember', 'Zapamatovat');

        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary');

        $form->onSuccess[] = function(Form $form, $values) {
            $loginSuccess = false;
            try {
                $this->getUser()->setAuthenticator($this->customerAuthenticator);
                $this->getUser()->login($values->username, $values->password);
                $this->getUser()->setExpiration($values->remember ? '14 days' : '30 minutes', IUserStorage::CLEAR_IDENTITY);
                $this->flashMessage('Přihlášení bylo úspěšné!', 'success');
                $loginSuccess = true;
            } catch (AuthenticationException $e) {
                if ($e->getCode() == 4) {
                    $this->flashMessage($e->getMessage(), 'warning');
                } else {
                    $this->flashMessage('Špatné přihlašovací údaje', 'error');
                }
            }

            if ($loginSuccess) {
                $this->redirect('User:reservation');
            }
        };

        return $form;
    }

    protected function createComponentRegisterForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addEmail('email', 'E-mail')
            ->setRequired('Prosím vyplňte svůj e-mail')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        /*$form->addText('idNo', 'IČ')
            //->setRequired('Prosím vyplňte svoje IČ')
            ->setHtmlAttribute('placeholder', 'Vyhledat dle názvu firmy nebo IČ')
            ->setHtmlAttribute('class', 'autocomplete-input form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('data-toggle', 'completer')
            ->setHtmlAttribute('data-preload', 'false')
            ->setHtmlAttribute('data-suggest', 'true')
            ->setHtmlAttribute('data-minlen', '3');*/

        /*$postData = $this->getRequest()->getPost();
        if ($postData) {
            $postData['idNo'] = $postData['idNo'] ?: $postData['textidNo'];
            $form['idNo']->setRequired(false);
            $form['idNo']->setHtmlAttribute('value-autocmp', $postData['idNo']);
        }*/

        /*$form->addText('vatNo', 'DIČ')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');*/

        $form->addPassword('password', 'Heslo')
            ->setRequired('Prosím vyplňte své heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Heslo znovu')
            ->setRequired('Prosím vyplňte své heslo znovu')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6)
            ->addRule($form::EQUAL, 'Hesla se neshodují', $form['password']);

        /*$form->addText('company', 'Název firmy')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('workshop', 'Provozovna')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', 'V případě, že má subjekt více provozoven, uveďte adresu a název provozovny')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');*/

        $form->addText('name', 'Jméno')
            ->setRequired('Prosím vyplňte své jméno')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('surname', 'Příjmení')
            ->setRequired('Prosím vyplňte své příjmení')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('street', 'Ulice a č. p.')
            ->setRequired('Prosím vyplňte svou ulici')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('city', 'Město')
            ->setRequired('Prosím vyplňte své město')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('zip', 'PSČ')
            ->setRequired('Prosím vyplňte své PSČ')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('phone', 'Telefon')
            ->setRequired('Prosím vyplňte svůj telefon')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addCheckbox('agree', 'Souhlasím se zpracováním osobních údajů')
            ->setRequired(true);

        /*$form->addCheckbox('agreePayment', 'Souhlasím s fakturací na zadané fakturační údaje bez možnosti změny')
            ->setRequired(true);*/

        $form->addInvisibleReCaptcha('captcha', true, 'Bez ověření skrz Recaptchu nelze pokračovat. Zkuste formulář odeslat znovu.');

        $form->addSubmit('send', 'Vytvořit účet')
            ->setHtmlAttribute('class', 'btn-wide btn-pill btn-shadow btn-hover-shine btn btn-primary btn-lg');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'error');
                }
            }
        };

        $form->onValidate[] = function(Form $form) {
            $values2 = $this->request->getPost();
            /*$values2['idNo'] = $values2['idNo'] ?: $values2['textidNo'];
            $values2['idNo'] = trim($values2['idNo']);
            
            if (!$values2['idNo']) {
                $form->addError('Prosím vyplňte Vaše IČ.');
            } else {

            }*/

            $qb = $this->em->getCustomerRepository()->createQueryBuilder('c')
                ->join('c.types', 't')
                ->where('c.email = :email')
                ->setParameters([
                    'email' => $values2['email']
                ])
                ->setMaxResults(1);
            $customer = $qb->getQuery()->getOneOrNullResult();
            if ($customer) {
                $form->addError(sprintf('Zákazník s e-mailem %s je již registrován. Pokud je to skutečně váš email, v přihlašovacím formuláři najdete možnost "Zapomněl jsem heslo".', $values2['email']));
            }
        };

        $form->onSuccess[] = function(Form $form, $values) {
            $values2 = $this->request->getPost();
            foreach ($values2 as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $values2[$k] = trim($v);
            }
            //$values2['idNo'] = $values2['idNo'] ?: $values2['textidNo'];
            $values2['fullname'] = $values2['name'].' '.$values2['surname'];
            $customer = $this->customerFac->createFromRegister($values2);
            if ($customer) {
                $this->mailSender->sendCustomerCreation($customer, $values2['password']);
                //$this->sess->registerSuccess = true;
                $this->flashMessage('Registrace proběhla v pořádku.', 'success');
                $this->redirect('User:login');
            }

            $this->flashMessage('Při zpracovávání formuláře došlo k chybě. Zkuste to znovu.', 'error');
        };

        return $form;
    }

    protected function createComponentPasswordRecoveryForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addEmail('email', 'E-mail')
            ->setRequired('Prosím vyplňte svůj e-mail')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addInvisibleReCaptcha('captcha', true, 'Bez ověření skrz Recaptchu nelze pokračovat. Zkuste formulář odeslat znovu.');

        $form->addSubmit('send', 'Obnovit heslo')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'error');
                }
            }
        };

        $form->onValidate[] = function(Form $form) {
            $values2 = $this->request->getPost();
            $customer = $this->em->getCustomerRepository()->findOneBy(['email' => $values2['email']]);
            if (!$customer) {
                $form->addError(sprintf('Zákazník s e-mailem %s neexistuje.', $values2['email']));
            }
        };

        $form->onSuccess[] = function(Form $form, $values) {
            $customer = $this->customerFac->recoveryPassword($values);
            if ($customer) {
                $this->mailSender->sendCustomerPasswordRecovery($customer);
                $this->flashMessage('E-mail s odkazem pro obnovení hesla byl odeslán.', 'success');
                $this->redirect('this');
            }

            $this->flashMessage('Při zpracování formuláře došlo k chybě. Zkuste to znovu.', 'error');
        };

        return $form;
    }

    protected function createComponentNewPasswordForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addHidden('email', '');
        $form->addHidden('hash', '');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Prosím vyplňte své heslo')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Heslo znovu')
            ->setRequired('Prosím vyplňte své heslo znovu')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6)
            ->addRule($form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addSubmit('send', 'Nastavit nové heslo')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'error');
                }
            }
        };

        $form->onValidate[] = function(Form $form) {
            $values2 = $this->request->getPost();
            $customer = $this->em->getCustomerRepository()->findOneBy(['email' => $values2['email']]);
            if (!$customer) {
                $form->addError(sprintf('Zákazník s e-mailem %s neexistuje.', $values2['email']));
            } else if ($customer->recoveryHash != $values2['hash']) {
                $form->addError('Pro obnovu hesla nemáte k dispozici správný hash!');
            }
        };

        $form->onSuccess[] = function(Form $form, $values) {
            $customer = $this->customerFac->newPassword($values);
            if ($customer) {
                $this->flashMessage('Heslo bylo úspěšně nastaveno.', 'success');
                $this->redirect('User:login');
            }

            $this->flashMessage('Při zpracování formuláře došlo k chybě. Zkuste to znovu.', 'error');
        };

        return $form;
    }

    protected function createComponentProfileForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $form->addText('email', 'E-mail')
            ->setRequired('Prosím vyplňte svůj e-mail')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', true)
            ->setOmitted();

        $form->addText('idNo', 'IČ')
            ->setRequired('Prosím vyplňte svoje IČ')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', true)
            ->setOmitted();

        $form->addText('vatNo', 'DIČ')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('readonly', true)
            ->setOmitted();

        $form->addPassword('password', 'Heslo')
            ->setRequired(false)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6);

        $form->addPassword('passwordVerify', 'Heslo znovu')
            ->setRequired(false)
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off')
            ->addRule($form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 6)
            ->addRule($form::EQUAL, 'Hesla se neshodují', $form['password']);

        $form->addText('company', 'Název firmy')
            ->setRequired(false)
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('name', 'Jméno')
            ->setRequired('Prosím vyplňte své jméno')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('surname', 'Příjmení')
            ->setRequired('Prosím vyplňte své příjmení')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('street', 'Ulice a č. p.')
            ->setRequired('Prosím vyplňte svou ulici')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('city', 'Město')
            ->setRequired('Prosím vyplňte své město')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('zip', 'PSČ')
            ->setRequired('Prosím vyplňte své PSČ')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        $form->addText('phone', 'Telefon')
            ->setRequired('Prosím vyplňte svůj telefon')
            ->setHtmlAttribute('class', 'form-control')
            ->setHtmlAttribute('autocomplete', 'off');

        /*$states = $this->em->getProcessStateRepository()->findPairs('id', 'nameForCustomer', ['active' => true, 'notification' => true], ['order' => 'ASC']);

        $form->addCheckboxList('notifications', '', $states);
        $form->addCheckbox('contractNotify', 'nová zakázka + nový dokument');*/

        $form->addSubmit('send', 'Uložit')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary');

        $form->onError[] = function(Form $form) {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'error');
                }
            }
        };

        $form->onSuccess[] = function(Form $form, $values) {
            $values2 = $this->getRequest()->getPost();

            if (isset($values2['addNotifyEmail'])) {
                $customer = $this->em->getCustomerRepository()->find($this->user->id);
                $email = trim($values2['notifyEmail']);
                if ($customer->email == $email) {
                    $this->flashMessage('Na zadaný e-mail je tento účet registrován a nelze ho přidat. Notifikace na tento e-mail chodí automaticky.', 'info');
                } else {
                    $ent = $this->em->getCustomerNotifyEmailRepository()->findOneBy(['customer' => $this->user->id, 'email' => $email]);
                    if (!$ent) {
                        $ent = new CustomerNotifyEmail();
                        $ent->setCustomer($customer);
                        $ent->setEmail($email);
                        $this->em->persist($ent);
                        $this->em->flush($ent);
                        $this->flashMessage('E-mail byl úspěšně přidán.', 'success');
                    } else {
                        $this->flashMessage('E-mail již je zadán.', 'warning');
                    }
                    if ($this->isAjax()) {
                        $this->redrawControl('notify-emails');
                    }
                }
                return;
            }

            $previousData = $this->sess->userProfile ?? [];
            $findDifferences = [];
            foreach ($values as $k => $v) {
                if (in_array($k, ['password', 'passwordVerify', 'notifications', 'contractNotify'])) {
                    continue;
                }
                if (isset($previousData[$k]) && trim($previousData[$k]) != trim($v)) {
                    $findDifferences[] = $k;
                }
            }
            $customer = $this->customerFac->profileSave($values, $this->user->id);
            if ($customer) {
                if ($findDifferences && count($findDifferences)) {
                    $this->mailSender->sendAdminChangeProfile($customer->id, $findDifferences, $previousData);
                }
                $this->flashMessage('Profil byl úspěšně uložen.', 'success');
                $this->redirect('this');
            }

            $this->flashMessage('Při zpracování formuláře došlo k chybě. Zkuste to znovu.', 'error');
        };

        return $form;
    }

    public function handleRemoveNotifyEmail($email)
    {
        $ent = $this->em->getCustomerNotifyEmailRepository()->findOneBy(['customer' => $this->user->id, 'email' => $email]);
        if ($ent) {
            $this->em->remove($ent);
            $this->em->flush($ent);
        }

        if ($this->isAjax()) {
            $this->redrawControl('notify-emails');
        } else {
             $this->redirect('this');
        }
    }
}