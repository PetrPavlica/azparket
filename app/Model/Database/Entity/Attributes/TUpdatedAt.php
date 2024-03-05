<?php declare(strict_types = 1);

namespace App\Model\Database\Entity\Attributes;

use Doctrine\ORM\Mapping as ORM;

trait TUpdatedAt
{

    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=TRUE)
     *
     * GRID type='datetime'
     * GRID title="Aktualizováno"
     * GRID sortable='true'
     * GRID filter='date-range'
     * GRID visible='false'
     */
    protected $updatedAt;

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Doctrine annotation
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @internal
     */
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

}