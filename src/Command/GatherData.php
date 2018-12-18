<?php

namespace App\Command;

use App\Service\EntityManager;
use App\Service\Observer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GatherData extends Command
{
    private $em;
    private $observer;

    public function __construct(EntityManager $em, Observer $observer)
    {
        $this->em = $em;
        $this->observer = $observer;
        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName("app:gatherdata")
            ->setDescription("Gathers data");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting GatherData.php!");

        $observers = $this->observer->createObservers();

        foreach ($observers as $observer) {
            try {
                $observer->loadEntities($this->em->getEm());
                $observer->run();
            } catch (\Exception $ex) {
                $output->writeln("Error: {$ex}");
            }

        }
    }
}