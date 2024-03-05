<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\BannerPartnerLanguageRepository")
 * @ORM\Table(name="`banner_partner_language`")
 */
class BannerPartnerLanguage extends AbstractEntity
{

    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="BannerPartner")
     */
    protected $banner;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $link;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}