<?php

namespace App\IntraModule\Presenters;

use App\Model\Facade\BannerPartner as BannerPartnerFacade;
use App\Model\Database\Entity\BannerPartner;
use App\Model\Database\Entity\BannerPartnerLanguage;
use Nette\Application\UI\Form;

class BannerPartnerPresenter extends BasePresenter
{
    /** @var BannerPartnerFacade @inject */
    public $bannerFac;

    public function renderEdit($id)
    {
        if ($id) {
            $qb = $this->em->getBannerPartnerRepository()->createQueryBuilder('b');
            $qb->select('b,bl')
                ->leftJoin('App\Model\Database\Entity\BannerPartnerLanguage', 'bl', 'WITH', 'b.id = bl.banner')
                ->where('b.id = :id')->setParameter('id', $id);
            $result = $qb->getQuery()->getResult();
            $this['bannerForm']->setDefaults($this->ed->get($result[0]));
            $this->template->banner = $result[0];
            $langData = [];
            foreach($result as $k => $r) {
                if ($k == 0 || !$r) {
                    continue;
                }
                $langData[$r->lang->code] = $this->ed->get($r);
            }
            $this->template->dataLang = $langData;
        } else {
            $this['bannerForm']['orderBanner']->setDefaultValue(0);
        }
    }

    /**
     * ACL name='Tabulka s přehledem bannerů'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(BannerPartner::class, get_class(), __FUNCTION__);
        $data = $this->db->query('
            SELECT b.*, bl.name, bl.active, NULL as has_children
            FROM banner_partner b
            LEFT JOIN banner_partner_language bl ON b.id = bl.banner_id
            LEFT JOIN language l ON bl.lang_id = l.id
            WHERE l.code = ?', $this->locale)->fetchAll();
        $grid->setDataSource($data);
        $grid->setDefaultSort(['order_banner' => 'ASC']);
        $grid->setTreeView([$this, 'getChildren'], 'has_children');

        $grid->addColumnText('name', 'Název');
        $grid->addColumnNumber('order_banner', 'Pořadí');
        $column = $grid->addColumnStatus('active', 'Zobrazit');
        if ($column) {
            $column->setAlign('center')
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'bannerActiveChange'];
        }

        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'BannerPartner:edit', ['id' => 'id', 'slug' => 'type']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
                
        $action = $multiAction->addActionCallback('delete', 'Smazat', function($itemId) {
            if ($this->bannerFac->deleteBanner($itemId)) {
                $this->flashMessage('Požadovaný banner byl odstraněn', 'success');
                $this->redirect('this');
            } else {
                $this->flashMessage('Požadovaný banner se nepodařilo odstranit!', 'error');
            }
        });
        $action->setIcon('times')
            ->setTitle('Smazat')
            ->setConfirmation(new \Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation('Opravdu chcete tento záznam smazat?'))
            ->setClass('text-danger dropdown-item');
        
        $grid->setColumnsOrder(['id','name', 'type', 'order_banner', 'active', 'updatedAt', 'createdAt']);

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit banneru'
     */
    public function createComponentBannerForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(BannerPartner::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Banner se podařilo uložit.', 'success'], ['Nepodařilo se uložit banner!', 'warning']);
        $form->setRedirect('BannerPartner:');
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'bannerFormSuccess'];
        $form->addUpload('image', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 3 MB', 3072 * 1024/* v bytech */);

        return $form;
    }

    public function bannerFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $image = $values->image;
        unset($values->image);
        // ukládám formulář  pomocí automatického save
        $banner = $this->formGenerator->processForm($form, $values, true);

        if ($banner) {
            $this->bannerFac->updateLanguages($banner, $values2);

            if ($image->hasFile()) {

                if ($banner->image) {
                    if (file_exists($banner->image)) {
                        @unlink($banner->image);
                    }
                }

                $ext = pathinfo($image->name, PATHINFO_EXTENSION);

                $nameEx = $banner->id . '.' . $ext;
                $dir = '_data/partners/';
                $tmp = $dir.$nameEx;

                $image->move($tmp);
                $banner->setImage($tmp);

                $this->em->persist($banner);
                $this->em->flush();
            }
        }

        if (isset($values2['send'])) {
            $this->redirect('BannerPartner:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('BannerPartner:edit', ['id' => $banner->id]);
        }
    }

    public function bannerActiveChange($id, $status)
    {
        $this->bannerFac->changeActive($id, $status, $this->locale);

        if ($this->isAjax()) {
            $data = $this->db->query('
            SELECT b.*, bl.name, bl.active, NULL as has_children
            FROM banner_partner b
            LEFT JOIN banner_partner_language bl ON b.id = bl.banner_id
            LEFT JOIN language l ON bl.lang_id = l.id
            WHERE l.code = ?', $this->locale)->fetchAll();
            $this['table']->setDataSource($data);
            $this['table']->redrawItem($id, 'id');
        } else {
            $this->redirect('this');
        }
    }

    /**
     * @param  int      $item_id
     * @param  int|NULL $prev_id
     * @param  int|NULL $next_id
     * @return void
     */
    public function handleSort($item_id, $prev_id, $next_id)
    {
        if (!$item_id) {
            return;
        }
        $item = $this->bannerFac->get()->find($item_id);

        /**
         * 1, Find out order of item BEFORE current item
         */
        if (!$prev_id) {
            $previousItem = NULL;
        } else {
            $previousItem = $this->bannerFac->get()->find($prev_id);
        }

        /**
         * 2, Find out order of item AFTER current item
         */
        if (!$next_id) {
            $nextItem = NULL;
        } else {
            $nextItem = $this->bannerFac->get()->find($next_id);
        }

        /**
         * 3, Find all items that have to be moved one position up
         */
        $itemsToMoveUp = $this->bannerFac->get()->createQueryBuilder('b')
            ->where('b.orderBanner <= :order')
            ->setParameter('order', $previousItem ? $previousItem->getOrderBanner() : 0)
            ->andWhere('b.orderBanner > :order2')
            ->setParameter('order2', $item->getOrderBanner())
            ->getQuery()
            ->getResult();

        foreach ($itemsToMoveUp as $t) {
            $t->setOrderBanner($t->getOrderBanner() - 1);
            $this->em->persist($t);
        }

        /**
         * 3, Find all items that have to be moved one position down
         */
        $itemsToMoveDown = $this->bannerFac->get()->createQueryBuilder('b')
            ->where('b.orderBanner >= :order')
            ->setParameter('order', $nextItem ? $nextItem->getOrderBanner() : 0)
            ->andWhere('b.orderBanner < :order2')
            ->setParameter('order2', $item->getOrderBanner())
            ->getQuery()
            ->getResult();

        foreach ($itemsToMoveDown as $t) {
            $t->setOrderBanner($t->getOrderBanner() + 1);
            $this->em->persist($t);
        }

        /**
         * Update current item order
         */
        if ($previousItem) {
            $item->setOrderBanner($previousItem->getOrderBanner() + 1);
        } else if ($nextItem) {
            $item->setOrderBanner($nextItem->getOrderBanner() - 1);
        } else {
            $item->setOrderBanner(1);
        }

        $this->em->persist($item)->flush();

        $this->flashMessage("Pořadí bylo úspěšně upraveno.", 'success');

        $this->redirect('this');
    }

    public function handleDelete($id)
    {
        $banner = $this->db->table('banner_partner')->wherePrimary($id);
        if ($banner) {
            $this->bannerFac->deleteImage($id);
            $this->db->query('DELETE FROM banner_partner_language WHERE banner_id = ' . $id);
            $this->db->query('DELETE FROM banner_partner WHERE id = ' . $id);
            $this->flashMessage('Banner byl úspěšně smazán.', 'info');
        } else {
            $this->flashMessage('Banner se nepodařilo nalézt.', 'error');
        }
    }

    public function handleDeleteImg($bannerId)
    {
        $res = $this->bannerFac->deleteImage($bannerId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        if ($this->isAjax()) {
            $this->redrawControl('img');
        } else {
            $this->redirect('this');
        }
    }
}