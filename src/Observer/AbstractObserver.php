<?php

namespace App\Observer;

use App\Entity\Error;
use App\Entity\Observer;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;


abstract class AbstractObserver
{
    protected $observerType;
    protected $domConfig;
    protected $priceMatchRegex;
    protected $observers;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;

    /**
     * @return mixed
     */
    public abstract function run();

    /**
     * @param string|null $domElements
     * @return float|null
     */
    public abstract function parseHtmlAndGetPrice(?string $domElements): ?float;

    /**
     * @param string $domConfig
     * @param string $observerType
     * @param string $priceMatchRegex
     */
    protected function loadDomConfiguration(string $domConfig, string $observerType, string $priceMatchRegex)
    {
        $this->domConfig = $domConfig;
        $this->observerType = $observerType;
        $this->priceMatchRegex = $priceMatchRegex;
        $this->crawler = new Crawler();
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function loadEntities(EntityManagerInterface $entityManager)
    {
        $this->observers = $entityManager
            ->getRepository(Observer::class)
            ->findBy(['type' => $this->observerType]);

        $this->em = $entityManager;
    }

    /**
     * @param Observer $observer
     * @param string $msg
     * @throws \Exception
     */
    protected function saveError(Observer $observer, string $msg = "")
    {
        $error = new Error();

        $error->setObserver($observer);
        $error->setCreatedAt(new \DateTime());
        $error->setMsg($msg);

        $this->em->persist($error);
        $this->em->flush();
    }

    protected function saveProduct(Observer $observer, float $price) {
        $product = new Product();
        $product->setObserver($observer);
        $product->setPrice($price);
        $product->setCreatedAt(new \DateTime());

        $this->em->persist($product);
        $this->em->flush();
    }
}
