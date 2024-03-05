<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\OperationLogSuggestionRepository")
 * @ORM\Table(name="`operation_log_suggestion`")
 * @ORM\HasLifecycleCallbacks
 */
class OperationLogSuggestion extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     * FORM type='text'
     * FORM title="Text připomínky"
     * FORM attribute-class="form-control"
     *
     * GRID type='text'
     * GRID title="Text připomínky"
     * GRID visible='true'
     * GRID filter='single'
     * GRID sortable='true'
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="OperationLog", inversedBy="logSuggestions")
     */
    protected $operationLog;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}