<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\TaskStateRepository")
 * @ORM\Table(name="`task_state`")
 * @ORM\HasLifecycleCallbacks
 */
class TaskState extends AbstractEntity
{
    use TId;
    use TCreatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class="form-control"
     * FORM required="Název je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Priorita (pořadí)"
     * FORM rule-integer='Prosím zadávejte pouze celá čísla'
     * FORM attribute-class="form-control"
     * FORM default-value=0
     *
     * GRID type='integer'
     * GRID title="Priorita (pořadí)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderType;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Viditelný na nástěnce?"
     * FORM attribute-class="m-0 mr-2 mt-2"
     * FORM default-value='1'
     *
     * GRID type='bool'
     * GRID title="Viditelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $forDashboard;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
     * FORM attribute-class="m-0 mr-2 mt-2"
     * FORM default-value='1'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='false'
     * GRID align='center'
     */
    protected $active;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}