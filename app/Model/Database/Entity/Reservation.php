<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ReservationRepository")
 * @ORM\Table(name="`reservation`")
 * @ORM\HasLifecycleCallbacks
 */
class Reservation extends AbstractEntity
{

    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Customer")
     * FORM type='autocomplete'
     * FORM title='Zákazník'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Customer'
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='fullname'
     * GRID visible='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID sortable='true'
     * GRID filter=single-entity #['fullname']
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="ReservationItem", inversedBy="reservations")
     * FORM type='select'
     * FORM title="Rezervovatelná položka"
     * FORM prompt='-- zvolte položku'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search="true"
     * FORM data-entity-values=ReservationItem[$name$][][]
     * 
     * GRID type='text'
     * GRID title="Položka"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='ReservationItem'
     * GRID entity-alias='ri'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $reservationItem;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FOsRM type='datetime'
     * FOsRM title="Datum a čas od"
     * FOsRM attribute-class='form-control input-md flatPick'
     * FOsRM attribute-placeholder='Datum a čas od'
     *
     * GRID type='datetime'
     * GRID title="Datum od"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateFrom;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * FOsRM type='datetime'
     * FOsRM title="Datum a čas do"
     * FOsRM attribute-class='form-control input-md flatPick'
     * FOsRM attribute-placeholder='Datum a čas do'
     *
     * GRID type='datetime'
     * GRID title="Datum do"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateTo;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Zrušeno"
     *
     * GRID type='bool'
     * GRID title="Zrušeno"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ano'|'1' > 'Ne']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $canceled;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='hidden'
     * FORM data-entity=User[name]
     *
     * GRsID type='translate-text'
     * GRsID title="Založil"
     * GRsID entity-link='name'
     * GRsID visible='false'
     * GRsID entity='User'
     * GRsID entity-alias='uorig'
     * GRsID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $originator;
    
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
    protected $price;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>