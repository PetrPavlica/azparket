<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerInWorkerTenderRepository")
 * @ORM\Table(name="`worker_in_worker_tender`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkerInWorkerTender extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Worker", inversedBy="workerTenders")
     */
    protected $worker;

    /**
     * @ORM\ManyToOne(targetEntity="WorkerTender", inversedBy="workers")
     */
    protected $tender;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title='Výsledek'
     * FORM prompt='Nic není vybráno'
     * FORM data-own=['A' > 'A' | 'B' > 'B' | 'C' > 'C' | 'D' > 'D' | 'E' > 'E' | 'F' > 'Nesplnil/a']
     * FORM attribute-class="form-control selectpicker"
     *
     * GRID type='text'
     * GRID title="Výsledek"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše' | 'A' > 'A' | 'B' > 'B' | 'C' > 'C' | 'D' > 'D' | 'E' > 'E' | 'F' > 'Nesplnil/a']
     * GRID visible='true'
     * GRID inline-type='select'
     * GRID inline-prompt=' '
     * GRID inline-data-own=['A' > 'A' | 'B' > 'B' | 'C' > 'C' | 'D' > 'D' | 'E' > 'E' | 'F' > 'Nesplnil/a']
     */
    protected $result;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='popis pracovní pozice'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $resultDesc;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}