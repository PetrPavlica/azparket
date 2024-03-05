<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use App\Model\Database\Entity\MenuLanguage;
use Nette\Database\Explorer;
use Ublaboo\ImageStorage\ImageStorage;
use Nette\Utils\DateTime;

class Menu
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

    public function updateLanguages($menu, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getMenuLanguageRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setNameOnFront(!empty($values['nameOnFront'][$lang]) ? $values['nameOnFront'][$lang] : null);
                    $ent->setNameOnSubFront(!empty($values['nameOnSubFront'][$lang]) ? $values['nameOnSubFront'][$lang] : null);
                    $ent->setUrl(!empty($values['url'][$lang]) ? $values['url'][$lang] : null);
                    $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                    $ent->setVisible(isset($values['visible'][$lang]) ? true : false);
                    $ent->setShowUp(isset($values['showUp'][$lang]) ? true : false);
                    $ent->setNewWindow(isset($values['newWindow'][$lang]) ? true : false);
                    $ent->setShowOnHomepage(isset($values['showOnHomepage'][$lang]) ? true : false);
                    $ent->setShowSignpost(isset($values['showSignpost'][$lang]) ? true : false);
                    $ent->setTitle(!empty($values['title'][$lang]) ? $values['title'][$lang] : null);
                    $ent->setKeywords(!empty($values['keywords'][$lang]) ? $values['keywords'][$lang] : null);
                    $ent->setDescription(!empty($values['description'][$lang]) ? $values['description'][$lang] : null);
                    $ent->setMenuDescription(!empty($values['menuDescription'][$lang]) ? $values['menuDescription'][$lang] : null);
                }
            } else {
                $ent = new MenuLanguage();
                $ent->setMenu($menu);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setNameOnFront(!empty($values['nameOnFront'][$lang]) ? $values['nameOnFront'][$lang] : null);
                $ent->setNameOnSubFront(!empty($values['nameOnSubFront'][$lang]) ? $values['nameOnSubFront'][$lang] : null);
                $ent->setUrl(!empty($values['url'][$lang]) ? $values['url'][$lang] : null);
                $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                $ent->setVisible(isset($values['visible'][$lang]) ? true : false);
                $ent->setShowUp(isset($values['showUp'][$lang]) ? true : false);
                $ent->setNewWindow(isset($values['newWindow'][$lang]) ? true : false);
                $ent->setShowOnHomepage(isset($values['showOnHomepage'][$lang]) ? true : false);
                $ent->setShowSignpost(isset($values['showSignpost'][$lang]) ? true : false);
                $ent->setTitle(!empty($values['title'][$lang]) ? $values['title'][$lang] : null);
                $ent->setKeywords(!empty($values['keywords'][$lang]) ? $values['keywords'][$lang] : null);
                $ent->setDescription(!empty($values['description'][$lang]) ? $values['description'][$lang] : null);
                $ent->setMenuDescription(!empty($values['menuDescription'][$lang]) ? $values['menuDescription'][$lang] : null);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function changeVisible($id, $status, $locale)
    {
        $lang = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
        $menu = $this->em->getMenuLanguageRepository()->findOneBy(['menu' => $id, 'lang' => $lang]);
        if ($menu) {
            $menu->setVisible($status);
            $this->em->flush();
        }
    }

    public function changeShowUp($id, $status, $locale)
    {
        $lang = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
        $menu = $this->em->getMenuLanguageRepository()->findOneBy(['menu' => $id, 'lang' => $lang]);
        if ($menu) {
            $menu->setShowUp($status);
            $this->em->flush();
        }
    }

    public function changeNewWindow($id, $status, $locale)
    {
        $lang = $this->em->getLanguageRepository()->findOneBy(['code' => $locale]);
        $menu = $this->em->getMenuLanguageRepository()->findOneBy(['menu' => $id, 'lang' => $lang]);
        if ($menu) {
            $menu->setNewWindow($status);
            $this->em->flush();
        }
    }

    public function deleteImage($menuId)
    {
        $menu = $this->em->getMenuRepository()->find($menuId);
        if ($menu) {
            if (file_exists($menu->image)) {
                unlink($menu->image);
            }
            $menu->setImage(null);
            $this->em->flush();
            return true;
        }
        return false;
    }

    public function deleteMenu($menu)
    {
        if (is_numeric($menu)) {
            $menu = $this->em->getMenuRepository()->find($menu);;
            if (!$menu) {
                return false;
            }
        }
        $this->deleteImage($menu);
        $langs = $this->em->getMenuLanguageRepository()->findBy(['menu' => $menu]);
        foreach ($langs as $l) {
            $this->em->remove($l);
        }
        $this->em->remove($menu);
        $this->em->flush();
        return true;
    }
}