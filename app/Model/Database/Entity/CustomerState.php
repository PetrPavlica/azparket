<?php

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\CustomerStateRepository")
 * @ORM\Table(name="`customer_state`")
 * @ORM\HasLifecycleCallbacks
 */
class CustomerState extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder="Název"
     * FORM required="Název je povinné pole!"
     * FORM order=1
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-class='form-control input-md'
     * FORM required="Pořadí je povinné pole!"
     * FORM order=2
     *
     * GRID type='text'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $stateOrder;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Viditelný"
     * FORM default-value='true'
     * FORM order=4
     *
     * GRID type='bool'
     * GRID title="Viditelný"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Skrytý'|'1' > 'Viditelný']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $visible;

    /**
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title="Poznámka"
     * FORM attribute-placeholder='Volitelná poznámka'
     * FORM attribute-class='form-control input-md'
     * FORM order=3
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $description;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}