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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ItemTypeInItemRepository")
 * @ORM\Table(name="`item_type_in_item`")
 */
class ItemTypeInItem extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="ItemType", inversedBy="items")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="ItemInProcess", inversedBy="types")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $item;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}