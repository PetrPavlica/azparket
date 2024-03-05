<?php

namespace App\IntraModule\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\IUserStorage;

class SignPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();
        if ($this->user->loggedIn) {
            $this->redirect('Homepage:default');
        }
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout('login');
    }

    public function renderReset($hash)
    {
        $user = null;
        if ($hash && $hash != "") {
            $user = $this->userFacade->gEMUser()->findOneBy(['recoveryHash' => $hash]);
        }
        if (!$user) {
            $this->flashMessage('Uvedený odkaz již není platný!', 'warning');
            $this->redirect('Sign:in');
        }
        $this['resetForm']->setDefaults(['hash' => $hash]);
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Prosím vyplňte své uživatelské jméno')
            ->setHtmlAttribute('placeholder', 'Zadejte uživatelské jméno')
            ->setHtmlAttribute('class', 'form-control');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Prosím vyplňte své heslo')
            ->setHtmlAttribute('placeholder', 'Zadejte heslo')
            ->setHtmlAttribute('class', 'form-control');

        $form->addCheckbox('remember', 'Zapamatovat');

        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-primary btn-lg');

        $form->onError[] = function(Form $form): void {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };

        $form->onSuccess[] = function(Form $form, $values): void {
            $loginSuccess = false;
            try {
                $this->getUser()->login($values->username, $values->password);
                $this->getUser()->setExpiration($values->remember ? '14 days' : '30 minutes', IUserStorage::CLEAR_IDENTITY);
                $this->flashMessage('Přihlášení bylo úspěšné!', 'success');
                $loginSuccess = true;
            } catch (AuthenticationException $e) {
                $this->flashMessage('Špatné přihlašovací údaje', 'error');
            }

            if ($loginSuccess) {
                $this->redirect('Homepage:');
            }
        };
        return $form;
    }

    protected function createComponentRecoveryForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('send')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary btn-block');

        $form->onError[] = function(Form $form): void {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };

        $form->onSuccess[] = function(Form $form, $values): void {
            if ($values->email) {
                $this->mailSender->sendRecoveryEmail($values->email);
            }
            $this->flashMessage('Na uvedenou adresu Vám byl zaslán email s dalšími instrukcemi.', 'success');
        };

        return $form;
    }

    protected function createComponentResetForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addPassword('pass1')
            ->setRequired('Prosím zadejte nové heslo.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addPassword('pass2')
            ->setRequired('Prosím zadejte nové heslo ještě jednou.')
            ->setHtmlAttribute('class', 'form-control');

        $form->addSubmit('send')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary btn-block');

        $form->addHidden('hash');

        $form->onError[] = function(Form $form): void {
            if ($form->hasErrors()) {
                foreach ($form->getErrors() as $e) {
                    $this->flashMessage($e, 'warning');
                }
            }
        };

        $form->onSuccess[] = function(Form $form, $values): void {
            if ($values->pass1 != $values->pass2) {
                $this->flashMessage('Zadané hesla nejsou stejná! Prosím vyplňte je znovu.', 'warning');
                return;
            }
            $user = null;
            $hash = $values->hash;
            if ($hash && $hash != "") {
                $user = $this->userFacade->gEMUser()->findOneBy(['recoveryHash' => $hash]);
            }
            if (!$user) {
                $this->flashMessage('Uvedený odkaz již není platný!', 'warning');
                $this->redirect('Sign:in');
            }
            $user->setRecoveryHash(null);
            $user->setPassword(Nette\Security\Passwords::hash($values->pass1));
            $this->userFacade->save();

            $this->flashMessage('Heslo bylo úspěšne upraveno.', 'success');
            $this->redirect('Sign:');
        };

        return $form;
    }
}