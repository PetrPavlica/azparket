<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\DeliveryPriceRepository")
 * @ORM\HasLifecycleCallbacks
 */
class DeliveryPrice extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Min. vzdálenost"
     * FORM attribute-placeholder='Min. vzdálenost'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze celá čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Min. vzdálenost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $minDist;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Max. vzdálenost"
     * FORM attribute-placeholder='Max. vzdálenost'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze celá čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Max. vzdálenost"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $maxDist;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='float'
     * FORM title="Cena"
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Cena"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $price;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     * FORM type='checkbox'
     * FORM title="Paušál"
     * FORM default-value=0
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Paušál"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID replacement=select #['0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     */
    protected $flat;

    public function __construct($data = null)
    {
        parent::__construct($data);
        $this->flat = 0;
    }
}