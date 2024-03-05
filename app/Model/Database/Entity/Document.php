<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\DocumentRepository")
 * @ORM\Table(name="`document`")
 * @ORM\HasLifecycleCallbacks
 */
class Document extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * FORM type='textarea'
     * FORM title='Poznámka'
     *
     * GRID type='text'
     * GRID title="Poznámka"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Field")
     * FORM type='select'
     * FORM prompt='--vyberte'
     * FORM title='Obor'
     * FORM required='Obor je povinné pole!'
     * FORM data-entity=Field[name]
     *
     * GRID type='translate-text'
     * GRID title="Obor"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='Field'
     * GRID entity-alias='f'
     * GRID filter='single'
     */
    protected $field;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * GRID type='translate-text'
     * GRID title="Založil"
     * GRID entity-link='name'
     * GRID visible='true'
     * GRID entity='User'
     * GRID entity-alias='u'
     * GRID filter=multiselect-entity #[name]['id' > 'ASC']
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * FORM type='upload'
     * FORM title='Soubor'
     * FORM dir='_data/documents'
     *
     * GRID type='file'
     * GRID title='Soubor'
     */
    protected $document;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}