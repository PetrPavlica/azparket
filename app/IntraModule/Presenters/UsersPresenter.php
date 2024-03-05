<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\User;
use App\Model\Database\Entity\PermissionItem;
use Doctrine\Common\Collections\Criteria;
use Nette\Security\Passwords;

class UsersPresenter extends BasePresenter
{
    /** @var Passwords @inject */
    public Passwords $passwords;

    /**
     * ACL name='Správa uživatelů'
     * ACL rejection='Nemáte přístup ke správě uživatelů.'
     */
    public function startup()
    {
        parent::startup();
        $this->sess = $this->session->getSection('users');
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nového uživatele'
     */
    public function renderEdit($id, $backHomepage = "")
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id != $this->user->id) { //ošetření, že pokud nemám oprávnění na tabulku, tak nemůžu upravovat jiného uživatele než sebe.
            $this->acl->mapFunction($this, $this->user, get_class(), 'createComponentTable');
        }
        if ($id == 1 && $this->user->id != 1) {
            $this->flashMessage('Tohoto uživatele nelze upravit!', 'warning');
            $this->redirect('Users:');
        }

        if ($id) {
            $entity = $this->em->getUserRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Uživatel nebyl nalezen!', 'error');
                $this->redirect('Users:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this['form']->setDefaults(['redirectHomepage' => $backHomepage]);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s přehledem uživatelů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(User::class, get_class(), __FUNCTION__, 'default', ['isHidden' => 0]);

        $grid = $this->gridGen->setClicableRows($grid, $this, 'Users:edit');
        $this->gridGen->addExportToExcel($grid, $this);
        $this->gridGen->addExportToCSV($grid, $this);

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Users:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $this->gridGen->addButtonDeleteCallback();
        $grid->allowRowsMultiAction('multiAction', 'delete', function($item) {
            if ($item->id == 1) {
                return false;
            } else {
                return true;
            }
        });
        return $grid;

    }

    /**
     * ACL name='Formulář pro přidání/editaci uživatelů'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(User::class, $this->user, $this, __FUNCTION__);

        $form->setMessages(['Podařilo se uložit uživatele', 'success'], ['Nepodařilo se uživatele uložit!', 'warning']);
        $form->isRedirect = false;
        $form->addHidden('redirectHomepage');
        $form->onSuccess[] = [$this, 'processFormUser'];
        $form->onValidate[] = function($form) {
            $values = $form->getValues();
            if ($values->username != '') { // Kontrola jedinečnosti Data
                $criteriaStart = new Criteria();
                $criteriaStart->where(Criteria::expr()->notIn('id', [$values->id]));
                $criteriaStart->andWhere(Criteria::expr()->eq('username', $values->username));
                $service = $this->em->getUserRepository()->matching($criteriaStart)->getValues();
                if ($service) {
                    $form->addError('Pozor! Uživatele se nepodařilo uložit. Pod tímto Přihlašovacím jmén (loginem) máte již uloženého jiného uživatele!');
                }
            }
        };
        return $form;
    }

    public function processFormUser($form, $values)
    {
        $values2 = $this->request->getPost();

        if (isset($values['password'])) {
            if ($values['password'] != '') {
                $values['password'] = $this->passwords->hash($values['password']);
            } else {
                unset($values['password']);
            }
        }

        $user = $this->formGenerator->processForm($form, $values, true);

        if (!$user) {
            return;
        }

        if($user && !$user->menu) {
            $user->setMenu('{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1,"10":1,"11":1,"12":1,"13":1,"14":1,"15":1,"16":1,"17":1,"18":1,"19":1,"20":1}');
            $this->em->flush();
        }

        if ($user && $user->id == $this->getUser()->getId()) {
            $arr = $this->ed->get($user);
            unset($arr['password']);

            if ($user->group) {
                $arr['groupName'] = $user->group->name;
            }

            $this->getUser()->login(new \Nette\Security\SimpleIdentity($user->getId(), $user->getPermissions(), $arr));
        }

        if (isset($values2['send'])) {
            if ($values->redirectHomepage == "1") {
                $this->redirect('Homepage:default');
            } else {
                $this->redirect('Users:');
            }
        } else {
            $this->redirect('Users:edit', ['id' => $user->id]);
        }
    }
}
