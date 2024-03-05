<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OperationLogItemRepository")
 * @ORM\Table(name="`operation_log_item`")
 * @ORM\HasLifecycleCallbacks
 */
class OperationLogItem extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $externalId;

    /**
     * @var string
     * @ORM\Column(type="integer", nullable=true)
     *
     * GRID type='integer'
     * GRID title="P.č."
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $interNumber;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORmM type='text'
     * FORmM title="Tyč"
     * FORmM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Tyč"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $rod;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORmM type='text'
     * FORmM title="Kód dílce"
     * FORmM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Kód dílce"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $code;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORmM type='text'
     * FORmM title="Typ"
     * FORmM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Typ"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $typ;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORmM type='text'
     * FORmM title="Počet kusů"
     * FORmM rule-float ='Prosím číslo'
     * FORmM required='0'
     *
     * GRID type='text'
     * GRID title="Počet kusů"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $counts;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Ověření tlouškoměru"
     * FORM data-own=['OK' > 'OK'|'NOK' > 'NOK']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Ověření tlouškoměru"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'OK' > 'OK'|'NOK' > 'NOK']
     */
    protected $result1;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $result1Changed;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Hodnocení (dle prův. VP)"
     * FORM data-own=['OK' > 'OK'|'NOK' > 'NOK']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Hodnocení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'OK' > 'OK'|'NOK' > 'NOK']
     */
    protected $result2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $result2Changed;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Vrácené díly"
     * FORM rule-float ='Prosím číslo'
     * FORM required='0'
     *
     * GRID type='text'
     * GRID title="Vrácené díly"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $countsResult2;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $countsResult2Changed;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Kód vady / Poznámka"
     *
     * GRID type='text'
     * GRID title="Kód vady / Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $note;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $noteChanged;

    /**
     * @ORM\ManyToOne(targetEntity="OperationLog", inversedBy="logItems")
     */
    protected $operationLog;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}