<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Banner as BannerEntity;
use App\Model\Database\Entity\BannerLanguage;
use App\Model\Database\EntityManager;
use Nette\Database\Explorer;
use Ublaboo\ImageStorage\ImageStorage;
use Nette\Utils\DateTime;

class Banner
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

    public function updateLanguages($banner, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getBannerLanguageRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setText(!empty($values['text'][$lang]) ? $values['text'][$lang] : null);
                    $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                }
            } else {
                $ent = new BannerLanguage();
                $ent->setBanner($banner);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setText(!empty($values['text'][$lang]) ? $values['text'][$lang] : null);
                $ent->setLink(!empty($values['link'][$lang]) ? $values['link'][$lang] : null);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function changeActive($id, $status, $locale)
    {
        $banner = $this->em->getBannerLanguageRepository()->findOneBy(['banner' => $id, 'lang.code' => $locale]);
        if ($banner) {
            $banner->setActive($status);
            $this->em->flush();
        }
    }

    public function deleteImage($bannerId)
    {
        $banner = $this->em->getBannerRepository()->find($bannerId);
        bdump($banner);
        if ($banner) {
            if (file_exists($banner->image)) {
                $this->imageStorage->delete($banner->image);
                //@unlink($banner->image);
            }
            $banner->setImage(null);
            $this->em->flush();
            return true;
        }
        return false;
    }

    public function deleteBanner($bannerId){
        $banner = $this->em->getBannerRepository()->find($bannerId);
        if ($banner) {
           $this->deleteImage($bannerId);
           $bannersLang = $this->em->getBannerLanguageRepository()->findBy(["banner" => $banner]);
           foreach ($bannersLang as $bannerLang) {
            $this->em->remove($bannerLang);
            $this->em->flush($bannerLang);
           }
           $this->em->remove($banner);
           $this->em->flush($banner);
            return true;
        }
        return false;
    }
}