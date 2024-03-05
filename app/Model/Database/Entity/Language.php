<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\LanguageRepository")
 * @ORM\Table(name="`language`")
 */
class Language extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Název"
     * FORM attribute-placeholder='Název'
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
     * @ORM\Column(type="string", nullable=true)
     * FORM type='text'
     * FORM title="Kód"
     * FORM attribute-placeholder='Kód'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='text'
     * GRID title="Kód"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $code;

    /**
     * @ORM\Column(type="integer")
     * FORM type='integer'
     * FORM title="Pořadí"
     * FORM attribute-placeholder='Pořadí'
     * FORM required="Toto je je povinné pole!"
     * FORM rule-integer='Prosím zadávejte pouze čísla'
     * FORM attribute-class='form-control input-md'
     *
     * GRID type='integer'
     * GRID title="Pořadí"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $orderCode;

    /**
     * @ORM\Column(type="boolean")
     * FORM type='checkbox'
     * FORM title="Základní"
     *
     * GRID type='bool'
     * GRID title="Základní"
     * GRID sortable='true'
     * GRID filter=select #['' > 'Vše'|'0' > 'Ne'|'1' > 'Ano']
     * GRID visible='true'
     * GRID align='center'
     */
    protected $defaultCode;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

}

?>