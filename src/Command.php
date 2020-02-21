<?php

declare(strict_types = 1);

namespace App;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Command extends SymfonyCommand
{
    /**
     * @var string
     */
    protected static $defaultName = 'destination-calculator';

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("Hi! Let's find the average destination and the worst direction :)\n");
        $helper = $this->getHelper('question');

        $numberOfPeople = (int) $helper->ask($input, $output, new Question("Please enter the number of people\n"));

        $output->writeln("Please enter the routes that you gave from each of $numberOfPeople people");
        $routes = [];
        for ($i = 0; $i < $numberOfPeople; $i++) {
            $personNumber = $i + 1;
            $output->writeln("\nThe route of the person $personNumber");

            $route = [];
            $x = $helper->ask($input, $output, new Question("X coordinate of the location when you meet the person\n"));
            $y = $helper->ask($input, $output, new Question("Y coordinate of the location when you meet the person\n"));
            if ($x == 'q') {
                break;
            }
            $route['location'] = [$x, $y];

            $output->writeln("Enter the directions");
            $output->writeln("When these directions end, enter 'q' as the answer to the question about next turn");

            $isFirst = true;
            $directions = [];
            do {
                if (!$isFirst) {
                    $output->writeln("\nNext direction");
                }

                $direction = [];
                if ($isFirst) {
                    $direction['start'] = $helper->ask($input, $output, new Question("start - the initial direction you are facing in degrees (east is 0 degrees, north is 90 degrees)\n"));
                } else {
                    $direction['turn'] = $helper->ask($input, $output, new Question("turn - n angle in degrees you should turn. A positive α indicates to turn to the left\n"));
                    if ($direction['turn'] == 'q') {
                        break;
                    }
                }

                $direction['walk'] = $helper->ask($input, $output, new Question("walk - a number of units to walk\n"));
                $route['directions'][] = $direction;
                $isFirst = false;
            } while (true);

            $routes[] = $route;
        }

        $output->writeln("\nThe result of calculation:");
        $results = (new DestinationCalculator(4))->calculate($numberOfPeople, $routes);
        foreach ($results as $caption => $result) {
            $result = is_array($result) ? implode(' ', $result) : $result;
            $output->writeln("$caption: $result");
        }
        $output->writeln('');

        return 0;
    }
}
