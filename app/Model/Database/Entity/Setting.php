<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\SettingRepository")
 * @ORM\Table(name="`setting`")
 * @ORM\HasLifecycleCallbacks
 */
class Setting extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * FORM type='text'
     * FORM title="Kód"
     * FORM attribute-placeholder='Kód'
     * FORM attribute-class='form-control input-md'
     * FORM disabled=""
     * FORM readonly=""
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

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}