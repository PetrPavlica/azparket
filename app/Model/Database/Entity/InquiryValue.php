<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TUpdatedAt;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;

/**
 * @ORM\Entity(repositoryClass="App\Model\Database\Repository\InquiryValueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class InquiryValue extends AbstractEntity
{
    use TId;

    /**
     * @ORM\ManyToOne(targetEntity="Inquiry", inversedBy="values")
     */
    protected $inquiry;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     */
    protected $value;



    public function __construct($data = null)
    {
        parent::__construct($data);
    }
}