<?php

namespace App\IntraModule\Presenters;

use App\Model\ACLForm;
use App\Model\Facade\Article as ArticleFacade;
use App\Model\Database\Entity\Article;
use App\Model\Database\Entity\PermissionItem;
use Nette\Application\UI\Form;
use Nette\Database\Row;
use Nette\Utils\DateTime;
use Ublaboo\ImageStorage\ImageStorage;
use Ublaboo\NetteDatabaseDataSource\NetteDatabaseDataSource;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use App\Model\Database\Utils\SQLHelper;

class ArticlePresenter extends BasePresenter
{
    /** @var ArticleFacade @inject */
    public $articleFac;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /** @var Row */
    public $menu;

    /** @var SQLHelper */
    private $SQLHelper;

    /**
     * ACL name='Správa článků - sekce'
     */
    public function startup()
    {
        parent::startup();
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__, PermissionItem::TYPE_PRESENTER);

        $menu = $this->getParameter('menu', null);

        if ($menu) {
            $this->menu = $this->db->query('
                SELECT m.id, ml.name, m.parent_menu_id
                FROM menu m
                LEFT JOIN menu_language ml ON m.id = ml.menu_id
                LEFT JOIN language l ON ml.lang_id = l.id
                WHERE l.code = ? and m.id = ?
            ', $this->locale, $menu)->fetch();
        }

        $this->SQLHelper = new SQLHelper();
    }

    private function createTree(&$list, $parent)
    {
        $tree = array();
        foreach ($parent as $k => $l) {
            if (isset($list[$l['id']])) {
                $l['children'] = $this->createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
        return $tree;
    }

    public function renderDefault()
    {
        $menu = $this->db->query('
            SELECT m.id, COALESCE(m.parent_menu_id, 0) as parent_menu_id, ml.name
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ?
        ', $this->locale)->fetchAll();

        $parent = $this->db->query('
            SELECT m.id, COALESCE(m.parent_menu_id, 0) as parent_menu_id, ml.name
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and m.parent_menu_id is null
        ', $this->locale)->fetchAll();

        $structureMenu = array();
        foreach ($menu as $a) {
            $structureMenu[$a->parent_menu_id][] = $a;
        }
        $tree = $this->createTree($structureMenu, $parent);

        $this->template->structureMenu = $tree;
        $this->template->activeMenu = $this->getParameter('menu', null);

        if ($this->menu) {
            $this['searchForm']->setDefaults(['menu' => $this->menu->id]);
            $name = $this->menu->name;
            $parentMenu = $this->menu->parent_menu_id;
            while($parentMenu) {
                $menu = $this->articleFac->getMenu($parentMenu, $this->locale);
                if (!empty($name)) {
                    $name = $menu->name . ' -> ' . $name;
                } else {
                    $name = $menu->name;
                }
                $parentMenu = $menu->parent_menu_id;
            }
            $this['searchForm']->setAutocmp('menu', $name);
        }
    }

    public function renderEdit($id, $slug = 'default')
    {
        $this->setView('edit'.$slug);
        if ($id) {
            $article = $this->em->getArticleRepository()->find($id);
            if (!$article) {
                $this->flashMessage('Požadovaný záznam nebyl nalezen!', 'warning');

                switch ($slug) {
                    case 'new':
                    $this->redirect('Article:news');
                    case 'event':
                    $this->redirect('Article:events');
                    case 'zo':
                    $this->redirect('Article:zo');
                    case 'book':
                    $this->redirect('Article:books');
                    default:
                    $this->redirect('Article:');
                }
            }
            $arr = $this->ed->get($article);
            $this->template->article = $article;
            if ($article->menu) {
                $menuItems = $this['form']['menu']->getItems();
                foreach($article->menu as $m) {
                    if (array_key_exists($m->menu->id, $menuItems)) {
                        $arr['menu'][] = $m->menu->id;
                    }
                }
            }
            $arr['type'] = $slug;
            $this['form']->setDefaults($arr);
            $result = $this->db->query('
                SELECT at.*, l.code
                FROM article_'.$slug.' at
                LEFT JOIN language l ON l.id = at.lang_id
                WHERE at.article_id = ?
            ', $article->id)->fetchAll();

            if ($result && isset($result[0]['date_start'])) {
                $result[0]['date_start'] = $result[0]['date_start']->format('d. m. Y');
            }
            $langData = [];
            foreach($result as $k => $r) {
                $langData[$r->code] = $r;
            }
            $this->template->dataLang = $langData;
        } else {
            $this['form']->setDefaults(['type' => $slug]);
            $this['form']->setDefaults(['publish' => (new DateTime())->format('j. n. Y H:i')]);
            bdump($slug);
            $defaultMenu = null;
            if ($slug == 'new') {
                $defaultMenu = 9;
            } else if ($slug == 'event') {
                $defaultMenu = 10;
            } else if ($slug == 'zo') {
                $defaultMenu = 70;
            }/*else if ($slug == 'book') {
            }*/
            
            bdump($this['form']['menu']->getItems());
            if ($defaultMenu && array_key_exists($defaultMenu, $this['form']['menu']->getItems())) {
                $this['form']['menu']->setDefaultValue($defaultMenu);
            }
        }
    }

    /**
     * ACL name='Tabulka článků'
     */
    public function createComponentTable()
    {
        $grid = $this->gridGen->generateGridByAnnotation(Article::class, get_class(), __FUNCTION__);

        $type = $this->getParameter('action');

        $this->gridGen->setClicableRows($grid, $this, 'Article:edit', 'id', ['slug' => ($type != 'default' ? ($type == 'zo' ? 'zo' : substr($type, 0, -1)) : 'default')]);
        
        $multiAction = $grid->getAction('multiAction');
        $multiAction->addAction('edit', 'Upravit', 'Article:edit', ['id' => 'id', 'slug' => 'type']);
        $action = $multiAction->getAction('edit');
        if ($action)
            $action->setIcon('edit')
                ->setTitle('Úprava');

        
        $presenter = $this;
        $action = $multiAction->addActionCallback('delete', 'Smazat', function($itemId) use ($presenter) {
            $presenter->handleDelete($itemId);
        });
        $action->setIcon('times')
            ->setTitle('Smazat')
            ->setConfirmation(new StringConfirmation('Opravdu chcete tento záznam smazat?'))
            ->setClass('text-danger dropdown-item');

        if ($type == 'news') {
            $datasource = $this->em->getArticleRepository()->createQueryBuilder('a')
                ->select('
                    a, a.id, a.type, a.orderArticle as order_article,
                    COALESCE(an.name) AS name,
                    COALESCE(an.active) AS active,
                    COALESCE(an.showName) AS show_name
                ')->innerJoin('App\Model\Database\Entity\Language', 'l')
                ->leftJoin('App\Model\Database\Entity\ArticleNew', 'an', 'WITH', 'a.id = an.article AND a.type = \'new\' AND an.lang = l.id')
                ->where('l.code = :locale AND a.type = \'new\'')
                ->groupBy('a.id')
                ->setParameter('locale', $this->locale);

                $grid->getColumn('type')->setDefaultHide('true');
        } else if ($type == 'events') {
            $datasource = $this->em->getArticleRepository()->createQueryBuilder('a')
                ->select('
                    a, a.id, a.type, a.orderArticle as order_article,
                    COALESCE(ae.name) AS name,
                    COALESCE(ae.active) AS active,
                    COALESCE(ae.showName) AS show_name
                ')
                ->innerJoin('App\Model\Database\Entity\Language', 'l')
                ->leftJoin('App\Model\Database\Entity\ArticleEvent', 'ae', 'WITH', 'a.id = ae.article AND a.type = \'event\' AND ae.lang = l.id')
                ->where('l.code = :locale AND a.type = \'event\'')
                ->groupBy('a.id')
                ->setParameter('locale', $this->locale);
                
                $grid->getColumn('type')->setDefaultHide('true');
        } else if ($type == 'zo') {
            $datasource = $this->em->getArticleRepository()->createQueryBuilder('a')
                ->select('
                    a, a.id, a.type, a.orderArticle as order_article,
                    COALESCE(an.name) AS name,
                    COALESCE(an.active) AS active,
                    COALESCE(an.showName) AS show_name
                ')->innerJoin('App\Model\Database\Entity\Language', 'l')
                ->leftJoin('App\Model\Database\Entity\ArticleZO', 'an', 'WITH', 'a.id = an.article AND a.type = \'zo\' AND an.lang = l.id')
                ->where('l.code = :locale AND a.type = \'zo\'')
                ->groupBy('a.id')
                ->setParameter('locale', $this->locale);

                $grid->getColumn('type')->setDefaultHide('true');
        }/* else if ($type == 'books') {
            $datasource = $this->em->getArticleRepository()->createQueryBuilder('a')
                ->select('
                    a, a.id, a.type, a.orderArticle as order_article,
                    COALESCE(ab.name) AS name,
                    COALESCE(ab.active) AS active,
                    COALESCE(ab.showName) AS show_name
                ')
                ->innerJoin('App\Model\Database\Entity\Language', 'l')
                ->leftJoin('App\Model\Database\Entity\ArticleBook', 'ab', 'WITH', 'a.id = ab.article AND a.type = \'book\' AND ab.lang = l.id')
                ->where('l.code = :locale AND a.type = \'book\'')
                ->groupBy('a.id')
                ->setParameter('locale', $this->locale);
                
                $grid->getColumn('type')->setDefaultHide('true');
        }*/ else {
            $datasource = $this->em->getArticleRepository()->createQueryBuilder('a')
                ->select('
                    a, a.id, a.type, a.orderArticle as order_article,
                    COALESCE(ad.name, ann.name) AS name,
                    COALESCE(ad.active, ann.active) AS active,
                    COALESCE(ad.showName, ann.showName) AS show_name,
                    COALESCE(ad.dropdown) AS dropdown
                ')->innerJoin('App\Model\Database\Entity\Language', 'l')
                ->leftJoin('App\Model\Database\Entity\ArticleDefault', 'ad', 'WITH', "a.id = ad.article AND a.type = 'default' AND ad.lang = l.id")
                ->leftJoin('App\Model\Database\Entity\ArticleTemplate', 'ann', 'WITH', "a.id = ann.article AND a.type = 'template' AND ann.lang = l.id")
                ->where("l.code = :locale AND a.type NOT IN ('zo', 'new', 'event', 'book')")
                ->groupBy('a.id')
                ->setParameter('locale', $this->locale);
        }
        $datasource->leftJoin('a.menu', 'aim')
            ->leftJoin('aim.menu', 'm')
            ->leftJoin('App\Model\Database\Entity\MenuLanguage', 'ml', 'WITH', 'm.id = ml.menu');
        $grid->setDataSource($datasource);


        $column = $grid->addColumnText('name', 'Název');
        if ($column) {
            $column->addCellAttributes(['class' => 'clickable'])->setSortable(true)->setFilterText('name')->setCondition(function($qb, $value) use ($type) {
                
                if ($type == 'news') {
                    $search = $this->SQLHelper->termToLike($value, '', ['an.name']);
                } elseif ($type == 'events') {
                    $search = $this->SQLHelper->termToLike($value, '', ['ae.name']);
                } else if ($type == 'zo') {
                    $search = $this->SQLHelper->termToLike($value, '', ['an.name']);
                }
                /*elseif ($type == 'books') {
                   $search = $this->SQLHelper->termToLike($value, '', ['ab.name']);
                }*/
                else {
                    $search = $this->SQLHelper->termToLike($value, '', ['ad.name', 'ann.name']);
                }
                
                $qb->andWhere($search);
                return $qb;
            });
            /*->setCondition(function ($dibi, $value) {
                return $dibi->where('(ad.name like %~like~ or an.name like %~like~ or ann.name like %~like~)', $value, $value);
                //return $sql;
            });*/
        }

        $column = $grid->addColumnStatus('active', 'Zobrazit');
        if ($column) {
            $column->setSortable(true)
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'articleActiveChange'];
            //$column->setFilterSelect(['' => 'Vše', 1 => 'Ano', 0 => 'Ne'])->setTranslateOptions();
        }

        $column = $grid->addColumnStatus('show_name', 'Zobrazit název');
        if ($column) {
            $column->setSortable(true)
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'articleShowNameChange'];
            //$column->setFilterSelect(['' => 'Vše', 1 => 'Ano', 0 => 'Ne'])->setTranslateOptions();
        }

        /*$column = $grid->addColumnStatus('dropdown', 'Rozevírací');
        if ($column) {
            $column->setSortable(true)
                ->addOption(1, 'Ano')
                ->setClass('btn-success')
                ->endOption()
                ->addOption(0, 'Ne')
                ->setClass('btn-danger')
                ->endOption()
                ->onChange[] = [$this, 'articleDropdownChange'];
            $column->setFilterSelect(['' => 'Vše', 1 => 'Ano', 0 => 'Ne'])->setTranslateOptions();
        }*/

        $column = $grid->addColumnText('menu', 'Zařazeno');
        if ($column) {
            $column->addCellAttributes(['class' => 'clickable'])->setRenderer(function($item) {
                $menu = $this->db->query('
                    SELECT ml.name FROM menu m
                    LEFT JOIN menu_language ml ON m.id = ml.menu_id
                    LEFT JOIN article_in_menu aim ON aim.menu_id = m.id
                    LEFT JOIN article a ON a.id = aim.article_id
                    LEFT JOIN language l ON ml.lang_id = l.id
                    WHERE a.id = ? and l.code = ?
                    GROUP BY m.id
                    ', $item['id'], $this->locale)->fetchAll();
                $content = [];
                if ($menu) {
                    foreach($menu as $m) {
                        $content[] = $m->name;
                    }
                }
                return implode(', ', $content);
            });
            $column->setFilterText('a.menu')->setCondition(function($qb, $value) {
                $search = $this->SQLHelper->termToLike($value, 'ml', ['name']);
                $qb->andWhere($search);
                return $qb;
            });
            
            /*->setCondition(function ($dibi, $value) {

                $dibi->leftJoin('article_in_menu', 'aim', 'ON', 'aim.article_id = a.id')
                    ->leftJoin('menu', 'm', 'ON', 'm.id = aim.menu_id')
                    ->leftJoin('menu_language', 'ml', 'ON', 'm.id = ml.menu_id')
                    ->where('ml.name like %~like~', $value);

                return $dibi;
            });*/
        }

        $column = $grid->addColumnNumber('order_article', 'Pořadí');
        if ($column) {
            $column->setSortable(true)->setFilterText('a.order_article');
        }

        if ($this->menu) {
            unset($_SESSION['__NF']['DATA']['Intra:Article:table-simpleGrid']);
            $_SESSION['__NF']['DATA']['Intra:Article:table-simpleGrid']['visibleFilters'] = true;
            $grid->setDefaultFilter(['menu' => $this->menu->name], false);
        }

        //$grid->setDefaultSort(['id', 'DESC']);

        return $grid;
    }

    /**
     * ACL name='Formulář pro přidání/edit článku'
     */
    public function createComponentForm()
    {
        $form = $this->formGenerator->generateFormByAnnotation(Article::class, $this->user, $this, __FUNCTION__);

        $form->components['menu']->setItems($this->getSelectBoxMenuAll());
        $form->setMessages(['Podařilo se uložit článek', 'success'], ['Nepodařilo se uložit článek!', 'warning']);
    
        if (in_array($this->getParameter('slug'), ['new', 'zo'])) {
        } else if ($this->getParameter('slug') == 'event') {
            $form->setRedirect('Article:events');
        } /*else if ($this->getParameter('slug') == 'book') {
            $form->setRedirect('Article:books');
        }*/ else {
            $form->setRedirect('Article:default');
        }
        $form->isRedirect = false;
        $form->onSuccess[] = [$this, 'articleFormSuccess'];
        return $form;
    }

    public function articleFormSuccess($form, $values)
    {
        $values2 = $this->request->getPost();

        // ukládám formulář  pomocí automatického save
        $article = $this->formGenerator->processForm($form, $values, true);

        if ($article) {
            if ($article->type == 'default') {
                $this->articleFac->updateDefault($article, $values2);
            } elseif($article->type == 'new') {
                $this->articleFac->updateNew($article, $values2);
            } elseif($article->type == 'event') {
                $this->articleFac->updateEvent($article, $values2);
            } elseif($article->type == 'zo') {
                $this->articleFac->updateZo($article, $values2);
            } /*elseif($article->type == 'book') {
                $this->articleFac->updateBook($article, $values2);
            } */elseif($article->type == 'template') {
                $this->articleFac->updateTemplate($article, $values2);
            }

            if (isset($values2['send'])) {
                if ($this->getParameter('slug') == 'new') {
                    $this->redirect('Article:news');
                } else if ($this->getParameter('slug') == 'event') {
                    $this->redirect('Article:events');
                } else if ($this->getParameter('slug') == 'zo') {
                    $this->redirect('Article:zo');
                } /*else if ($this->getParameter('slug') == 'book') {
                    $this->redirect('Article:books');
                } */else {
                    $this->redirect('Article:default');
                }
            } elseif (isset($values2['sendSave'])) {
                $this->redirect('Article:edit', ['id' => $article->id, 'slug' => $article->type]);
            }
        }
    }

    /**
     * ACL name='Formulář pro hledání článku'
     */
    public function createComponentSearchForm()
    {
        $form = new ACLForm();
        $form->setScope($this->user, get_class($this), __FUNCTION__, $this->acl);
        $form->addHidden('menu')
            ->setAttribute('data-preload', "false")
            ->setAttribute('data-suggest', "true")
            ->setAttribute('data-minlen', "1")
            ->setAttribute('class', "form-control autocomplete-input")
            ->setAttribute('data-toggle', 'completer')
            ->setAttribute('title', 'Vyhledat menu')
            ->setAttribute('placeholder', 'Vyhledat menu')
            ->setAttribute('autocomplete', 'true');
        $form->onSuccess[] = [$this, 'successSearchForm'];

        return $form;
    }

    public function successSearchForm(Form $form, $values)
    {
        if ($values->menu) {
            $this->redirect('Article:default', ['menu' => $values->menu]);
        } else {
            $this->redirect('Article:default');
        }
    }

    public function articleActiveChange($id, $status)
    {
        $this->articleFac->changeActive($id, $status, $this->locale);

        if ($this->isAjax()) {
            $this['table']->redrawItem($id, 'a.id');
        } else {
            $this->redirect('this');
        }
    }

    public function articleShowNameChange($id, $status)
    {
        $this->articleFac->changeShowName($id, $status, $this->locale);

        if ($this->isAjax()) {
            $this['table']->redrawItem($id, 'a.id');
        } else {
            $this->redirect('this');
        }
    }

    public function articleDropdownChange($id, $status)
    {
        $this->articleFac->changeDropdown($id, $status, $this->locale);

        if ($this->isAjax()) {
            $this['table']->redrawItem($id, 'a.id');
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
            WHERE ml.lang_id = '.$lang->id.' and m.parent_menu_id '.($parent ? ' = '.$parent : 'is null').' AND m.hide_in_select = 0
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

    public function handleDelete($id)
    {
        $article = $this->db->query('SELECT * FROM article WHERE id = '.$id)->fetch();
        if ($article) {
            $this->db->beginTransaction();
            $images = $this->db->query('SELECT * FROM article_image WHERE article_id = ' . $id)->fetchAll();
            if ($images) {
                foreach ($images as $i) {
                    if ($i->path) {
                        $this->imageStorage->delete($i->path);
                        //@unlink($i->path);
                    }
                }
                $this->db->query('DELETE FROM article_image WHERE article_id = ' . $id);
            }
            if (is_dir('_data/article-images/' . $id)) {
                @rmdir('_data/article-images/' . $id);
            }
            $files = $this->db->query('SELECT * FROM article_file WHERE article_id = ' . $id)->fetchAll();
            if ($files) {
                $firstFile = null;
                foreach ($files as $i) {
                    if ($i->path) {
                        $firstFile = $i->path;
                        @unlink($i->path);
                    }
                    $this->db->query('DELETE FROM article_file_in_language WHERE file_id = ' . $i->id);
                }
                if ($firstFile && is_dir(dirname($firstFile))) {
                    rmdir(dirname($firstFile));
                }
                $this->db->query('DELETE FROM article_file WHERE article_id = ' . $id);
            }
            if (is_dir('article-files/' . $id)) {
                @rmdir('article-files/' . $id);
            }
            $this->db->commit();
            $this->db->query('DELETE FROM article_in_menu WHERE article_id = ' . $id);
            $this->db->query('DELETE FROM article_'.$article->type.' WHERE article_id = ' . $id);
            $this->db->query('DELETE FROM article WHERE id = ' . $id);
            $this->flashMessage('Článek byl úspěšně smazán.');
        } else {
            $this->flashMessage('Článek nebyl naleze.', 'error');
        }
        $this->redirect('this');
    }

    /**
     * ACL name='Formulář pro přidání/edit galerie článku'
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

        $this->articleFac->updateGallery($values2);

        $this->flashMessage('Fotogalerie byla úspěšně upravena!', 'success');

        if ($this->isAjax()) {
            $this->redrawControl('images');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit souborů článku'
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

        $this->articleFac->updateFiles($values2);

        $this->flashMessage('Soubory byly úspěšně upraveny!', 'success');

        if ($this->isAjax()) {
            $this->redrawControl('files');
        }
    }

    /**
     * ACL name='Formulář pro přidání/edit fotogalerie článku'
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
        $path = '_data/article-images/'.$values->id.'/';
        if (!is_dir('_data/article-images')) {
            mkdir('_data/article-images', 0777);
        }
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $res = $this->articleFac->addImage($values->id, $path.$f->getName());
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
            $this->redrawControl('images');
        }
    }

    public function handleDeleteImg($imgId)
    {
        $res = $this->articleFac->deleteImage($imgId);
        if ($res) {
            $this->flashMessage('Obrázek '.$res.' byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Obrázek se nepodařilo smazat.', 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('images');
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
        $path = 'article-files/'.$values->id.'/';
        if (!is_dir('article-files')) {
            mkdir('article-files', 0777);
        }
        if (!is_dir($path)) {
            mkdir($path, 0777);
        }
        $files = $this->request->getFiles();
        if ($files) {
            foreach($files as $f) {
                $res = $this->articleFac->addFile($values->id, $path.$f->getName());
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
            $this->redrawControl('files');
        }
    }

    public function handleDeleteFile($fileId)
    {
        $res = $this->articleFac->deleteFile($fileId);
        if ($res) {
            $this->flashMessage('Soubor '.$res.' byl úspěšně smazán.', 'success');
        } else {
            $this->flashMessage('Soubor se nepodařilo smazat.', 'error');
        }

        if ($this->isAjax()) {
            $this->redrawControl('files');
        }
    }

    public function handleGetMenu($term)
    {
        $result = $this->articleFac->getDataAutocompleteMenu($term, $this->locale);
        $this->payload->autoComplete = json_encode($result);
        $this->sendPayload();
    }

    public function handleResetFilter()
    {
        unset($_SESSION['__NF']['DATA']['Intra:Article:table-simpleGrid']['menu']);
        $this->redirect('this');
    }
}