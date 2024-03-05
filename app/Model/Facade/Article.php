<?php

namespace App\Model\Facade;


use App\Model\Database\Entity\Article as ArticleEntity;
use App\Model\Database\Entity\ArticleDefault;
use App\Model\Database\Entity\ArticleNew;
use App\Model\Database\Entity\ArticleEvent;
use App\Model\Database\Entity\ArticleTemplate;
use App\Model\Database\EntityManager;
use Nette\Database\Explorer;
use Ublaboo\ImageStorage\ImageStorage;
use Nette\Utils\DateTime;

class Article
{

    /** @var EntityManager */
    private EntityManager $em;

    /** @var Explorer */
    protected $db;

    /** @var ImageStorage */
    public $imageStorage;

    /**
     * Construct
     * @param EntityManager $em
     * @param Explorer $db
     * @param ImageStorage $imageStorage
     */
    public function __construct(EntityManager $em, Explorer $db, ImageStorage $imageStorage) {
        $this->em = $em;
        $this->db = $db;
        $this->imageStorage = $imageStorage;
    }

    public function updateDefault($article, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getArticleDefaultRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                    $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                    $ent->setDropdown(isset($values['dropdown'][$lang]) ? true : false);
                    $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                }
            } else {
                $ent = new ArticleDefault();
                $ent->setArticle($article);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                $ent->setDropdown(isset($values['dropdown'][$lang]) ? true : false);
                $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function updateNew($article, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getArticleNewRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                    $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                    $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                    $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                    $ent->setDateStart(!empty($values['dateStart'][$lang]) ? date_create_from_format('d. m. Y', $values['dateStart'][$lang]) : null);
                    $ent->setShowInCalendar(isset($values['showInCalendar'][$lang]) ? true : false);
                    
                }
            } else {
                $ent = new ArticleNew();
                $ent->setArticle($article);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                $ent->setDateStart(!empty($values['dateStart'][$lang]) ? date_create_from_format('d. m. Y', $values['dateStart'][$lang]) : null);
                $ent->setShowInCalendar(isset($values['showInCalendar'][$lang]) ? true : false);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function updateEvent($article, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getArticleEventRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                    $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                    $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                    $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                    $ent->setDateText(!empty($values['dateText'][$lang]) ? $values['dateText'][$lang] : null);
                    $ent->setDateStart(!empty($values['dateStart'][$lang]) ? date_create_from_format('d. m. Y', $values['dateStart'][$lang]) : null);
                    $ent->setPrimaryOnHP(isset($values['primaryOnHP'][$lang]) ? true : false);
                }
            } else {
                $ent = new ArticleEvent();
                $ent->setArticle($article);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                $ent->setLang($langEnt);
                $ent->setDateText(!empty($values['dateText'][$lang]) ? $values['dateText'][$lang] : null);
                $ent->setDateStart(!empty($values['dateStart'][$lang]) ? date_create_from_format('d. m. Y', $values['dateStart'][$lang]) : null);
                $ent->setPrimaryOnHP(isset($values['primaryOnHP'][$lang]) ? true : false);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function updateTemplate($article, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getArticleTemplateRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                    $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                    $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                    $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                }
            } else {
                $ent = new ArticleTemplate();
                $ent->setArticle($article);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setShowName(isset($values['showName'][$lang]) ? true : false);
                $ent->setPerex(!empty($values['perex'][$lang]) ? $values['perex'][$lang] : null);
                $ent->setContent(!empty($values['content'][$lang]) ? $values['content'][$lang] : null);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function changeActive($id, $status, $locale)
    {
        $mainArticle = $this->em->getArticleRepository()->find($id);
        if ($mainArticle) {
            $language = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
            if ($mainArticle->type == 'default') {
                $article = $this->em->getArticleDefaultRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'new') {
                $article = $this->em->getArticleNewRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'event') {
                $article = $this->em->getArticleEventRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'template') {
                $article = $this->em->getArticleTemplateRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            }
            if (isset($article) && $article) {
                $article->setActive($status);
                $this->em->flush();
            }
        }
    }

    public function changeShowName($id, $status, $locale)
    {
        $mainArticle = $this->em->getArticleRepository()->find($id);
        if ($mainArticle) {
            $language = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
            if ($mainArticle->type == 'default') {
                $article = $this->em->getArticleDefaultRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'new') {
                $article = $this->em->getArticleNewRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'template') {
                $article = $this->em->getArticleTemplateRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            } elseif($mainArticle->type == 'event') {
                $article = $this->em->getArticleEventRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            }
            if (isset($article) && $article) {
                $article->setShowName($status);
                $this->em->flush();
            }
        }
    }

    public function changeDropdown($id, $status, $locale)
    {
        $mainArticle = $this->em->getArticleRepository()->find($id);
        if ($mainArticle) {
            if ($mainArticle->type == 'default') {
                $language = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
                $article = $this->em->getArticleDefaultRepository()->findOneBy(['article' => $id, 'lang' => $language]);
            }
            if (isset($article) && $article) {
                $article->setDropdown($status);
                $this->em->flush();
            }
        }
    }

    public function addImage($articleId, $image)
    {
        $checkImage = $this->db->query('SELECT id FROM article_image WHERE article_id = ? and path = ?', $articleId, $image)->fetch();
        
        if (!$checkImage) {
            $lastOrder = $this->db->query('SELECT order_img FROM article_image WHERE article_id = ? ORDER BY order_img DESC', $articleId)->fetchField();
            if (!$lastOrder) {
                $lastOrder = 1;
            } else {
                $lastOrder++;
            }
            $data = [
                'article_id' => $articleId,
                'path' => $image,
                'order_img' => $lastOrder,
                'alt' => pathinfo($image,PATHINFO_FILENAME),
            ];
            $this->db->table('article_image')->insert($data);

            return true;
        }

        return false;
    }

    public function deleteImage($imgId)
    {
        $image = $this->em->getArticleImageRepository()->find($imgId);
        if ($image) {
            $this->imageStorage->delete($image->path);
            //@unlink($image->path);
            $this->em->remove($image);
            $this->em->flush();
            return basename($image->path);
        }

        return false;
    }

    public function updateGallery($values)
    {
        if (isset($values['imgId'])) {
            foreach($values['imgId'] as $k => $v) {
                $image = $this->em->getArticleImageRepository()->find($v);
                if ($image) {
                    $image->setAlt($values['imgAlt'][$k]);
                    $image->setOrderImg(intval($values['imgOrder'][$k]));
                }
            }
            $this->em->flush();
        }
    }

    public function addFile($articleId, $file)
    {
        $checkFile = $this->db->query('SELECT id FROM article_file WHERE article_id = ? and path = ?', $articleId, $file)->fetch();
        if (!$checkFile) {
            $lastOrder = $this->db->query('SELECT order_file FROM article_file WHERE article_id = ? ORDER BY order_file DESC', $articleId)->fetchField();
            if (!$lastOrder) {
                $lastOrder = 1;
            } else {
                $lastOrder++;
            }
            $data = [
                'article_id' => $articleId,
                'path' => $file,
                'order_file' => $lastOrder,
                'alt' => pathinfo($file,PATHINFO_FILENAME),
            ];
            $fileDB = $this->db->table('article_file')->insert($data);

            $lang = $this->em->getLanguageRepository()->findOneBy(['defaultCode' => true]);

            if ($lang && $fileDB) {
                $dataLang = [
                    'file_id' => $fileDB->id,
                    'lang_id' => $lang->id
                ];
                $this->db->table('article_file_in_language')->insert($dataLang);
            }

            return true;
        }

        return false;
    }

    public function deleteFile($fileId)
    {
        $file = $this->em->getArticleFileRepository()->find($fileId);
        if ($file) {
            @unlink($file->path);
            if ($file->langs) {
                foreach($file->langs as $l) {
                    $this->em->remove($l);
                }
            }
            $this->em->remove($file);
            $this->em->flush();
            return basename($file->path);
        }

        return false;
    }

    public function updateFiles($values)
    {
        if (isset($values['fileId'])) {
            foreach($values['fileId'] as $k => $v) {
                $file = $this->em->getArticleFileRepository()->findOneBy(['article' => $values['id'], 'id' => $v]);
                if ($file) {
                    $file->setAlt($values['fileAlt'][$k]);
                    $file->setOrderFile(intval($values['fileOrder'][$k]));

                    $langsOr = [];
                    if ($file->langs) {
                        foreach($file->langs as $l) {
                            $langsOr[] = $l->lang->id;
                        }
                    }
                    $langs = [];
                    if (isset($values['fileLangs'][$k])) {
                        foreach($values['fileLangs'][$k] as $l) {
                            $langs[] = $l;
                        }
                    }

                    $langsAdd = array_diff($langs, $langsOr);
                    $langsRemove = array_diff($langsOr, $langs);

                    if ($langsAdd) {
                        $this->db->beginTransaction();
                        foreach($langsAdd as $l) {
                            $datal = [
                                'file_id' => $file->id,
                                'lang_id' => $l,
                            ];
                            $this->db->table('article_file_in_language')->insert($datal);
                        }
                        $this->db->commit();
                    }

                    if ($langsRemove) {
                        $this->db->query('DELETE FROM article_file_in_language WHERE file_id = ? and lang_id IN (' . implode(',', $langsRemove) . ')', $file->id);
                    }
                    $this->em->flush();
                }
                $this->em->refresh($file);
            }
        }
    }

    public function getDefaultArticle($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT ad.*,
            (SELECT COUNT(ai.id) FROM article_image ai WHERE ad.article_id = ai.article_id) as imagesCount,
            (SELECT COUNT(af.id) FROM article_file af WHERE ad.article_id = af.article_id) as filesCount
            FROM article_default ad
            LEFT JOIN language l ON l.id = ad.lang_id
            WHERE ad.article_id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getNewArticle($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, an.*, ai.path, ai.alt
            FROM article a
            LEFT JOIN article_new an ON an.article_id = a.id
            LEFt JOIN article_image ai ON ai.id
             = ( SELECT aic.id
                 FROM article_image aic
                 WHERE aic.article_id = an.article_id
                 ORDER BY aic.order_img
                 LIMIT 1
               )
            LEFT JOIN language l ON l.id = an.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getEventArticle($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, ann.*, ai.path, ai.alt
            FROM article a
            LEFT JOIN article_event ann ON ann.article_id = a.id
            LEFt JOIN article_image ai ON ai.id
             = ( SELECT aic.id
                 FROM article_image aic
                 WHERE aic.article_id = ann.article_id
                 ORDER BY aic.order_img
                 LIMIT 1
               )
            LEFT JOIN language l ON l.id = ann.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getTemplateArticle($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, ann.*, ai.path, ai.alt
            FROM article a
            LEFT JOIN article_template ann ON ann.article_id = a.id
            LEFt JOIN article_image ai ON ai.id
             = ( SELECT aic.id
                 FROM article_image aic
                 WHERE aic.article_id = ann.article_id
                 ORDER BY aic.order_img
                 LIMIT 1
               )
            LEFT JOIN language l ON l.id = ann.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getNewArticleDetail($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, an.*,
            (SELECT COUNT(ai.id) FROM article_image ai WHERE an.article_id = ai.article_id) as imagesCount,
            (SELECT COUNT(af.id) FROM article_file af WHERE an.article_id = af.article_id) as filesCount
            FROM article a
            LEFT JOIN article_new an ON an.article_id = a.id
            LEFT JOIN language l ON l.id = an.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getEventArticleDetail($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, ann.*,
            (SELECT COUNT(ai.id) FROM article_image ai WHERE ann.article_id = ai.article_id) as imagesCount,
            (SELECT COUNT(af.id) FROM article_file af WHERE ann.article_id = af.article_id) as filesCount
            FROM article a
            LEFT JOIN article_event ann ON ann.article_id = a.id
            LEFT JOIN language l ON l.id = ann.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getTemplateArticleDetail($articleId, $locale)
    {
        $article = $this->db->queryArgs('
            SELECT a.publish, ann.*,
            (SELECT COUNT(ai.id) FROM article_image ai WHERE ann.article_id = ai.article_id) as imagesCount,
            (SELECT COUNT(af.id) FROM article_file af WHERE ann.article_id = af.article_id) as filesCount
            FROM article a
            LEFT JOIN article_template ann ON ann.article_id = a.id
            LEFT JOIN language l ON l.id = ann.lang_id
            WHERE a.id = ? and l.code = ?
        ', [$articleId, $locale])->fetch();

        return $article;
    }

    public function getImages($articleId)
    {
        $images = $this->db->queryArgs('
            SELECT *
            FROM article_image
            WHERE article_id = ?
            ORDER BY order_img
        ', [$articleId])->fetchAll();

        return $images;
    }

    public function getFiles($articleId, $locale)
    {
        $files = $this->db->queryArgs('
            SELECT a.*
            FROM article_file a
            LEFT JOIN article_file_in_language afil ON afil.file_id = a.id
            LEFT JOIN language l ON l.id = afil.lang_id
            WHERE a.article_id = ? and l.code = ?
            ORDER BY a.order_file
        ', [$articleId, $locale])->fetchAll();

        return $files;
    }

    public function getMenu($id, $locale)
    {
        return $this->db->queryArgs('
            SELECT m.id, ml.name, m.parent_menu_id
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and m.id = ?
        ', [$locale, $id])->fetch();
    }

    /**
     * Get searching data for product autocomplete
     * @param string $term of search
     * @param string $locale
     * @return array of results
     */
    public function getDataAutocompleteMenu($term, $locale)
    {
        $result2 = $this->db->queryArgs('
            SELECT m.id, ml.name, m.parent_menu_id
            FROM menu m
            LEFT JOIN menu_language ml ON m.id = ml.menu_id
            LEFT JOIN language l ON ml.lang_id = l.id
            WHERE l.code = ? and ml.name like "%'.$term.'%"
        ', [$locale])->fetchAll();

        $arr = [];
        foreach ($result2 as $it) {
            $name = $it->name;
            $parentMenu = $it->parent_menu_id;
            while($parentMenu) {
                $menu = $this->getMenu($parentMenu, $locale);
                if (!empty($name)) {
                    $name = $menu->name . ' -> ' . $name;
                } else {
                    $name = $menu->name;
                }
                $parentMenu = $menu->parent_menu_id;
            }
            $arr[$it->id] = [$name, $it->id];
        }
        return $arr;
    }
}