<?php

use PHPUnit\Framework\TestCase;
use App\DestinationCalculator;

class DestinationCalculatorTest extends TestCase
{
    /**
     * @dataProvider calculateProvider
     *
     * @param int   $numberOfPeople
     * @param array $routes
     * @param array $expectedResults
     */
    public function testCalculate(int $numberOfPeople, array $routes, array $expectedResults)
    {
        $calculator = new DestinationCalculator(4);
        $this->assertEquals($expectedResults, $calculator->calculate($numberOfPeople, $routes));
    }

    /**
     * @return array
     */
    public function calculateProvider(): array
    {
        return [
            [
                3,
                // routes
                [
                    [
                        'location'   => [87.342, 34.30],
                        'directions' => [
                            ['start' => 0, 'walk' => 10.0],
                        ],
                    ],
                    [
                        'location'   => [2.6762, 75.2811],
                        'directions' => [
                            ['start' => -45.0, 'walk' => 40],
                            ['turn'  => 40.0,  'walk' => 60],
                        ],
                    ],
                    [
                        'location'   => [58.518, 93.508],
                        'directions' => [
                            ['start' => 270, 'walk' => 50],
                            ['turn'  => 90,  'walk' => 40],
                            ['turn'  => 13,  'walk' => 5],
                        ],
                    ],
                ],
                // expected results
                [
                    'average destination'        => [97.1547, 40.2334],
                    'worst destination distance' => 7.63097,
                ],
            ],
            [
                2,
                // routes
                [
                    [
                        'location'   => [30, 40],
                        'directions' => [
                            ['start' => 90, 'walk' => 5],
                        ],
                    ],
                    [
                        'location'   => [40, 50],
                        'directions' => [
                            ['start' => 180, 'walk' => 10],
                            ['turn'  => 90,  'walk' => 5],
                        ],
                    ],
                ],
                // expected results
                [
                    'average destination'        => [30, 45],
                    'worst destination distance' => 0,
                ],
            ],
        ];
    }
}
