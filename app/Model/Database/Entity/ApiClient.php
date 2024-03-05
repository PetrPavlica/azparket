<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\ApiClientRepository")
 * @ORM\Table(name="`api_client`")
 * @ORM\HasLifecycleCallbacks
 */
class ApiClient extends AbstractEntity
{
    use TId;
    use TCreatedAt;
    use TUpdatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    protected $token;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    public function __construct($data = null)
    {
        $this->active = true;
        parent::__construct($data);
    }
}