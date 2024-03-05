<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\WebSetting;
use App\Model\Facade\WebSetting as WebSettingFacade;
use Nette;
use App\Model\Database\Entity\PermissionItem;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;

class WebSettingPresenter extends BasePresenter
{
    /** @var WebSettingFacade @inject */
    public $webSettingFac;

    /**
     * ACL name='Správa webového nastavení'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    public function renderEdit($id)
    {
        if ($id) {
            $qb = $this->em->getWebSettingRepository()->createQueryBuilder('w');
            $qb->select('w,wl')
                ->leftJoin(\App\Model\Database\Entity\WebSettingLanguage::class, 'wl', 'WITH', 'w.id = wl.setting')
                ->where('w.id = :id')->setParameter('id', $id);
            $result = $qb->getQuery()->getResult();
            $arr = $this->ed->get($result[0]);
            $this->template->entity = $result[0];
            $this['form']->setDefaults($arr);
            $langData = [];
            foreach($result as $k => $r) {
                if ($k == 0 || !$r) {
                    continue;
                }
                $langData[$r->lang->code] = $this->ed->get($r);
            }
            $this->template->dataLang = $langData;
        }

        $this->template->id = $id;
    }

    /**
     * ACL name='Tabulka webového nastavení'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(WebSetting::class, get_class(), __FUNCTION__);
        $grid = $this->gridGen->setClicableRows($grid, $this, 'WebSetting:edit');

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'WebSetting:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        if ($this->user->getId() == 1) {
            $this->gridGen->addButtonDeleteCallback();
        }

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit webového nastavení'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(WebSetting::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Podařilo se uložit webové nastavení', 'success'], ['Nepodařilo se uložit webové nastavení!', 'warning']);
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'webSettingFormSuccess'];
        $form->onError[] = [$this, 'webSettingFormError'];

        if ($this->user->getId() != 1) {
            $form->getComponent('description')->setDisabled();
            $form->getComponent('type')->setDisabled();
            $form->getComponent('code')->setDisabled();
        }
        return $form;
    }

    public function webSettingFormError($form)
    {
        bdump($form->getErrors());
    }

    public function webSettingFormSuccess($form, $values)
    {

        $values2 = $this->getRequest()->getPost();

        if ($this->user->getId() == 1) {
            $setting = $this->formGenerator->processForm($form, $values, true);
        } else {
            $setting = $this->em->getWebSettingRepository()->find($values->id);
        }

        if ($setting) {
            $this->webSettingFac->updateLanguages($setting, $values2);
            $this->flashMessage('Uloženo', 'success');
        } else {
            $this->flashMessage('Záznam se nepodařilo uložit', 'error');
            return;
        }

        // Clean cache
        $this->cache->clean([
            Cache::TAGS => ["webSetting"],
        ]);
        if (isset($values2['send'])) {
            $this->redirect('WebSetting:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('WebSetting:edit', ['id' => $setting->id]);
        }
    }

}
