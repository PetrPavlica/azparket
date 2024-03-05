<?php

namespace App\Model\Facade;

use App\Model\Database\EntityManager;
use Nette\Caching\Cache;
use Nette\Database\Context;

class Translation
{
    /** @var EntityManager */
    private EntityManager $em;

    /** @var Context */
    protected $db;

    public function __construct(EntityManager $em, Context $db)
    {
        $this->em = $em;
        $this->db = $db;
    }


    public function getMessages($locale)
    {
        return $this->db->queryArgs('
            SELECT t.key_m, t.message
            FROM translations t
            LEFT JOIN language l ON l.id = t.lang_id
            WHERE l.code = ?', [$locale])->fetchAll();
    }
}