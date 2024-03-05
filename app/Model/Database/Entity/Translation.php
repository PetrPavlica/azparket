<?php

namespace App\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TUpdatedAt;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\TranslationRepository")
 * @ORM\Table(name="translation", uniqueConstraints={@ORM\UniqueConstraint(name="unique_idx", columns={"key_m", "lang_id"})})
 */
class Translation extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string")
     * GRID type='text'
     * GRID title="Klíč"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     * GRID filter='single'
     */
    protected $keyM;

    /**
     * @ORM\ManyToOne(targetEntity="Language")
     * GRID type='text'
     * GRID title="Jazyk"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID sortable='true'
     * GRID entity='Language'
     * GRID entity-alias='l'
     * GRID filter=select-entity #[name]
     */
    protected $lang;

    /**
     * @ORM\Column(type="text", nullable=true)
     * GRID type='text'
     * GRID title="Přeložený text"
     * GRID sortable='true'
     * GRID visible='true'
     * GRID align='left'
     * GRID filter='single'
     */
    protected $message;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}