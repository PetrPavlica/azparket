<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MachineInExternServiceVisitRepository")
 * @ORM\Table(name="`machine_in_extern_service_visit`")
 * @ORM\HasLifecycleCallbacks
 */
class MachineInExternServiceVisit extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Machine", inversedBy="externServiceVisits")
     */
    protected $machine;

    /**
     * @ORM\ManyToOne(targetEntity="ExternServiceVisit", inversedBy="machines")
     */
    protected $externServiceVisit;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='select'
     * FORM title='Výsledek servisu'
     * FORM prompt='Nic není vybráno'
     * FORM data-own=['1' > 'Neopraveno' | '2' > 'Závadné' | '3' > 'Prolídka v pořádku' | '4' > 'Opraveno']
     * FORM attribute-class="form-control selectpicker"
     *
     * GRID type='text'
     * GRID title="Výsledek"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše' | '1' > 'Neopraveno' | '3' > 'Závadné' | '3' > 'Prolídka v pořádku' | '4' > 'Opraveno']
     * GRID visible='true'
     * GRID inline-type='select'
     * GRID inline-prompt=' '
     * GRID inline-data-own=['1' > 'Neopraveno' | '2' > 'Závadné' | '3' > 'Prolídka v pořádku' | '4' > 'Opraveno']
     */
    protected $result;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka k servisu"
     * FORM attribute-placeholder=''
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Poznámka"
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