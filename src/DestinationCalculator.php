<?php

declare(strict_types = 1);

namespace App;

class DestinationCalculator
{
    /**
     * @var int
     */
    private int $scale;

    /**
     * @param int $scale
     */
    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    /**
     * @param int   $numberOfPeople
     * @param array $routes
     *
     * @return array
     */
    public function calculate(int $numberOfPeople, array $routes): array
    {
        if ($numberOfPeople <= 0) {
            throw new \InvalidArgumentException('The number of people must be greater than 0');
        }
        if (count($routes) == 0) {
            throw new \InvalidArgumentException('The number of routes must be at least 1');
        }

        $destinations       = $this->calculateDestinations($routes);
        $averageDestination = $this->calculateAverageDestination($numberOfPeople, $destinations);

        return [
            'average destination'        => [
                round($averageDestination[0], $this->scale),
                round($averageDestination[1], $this->scale),
            ],
            'worst destination distance' => $this->calculateWorstDistance($averageDestination, $destinations),
        ];
    }

    /**
     * @param array $routes
     *
     * @return array
     */
    private function calculateDestinations(array $routes): array
    {
        $destinations = [];
        foreach ($routes as $route) {
            $destinations[] = $this->calculateDestination($route);
        }

        return $destinations;
    }

    /**
     * @param array $route
     *
     * @return array
     */
    private function calculateDestination(array $route): array
    {
        if (empty($route['directions']) || !is_array($route['directions'])) {
            throw new \InvalidArgumentException('Each route must contain at least 1 direction and it must be an array');
        }

        list($x, $y) = $this->getCoordinates($route);
        $previousAngle = 0;
        $isFirst = true;
        foreach ($route['directions'] as $direction) {
            if (empty($direction['walk']) || !is_numeric($direction['walk'])) {
                throw new \InvalidArgumentException(
                    "Each direction must contain 'walk' element, which is a number of units to walk. " .
                    "A positive α indicates to turn to the left."
                );
            }
            $walk = (float) $direction['walk'];

            $angle = $this->getDirectionAngle($direction, $isFirst) + $previousAngle;
            $previousAngle = $angle;
            $angle = deg2rad($angle < 0 ? 360 + $angle : $angle);

            $x += $walk * cos($angle);
            $y += $walk * sin($angle);

            $isFirst = false;
        }

        return [$x, $y];
    }

    /**
     * @param array $route
     *
     * @return array
     */
    private function getCoordinates(array $route): array
    {
        if (empty($route['location']) || !is_array($route['location']) || count($route['location']) != 2) {
            throw new \InvalidArgumentException(
                "Each route must contain an array element 'location' with 2 elements: x and y coordinates"
            );
        }

        list($x, $y) = $route['location'];
        if (!is_numeric($x) || !is_numeric($y)) {
            throw new \InvalidArgumentException('The coordinates must be numeric values');
        }

        return [(float) $x, (float) $y];
    }

    /**
     * @param array $direction
     * @param bool  $isFirstDirection
     *
     * @return float
     */
    private function getDirectionAngle(array $direction, bool $isFirstDirection): float
    {
        if ($isFirstDirection && (!isset($direction['start']) || !is_numeric($direction['start']))) {
            throw new \InvalidArgumentException(
                "The first direction must contain 'start' element, " .
                "which is an angle of the initial direction in degrees"
            );
        } elseif (!$isFirstDirection && (!isset($direction['turn']) || !is_numeric($direction['turn']))) {
            throw new \InvalidArgumentException(
                "The second and subsequent directions must contain 'turn' element, " .
                "which is an angle of turn in degrees"
            );
        }

        return (float) ($isFirstDirection ? $direction['start'] : $direction['turn']);
    }

    /**
     * @param int   $numberOfPeople
     * @param array $destinations
     *
     * @return array
     */
    private function calculateAverageDestination(int $numberOfPeople, array $destinations): array
    {
        return [
            array_sum(array_column($destinations, 0)) / $numberOfPeople,
            array_sum(array_column($destinations, 1)) / $numberOfPeople,
        ];
    }

    /**
     * @param array $averageDestination
     * @param array $destinations
     *
     * @return float
     */
    private function calculateWorstDistance(array $averageDestination, array $destinations): float
    {
        list($aX, $aY) = $averageDestination;
        $worstDistance = 0;
        foreach ($destinations as $destination) {
            list($dX, $dY) = $destination;
            $distance = sqrt(pow($aX - $dX, 2) + pow($aY - $dY, 2));
            if ($distance > $worstDistance) {
                $worstDistance = $distance;
            }
        }

        return round($worstDistance, 5);
    }
}
