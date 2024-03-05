<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\IUserStorage;

final class SignPresenter extends BasePresenter
{
	/** @persistent */
	public string $backlink = '';

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLayout(__DIR__.'/templates/@login.latte');
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Uživatelské jméno')
            ->setRequired('Prosím vyplňte své uživatelské jméno')
            ->setHtmlAttribute('placeholder', '')
            ->setHtmlAttribute('class', 'form-control');

        $form->addPassword('password', 'Heslo')
            ->setRequired('Prosím vyplňte své heslo')
            ->setHtmlAttribute('class', 'form-control');

        $form->addCheckbox('remember', 'Zapamatovat');

        $form->addSubmit('send', 'Přihlásit se')
            ->setHtmlAttribute('class', 'btn btn-lg btn-primary btn-block');

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
                    $this->flashMessage('Zákaznický účet není schválen.', 'warning');
                } else {
                    $this->flashMessage('Špatné přihlašovací údaje', 'error');
                }
            }

            if ($loginSuccess) {
                $this->redirect('Homepage:');
            }
        };

        return $form;
    }
}
