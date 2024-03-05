<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerTenderTypeRepository")
 * @ORM\Table(name="`worker_tender_type`")
 * @ORM\HasLifecycleCallbacks
 * 
 */
class WorkerTenderType extends AbstractEntity
{
    use TId;
    use TCreatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Barva"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Barva"
     * GRID visible='true'
     * GRID filter='single'
     */
    protected $color;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $workerColumn;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM required='0'
     * FORM title="Priorita (pořadí)"
     * FORM rule-integer='Prosím zadávejte pouze celá čísla'
     * FORM attribute-class="form-control"
     *
     * GRID type='number'
     * GRID title="Priorita"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderN;

    /**
     * @ORM\OneToMany(targetEntity="WorkerTender", mappedBy="tenderType")
     */
    protected $workerTenders;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}