<?php

namespace App\Observer;

class Komputronik extends AbstractObserver
{
    /**
     * @throws \Exception
     */
    public function run()
    {
        parent::run();
    }

    /**
     * @param string|null $html
     * @return float|null
     * @throws \Exception
     */
    public function parseHtmlAndGetPrice(?string $html): ?float
    {
        return parent::parseHtmlAndGetPrice($html);
    }
}
