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
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\CurrencyRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Currency extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód"
     * FORM attribute-placeholder='Kód'
     * FORM required="Kód je povinné pole!"
     * FORM rule-length="Délka kódu musí být %d znaky" #[3]
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název'
     * FORM required="Jméno a příjmení je povinné pole!"
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
     * FORM title="Pořadí (priorita)"
     * FORM attribute-placeholder='Pořadí'
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     *
     * GRID type='integer'
     * GRID title="Pořadí (priorita)"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderCurrency;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Měnový kurz oproti CZK"
     * FORM attribute-placeholder='Kurz'
     * FORM rule-float ='Prosím zadávejte desetinné číslo'
     * FORM required="Měnový kurz je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Měnový kurz"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $exchangeRate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * FORM type='text'
     * FORM title="Počet desetinných míst bez zaokrouhlení"
     * FORM attribute-placeholder='Počet desetinných míst'
     * FORM rule-number ='Prosím zadávejte desetinné číslo'
     * FORM required="Počet desetinných míst je povinné pole!"
     *
     * GRID type='text'
     * GRID title="Počet desetinných míst"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='false'
     */
    protected $countDecimal;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení před"
     * FORM attribute-placeholder='Označení před'
     *
     * GRID type='text'
     * GRID title="Označení před"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $markBefore;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Označení za"
     * FORM attribute-placeholder='Označení za'
     *
     * GRID type='text'
     * GRID title="Označení za"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $markBehind;

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