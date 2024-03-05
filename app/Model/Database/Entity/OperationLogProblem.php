<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OperationLogProblemRepository")
 * @ORM\Table(name="`operation_log_problem`")
 * @ORM\HasLifecycleCallbacks
 */
class OperationLogProblem extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Hodina"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Hodina zjištění"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $hour;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Popis závady"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Popis závady"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='select'
     * FORM title="Zastavit linku?"
     * FORM data-own=['ANO' > 'ANO'|'NE' > 'NE']
     * FORM prompt="- nevybráno"
     *
     * GRID type='text'
     * GRID title="Zastavit linku"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     * GRID filter=select #['' > 'Vše'|'ANO' > 'ANO'|'NE' > 'NE']
     */
    protected $stopLine;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum"
     *
     * GRID type='date'
     * GRID title="Datum"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $dateFix;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Hodina"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Hodina odstranění"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $hourFix;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Hodina uvolnění linky"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Hodina uvolnění"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $hourRelease;

    /**
     * @ORM\ManyToOne(targetEntity="OperationLog", inversedBy="logProblems")
     */
    protected $operationLog;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}