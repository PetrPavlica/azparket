<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use App\Model\Database\Entity\WebSetting as WebSettingEntity;
use App\Model\Database\Entity\WebSettingLanguage;
use Nette\Database\Explorer;

class WebSetting
{

    /** @var EntityManager */
    private EntityManager $em;

    /** @var Explorer */
    protected $db;

    /**
     * Construct
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, Explorer $db) {
        $this->em = $em;
        $this->db = $db;
    }

    public function updateLanguages($setting, $values)
    {
        if (isset($values['value'])) {
            foreach ($values['value'] as $lang => $v) {
                $langEnt = $this->em->getLanguageRepository()->findOneBy(['code' => $lang]);
                if (isset($values['langId'][$lang]) && intval($values['langId'][$lang])) {
                    $ent = $this->em->getWebSettingLanguageRepository()->find(intval($values['langId'][$lang]));
                    if ($ent) {
                        $ent->setValue(!empty($values['value'][$lang]) ? $values['value'][$lang] : null);
                    }
                } else {
                    $ent = new WebSettingLanguage();
                    $ent->setSetting($setting);
                    $ent->setValue(!empty($values['value'][$lang]) ? $values['value'][$lang] : null);
                    $ent->setLang($langEnt);
                    $this->em->persist($ent);
                }
            }
        }

        $this->em->flush();
    }
}