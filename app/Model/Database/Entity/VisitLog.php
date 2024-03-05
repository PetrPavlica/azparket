<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\VisitLogRepository")
 * @ORM\Table(name="`visit_log`")
 * @ORM\HasLifecycleCallbacks
 */
class VisitLog extends AbstractEntity
{
    use TId;
    use TCreatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Visit", inversedBy="visitLog")
     * 
     * GRID type='link'
     * GRID title="Výjezd"
     * GRID link-target="Visit:edit"
     * GRID link-params=['id' > 'visit.id']
     * GRID visible='true'
     * GRID filter=single-entity #['name']
     * GRID entity-alias='vis'
     * GRID entity-link=name
     * GRID entity='Visit'
     */
    protected $visit;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * GRID type='text'
     * GRID title="Změnil"
     * GRID visible='true'
     * GRID filter=single-entity #['name']
     * GRID entity-alias='usrch'
     * GRID entity-link=name
     * GRID entity='User'
     */
    protected $user;

    /**
     * @var DateTime|NULL
     * @ORM\Column(type="datetime", nullable=true)
     *
     * GRID type='datetime'
     * GRID title="Datum a čas"
     * GRID format-time="d. m. Y H:i:s"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='true'
     * GRIiD inline-type='date'
     */
    protected $foundedDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * GRID type='text'
     * GRID title="Změny"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $text;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * GRID type='text'
     * GRID title="Starý"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $oldText;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * GRID type='text'
     * GRID title="Nový"
     * GRID sortable='true'
     * GRID filter='single'
     * GRID visible='true'
     */
    protected $newText;

    public function __construct($data = null)
    {
        $this->foundedDate = new DateTime();
        parent::__construct($data);
    }
}