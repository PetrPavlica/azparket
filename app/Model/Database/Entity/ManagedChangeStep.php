<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ManagedChangeStepRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ManagedChangeStep extends AbstractEntity
{
    use TId;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Činnost řízení realizace"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Činnost řízení realizace"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $implementationManagement;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-class='md-textarea form-control'
     * FORM section=item
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Odpovídá"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Odpovídá"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $responsible;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Termín do"
     * FORM attribute-class="form-control"
     *
     * GRID type='date'
     * GRID title="Termín do"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $deadline;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="date", nullable=true)
     * FORM type='date'
     * FORM title="Datum splnění"
     * FORM attribute-class="form-control"
     *
     * GRID type='date'
     * GRID title="Datum splnění"
     * GRID sortable='true'
     * GRID filter='date'
     * GRID visible='true'
     */
    protected $completionDate;

    /**
     * @ORM\ManyToOne(targetEntity="ManagedChange")
     */
    protected $managedChange;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}