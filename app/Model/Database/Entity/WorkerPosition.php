<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\WorkerPositionRepository")
 * @ORM\Table(name="`worker_position`")
 * @ORM\HasLifecycleCallbacks
 */
class WorkerPosition extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * FORM type='text'
     * FORM title="Název pozice"
     * FORM attribute-class="form-control"
     * FORM required="Název pozice je povinné pole"
     *
     * GRID type='text'
     * GRID title="Název pozice"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Zkratka pozice"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Zkratka pozice"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $short;

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
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="WorkerPositionSuperiority", mappedBy="subordinatePosition")
     * FORM type='multiselect'
     * FORM title="Přímé nadřazené pozice"
     * FORM attribute-multiple='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search='true'
     * FORM data-entity=WorkerPosition[name][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerPositionSuperiority[subordinatePosition][superiorPosition]
     * 
     * GRID type='multi-text'
     * GRID title="Přímé nadřazené pozice"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=superiorPosition
     * GRID entity-link=name
     */
    protected $superiorPositions;

    /**
     * @ORM\OneToMany(targetEntity="WorkerPositionSuperiority", mappedBy="superiorPosition")
     * FORM type='multiselect'
     * FORM title="Přímé podřazené pozice"
     * FORM attribute-multiple='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM attribute-data-live-search='true'
     * FORM data-entity=WorkerPosition[name][]['name' => 'ASC']
     * FORM multiselect-entity=WorkerPositionSuperiority[superiorPosition][subordinatePosition]
     * 
     * GRID type='multi-text'
     * GRID title="Přímé podřazené pozice"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=subordinatePosition
     * GRID entity-link=name
     */
    protected $subordinatePositions;

    /**
     * @ORM\OneToMany(targetEntity="SkillInWorkerPosition", mappedBy="position")
     * FORM type='multiselect'
     * FORM title="Dovednosti pracovní pozice"
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=Skill[name][]['name' => 'ASC']
     * FORM multiselect-entity=SkillInWorkerPosition[position][skill]
     * 
     * GRID type='multi-text'
     * GRID title="Dovednosti pracovní pozice"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=skill
     * GRID entity-link=name
     */
    protected $skills;

    /**
     * @ORM\OneToMany(targetEntity="WorkerPositionInWorkplace", mappedBy="position")
     * FORM type='multiselect'
     * FORM title="Pracoviště této pozice"
     * FORM attribute-multiple='true'
     * FORM attribute-data-live-search='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM multiselect-entity=WorkerPositionInWorkplace[position][workplace]
     * FORM data-entity-values=Workplace[$name$][]['name' => 'ASC']
     * 
     * GRID type='multi-text'
     * GRID title="Pracoviště"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=workplace
     * GRID entity-link=name
     */
    protected $workplace;

    /**
     * @ORM\OneToMany(targetEntity="Worker", mappedBy="workerPosition")
     * 
     * GRID type='text'
     * GRID title="Zaměstnanci v této pozici"
     * GRID visible='true'
     * GRID entity='Worker'
     * GRID entity-link=name
     * GRID filter=single #['name']
     */
    protected $workers;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}