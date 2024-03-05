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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VatRepository")
 * @ORM\Table(name="`vat`")
 * @ORM\HasLifecycleCallbacks
 */
class Vat extends AbstractEntity
{
    use TId;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Označení"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Výše DPH"
     * FORM rule-float='Prosím zadávejte pouze čísla'
     * FORM rule-min='Zadejte číslo větší nebo rovno 0'#[0]
     *
     * GRID type='text'
     * GRID title="Výše DPH"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $value;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}