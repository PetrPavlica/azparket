<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ReservationItemRepository")
 * @ORM\Table(name="`reservation_item`")
 * @ORM\HasLifecycleCallbacks
 */
class ReservationItem extends AbstractEntity
{

    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Název'
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="Reservation", mappedBy="reservationItem")
     */
    protected $reservations;

    /**
     * @ORM\Column(type="integer")
     * FORM type='text'
     * FORM title="Doba dílu rezervace [minuty]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Doba dílu rezervace'
     */
    protected $reservablePeriod;

    /**
     * @ORM\Column(type="integer")
     * FORM type='text'
     * FORM title="Minimální rezervovatelná doba zákazníkem [minuty]"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Minimální rezervovatelná doba'
     */
    protected $minReservablePeriod;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Cena za hodinu bez DPH"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Nabídnutá cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $pricePerHour;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeMondayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeMondayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeTuesdayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeTuesdayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeWednesdayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeWednesdayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeThursdayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeThursdayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeFridayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeFridayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeSaturdayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeSaturdayTo;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeSundayFrom;

    /**
     * @ORM\Column(type="string", nullable=true, length="5")
     * FORM type='time'
     * FORM title=""
     * FORM attribute-class='form-control input-md'
     */
    protected $timeSundayTo;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title=" Aktivní"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>