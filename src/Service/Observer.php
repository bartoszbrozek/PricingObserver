<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class Observer
{
    public function createObservers(): array
    {
        $observers = $this->getObservers();

        $observerObjects = [];

        foreach ($observers as $observerName => $observer) {
            $instance = new $observer['class']();
            $instance->loadDomConfiguration($observer['domConfigs'], $observerName, $observer['priceMatchRegex']);
            $observerObjects[] = $instance;
        }

        return $observerObjects;
    }

    public function getObservers(): array
    {
        return Yaml::parseFile(dirname(__DIR__) . '/../config/observers.yaml');
    }

    public function getObserversForSelect(): array
    {
        $observers = $this->getObservers();

        $data = [];
        foreach ($observers as $name => $observer) {
            if ($observer['enabled'] && class_exists($observer['class'])) {
                $data[$name] = $observer['class'];
            }
        }

        $observers = array_keys($data);

        return array_combine($observers, $observers);
    }

    public function gatherData(array $observers)
    {
        foreach ($observers as $observer) {
            try {
                $observer->run();
            } catch (\Exception $ex) {

            }
        }
    }
}
