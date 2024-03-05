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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ProcessRepository")
 * @ORM\Table(name="`process`")
 * @ORM\HasLifecycleCallbacks
 *
 * DETAIL-SECTION default=1
 */
class Process extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='hidden'
     *
     * GRID type='text'
     * GRID title="Označení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     *
     * DETAIL title="Číslo OP"
     */
    protected $bpNumber;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="processes")
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
     * GRID filter=single-entity #['name']
     *
     * DETAIL title="Zákazník"
     * DETAIL entity-value='$company$, $name$ $surname$'
     */
    protected $customer;

    /**
     * @ORM\ManyToOne(targetEntity="ProcessState")
     * FORM type='select'
     * FORM title='Stav'
     * FORM data-entity=ProcessState[name]
     * FORM data-entity-values=ProcessState[$name$][][]
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Stav"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='ProcessState'
     * GRID entity-alias='ps'
     * GRID filter=single-entity #['name']
     *
     * DETAIL title="Stav"
     * DETAIL entity-column=name
     */
    protected $processState;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Číslo objednávky zákazníka"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder='Číslo objednávky'
     *
     * GRID type='text'
     * GRID title="Číslo objednávky zákazníka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $customerOrderNo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * GRID type='datetime'
     * GRID title="Datum objednávky"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     *
     * DETAIL title="Datum objednávky"
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum změny stavu"
     * GRID format-time="d. m. Y H:i:s"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $inStateDate;

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
     * GRID visible='false'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * FORM type='hidden'
     * FORM data-entity=User[name]
     *
     * GRID type='text'
     * GRID title="Vygeneroval"
     * GRID entity-link='name'
     * GRID visible='false'
     * GRID entity='User'
     * GRID entity-alias='user'
     * GRID filter=single-entity #['name']
     */
    protected $originator;

    /**
     * @ORM\OneToMany(targetEntity="ItemInProcess", mappedBy="process")
     * 
     */
    protected $items;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}