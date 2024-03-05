<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\CustomerNotificationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CustomerNotification extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="notifications")
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
     */
    protected $processState;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Aktivní"
     * FORM default-value='true'
     *
     * GRID type='bool'
     * GRID title="Aktivní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $active;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}