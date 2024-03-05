<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\TrafficRepository")
 * @ORM\Table(name="`traffic`")
 * @ORM\HasLifecycleCallbacks
 */
class Traffic extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo provozovny"
     * FORM attribute-placeholder='Číslo provozovny'
     *
     * GRID type='text'
     * GRID title="Číslo provozovny"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $num;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='email'
     * FORM title="Email"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Email'
     *
     * GRID type='text'
     * GRID title="Email"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Telefon/mobil"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Telefon'
     *
     * GRID type='text'
     * GRID title="Telefon/mobil"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Ulice a č. p."
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Ulice'
     *
     * GRID type='text'
     * GRID title="Ulice fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $street;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Město"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Město'
     *
     * GRID type='text'
     * GRID title="Město fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $city;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="PSČ"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='PSČ'
     *
     * GRID type='text'
     * GRID title="PSČ fakturační"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $zip;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
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

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-class='form-control'
     * FORM attribute-rows='3'
     * FORM attribute-style="height: 119px"
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Customer")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Zákazník"
     * FORM prompt="Nic není vybráno"
     * FORM attribute-data-live-search='true'
     * FORM data-entity-values=Customer[$name$]['active' => '1']['name' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="CustomerOrdered")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Objednavatel"
     * FORM prompt="Nic není vybráno"
     * FORM attribute-data-live-search='true'
     * FORM data-entity-values=CustomerOrdered[$name$]['active' => '1']['name' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Objednavatel"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='CustomerOrdered'
     * GRID entity-alias='cusorde'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $customerOrdered;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Je Paušál?"
     * FORM default-value='0'
     *
     * GRID type='bool'
     * GRID title="Je Paušál?"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $isCostProgram;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Paušál"
     * FORM attribute-placeholder='Paušál'
     *
     * GRID type='text'
     * GRID title="Paušál"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costProgram;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Počet km tam"
     * FORM attribute-placeholder='Počet km tam'
     *
     * GRID type='text'
     * GRID title="Počet km tam"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costDistance;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Cesta tam z"
     *
     * GRID type='text'
     * GRID title="Cesta tam z"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costFrom;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Cesta tam do"
     *
     * GRID type='text'
     * GRID title="Cesta tam do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costTo;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Počet km zpět"
     * FORM attribute-placeholder='Počet km zpět'
     *
     * GRID type='text'
     * GRID title="Počet km zpět"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costBackDistance;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Cesta zpět z"
     *
     * GRID type='text'
     * GRID title="Cesta zpět z"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costBackFrom;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Cesta zpět do"
     *
     * GRID type='text'
     * GRID title="Cesta zpět do"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $costBackTo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ztráta času tam hodin"
     * FORM data-own=['0' > '0' | '1' > '1' | '2' > '2' | '3' > '3' | '4' > '4' | '5' > '5' | '6' > '6' | '7' > '7' | '8' > '8' | '9' > '9' | '10' > '10' | '11' > '11' | '12' > '12' | '13' > '13' | '14' > '14' | '15' > '15' | '16' > '16' | '17' > '17' | '18' > '18' | '19' > '19' | '20' > '20' | '21' > '21' | '22' > '22' | '23' > '23']
     * FORM prompt="--"
     */
    protected $costLostHoursDistance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ztráta času tam minut"
     * FORM data-own=['0' > '00' | '5' > '30']
     * FORM prompt="--"
     */
    protected $costLostMinutesDistance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ztráta času zpět hodin"
     * FORM data-own=['0' > '0' | '1' > '1' | '2' > '2' | '3' > '3' | '4' > '4' | '5' > '5' | '6' > '6' | '7' > '7' | '8' > '8' | '9' > '9' | '10' > '10' | '11' > '11' | '12' > '12' | '13' > '13' | '14' > '14' | '15' > '15' | '16' > '16' | '17' > '17' | '18' > '18' | '19' > '19' | '20' > '20' | '21' > '21' | '22' > '22' | '23' > '23']
     * FORM prompt="--"
     */
    protected $costLostHoursBackDistance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='select'
     * FORM title="Ztráta času zpět minut"
     * FORM data-own=['0' > '00' | '5' > '30']
     * FORM prompt="--"
     */
    protected $costLostMinutesBackDistance;

    /**
     * @ORM\OneToMany(targetEntity="WorkerOnTraffic", mappedBy="traffic")
     * FORM type='multiselect'
     * FORM title="Zaměstnanec"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=Worker[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerOnTraffic[traffic][worker]
     *
     * GRID type='multi-text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-join-column='worker'
     * GRID entity-alias='wot'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $worker;

    /**
     * @ORM\OneToMany(targetEntity="WorkerOnTrafficSubstitute", mappedBy="traffic")
     * FORM type='multiselect'
     * FORM title="Zaměstnanec zástup"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=Worker[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerOnTrafficSubstitute[traffic][worker]
     *
     * GRID type='multi-text'
     * GRID title="Zaměstnanec zástup"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-join-column='worker'
     * GRID entity-alias='wots'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $workerSubstitute;

    public function __construct($data = null)
    {
        $this->active = true;
        parent::__construct($data);
    }

}