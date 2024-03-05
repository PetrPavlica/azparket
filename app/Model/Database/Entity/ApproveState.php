<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApproveStateRepository")
 * @ORM\Table(name="`approve_state`")
 */
class ApproveState extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-class='form-control input-md'
     * FORM attribute-placeholder="Název"
     * FORM required="Název je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="integer", name="`order`", nullable=true)
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-class='form-control input-md'
     * FORM required="Pořadí je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $order;

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