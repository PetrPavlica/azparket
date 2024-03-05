<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ServiceRepository")
 * @ORM\Table(name="`service`")
 * @ORM\HasLifecycleCallbacks
 */
class Service extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * FORM type='select'
     * FORM title="Zaměstnanec"
     * FORM prompt="Nevybráno"
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Worker[name][][]
     * FORM required="Toto je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-alias='wor'
     * GRID filter=single-entity #['name']
     */
    protected $worker;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum služby"
     *
     * GRID type='date'
     * GRID title="Datum služby"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateService;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}