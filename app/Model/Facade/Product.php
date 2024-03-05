<?php

namespace App\Model\Facade;

use App\Model\Database\Entity\Product as ProductEnt;
use App\Model\Database\Entity\ProductLanguage;
use Nette\Utils\Strings;
use App\Model\Database\EntityManager;
use Nette\Database\Explorer;
use Ublaboo\ImageStorage\ImageStorage;

class Product
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
    public function __construct(EntityManager $em, Explorer $db, ImageStorage $imageStorage)
    {
        $this->em = $em;
        $this->db = $db;
        $this->imageStorage = $imageStorage;
    }

    public function updateLanguages($product, $values)
    {
        foreach($values['name'] as $lang => $v) {
            $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
            $url = $this->generateUrl($product->id, $values['name'][$lang], $lang);
            if (empty($url)) {
                $url = null;
            }
            if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                $ent = $this->em->getProductLanguageRepository()->find(intval($values['langId'][$lang]));
                if ($ent) {
                    $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                    $ent->setUrl($url);
                    $ent->setActive(isset($values['active'][$lang]) ? true : false);
                    $ent->setShortDescription(!empty($values['shortDescription'][$lang]) ? $values['shortDescription'][$lang] : null);
                    $ent->setDescription(!empty($values['description'][$lang]) ? $values['description'][$lang] : null);
                }
            } else {
                $ent = new ProductLanguage();
                $ent->setProduct($product);
                $ent->setName(!empty($values['name'][$lang]) ? $values['name'][$lang] : null);
                $ent->setUrl($url);
                $ent->setActive(isset($values['active'][$lang]) ? true : false);
                $ent->setShortDescription(!empty($values['shortDescription'][$lang]) ? $values['shortDescription'][$lang] : null);
                $ent->setDescription(!empty($values['description'][$lang]) ? $values['description'][$lang] : null);
                $ent->setLang($langEnt);
                $this->em->persist($ent);
            }
        }

        $this->em->flush();
    }

    public function generateUrl($idProduct, $name, $locale)
    {
        $counter = null;
        $slug = Strings::webalize($name);

        update:
        $res = $this->em->getProductLanguageRepository()->createQueryBuilder('pl')
            ->leftJoin('pl.lang', 'l')
            ->where('pl.url = :slug AND pl.product != :idProduct AND l.code = :locale')
            ->setParameters(['slug' => $slug . $counter, 'idProduct' => $idProduct, 'locale' => $locale])
            ->getQuery()->getResult();
        if ($res) {
            $counter++;
            goto update;
        }

        return $slug.$counter;
    }

    public function addImage($productId, $image)
    {
        $checkImage = $this->db->query('SELECT id FROM product_image WHERE product_id = ? and path = ?', $productId, $image)->fetch();
        if (!$checkImage) {
            $lastOrder = $this->db->query('SELECT order_img FROM product_image WHERE product_id = ? ORDER BY order_img DESC', $productId)->fetchField();
            if (!$lastOrder) {
                $lastOrder = 1;
            } else {
                $lastOrder++;
            }
            $data = [
                'product_id' => $productId,
                'path' => $image,
                'order_img' => $lastOrder,
                'is_main' => $lastOrder == 1 ? 1 : 0,
                'alt' => pathinfo($image,PATHINFO_FILENAME),
            ];
            $this->db->table('product_image')->insert($data);

            return true;
        }

        return false;
    }

    public function deleteImage($imgId)
    {
        $image = $this->em->getProductImageRepository()->find($imgId);
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
                $image = $this->em->getProductImageRepository()->find($v);
                if ($image) {
                    $image->setAlt($values['imgAlt'][$k]);
                    $image->setOrderImg(intval($values['imgOrder'][$k]));
                    $image->setIsMain(isset($values['isMain']) && $values['isMain'] == $k ? true : false);
                }
            }
            $this->em->flush();
        }
    }

    public function addFile($productId, $file)
    {
        $checkFile = $this->db->query('SELECT id FROM product_file WHERE product_id = ? and path = ?', $productId, $file)->fetch();
        if (!$checkFile) {
            $lastOrder = $this->db->query('SELECT order_file FROM product_file WHERE product_id = ? ORDER BY order_file DESC', $productId)->fetchField();
            if (!$lastOrder) {
                $lastOrder = 1;
            } else {
                $lastOrder++;
            }
            $data = [
                'product_id' => $productId,
                'path' => $file,
                'order_file' => $lastOrder,
                'alt' => pathinfo($file,PATHINFO_FILENAME),
            ];
            $fileDB = $this->db->table('product_file')->insert($data);

            $lang = $this->em->getLanguageRepository()->findOneBy(['defaultCode' => true]);

            if ($lang && $fileDB) {
                $dataLang = [
                    'file_id' => $fileDB->id,
                    'lang_id' => $lang->id
                ];
                $this->db->table('product_file_in_language')->insert($dataLang);
            }

            return true;
        }

        return false;
    }

    public function deleteFile($fileId)
    {
        $file = $this->em->getProductFileRepository()->find($fileId);
        if ($file) {
            @unlink($file->path);
            if ($file->langs) {
                foreach($file->langs as $l) {
                    $this->em->remove($l);
                }
                $this->em->flush();
            }
            $this->em->remove($file);
            return basename($file->path);
        }

        return false;
    }

    public function updateFiles($values)
    {
        if (isset($values['fileId'])) {
            foreach($values['fileId'] as $k => $v) {
                $file = $this->em->getProductFileRepository()->findOneBy(['product' => $values['id'], 'id' => $v]);
                if ($file) {
                    $file->setAlt($values['fileAlt'][$k]);
                    $file->setOrderFile(intval($values['fileOrder'][$k]));
                    $file->setSection(!empty($values['fileSection'][$k]) ? intval($values['fileSection'][$k]) : null);

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
                            $this->db->table('product_file_in_language')->insert($datal);
                        }
                        $this->db->commit();
                    }

                    if ($langsRemove) {
                        $this->db->query('DELETE FROM product_file_in_language WHERE file_id = ? and lang_id IN (' . implode(',', $langsRemove) . ')', $file->id);
                    }
                    $this->em->flush();
                }
                $this->em->refresh($file);
            }
        }
    }

    public function changeActive($id, $status, $locale)
    {
        $product = $this->em->getProductLanguageRepository()->findOneBy(['product' => $id, 'lang.code' => $locale]);
        if ($product) {
            $product->setActive($status);
            $this->em->flush();
        }
    }
}