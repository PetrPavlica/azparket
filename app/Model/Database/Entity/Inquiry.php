<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\InquiryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Inquiry extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Configurator")
     *
     * GRID type='text'
     * GRID title="Konfigurátor"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Configurator'
     * GRID entity-alias='conf'
     * GRID value-mask=#[$name$]
     * GRID filter=single-entity #['name']
     */
    protected $configurator;

    /**
     * @ORM\OneToMany(targetEntity="InquiryValue", mappedBy="inquiry", cascade={"remove"})
     */
    protected $values;

    /**
     * @ORM\OneToMany(targetEntity="InquiryProduct", mappedBy="inquiry", cascade={"remove"})
     * FORM type='multiselect'
     * FORM title="Výsledek konfigurace"
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Product[id][][]
     * FORM multiselect-entity=InquiryProduct[node][product]
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     */
    protected $products;

    
    /**
     * @ORM\OneToMany(targetEntity="Offer", mappedBy="inquiry")
     */
    protected $offer;

    /**
     * @ORM\ManyToOne(targetEntity="Customer")
     *
     * GRID type='text'
     * GRID title="Zákazník"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Customer'
     * GRID entity-alias='cus'
     * GRID value-mask=#[$company$ $name$ $surname$]
     * GRID filter=single-entity #['name']
     */
    protected $customer;

    /**
     * Means customer was connected by db query w/ customer prop. search conditions
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * GRID type='bool'
     * GRID title="Auto. přiřazený zákazník"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $customerAuto;

    /**
     * @ORM\Column(type="text", nullable="true")
     * FORM type="textarea"
     * FORM disabled=""
     * FORM title="Zpráva od zákazníka"
     */
    protected $message;

    /**
     * @ORM\Column(type="text", nullable="true")
     * FORM type="textarea"
     * FORM title="Poznámka obchodníka"
     */
    protected $note;

    /**
     * @ORM\Column(type="text", nullable="true")
     * GRID type='text'
     * GRID title="Město instalace"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $installCity;
    
    /**
     * @ORM\Column(type="text", nullable="true")
     * GRID type='text'
     * GRID title="PSČ instalace"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $installZip;
    
    /**
     * Means this inquiry needs salesman to reply to
     * @ORM\Column(type="boolean", nullable=false)
     * 
     * GRID type='bool'
     * GRID title="Vyžaduje obchodníka"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $needsSalesman;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * 
     * GRID type='bool'
     * GRID title="Montáž k rodinnému domu"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $forFamilyHouse;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->needsSalesman = 1;
    }
}