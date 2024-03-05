<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WebSettingLanguageRepository")
 * @ORM\Table(name="`web_setting_language`")
 */
class WebSettingLanguage extends AbstractEntity
{

    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="WebSetting")
     * @ORM\JoinColumn(name="setting_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $setting;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='text'
     * FORM title="Hodnota"
     * FORM attribute-placeholder='Hodnota'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Hodnota"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $value;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     */
    protected $lang;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}