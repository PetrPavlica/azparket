<?php


namespace App\Model\Database\Entity\Attributes;


trait TCustomInvoice
{
    /**
     * @var \DateTime|NULL
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    protected $deletedAt;
}