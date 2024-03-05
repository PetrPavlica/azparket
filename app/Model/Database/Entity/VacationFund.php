<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VacationFundRepository")
 * @ORM\HasLifecycleCallbacks
 */
class VacationFund extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Worker")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Zaměstnanec"
     * FORM prompt="Nic není vybráno"
     * FORM data-entity-values=Worker[$surname$ $name$][]['surname' => 'ASC']
     * FORM required="Zaměstnanec je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='surname'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-alias='wrrk'
     * GRID value-mask=#[$surname$ $name$]
     * GRID filter=single-entity #['name', 'surname']
     */
    protected $worker;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Rok"
     *
     * GRID type='text'
     * GRID title="Rok"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Základ dovolené"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Základ dovolené"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $hoursBase;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Zásluhové volno"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Zásluhové volno"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $hoursPlus;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Krácení dovolené"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Krácení dovolené"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $hoursMinus;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}