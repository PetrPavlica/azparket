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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VisitProcessRepository")
 * @ORM\Table(name="`visit_process`")
 * @ORM\HasLifecycleCallbacks
 */
class VisitProcess extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="ID Zakázky"
     * FORM attribute-placeholder='ID Zakázky'
     * FORM required="ID Zakázky je povinné pole!"
     *
     * GRID type='text'
     * GRID title="ID Zakázky"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Typ opravy"
     * FORM attribute-placeholder='Typ opravy'
     * FORM required="Typ opravy je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Typ opravy"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='Popis'
     * FORM attribute-class='form-control input-md'
     * FORM attribute-style="height: 200px"
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="WorkerOnVisitProcess", mappedBy="visitProcess")
     * FORM type='multiselect'
     * FORM title="Zaměstnanec"
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-multiple='true'
     * FORM attr-data-live-search='true'
     * FORM data-entity-values=Worker[$name$][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerOnVisitProcess[visitProcess][worker]
     *
     * GRID type='multi-text'
     * GRID title="Zaměstnanec"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Worker'
     * GRID entity-join-column='worker'
     * GRID entity-alias='siwtvp'
     * GRID filter=single-entity #['name']['name' > 'ASC']
     */
    protected $worker;

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
     * FORM type='autocomplete'
     * FORM title='Objednavatel'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='CustomerOrdered'
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
     * @ORM\ManyToOne(targetEntity="Traffic")
     * FORM type='autocomplete'
     * FORM title='Provozovna'
     * FORM attribute-data-preload="false"
     * FORM attribute-data-suggest="true"
     * FORM attribute-data-minlen="1"
     * FORM attribute-class="form-control autocomplete-input"
     * FORM autocomplete-entity='Traffic'
     *
     * GRID type='text'
     * GRID title="Provozovna"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='Traffic'
     * GRID entity-alias='traff'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $traffic;

    /**
     * @ORM\ManyToOne(targetEntity="VisitProcessState")
     * FORM type='select'
     * FORM attribute-class="form-control selectpicker"
     * FORM title="Stav OP"
     * FOsRM prompt="Nic není vybráno"
     * FORM data-entity-values=VisitProcessState[$name$]['active' => '1']['stateOrder' => 'ASC']
     *
     * GRID type='text'
     * GRID title="Stav OP"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID sortable='true'
     * GRID entity='VisitProcessState'
     * GRID entity-alias='vps'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $state;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum přijetí objednávky"
     *
     * GRID type='date'
     * GRID title="Datum přijetí objednávky"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateAcceptOrder;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum zaslání nabídky"
     *
     * GRID type='date'
     * GRID title="Datum zaslání nabídky"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateSendOffer;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum objednání dílu"
     *
     * GRID type='date'
     * GRID title="Datum objednání dílu"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateOrderPart;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum zaslání dílu"
     *
     * GRID type='date'
     * GRID title="Datum zaslání dílu"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateSendPart;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum dokončení"
     *
     * GRID type='date'
     * GRID title="Datum dokončení"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     */
    protected $dateFinished;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * FORM type='checkbox'
     * FORM title="Interní označení"
     * FORM default-value='0'
     */
    protected $isIntOrderId;

    /**
     * @ORM\OneToMany(targetEntity="Visit", mappedBy="visitProcess")
     */
    protected $visits;

    /**
     * GRID type='text'
     * GRID title="Nutno objednat"
     * GRID visible='true'
     * GRID sortable='false'
     * GRID filter=single
     */
    protected $materialNeedBuyVisit;

    public function __construct($data = null)
    {
        $this->isIntOrderId = false;
        parent::__construct($data);
    }

}