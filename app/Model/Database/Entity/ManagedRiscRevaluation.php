<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ManagedRiscRevaluationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ManagedRiscRevaluation extends AbstractEntity
{
    use TId;
    use TUpdatedAt;

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum přehodnocení"
     *
     * GRID type='date'
     * GRID title="Datum přehodnocení"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $revaluationDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pravděpodonost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Pravděpodonost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $probability;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Závažnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Závažnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $relevance;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Odhalitelnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Odhalitelnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $detectability;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Přínos"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Přínos"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $benefit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Realizovatelnost"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Realizovatelnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $feasibility;

    
    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Odpovědnost"
     *
     * GRID type='text'
     * GRID title="Odpovědnost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $revalRespond;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Stav realizace"
     *
     * GRID type='text'
     * GRID title="Stav realizace"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $realizationState;

    /**
     * @ORM\ManyToOne(targetEntity="ManagedRisc", inversedBy="revaluations")
     */
    protected $managedRisc;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}