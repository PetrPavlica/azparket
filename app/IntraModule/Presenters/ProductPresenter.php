<?php

namespace App\IntraModule\Presenters;

use App\Model\Database\Entity\Product;
use App\Model\Database\Entity\ProductLanguage;
use App\Model\Database\Entity\PermissionItem;
use App\Model\ACLForm;
use App\Model\Facade\Product as ProductFacade;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Utils\DateTime;
use Ublaboo\ImageStorage\ImageStorage;

class ProductPresenter extends BasePresenter
{
    /** @var ProductFacade @inject */
    public $productFac;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /**
     * ACL name='Správa produktů - sekce'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        bdump($this->em->getProductRepository()->findOneBy([]));
    }

    public function renderEdit($id) {
        if ($id) {
            $qb = $this->em->createQueryBuilder();
            $qb->select('p,pl')->from(Product::class, 'p')
                ->leftJoin(ProductLanguage::class, 'pl', 'WITH', 'p.id = pl.product')
                ->where('p.id = :id')->setParameter('id', $id);
            $result = $qb->getQuery()->getResult();
            if (!$result) {
                $this->flashMessage('Požadovaný záznam se nepodařilo nalézt.', 'info');
                $this->redirect('Product:');
            }
            $this->template->product = $product = $result[0];
            $arr = $this->ed->get($result[0]);
            if ($result[0]->menu) {
                foreach($result[0]->menu as $m) {
                    $arr['menu'][] = $m->menu->id;
                }
            }
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
    }

    /**
     * ACL name='Tabulka produktů'
     */
    public function createComponentTable() {
        $grid = $this->gridGen->generateGridByAnnotation(Product::class, get_class(), __FUNCTION__);
        $this->gridGen->setClicableRows($grid, $this, 'Product:edit');

        // actions
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Product:edit', ['id' => 'id']);//, 'slug' => 'type'
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');
        $this->gridGen->addButtonDeleteCallback();

        // source
        $data = $this->db->query('
            SELECT
                p.*,
                p.active as productActive,
                p.created_at as createdAt, p.updated_at as updatedAt,
                pl.id as plId, pl.name, pl.active as plActive
            FROM product p
            LEFT JOIN product_language pl ON p.id = pl.product_id
            LEFT JOIN language l ON pl.lang_id = l.id
            WHERE pl.id IS NULL OR l.code = ?
        ', $this->locale)->fetchAll();
        $grid->setDataSource($data);

        // cols
        $column = $grid->addColumnText('name', 'Název');
        if ($column) {
            $column->addCellAttributes(['class' => 'clickable'])
                ->setSortable(true)
                ->setFilterText('pl.name');
        }

        /*$column = $grid->addColumnText('menu', 'Zařazeno');
        if ($column) {
            $column->addCellAttributes(['class' => 'clickable'])
                ->setRenderer(function($item) {
                    $menu = $this->db->query('
                    SELECT ml.name
                    FROM menu m
                    LEFT JOIN menu_language ml ON m.id = ml.menu_id
                    LEFT JOIN product_in_menu pim ON pim.menu_id = m.id
                    LEFT JOIN product p ON p.id = pim.product_id
                    LEFT JOIN language l ON ml.lang_id = l.id
                    WHERE p.id = ? and l.code = ?
                    ', $item->id, $this->locale)->fetchAll();
                    $content = [];
                    if ($menu) {
                        foreach($menu as $m) {
                            $content[] = $m->name;
                        }
                    }
                    return implode(', ', $content);
            });
            $column->setFilterText('a.menu')->setCondition(function ($sql, $value) {
                $sql = '
                    SELECT p.order_product, p.active as productActive, p.is_imported,
                        p.klic_polozky, p.nazev_polozky,
                        p.alter_nazev,
                        p.created_at as createdAt, p.updated_at as updatedAt,

                        pl.id as plId, pl.name, pl.active
                    FROM product p
                    LEFT JOIN product_language pl ON p.id = pl.product_id
                    LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                    LEFT JOIN menu m ON m.id = pim.menu_id
                    LEFT JOIN menu_language ml ON m.id = ml.menu_id
                    LEFT JOIN language l ON pl.lang_id = l.id
                    WHERE (pl.id IS NULL OR l.code = ?) and ml.name like "%'.$value.'%"
                ';
                return $sql;
            });
        }*/

        $column = $grid->addColumnStatus('active', 'Zobrazit');
        if ($column) {
            $column->setSortable(true)
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'productActiveChange'];
            $column->setFilterSelect(['' => 'Vše', 1 => 'Ano', 0 => 'Ne'])->setTranslateOptions();
        }
                
        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit produktu'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Product::class, $this->user, $this, __FUNCTION__);

        $form->components['menu']->setItems($this->getSelectBoxMenuAll());
        $form->setMessages(['Podařilo se uložit produkt', 'success'], ['Nepodařilo se uložit produkt!', 'warning']);
        $form->setRedirect('Product:default');
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'productFormSuccess'];
        return $form;
    }

    public function productFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();
        $values['updated'] = new DateTime();

        // ukládám formulář  pomocí automatického save
        $product = $this->formGenerator->processForm($form, $values, true);

        if ($product) {
            $this->productFac->updateLanguages($product, $values2);
        }

        // clean cache
        $this->cache->clean([
            Cache::TAGS => ["productUrl"],
        ]);

        if (isset($values2['send'])) {
            $this->redirect('Product:default');
        } elseif (isset($values2['sendSave'])) {
            $this->redirect('Product:edit', ['id' => $product->id]);
        }
    }

    public function productActiveChange($id, $status)
    {
        $this->productFac->changeActive($id, $status, $this->locale);

        if ($this->isAjax()) {
            $this['table']->getGrid()->redrawItem($id, 'p.id');
        } else {
            $this->redirect('this');
        }
    }

    private function getSelectBoxMenuAll($parent = null, $output = [], $level = 1)
    {
        //$menu = $this->em->getRepository(Menu::class)->findBy([], ['id' => 'ASC']);
        $lang = $this->em->getLanguageRepository()->findOneBy(['defaultCode' => true]);
        $menu = $this->db->query('
            SELECT m.id, ml.name, (
            SELECT COUNT(mm.id) 
            FROM menu mm 
            WHERE mm.parent_menu_id = m.id) as count
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            WHERE ml.lang_id = '.$lang->id.' and (m.parent_menu_id '.($parent ? ' = '.$parent : 'is null').' '.($parent && $parent == 3 ? 'or m.id = 1' : '').')
            ORDER BY m.order_page ASC
        ')->fetchAll();

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

    /**
     * ACL name='Formulář pro přidání/edit galerie produktu'
     */
    public function createComponentGalleryForm()
    {
        $form = new ACLForm();
        $form->setScope($this->user, get_class(), __FUNCTION__, $this->acl);
        $form->addHidden('id');
        $form->addSubmitAcl('send', 'Uložit změny');
        $form->onSuccess[] = [$this, 'galleryFormSuccess'];
        return $form;
    }

    public function galleryFormSuccess(Form $form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        $this->productFac->updateGallery($values2);

        $this->flashMessage('Fotogalerie byla úspěšně upravena!', 'success');

        if ($this->isAjax()) {
            $this->redrawControl('product-images');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit souborů produktu'
     */
    public function createComponentFilesForm()
    {
        $form = new ACLForm();
        $form->setScope($this->user, get_class(), __FUNCTION__, $this->acl);
        $form->addHidden('id');
        $form->addSubmitAcl('send', 'Uložit změny');
        $form->onSuccess[] = [$this, 'filesFormSuccess'];
        return $form;
    }

    public function filesFormSuccess(Form $form, $values)
    {
        $values2 = $this->getRequest()->getPost();

        $this->productFac->updateFiles($values2);

        $this->flashMessage('Soubory byly úspěšně upraveny!', 'success');

        if ($this->isAjax()) {
            $this->redrawControl('product-files');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit fotogalerie produktu'
     */
    public function createComponentPhotogalleryForm()
    {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->onSuccess[] = [$this, 'photogalleryFormSuccess'];
        return $form;
    }

    public function photogalleryFormSuccess(Form $form, $values)
    {
        if (!isset($this->sess->files)) {
            $this->sess->files = [];
        }
        if (!isset($this->sess->msg)) {
            $this->sess->msg = [];
        }
        $path = '_data/product-images/'.$values->id.'/';
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $res = $this->productFac->addImage($values->id, $path.$f->getName());
                if ($res) {
                    $f->move($path . $f->getName());
                    $this->sess->files[] = $f->getName();
                } else {
                    $this->sess->msg[] = 'Soubor '.$f->getName().' je již nahraný.';
                }
            }
        }
    }

    public function handleUpdatePhotogallery()
    {
        if ($this->sess->files) {
            foreach ($this->sess->files as $f) {
                $this->flashMessage('Obrázek ' . $f . ' byl úspěšně nahrán.', 'success');
            }
        }
        if ($this->sess->msg) {
            foreach ($this->sess->msg as $m) {
                $this->flashMessage($m, 'info');
            }
        }
        unset($this->sess->files);
        unset($this->sess->msg);
        if ($this->isAjax()) {
            $this->redrawControl('product-images');
        }
    }

    public function handleDeleteImg($imgId)
    {
        $res = $this->productFac->deleteImage($imgId);
        if ($res) {
            $this->flashMessage('Obrázek '.$res.' byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Obrázek se nepodařilo smazat.', 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('product-images');
        }
    }

    public function createComponentUploadFilesForm()
    {
        $form = new ACLForm();
        $form->addHidden('id');
        $form->onSuccess[] = [$this, 'uploadFilesFormSuccess'];
        return $form;
    }

    public function uploadFilesFormSuccess(Form $form, $values)
    {
        if (!isset($this->sess->files)) {
            $this->sess->files = [];
        }
        if (!isset($this->sess->msg)) {
            $this->sess->msg = [];
        }
        $path = '_data/product-files/'.$values->id.'/';
        if (!is_dir('_data/product-files')) {
            mkdir('_data/product-files', 0777);
        }
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $res = $this->productFac->addFile($values->id, $path.$f->getName());
                if ($res) {
                    $f->move($path . $f->getName());
                    $this->sess->files[] = $f->getName();
                } else {
                    $this->sess->msg[] = 'Soubor '.$f->getName().' je již nahraný.';
                }
            }
        }
    }

    public function handleUpdateFiles()
    {
        if ($this->sess->files) {
            foreach ($this->sess->files as $f) {
                $this->flashMessage('Soubor ' . $f . ' byl úspěšně nahrán.', 'success');
            }
        }
        if ($this->sess->msg) {
            foreach ($this->sess->msg as $m) {
                $this->flashMessage($m, 'info');
            }
        }
        unset($this->sess->files);
        unset($this->sess->msg);
        if ($this->isAjax()) {
            $this->redrawControl('product-files');
        }
    }

    public function handleDeleteFile($fileId)
    {
        $res = $this->productFac->deleteFile($fileId);
        if ($res) {
            $this->flashMessage('Soubor '.$res.' byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Soubor se nepodařilo smazat.', 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('product-files');
        }
    }

    public function handleDelete($id)
    {
        $this->db->beginTransaction();
        $images = $this->db->query('SELECT * FROM product_image WHERE product_id = '.$id)->fetchAll();
        if ($images) {
            foreach($images as $i) {
                if ($i->path) {
                    $this->imageStorage->delete($i->path);
                    //@unlink($i->path);
                }
            }
            $this->db->query('DELETE FROM product_image WHERE product_id = '.$id);
        }
        if (is_dir('_data/product-images/'.$id)) {
            rmdir('_data/product-images/' . $id);
        }
        $files = $this->db->query('SELECT * FROM product_file WHERE product_id = '.$id)->fetchAll();
        if ($files) {
            $firstFile = null;
            foreach($files as $i) {
                if ($i->path) {
                    $firstFile = $i->path;
                    @unlink($i->path);
                }
                $this->db->query('DELETE FROM product_file_in_language WHERE file_id = '.$i->id);
            }
            if ($firstFile && is_dir(dirname($firstFile))) {
                rmdir(dirname($firstFile));
            }
            $this->db->query('DELETE FROM product_file WHERE product_id = '.$id);
        }
        if (is_dir('_data/product-files/'.$id)) {
            rmdir('_data/product-files/' . $id);
        }
        $this->db->commit();
        $this->db->query('DELETE FROM product_in_menu WHERE product_id = '.$id);
        $this->db->query('DELETE FROM product_language WHERE product_id = '.$id);
        $this->db->query('DELETE FROM product WHERE id = '.$id);
        $this->flashMessage('Produkt byl úspěšně smazán.');
    }
}