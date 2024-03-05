<?php

namespace App\IntraModule\Presenters;


use App\Model\Facade\Banner as BannerFacade;
use Nette\Application\UI\Form;
use App\Model\Database\Entity;

class BannerPresenter extends BasePresenter
{
    /** @var BannerFacade @inject */
    public $bannerFac;

    /**
     * ACL name='Správa bannerů'
     * ACL rejection='Nemáte přístup k správě bannerů.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, Entity\PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidání banneru'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($id) {
            $qb = $this->em->getBannerRepository()->createQueryBuilder('b');
            $qb->select('b,bl')
                ->leftJoin('App\Model\Database\Entity\BannerLanguage', 'bl', 'WITH', 'b.id = bl.banner')
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
        $grid = $this->gridGen->generateGridByAnnotation(\App\Model\Database\Entity\Banner::class, get_class(), __FUNCTION__);
        $data = $this->db->query('
            SELECT b.*, b.created_at as createdAt, b.updated_at as updatedAt, bl.name, bl.active, NULL as has_children
            FROM banner b
            LEFT JOIN banner_language bl ON b.id = bl.banner_id
            LEFT JOIN language l ON bl.lang_id = l.id
            WHERE l.code = ?', $this->locale)->fetchAll();
        $grid->setDataSource($data);
        $grid->setDefaultSort(['order_banner' => 'ASC']);
        $grid->setTreeView([$this, 'getChildren'], 'has_children');
        $grid->setSortable(true);
        
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Banner:edit');
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

        $grid->setColumnsOrder(['id','name', 'type', 'order_banner', 'active', 'updatedAt', 'createdAt']);
        
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit banneru'
     */
    public function createComponentBannerForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(\App\Model\Database\Entity\Banner::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Banner se podařilo uložit.', 'success'], ['Nepodařilo se uložit banner!', 'warning']);
        $form->setRedirect('Banner:');
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'bannerFormSuccess'];
        $form->addUpload('image', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 5 MB', 5120 * 1024/* v bytech */);

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
                $dir = '_data/banners/';
                $tmp = $dir.$nameEx;

                $image->move($tmp);
                $banner->setImage($tmp);

                $this->em->persist($banner);
                $this->em->flush();
            }
        }

        if (isset($values2['send'])) {
            $this->redirect('Banner:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('Banner:edit', ['id' => $banner->id]);
        }
    }

    public function bannerActiveChange($id, $status)
    {
        $this->bannerFac->changeActive($id, $status, $this->locale);

        if ($this->isAjax()) {
            $data = $this->db->query('
            SELECT b.*, bl.name, bl.active, NULL as has_children
            FROM banner b
            LEFT JOIN banner_language bl ON b.id = bl.banner_id
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
        $item = $this->em->getBannerRepository()->find($item_id);

        /**
         * 1, Find out order of item BEFORE current item
         */
        if (!$prev_id) {
            $previousItem = NULL;
        } else {
            $previousItem = $this->em->getBannerRepository()->find($prev_id);
        }

        /**
         * 2, Find out order of item AFTER current item
         */
        if (!$next_id) {
            $nextItem = NULL;
        } else {
            $nextItem = $this->em->getBannerRepository()->find($next_id);
        }

        /**
         * 3, Find all items that have to be moved one position up
         */
        $itemsToMoveUp = $this->em->getBannerRepository()->createQueryBuilder('b')
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
        $itemsToMoveDown = $this->em->getBannerRepository()->createQueryBuilder('b')
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

        $this->em->persist($item);
        $this->em->flush();

        $this->flashMessage("Pořadí bylo úspěšně upraveno.", 'success');

        $this->redirect('this');
    }

    public function handleDelete($id)
    {
        $banner = $this->db->table('banner')->wherePrimary($id);
        if ($banner) {
            $this->bannerFac->deleteImage($id);
            $this->db->query('DELETE FROM banner_language WHERE banner_id = ' . $id);
            $this->db->query('DELETE FROM banner WHERE id = ' . $id);
            $this->flashMessage('Banner byl úspěšně smazán.', 'info');
        } else {
            $this->flashMessage('Banner se nepodařilo nalézt.', 'error');
        }
    }

    public function handleDeleteImg($bannerId)
    {
        bdump("test");
        $res = $this->bannerFac->deleteImage($bannerId);
        if ($res) {
            $this->flashMessage('Podařilo se smazat obrázek.');
        } else {
            $this->flashMessage('Nepodařilo se smazat obrázek.', 'warning');
        }

        //if ($this->isAjax()) {
            $this->redrawControl('img');
        /*} else {
            $this->redirect('this');
        }*/
    }
}