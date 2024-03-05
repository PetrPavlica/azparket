<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\DateTime;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ItemInProcessRepository")
 * @ORM\Table(name="`item_in_process`")
 */
class ItemInProcess extends AbstractEntity
{
    use TId;

    /**
     * @ORM\Column(type="string", nullable=false)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název položky'
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
     * @ORM\OneToMany(targetEntity="ItemTypeInItem", mappedBy="item")
     * FORM type='multiselect'
     * FORM title="Typy položky"
     * FORM attribute-placeholder='Typy položky'
     * FORM attribute-multiple='true'
     * FORM attribute-class='form-control selectpicker'
     * FORM data-entity=ItemType[name][]['name' => 'ASC']
     * FORM multiselect-entity=ItemTypeInItem[item][type]
     *
     * GRID type='multi-text'
     * GRID title="Typy položky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID entity-join-column=type
     * GRID entity-link=name
     */
    protected $types;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='textarea'
     * FORM title="Popis"
     * FORM attribute-placeholder='Popis'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Popis"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Process", inversedBy="items")
     */
    protected $process;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}