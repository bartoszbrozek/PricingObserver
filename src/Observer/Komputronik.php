<?php

namespace App\Observer;

use App\Entity\Product;

class Komputronik extends AbstractObserver
{
    public function __construct(string $configuration, string $observerName, string $priceMatchRegex)
    {
        $this->loadDomConfiguration($configuration, $observerName, $priceMatchRegex);
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        foreach ($this->observers as $observer) {
            $this->crawler->clear();
            $this->crawler->addHtmlContent(file_get_contents($observer->getAddress()));

            if (!\is_countable($this->crawler->filter($this->domConfig)->first())) {
                $this->saveError($observer, "DOM elements are not countable");
                continue;
            }

            foreach ($this->crawler->filter($this->domConfig)->first() as $domElement) {
                try {
                    $price = $this->parseHtmlAndGetPrice($domElement->textContent);

                    if ($price === null) {
                        throw new \Exception("Could not get price");
                    }

                    $this->saveProduct($observer, $price);
                } catch (\Exception $ex) {
                    $this->saveError($observer, $ex->getMessage());
                }
            }
        }
    }

    /**
     * @param string|null $html
     * @return float|null
     * @throws \Exception
     */
    public function parseHtmlAndGetPrice(?string $html): ?float
    {
        if (empty($html)) {
            return null;
        }

        $html = str_replace(" ", "", $html);

        $matches = [];
        preg_match_all($this->priceMatchRegex, str_replace(",", ".", $html), $matches);

        if (empty($matches[0][0])) {
            throw new \Exception("Could not get price. Matches: ".json_encode($matches));
        }

        $price = "";

        foreach ($matches[0] as $partOfPrice) {
            $price .= $partOfPrice;
        }

        return (float)$price;
    }
}
