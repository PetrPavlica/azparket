<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ItemTypeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ItemType extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název typu'
     * FORM required='Název je povinné pole!'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Název"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="ItemTypeInItem", mappedBy="type")
     */
    protected $items;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}