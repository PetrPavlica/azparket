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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\MaterialNeedBuyRepository")
 * @ORM\Table(name="`material_need_buy`")
 * @ORM\HasLifecycleCallbacks
 */
class MaterialNeedBuy extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název materiálu"
     *
     * GRID type='text'
     * GRID title="Název materiálu"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Je objednán?"
     * FORM default-value='false'
     *
     * GRsID type='bool'
     * GRsID title="Je objednán?"
     * GRsID sortable='true'
     * GRsID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRsID visible='true'
     * GRsID align='center'
     */
    protected $isBuy;

    /**
     * @ORM\ManyToOne(targetEntity="Visit")
     *
     * GRID type='translate-text'
     * GRID title="Výjezd"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Visit'
     * GRID entity-alias='vis'
     * GRID filter='single'
     */
    protected $visit;

    /**
     * @ORM\ManyToOne(targetEntity="Material")
     *
     * GRID type='link'
     * GRID title="Materiál"
     * GRID link-target="Material:edit"
     * GRID link-params=['id' > 'material.id']
     * GRID visible='true'
     * GRID filter=single-entity #['name']
     * GRID entity-alias='mat'
     * GRID entity-link=name
     * GRID entity='Material'
     */
    protected $material;

    public function __construct($data = null)
    {
        $this->isBuy = false;
        parent::__construct($data);
    }

}