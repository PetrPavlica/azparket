<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WebSettingRepository")
 * @ORM\Table(name="`web_setting`")
 * @ORM\HasLifecycleCallbacks
 */
class WebSetting extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód"
     * FORM attribute-placeholder='Kód'
     * FORM attribute-class='form-control input-md'
     * FORM required='Kód je povinné pole!'
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='volitelná poznámka'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM prompt='-- vyberte'
     * FORM title='Typ'
     * FORM attribute-class='form-control'
     * FORM data-own=['classic' > 'Klasická hodnota' | 'editor' > 'Editor']
     * FORM required='Typ je povinné pole!'
     */
    protected $type;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>