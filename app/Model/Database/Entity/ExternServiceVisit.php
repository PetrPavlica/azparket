<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ExternServiceVisitRepository")
 * @ORM\Table(name="`extern_service_visit`")
 * @ORM\HasLifecycleCallbacks
 * 
 */
class ExternServiceVisit extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string")
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $name;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum prvního servisu"
     *
     * GRID type='date'
     * GRID title="Datum prvního servisu"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $visitDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Četnost servisu (měsíce)"
     * FORM attribute-class="form-control"
     *
     * GRID type='integer'
     * GRID title="Četnost servisu"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $repeatPeriod;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-class='form-control'
     * FORM attribute-rows='3'
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="MachineInExternServiceVisit", mappedBy="externServiceVisit")
     * 
     * GRID type='multi-text'
     * GRID title="Stroj v tomto servisu"
     * GRID visible='true'
     * GRID entity='Machine'
     * GRID entity-alias='ms'
     * GRID entity-join-column=machine
     * GRID entity-link=name
     * GRID filter=single-entity #['name']
     */
    protected $machines;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Barva kalendáře"
     * FORM attribute-class='form-control input-md color-care-select'
     *
     * GRID type='text'
     * GRID title="Kalendář"
     * GRID sortable='true'
     * GRID visible='false'
     */
    protected $calendarColor;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}