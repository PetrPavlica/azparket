<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Currency;
use App\Model\Database\Entity\PermissionItem;
use Nette\Caching\Cache;

class CurrencyPresenter extends BasePresenter
{
    /** @var \App\Model\Facade\Currency @inject */
    public \App\Model\Facade\Currency $currencyFac;

    /**
     * ACL name='Správa měny - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání nové měny'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $entity = $this->em->getCurrencyRepository()->find($id);
            if (!$entity) {
                $this->flashMessage('Měna nebyla nalezena.', 'error');
                $this->redirect('Currency:');
            }
            $arr = $this->ed->get($entity);

            $this['form']->setDefaults($arr);
            $this->template->entity = $entity;
        }
    }

    /**
     * ACL name='Tabulka s všech měn'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Currency::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'Currency:edit');

        $multiAction = $grid->getAction('multiAction');

        $multiAction->addAction('edit', 'Upravit', 'Currency:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit měn'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Currency::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit měnu', 'success'], ['Nepodařilo se uložit měnu!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'currencyFormSuccess'];
        return $form;
    }

    public function currencyFormSuccess($form, $values)
    {
        // ukládám formulář  pomocí automatického save
        $setting = $this->formGenerator->processForm($form, $values, true);
        // clean cache
        $this->cache->clean([
            Cache::TAGS => ["currency"],
        ]);
        $this->redirect('Currency:default');
    }

    /**
     * ACL name='Aktualizace kurzů dle ČNB akce'
     */
    public function handleActualizeExchangeRates()
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_METHOD);

        $messages = $this->currencyFac->checkActualExchangeRates();
        if (is_array($messages)) {
            foreach ($messages as $m) {
                $this->flashMessage($m, 'warning');
            }
        } else {
            $this->flashMessage('Všechny měny byly úspěšně aktualizovany dle ČNB', 'success');
        }

        $this->cache->clean([
            Cache::TAGS => ["currency"],
        ]);
        $this->redirect('this');
    }

}