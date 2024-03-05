<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Language;
use App\Model\Database\Entity\Menu;
use App\Model\Facade\Menu as MenuFacade;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use App\Model\Database\Entity\PermissionItem;
use Nette\Utils\DateTime;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;

class MenuPresenter extends BasePresenter
{
    /** @var MenuFacade @inject */
    public $menuFac;

    /**
     * ACL name='Správa menu'
     * ACL rejection='Nemáte přístup ke správě menu.'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení stránky s úpravou / přidáním nového menu'
     */
    public function renderEdit($id)
    {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);

        if ($id) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('m,ml')->from(Menu::class, 'm')
                ->leftJoin('App\Model\Database\Entity\MenuLanguage', 'ml', 'WITH', 'm.id = ml.menu')
                ->where('m.id = :id')->setParameter('id', $id);
            $result = $qb->getQuery()->getResult();
            $this['menuForm']->setDefaults($this->ed->get($result[0]));
            $this->template->menu = $result[0];
            $langData = [];
            foreach($result as $k => $r) {
                if ($k == 0) {
                    continue;
                }
                $langData[$r->lang->code] = $this->ed->get($r);
            }
            $this->template->dataLang = $langData;
        }
        $this->template->menuForm = $this['menuForm'];
    }

    /**
     * ACL name='Tabulka pro přehled menu'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Menu::class, get_class(), __FUNCTION__);

        //$this->gridGen->setClicableRows($grid, $this, 'Menu:edit');

        $data = $this->db->query('
            SELECT m.*, m.created_at as createdAt, m.updated_at as updatedAt, ml.name, ml.visible, ml.show_up, ml.new_window, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as childCount
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE m.parent_menu_id is null and l.code = ?', $this->locale)->fetchAll();
        
        $grid->setDataSource($data);
        $grid->setDefaultSort(['order_page' => 'ASC']);
        $grid->setTreeView([$this, 'getChildren'], 'childCount');
        
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Menu:edit');
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        $action = $multiAction->addActionCallback('delete', 'Smazat', function($itemId) {
            if ($this->menuFac->deleteMenu($itemId)) {
                $this->flashMessage('Požadované menu bylo odstraněno', 'success');
                $this->redirect('this');
            } else {
                $this->flashMessage('Požadované menu se nepodařilo odstranit!', 'error');
            }
        });
        $action->setIcon('times')
            ->setTitle('Smazat')
            ->setConfirmation(new StringConfirmation('Opravdu chcete tento záznam smazat?'))
            ->setClass('text-danger dropdown-item');

        $grid->addColumnText('name', 'Název');
        $grid->addColumnNumber('order_page', 'Pořadí');
        $column = $grid->addColumnStatus('visible', 'Zobrazit');
        if ($column) {
            $column->setAlign('center')
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'menuVisibleChange'];
        }
        $column = $grid->addColumnStatus('show_up', 'Horní menu');
        if ($column) {
            $column->setAlign('center')
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'menuShowUpChange'];
        }
        $column = $grid->addColumnStatus('new_window', 'Nové okno');
        if ($column) {
            $column->setAlign('center')
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'menuNewWindowChange'];
        }

        return $grid;//$this->tblFactory->create($grid);
    }

    /**
     * ACL name='Formulář pro přidání/edit menu'
     */
    public function createComponentMenuForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Menu::class, $this->user, $this, __FUNCTION__);
        $form->setMessages(['Menu se podařilo uložit.', 'success'], ['Nepodařilo se uložit menu!', 'warning']);
        $form->setRedirect('Menu:');
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'menuFormSuccess'];
        $form->components['parentMenu']->setItems($this->getSelectBoxMenuAll());
        $form->addUpload('image', 'Obrázek')
            ->setRequired(false)// nepovinný
            ->addRule(Form::IMAGE, 'Ikona musí být JPEG, PNG nebo GIF.')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost ikonky je 5 MB', 5120 * 1024/* v bytech */);

        return $form;
    }

    public function menuFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $image = $values->image;
        unset($values->image);
        // ukládám formulář  pomocí automatického save

        $menu = $this->formGenerator->processForm($form, $values, true);

        if ($menu) {
            $this->menuFac->updateLanguages($menu, $values2);

            if ($image->hasFile()) {

                if ($menu->image) {
                    if (file_exists($menu->image)) {
                        @unlink($menu->image);
                    }
                }

                $ext = pathinfo($values->image->name, PATHINFO_EXTENSION);

                $nameEx = $menu->id . '.' . $ext;
                $dir = 'menu-img/';
                $tmp = $dir.$nameEx;

                $values->image->move($tmp);
                $menu->setImage($tmp);
                $this->em->persist($menu);
                $this->em->flush();
            }
        }

        // clean cache
        $this->cache->clean([
            Cache::TAGS => ["menuUrl"],
        ]);

        if (isset($values2['send'])) {
            $this->redirect('Menu:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('Menu:edit', ['id' => $menu->id]);
        }
    }

    public function getChildren($id)
    {
        $data = $this->db->queryArgs('
            SELECT m.*, m.created_at as createdAt, m.updated_at as updatedAt, ml.name, ml.visible, ml.show_up, ml.new_window, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as childCount
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE m.parent_menu_id = ? and l.code = ?
            ORDER BY m.order_page', [$id, $this->locale])->fetchAll();
        return $data;
    }

    public function menuVisibleChange($id, $status)
    {
        $this->menuFac->changeVisible($id, $status, $this->locale);

        if ($this->isAjax()) {
            $data = $this->db->query('
            SELECT m.*, m.created_at as createdAt, m.updated_at as updatedAt, ml.name, ml.visible, ml.show_up, ml.new_window, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as childCount
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and m.id = ?', $this->locale, $id)->fetchAll();
            $this['table']->setDataSource($data);
            $this['table']->redrawControl('items');
        } else {
            $this->redirect('this');
        }
    }

    public function menuShowUpChange($id, $status)
    {
        $this->menuFac->changeShowUp($id, $status, $this->locale);

        if ($this->isAjax()) {
            $data = $this->db->query('
            SELECT m.*, m.created_at as createdAt, m.updated_at as updatedAt, ml.name, ml.visible, ml.show_up, ml.new_window, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as childCount
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and m.id = ?', $this->locale, $id)->fetchAll();
            $this['table']->setDataSource($data);
            $this['table']->redrawControl('items');
        } else {
            $this->redirect('this');
        }
    }

    public function menuNewWindowChange($id, $status)
    {
        $this->menuFac->changeNewWindow($id, $status, $this->locale);

        if ($this->isAjax()) {
            $data = $this->db->query('
            SELECT m.*, m.created_at as createdAt, m.updated_at as updatedAt, ml.name, ml.visible, ml.show_up, ml.new_window, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as childCount
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and m.id = ?', $this->locale, $id)->fetchAll();
            $this['table']->setDataSource($data);
            $this['table']->redrawControl('items');
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
        $item = $this->em->getMenuRepository()->find($item_id);

        /**
         * 1, Find out order of item BEFORE current item
         */
        if (!$prev_id) {
            $previousItem = NULL;
        } else {
            $previousItem = $this->em->getMenuRepository()->find($prev_id);
        }

        /**
         * 2, Find out order of item AFTER current item
         */
        if (!$next_id) {
            $nextItem = NULL;
        } else {
            $nextItem = $this->em->getMenuRepository()->find($next_id);
        }

        /**
         * 3, Find all items that have to be moved one position up
         */
        $itemsToMoveUp = $this->em->getMenuRepository()->createQueryBuilder('m')
            ->where('m.orderPage <= :order')
            ->setParameter('order', $previousItem ? $previousItem->getOrderPage() : 0)
            ->andWhere('m.orderPage > :order2')
            ->setParameter('order2', $item->getOrderPage())
            ->getQuery()
            ->getResult();

        foreach ($itemsToMoveUp as $t) {
            $t->setOrderPage($t->getOrderPage() - 1);
            $this->em->persist($t);
        }

        /**
         * 3, Find all items that have to be moved one position down
         */
        $itemsToMoveDown = $this->em->getMenuRepository()->createQueryBuilder('m')
            ->where('m.orderPage >= :order')
            ->setParameter('order', $nextItem ? $nextItem->getOrderPage() : 0)
            ->andWhere('m.orderPage < :order2')
            ->setParameter('order2', $item->getOrderPage())
            ->getQuery()
            ->getResult();

        foreach ($itemsToMoveDown as $t) {
            $t->setOrderPage($t->getOrderPage() + 1);
            $this->em->persist($t);
        }

        /**
         * Update current item order
         */
        if ($previousItem) {
            $item->setOrderPage($previousItem->getOrderPage() + 1);
        } else if ($nextItem) {
            $item->setOrderPage($nextItem->getOrderPage() - 1);
        } else {
            $item->setOrderPage(1);
        }

        $this->em->persist($item);
        $this->em->flush();

        $this->flashMessage("Pořadí bylo úspěšně upraveno.", 'success');

        $this->redirect('this');
    }

    private function getSelectBoxMenuAll($parent = null, $output = [], $level = 1)
    {
        //$menu = $this->em->getRepository(Menu::class)->findBy([], ['id' => 'ASC']);
        $lang = $this->em->getLanguageRepository()->findOneBy(['defaultCode' => true]);
        $menu = $this->db->queryArgs('
            SELECT m.id, ml.name, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as count
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            WHERE ml.lang_id = '.$lang->id.' and m.parent_menu_id '.($parent ? ' = '.$parent : 'is null').'
            ORDER BY m.order_page ASC
        ', [])->fetchAll();

        foreach ($menu as $m) {
            $prefix = '';
            for ($i = 1; $i < $level; $i++) {
                $prefix .= '&nbsp;&nbsp;&nbsp;';
            }
            $prefix = html_entity_decode($prefix);
            $output[$m->id] = $prefix.$m->name;
            if ($m->count) {
                $output = $this->getSelectBoxMenuAll($m->id, $output, $level + 1);
            }
        }

        return $output;
    }

    public function deleteMenu($id)
    {
        $this->db->query('DELETE FROM article_in_menu WHERE menu_id = '.$id);
        $this->db->query('DELETE FROM menu_language WHERE menu_id = '.$id);
        $this->db->query('DELETE FROM menu WHERE id = '.$id);
        $select = $this->db->query('SELECT id FROM menu WHERE parent_menu_id = ?', $id)->fetchField();
        if ($select) {
            $this->deleteMenu($select);
        }
    }

    public function handleDelete($id)
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->deleteMenu($id);
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->flashMessage('Menu bylo úspěšně smazáno.');
        //$this->redirect('Menu:');
    }

    public function handleDeleteImg($menuId)
    {
        $res = $this->menuFac->deleteImage($menuId);
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