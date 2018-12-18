<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class EntityManager
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getEm()
    {
        return $this->em;
    }
}
