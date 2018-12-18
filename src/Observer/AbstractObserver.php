<?php

namespace App\Observer;

use App\Entity\Error;
use App\Entity\Observer;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;


abstract class AbstractObserver
{
    /**
     * @var string
     */
    protected $observerType;

    /**
     * @var array
     */
    protected $domConfigs;

    /**
     * @var string
     */
    protected $priceMatchRegex;

    /**
     * @var array
     */
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
     * @var bool Is search finished
     */
    protected $searchFinished = false;

    /**
     * @var int Divide price
     */
    protected $divider = 1;

    /**
     * This method can be overridden for any needs
     *
     * @throws \Exception
     */
    protected function run() {
        foreach ($this->observers as $observer) {
            $this->searchFinished = false;

            dump("Starting: ".$observer->getName());

            try {
                $this->crawler->clear();
                $content = file_get_contents($observer->getAddress());

                foreach ($this->domConfigs as $domConfig) {
                    if ($this->searchFinished) {
                        dump("Search finished: ".$observer->getName());
                        break;
                    }

                    $config = is_array($domConfig) ? array_key_first($domConfig) : $domConfig;
                    $this->divider = is_array($domConfig) ? (int)$domConfig[$config][0] : 1;

                    $this->crawler->clear();
                    $this->crawler->addHtmlContent($content);

                    $domElements = $this->crawler->filter($config)->first();

                    if (!is_countable($domElements) || !is_object($domElements) || count($domElements) <= 0) {
                        $this->saveError($observer, "DOM elements are not countable. DOM Config used: " . $domConfig);
                        continue;
                    }

                    foreach ($domElements as $domElement) {
                        try {
                            $price = $this->parseHtmlAndGetPrice($domElement->textContent);

                            if ($price === null || empty($price)) {
                                throw new \Exception("Could not get price. DOM Config used: " . $domConfig);
                            }

                            $this->saveProduct($observer, $price);
                            break;
                        } catch (\Exception $ex) {
                            $this->saveError($observer, $ex->getMessage());
                        }
                    }
                }
            } catch (\Exception $ex) {
                $this->saveError($observer, $ex->getMessage());
            }
        }
    }

    /**
     * This method can be overridden for any needs
     *
     * @param string|null $html
     * @return float|null
     * @throws \Exception
     */
    protected function parseHtmlAndGetPrice(?string $html): ?float {
        if (empty($html)) {
            return null;
        }

        $html = str_replace(" ", "", $html);

        $matches = [];
        preg_match_all($this->priceMatchRegex, str_replace(",", ".", $html), $matches);

        if (empty($matches[0][0])) {
            throw new \Exception("Could not get price. Matches: " . json_encode($matches));
        }

        $price = "";

        foreach ($matches[0] as $partOfPrice) {
            $price .= $partOfPrice;
        }

        return ((float)$price) / $this->divider;
    }

    /**
     * @param array $domConfigs
     * @param string $observerType
     * @param string $priceMatchRegex
     */
    public function loadDomConfiguration(array $domConfigs, string $observerType, string $priceMatchRegex)
    {
        $this->domConfigs = $domConfigs;
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

        $this->searchFinished = true;
    }
}
