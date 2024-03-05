<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\MenuLanguage;
use App\Model\Database\EntityManager;
use App\Model\Database\Utils\EntityData;
use App\Model\Database\Entity\WebSetting;
use App\Model\Database\Utils\SQLHelper;
use Nette\Application\LinkGenerator;
use Nette\Utils\DateTime;
use Nette\Caching\IStorage;
use Ublaboo\ImageStorage\ImageStorage;
use Nette\Caching\Cache;
use Nette\Database\Context;
use Nette\Utils\ArrayHash;

class BaseFront
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var EntityData */
    private EntityData $ed;

    /** @var SQLHelper */
    private $SQLHelper;

    /** @var Cache */
    protected $cache;

    /** @var IStorage */
    protected $storage;

    /** @var ImageStorage */
    public $imageStorage;

    /** @var Context */
    protected $db;

    public function __construct(EntityManager $em, EntityData $ed, SQLHelper $sql, IStorage $storage, ImageStorage $imageStorage, Context $db)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->SQLHelper = $sql;
        $this->storage = $storage;
        $this->imageStorage = $imageStorage;
        $this->cache = new Cache($this->storage);
        $this->db = $db;
    }

    /**
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getMenuForSitemap($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and ml.visible = 1
        ', [$locale])->fetchAll();

        return $menu;
    }

    /**
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getMainMenu($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and (ml.url = "" or ml.url = "/")
        ', [$locale])->fetch();

        return $menu;
    }

    /**
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getMainMenuUl($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE ml.visible = 1 and ml.show_up = 1 and l.code = ?
            ORDER BY m.order_page
        ', [$locale])->fetchAll();

        return $menu;
    }

    /**
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getFooterMenu($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE ml.visible = 1 and l.code = ? and m.parent_menu_id = ?
            ORDER BY m.order_page
        ', [$locale, 2])->fetchAll();

        return $menu;
    }

    /**
     * @param string $locale
     * @return array
     */
    public function getActiveMenu($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE ml.visible = 1 and l.code = ?
            ORDER BY m.order_page
        ', [$locale])->fetchAll();

        $arr = [];

        if ($menu) {
            foreach ($menu as $m) {
                $arr[$m->id] = $m;
            }
        }

        return $arr;
    }

    public function getArticlesTopIDS($menu, $offset = null, $limit = null)
    {
        $articlesArr = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.id, a.type
                FROM article a
                LEFT JOIN article_in_menu aim ON aim.article_id = a.id
                WHERE aim.menu_id = ? and a.order_article < 1000
                ORDER BY a.order_article ASC, a.publish DESC
                ' . ($offset !== null && $limit !== null ? 'LIMIT ' . $offset . ',' . $limit : '') . '
		  ', [$menu->id])->fetchAll();

            if ($articles) {
                foreach ($articles as $a) {
                    $articlesArr[$a->id] = $a->type;
                }
            }
        }

        return $articlesArr;
    }

    public function getArticlesBottomIDS($menu)
    {
        $articlesArr = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.id, a.type
                FROM article a
                LEFT JOIN article_in_menu aim ON aim.article_id = a.id
                WHERE aim.menu_id = ? and a.order_article >= 1000
                ORDER BY a.order_article ASC, a.publish DESC
		  ', [$menu->id])->fetchAll();

            if ($articles) {
                foreach ($articles as $a) {
                    $articlesArr[$a->id] = $a->type;
                }
            }
        }

        return $articlesArr;
    }

    public function getArticlesDefaultIDS($menu)
    {
        $articlesArr = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.id, a.type
                FROM article a
                LEFT JOIN article_in_menu aim ON aim.article_id = a.id
                WHERE aim.menu_id = ? and a.type = "default"
                ORDER BY a.order_article
		  ', [$menu->id])->fetchAll();

            if ($articles) {
                foreach ($articles as $a) {
                    $articlesArr[$a->id] = $a->type;
                }
            }
        }

        return $articlesArr;
    }

    /**
     * @param $menu
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesTop($menu, $locale)
    {
        $articles = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.*, al.name, al.content, al.show_name, al.dropdown
                FROM article a
                LEFT JOIN article_default al ON al.article_id = a.id
                LEFT JOIN article_in_menu aim ON aim.article_id = a.id
                LEFT JOIN language l ON al.lang_id = l.id
                WHERE aim.menu_id = ? and al.active = 1 and l.code = ? and a.order_article < 1000
                ORDER BY a.order_article
		  ', [$menu->id, $locale])->fetchAll();
        }

        return $articles;
    }

    /**
     * @param $menu
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesBottom($menu, $locale)
    {
        $articles = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.*, al.name, al.content, al.show_name, al.dropdown
                FROM article a
                LEFT JOIN article_default al ON al.article_id = a.id
                LEFT JOIN article_in_menu aim ON aim.article_id = a.id
                LEFT JOIN language l ON al.lang_id = l.id
                WHERE aim.menu_id = ? and al.active = 1 and l.code = ? and a.order_article >= 1000
                ORDER BY a.order_article
		  ', [$menu->id, $locale])->fetchAll();
        }

        return $articles;
    }

    /**
     * @param $menu
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesNews($menu = null, $locale, $offset = null, $limit = 12, $order = null, $onlyPublished = true, $dateBetween = [], $calendarOnly = false)
    {
        $articles = $this->db->queryArgs(
            '
        SELECT a.*, an.name, an.perex, an.show_name, an.link, ai.path, ai.alt, an.date_start
                FROM article a
                LEFT JOIN article_new an ON an.article_id = a.id
                LEFT JOIN article_image ai ON ai.id
                = ( SELECT aic.id
                    FROM article_image aic
                    WHERE aic.article_id = an.article_id
                    ORDER BY aic.order_img
                    LIMIT 1
                )
        LEFT JOIN article_in_menu aim ON aim.article_id = a.id
		    LEFT JOIN language l ON an.lang_id = l.id
        WHERE an.active = 1 and l.code = ? ' . ($menu ? 'and aim.menu_id = ' . $menu->id : '') . ' ' .
                ($onlyPublished  ? 'and a.publish <= "' . date('Y-m-d H:i:s') . '"' : '') . ' ' .
                (((isset($dateBetween[0]) && $dateBetween[0])  ? ('and an.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '" ') : '') .
                ((isset($dateBetween[1]) && $dateBetween[1])  ? ('and an.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"')  : '')) .
                ($calendarOnly ? ' and an.show_in_calendar = 1' : '') . '
        GROUP BY a.id
        ORDER BY ' . ($order == 'date' ? 'COALESCE(an.date_start, a.publish) DESC' : 'a.order_article') . ' ' .
                ($offset !== null && $limit !== null ? 'LIMIT ' . $offset . ',' . $limit : ($limit !== null ? 'LIMIT ' . $limit : '')),
            [$locale]
        )->fetchAll();
        return $articles;
        }

    /**
     * @param $menu
     * @param string $locale
     * @param integer $offset
     * @param integer $limit
     * @param string $order
     * @param bool $onlyPublished
     * @param array $dateBetween Array of 1 or 2 datetimes [from, to].
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesEvents($menu, $locale, $offset = null, $limit = 12, $order = [], $onlyPublished = false, $dateBetween = [], $notInIds = [])
    {
        $articles = $this->db->queryArgs(
            '
		    SELECT a.*, ae.name, ae.perex, ae.show_name, ae.date_text, ae.date_start, ae.link, ai.path, ai.alt
		    FROM article a
		    LEFT JOIN article_event ae ON ae.article_id = a.id
		    LEFT JOIN article_image ai ON ai.id
             = ( SELECT aic.id
                 FROM article_image aic
                 WHERE aic.article_id = ae.article_id
                 ORDER BY aic.order_img
                 LIMIT 1
               )
        LEFT JOIN article_in_menu aim ON aim.article_id = a.id
		    LEFT JOIN language l ON ae.lang_id = l.id
        WHERE ae.active = 1 and l.code = ? ' . ($menu ? 'and aim.menu_id = ' . $menu->id : '') . ' ' .
            ($onlyPublished  ? 'and a.publish <= "' . date('Y-m-d H:i:s') . '"' : '') . ' ' .
                ((isset($dateBetween[0]) && $dateBetween[0])  ? ('and ae.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '" ') : '' .
                    ((isset($dateBetween[1]) && $dateBetween[1])  ? ('and ae.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"')  : '')) .
                ($notInIds ? ' AND a.id NOT IN (' . implode(',', $notInIds) . ')': '') . '
        GROUP BY a.id
        ORDER BY ' . (isset($order['date']) ? 'ae.date_start ' . $order['date'] : 'a.order_article') . ' ' .
                ($offset !== null && $limit !== null ? 'LIMIT ' . $offset . ',' . $limit : ($limit !== null ? 'LIMIT ' . $limit : '')),
            [$locale]
        )->fetchAll();
       
        return $articles;
    }

    /**
     * @param $menu
     * @param string $locale
     * @param integer $limit
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesEventsPrimary($menu, $locale, $limit = 12)
    {
        $articles = $this->db->queryArgs(
            '
        SELECT a.*, ae.name, ae.perex, ae.show_name, ae.date_text, ae.date_start, ae.link, ai.path, ai.alt
        FROM article a
        LEFT JOIN article_event ae ON ae.article_id = a.id
        LEFT JOIN article_image ai ON ai.id
            = ( SELECT aic.id
                FROM article_image aic
                WHERE aic.article_id = ae.article_id
                ORDER BY aic.order_img
                LIMIT 1
            )
        LEFT JOIN article_in_menu aim ON aim.article_id = a.id
        LEFT JOIN language l ON ae.lang_id = l.id
        WHERE ae.active = 1 and l.code = ? ' . ($menu ? 'and aim.menu_id = ' . $menu->id : '') . '
            AND ae.primary_on_hP = 1
        GROUP BY a.id
        ORDER BY a.order_article ASC, ae.date_start ASC ' .
            ($limit !== null ? 'LIMIT ' . $limit : ''),
            [$locale]
        )->fetchAll();
        return $articles;
    }

     /**
     * @param $menu
     * @param string $locale
     * @param integer $offset
     * @param integer $limit
     * @param string $order
     * @param bool $onlyPublished
     * @param array $dateBetween Array of 1 or 2 datetimes [from, to].
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesEventsAndNews($menu, $locale, $offset = null, $limit = 12, $order = [], $onlyPublished = false, $dateBetween = [], $notInIds = [])
    {
        $sql = '
        SELECT a.*,
            COALESCE(ae.name, an.name) as name,
            COALESCE(ae.perex, an.perex) as perex,
            COALESCE(ae.show_name, an.show_name) as show_name,
            ae.date_text,
            COALESCE(ae.date_start, an.date_start) as date_start,
            COALESCE(ae.link, an.link) as link,
            ai.path, ai.alt
        FROM article a
        LEFT JOIN article_event ae ON ae.article_id = a.id
        LEFT JOIN article_new an ON an.article_id = a.id
        LEFT JOIN article_image ai ON ai.id
            = ( SELECT aic.id
                FROM article_image aic
                WHERE aic.article_id = ae.article_id
                ORDER BY aic.order_img
                LIMIT 1
            )
        LEFT JOIN article_in_menu aim ON aim.article_id = a.id
        LEFT JOIN language el ON ae.lang_id = el.id
        LEFT JOIN language nl ON an.lang_id = nl.id
        WHERE (ae.active = 1 OR an.active = 1) and (el.code = ? OR nl.code = ?) ' .
            ($menu ? 'and aim.menu_id = ' . $menu->id : '') . ' ' .
            ($onlyPublished  ? 'and a.publish <= "' . date('Y-m-d H:i:s') . '"' : '') .
            '';// and (an.id IS NULL OR an.show_in_calendar = 1)';

        if (isset($dateBetween[0]) && $dateBetween[0] && isset($dateBetween[1]) && $dateBetween[1]) {
            $sql .= ' and (
                (
                    ae.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '" 
                    and ae.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"
                ) OR (
                    an.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '"
                    and an.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"
                )
            )';
        } else if (isset($dateBetween[0]) && $dateBetween[0]) {
            $sql .= ' and (
                ae.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '" 
                AND an.date_start >= "' . $dateBetween[0]->format('Y-m-d') . '" 
            )';
        } else if (isset($dateBetween[1]) && $dateBetween[1]) {
            $sql .= ' and (
                ae.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"
                AND an.date_start <= "' . $dateBetween[1]->format('Y-m-d') . '"
            )';
        }

        $sql .= '
            GROUP BY a.id
            ORDER BY ' . (isset($order['date']) ? 'ae.date_start ' . $order['date'] : 'a.order_article') . ' ' .
                ($offset !== null && $limit !== null ? 'LIMIT ' . $offset . ',' . $limit : ($limit !== null ? 'LIMIT ' . $limit : ''));
        
                
        $articles = $this->db->queryArgs($sql, [$locale, $locale])->fetchAll();
        return $articles;
    }

    /**
     * @param $menu
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getArticlesBooks($menu, $locale, $limit = 12, $order = null, $onlyPublished = false)
    {
        $articles = [];
        if ($menu) {
            $articles = $this->db->queryArgs(
                '
		    SELECT a.*, ab.name, ab.perex, ab.show_name, ab.link, ab.author, ai.path, ai.alt
		    FROM articles a
		    LEFT JOIN articles_book ab ON ab.article_id = a.id
		    LEFT JOIN articles_image ai ON ai.id
             = ( SELECT aic.id
                 FROM articles_image aic
                 WHERE aic.article_id = ab.article_id
                 ORDER BY aic.order_img
                 LIMIT 1
               )
		    LEFT JOIN language l ON ab.lang_id = l.id
		    WHERE ab.active = 1 and l.code = ? ' . ($onlyPublished  ? 'and a.publish <= "' . date('Y-m-d H:i:s') . '"' : '') . '
            ORDER BY ' . ($order == 'date' ? 'a.publish DESC' : 'a.order_article') . '
		    LIMIT ' . $limit,
                [$locale]
            )->fetchAll();
        }
        return $articles;
    }

    public function getArticlesTemplates($menu, $locale)
    {
        $articles = [];
        if ($menu) {
            $articles = $this->db->queryArgs('
                SELECT a.*, ann.name, ann.perex, ann.show_name, ann.link, ai.path, ai.alt
                FROM article a
                LEFT JOIN article_template ann ON ann.article_id = a.id
                LEFT JOIN article_image ai ON ai.id
                = ( SELECT aic.id
                    FROM article_image aic
                    WHERE aic.article_id = ann.article_id
                    ORDER BY aic.order_img
                    LIMIT 1
                )
                LEFT JOIN language l ON ann.lang_id = l.id
                WHERE ann.active = 1 and l.code = ?
                ORDER BY a.order_article
                LIMIT 12
		  ', [$locale])->fetchAll();
        }

        return $articles;
    }

    public function getArticlesByTerm($term, $locale)
    {
        $sql = 'SELECT a.id,
              COALESCE(ad.name, an.name, ann.name, null) AS name
            FROM article a
            JOIN language l
            LEFT JOIN article_default ad ON (a.id = ad.article_id and a.type = "default" and ad.lang_id = l.id)
            LEFT JOIN article_new an ON (a.id = an.article_id and a.type = "new" and an.lang_id = l.id)
            LEFT JOIN article_event ae ON (a.id = ae.article_id and a.type = "event" and ae.lang_id = l.id)
            LEFT JOIN article_template ann ON (a.id = ann.article_id and a.type = "template" and ann.lang_id = l.id)
            WHERE l.code = ?';
        $searchWords = explode(' ', $term);
        $searchAliases = ['ad', 'an', 'ae', 'ann'];
        if ($searchWords) {
            $sql .= ' and ( (';
            foreach ($searchWords as $k => $s) {
                foreach ($searchAliases as $ka => $a) {
                    $sql .= $a . '.name like "%' . $s . '%"';
                    if (($ka < count($searchAliases) - 1)) {
                        $sql .= ' OR ';
                    }
                    //$k < count($searchWords) - 1 ||
                    //$k == count($searchWords) - 1 &&
                }
                if ($k < count($searchWords) - 1) {
                    $sql .= ' ) AND ( ';
                } else {
                    $sql .= ' ) ';
                }
            }
            $sql .= ')';
        }
        $sql .= ' GROUP BY a.id';
        return $this->db->queryArgs($sql, [$locale])->fetchAll();
    }

    public function getProductsByTerm($term, $locale)
    {
        $sql = 'SELECT p.id, pl.name
            FROM product p
            LEFT JOIN product_language pl ON pl.product_id = p.id
            LEFT JOIN language l ON l.id = pl.lang_id
            WHERE pl.active = 1 and l.code = ?';
        $searchWords = explode(' ', $term);
        $searchAliases = ['pl'];
        if ($searchWords) {
            $sql .= ' and ( (';
            foreach($searchWords as $k => $s) {
                foreach($searchAliases as $ka => $a) {
                    $sql .= $a.'.name like "%' . $s . '%"';
                    if (($ka < count($searchAliases) - 1)) {
                        $sql .= ' OR ';
                    }
                    //$k < count($searchWords) - 1 ||
                    //$k == count($searchWords) - 1 &&
                }
                if ($k < count($searchWords) - 1) {
                    $sql .= ' ) AND ( ';
                } else {
                    $sql .= ' ) ';
                }
            }
            $sql .= ')';
        }
        $sql .= ' GROUP BY p.id';
        return $this->db->queryArgs($sql, [$locale])->fetchAll();
    }

    public function getArticle($id, $locale)
    {
        $sql = 'SELECT a.id, a.type,
              COALESCE(ad.name, an.name, ann.name, null) AS name
            FROM article a
            JOIN language l
            LEFT JOIN article_default ad ON (a.id = ad.article_id and a.type = "default" and ad.lang_id = l.id)
            LEFT JOIN article_new an ON (a.id = an.article_id and a.type = "new" and an.lang_id = l.id)
            LEFT JOIN article_template ann ON (a.id = ann.article_id and a.type = "template" and ann.lang_id = l.id)
            LEFT JOIN article_event ae ON (a.id = ae.article_id and a.type = "event" and ae.lang_id = l.id)
            WHERE a.id = ? and l.code = ?';
        return $this->db->queryArgs($sql, [$id, $locale])->fetch();
    }

    public function getArticleMenu($id)
    {
        $sql = 'SELECT aim.menu_id
            FROM article_in_menu aim
            WHERE aim.article_id = ?';
        return $this->db->queryArgs($sql, [$id])->fetchField();
    }

    /**
     * @param $menu
     * @param string $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getMainProducts($menu, $locale)
    {
        $products = [];
        if ($menu) {
            $products = $this->db->queryArgs('
                SELECT p.*, pl.name, pi.path, pi.alt
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                LEFT JOIN language l ON pl.lang_id = l.id
                WHERE pim.menu_id = ? and pl.active = 1 and l.code = ?
                ORDER BY p.order_product
		  ', [$menu->id, $locale])->fetchAll();
        }

        return $products;
    }

    /**
     * @param integer $id
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getMenu($id, $locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window, ml.title, ml.keywords, ml.description, ml.show_signpost
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE m.id = ? and ml.visible = 1 and l.code = ?
        ', [$id, $locale])->fetch();

        return $menu;
    }

    /**
     * @param integer $id
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getParentMenu($id, $locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window, ml.title, ml.keywords, ml.description
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE m.parent_menu_id = ? and ml.visible = 1 and l.code = ?
            ORDER BY m.order_page
        ', [$id, $locale])->fetchAll();

        return $menu;
    }

    /**
     * @param integer $id
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getGuideMenu($locale)
    {
        $menu = $this->db->queryArgs('
            SELECT m.*, ml.name, ml.url, ml.link, ml.new_window, ml.title, ml.keywords, ml.menu_description
            FROM menu m 
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE ml.visible = 1 and ml.show_on_homepage = 1 and l.code = ?
            ORDER BY m.order_page
            LIMIT 12
        ', [$locale])->fetchAll();

        return $menu;
    }

    /**
     * @param integer $id
     * @param string $locale
     * @return bool|\Nette\Database\IRow|\Nette\Database\Row|null
     */
    public function getTopMenu($id, $locale)
    {
        $lastMenu = null;
        $parent = $id;
        while(true) {
            if (!$parent) {
                break;
            }
            $menu = $this->db->queryArgs('
                SELECT m.*, ml.name, ml.url, ml.link, ml.new_window, ml.title, ml.keywords, ml.description
                FROM menu m 
                LEFT JOIN menu_language ml ON m.id = ml.menu_id
                LEFT JOIN language l ON ml.lang_id = l.id
                WHERE m.id = ? and ml.visible = 1 and l.code = ?
            ', [$parent, $locale])->fetch();
            if ($menu) {
                $lastMenu = $menu;
                $parent = $menu->parent_menu_id;
            } else {
                break;
            }
        }

        return $lastMenu;
    }

    private $menuData = [];

    /**
     * @param $id
     * @param $locale
     * @return array|mixed
     */
    public function getStructureMenu($id, $locale)
    {
        if (isset($this->menuData[$id])) {
            return $this->menuData[$id];
        }
        $menuIds = [];
        $menuArr = [];
        $parent = $id;
        while(true) {
            if (!$parent) {
                break;
            }
            $menu = $this->db->queryArgs('
                SELECT m.*, ml.name, ml.url, ml.link, ml.new_window, ml.title, ml.keywords, ml.description
                FROM menu m 
                LEFT JOIN menu_language ml ON m.id = ml.menu_id
                LEFT JOIN language l ON ml.lang_id = l.id
                WHERE m.id = ? and ml.visible = 1 and l.code = ?
            ', [$parent, $locale])->fetch();
            if ($menu) {
                $menuIds[] = $menu->id;
                $menuArr[] = $menu;
                $parent = $menu->parent_menu_id;
            } else {
                break;
            }
        }

        $this->menuData[$id] = [$menuIds, $menuArr];

        return [$menuIds, $menuArr];
    }

    /**
     * @param $menuIDS
     * @param $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getProductsList($menuIDS, $locale)
    {
        $products = [];
        if ($menuIDS && count($menuIDS) > 1) {
            $products = $this->db->queryArgs('
                SELECT p.id
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN language l ON pl.lang_id = l.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                WHERE pim.menu_id IN (?) and pl.active = 1 and l.code = ?
                GROUP BY p.id
		  ', [$menuIDS, $locale])->fetchAll();
        } else {
            $products = $this->db->queryArgs('
                SELECT p.id
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN language l ON pl.lang_id = l.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                WHERE pl.active = 1 and l.code = ?
                GROUP BY p.id
		  ', [$locale])->fetchAll();
        }

        $pIDS = [];
        if ($products) {
            foreach($products as $p) {
                $pIDS[] = $p->id;
            }
        }

        return $pIDS;
    }

    /**
     * @param $menuIDS
     * @param $locale
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getProducts($menuIDS, $locale)
    {
        $products = [];
        if ($menuIDS && count($menuIDS) > 1) {
            $products = $this->db->queryArgs('
                SELECT p.*, pl.name, pl.short_description, pi.path, pi.alt, pim.menu_id
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN language l ON pl.lang_id = l.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                WHERE pim.menu_id IN (?) and pl.active = 1 and l.code = ?
                GROUP BY p.id
                ORDER BY p.order_product, pl.name
		  ', [$menuIDS, $locale])->fetchAll();
        } else {
            $products = $this->db->queryArgs('
                SELECT p.*, pl.name, pl.short_description, pi.path, pi.alt, pim.menu_id
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN language l ON pl.lang_id = l.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                WHERE pl.active = 1 and l.code = ?
                GROUP BY p.id
                ORDER BY p.order_product, pl.name
		  ', [$locale])->fetchAll();
        }

        return $products;
    }

    /**
     * @param $id
     * @param $locale
     * @return array|bool|\Nette\Database\IRow|\Nette\Database\Row
     */
    public function getProduct($id, $locale)
    {
        $product = [];
        if ($id) {
            $product = $this->db->queryArgs('
                SELECT p.*, pl.name, pl.short_description, pl.description, pi.path, pi.alt, pim.menu_id
                FROM product p
                LEFT JOIN product_language pl ON pl.product_id = p.id
                LEFT JOIN product_in_menu pim ON pim.product_id = p.id
                LEFT JOIN language l ON pl.lang_id = l.id
                LEFT JOIN product_image pi ON pi.product_id = p.id and pi.is_main = 1
                WHERE p.id = ? and pl.active = 1 and l.code = ?
                GROUP BY p.id
		  ', [$id, $locale])->fetch();
        }

        return $product;
    }

    /**
     * @param $productID
     * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
     */
    public function getProductImages($productID)
    {
        $images = [];

        if ($productID) {
            $images = $this->db->queryArgs('
                SELECT pi.*
                FROM product_image pi
                WHERE pi.product_id = ? and (pi.is_main is null or pi.is_main = 0)
                ORDER BY pi.order_img
		  ', [$productID])->fetchAll();
        }

        return $images;
    }

    /**
     * @param $productID
     * @param $locale
     * @return array
     */
    public function getProductFiles($productID, $locale)
    {
        $files = [];

        if ($productID) {
            $files = $this->db->queryArgs('
                SELECT pf.*
                FROM product_file pf
                LEFT JOIN product_file_in_language pfil ON pfil.file_id = pf.id
                LEFT JOIN language l ON l.id = pfil.lang_id
                WHERE pf.product_id = ? and l.code = ?
                ORDER BY pf.order_file
		  ', [$productID, $locale])->fetchAll();
        }

        return $files;
    }

    public function getBanners($locale, $type)
    {
        return $this->db->query('
            SELECT b.*, bl.name, bl.link, bl.text
            FROM banner b
            LEFT JOIN banner_language bl ON b.id = bl.banner_id
            LEFT JOIN language l ON l.id = bl.lang_id
            WHERE l.code = ? and b.type = ? and bl.active = 1
        ', $locale, $type)->fetchAll();
    }

    public function getBannersPartners($locale)
    {
        return $this->db->query('
            SELECT b.*, bl.name, bl.link
            FROM banner_partner b
            LEFT JOIN banner_partner_language bl ON b.id = bl.banner_id
            LEFT JOIN language l ON l.id = bl.lang_id
            WHERE l.code = ? and bl.active = 1
        ', $locale)->fetchAll();
    }

    private function getRandomString($length = 5, $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        $str = "";

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $str;
    }

    /**
     * Check aktual exchange rates
     * @return array array of messages
     */
    public function checkActualExchangeRates() {
        $kurzy = file('http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt');

        $arr = [];
        foreach ($kurzy as $v) {
            $h = explode("|", $v);
            if (isset($h[3]) && isset($h[4]))
                $arr[$h[3]] = str_replace(',', '.', $h[4]);
        }
        $message = [];
        $entity = $this->em->getCurrencyRepository()->findAll();
        foreach ($entity as $item) {
            if (isset($arr[$item->code])) {
                $item->exchangeRate = $arr[$item->code];
            } else if ($item->code == 'CZK') {
                continue;
            } else {
                $message[] = "Nepodařilo se najít na lístku ČNB měnu: $item->name [$item->code]";
            }
        }
        $this->save();
        return $message;
    }

    /**
     * Get specific setting value by code
     * @param string $code
     * @return string value
     */
    public function setting($code)
    {
        return $this->em->getSettingRepository()->findOneBy(['code' => $code])->value;
    }

    /**
     * Get specific setting value by code
     * @param string $code
     * @return array value
     */
    public function settingEntity($code)
    {
        return $this->em->getSettingRepository()->findOneBy(['code' => $code]);
    }

    /**
     * Get all settings
     * @return array value
     */
    public function getSettings()
    {
        $key = 'settings';
        $arr = $this->cache->load($key);
        if (!$arr) {
            $set = $this->em->getSettingRepository()->findAll();
            foreach ($set as $s) {
                $arr[$s->code] = $s->value;
            }
            $this->cache->save($key, $arr, [
                Cache::EXPIRE => '24 hours',
                Cache::TAGS => 'settings'
            ]);
        }
        return $arr;
    }

    /**
     * Get all web settings
     * @param string $locale
     * @return array value
     */
    public function getWebSettings($locale)
    {
        $key = 'webSetting-'.$locale;
        $arr = $this->cache->load($key);
        if (!$arr) {
            $qb = $this->em->getConnection()->prepare('
                SELECT ws.code, wsl.value
                FROM web_setting ws
                LEFT JOIN web_setting_language wsl ON ws.id = wsl.setting_id
                LEFT JOIN language l ON l.id = wsl.lang_id
                WHERE l.code = ?
            ');
            $qb->execute([$locale]);
            $set = $qb->fetchAll();
            foreach ($set as $s) {
                $arr[$s['code']] = $s['value'];
            }
            $this->cache->save($key, $arr, [
                Cache::EXPIRE => '24 hours',
                Cache::TAGS => 'webSetting'
            ]);
        }
        return $arr;
    }
}