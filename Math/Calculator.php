<?php

namespace PhpConsole\Math;

use PhpConsole\Command\AbstractCommand;

/**
 * Class Calculator
 * @title   PhpConsole Calculator Assignment
 * @version v1.01
 * @usage   ./Calculate.php <method> [options...]
 */
class Calculator extends AbstractCommand
{

    /**
     * The sum of all natural numbers below -max that are multiples of divisors
     *      ex ./Calculate.php multiplesSum -divisors=[3,4] -max=1000
     *
     * @param array $divisors Array of natural numbers
     * @param int $max        The maximum number that a multiple cannot reach
     * @return int|null
     */
    final public function multiplesSum(array $divisors, $max)
    {
        if ($max <= 1) {
            $this->error('Parameter -max should be greater than 1');
            return null;
        }
        foreach ($divisors as $divisor) {
            if ($divisor < 1 || $divisor >= $max) {
                $this->error('Numbers in -divisors should be between 1 and ' . --$max);
                return null;
            }
        }
        $sum = 0;
        for ($i = 0; $i < $max; $i++) {
            foreach ($divisors as $divisor) {
                if (0 == $i % $divisor) {
                    $sum += $i;
                    break;
                }
            }
        }
        return $sum;
    }

    /**
     * The power of x to index y
     *      ex ./Calculate.php power -x=2 -y=2
     *
     * @param int $x The base to use
     * @param int $y The exponent
     * @return int|void
     */
    final public function power($x, $y)
    {
//        return pow($x,$y);
        if ($x < 0 || $y < 0) {
            $this->error('Parameters -x -y should be natural numbers');
            return null;
        }
        if ($y == 0) {
            return 1;
        }
        $power = $increment = $x;
        for ($i = 1; $i < $y; $i++) {
            for ($j = 1; $j < $x; $j++) {
                $power += $increment;
            }
            $increment = $power;
        }
        return $power;
    }

    /**
     * Calculate and print -max numbers for fibonacci series. Use recursion.
     *      ex ./Calculate.php fibonacciRecursion -firstNumber=0  -secondNumber=1 -max=10
     *
     * @param int $firstNumber  Start number
     * @param int $secondNumber Second number
     * @param int $max          Max numbers to print
     * @param array $series     fibonacci series
     * @return int|string
     */
    final public function fibonacciRecursion($firstNumber = 0, $secondNumber = 1, $max = 10, $series = [])
    {
        if (!isset($series[0])) {
            $series[] = $firstNumber;
            $series[] = $secondNumber;
        }
        $max--;
        if ($max == 1) {
            return 'found sum ' . array_sum($series) . ' for ' . count($series) . ' numbers ' . json_encode($series);
        }
        $newNumber = $firstNumber + $secondNumber;
        $series[] = $newNumber;
        return $this->fibonacciRecursion($secondNumber, $newNumber, $max, $series);
    }

    /**
     * Calculate and print -max numbers for fibonacci series without recursion.
     *      ex ./Calculate.php fibonacciWithoutRecursion -firstNumber=0  -secondNumber=1 -max=10
     *
     * @param int $firstNumber  Start number
     * @param int $secondNumber Second number
     * @param int $max          Max numbers to print
     * @return int|string
     */
    final public function fibonacciWithoutRecursion($firstNumber = 1, $secondNumber = 1, $max = 10)
    {
        $series = [$firstNumber, $secondNumber];
        for ($i = 2; $i < $max; $i++) {
            $series[$i] = $series[$i - 2] + $series[$i - 1];
        }
        return 'found sum ' . array_sum($series) . ' for ' . count($series) . ' numbers ' . json_encode($series);
    }


    protected function displayHelp()
    {
        parent::displayHelp();
        $assignment = 'Assignment: ' . PHP_EOL;
        $assignment .= 'A: The sum of all natural numbers below 10 that are multiples of 3 or 5 are 23 (3 + 5 + 6 + 9):' . PHP_EOL;
        $assignment .= './Calculate.php multiplesSum -divisors=[3,5] -max=10' . PHP_EOL;
        $assignment .= 'Result:' . $this->multiplesSum([3, 5], 10) . PHP_EOL . PHP_EOL;
        $assignment .= 'A Extra: Create a second algorithm to find the sum of all the multiples of 3 or 5 below 1000:' . PHP_EOL;
        $assignment .= './Calculate.php multiplesSum -divisors=[3,5] -max=1000' . PHP_EOL;
        $assignment .= 'Result:' . $this->multiplesSum([3, 5], 1000) . PHP_EOL . PHP_EOL;
        $assignment .= 'A Extra: Create a second algorithm to find the sum of all the multiples of 3 or 4 below 1000:' . PHP_EOL;
        $assignment .= './Calculate.php multiplesSum -divisors=[3,4] -max=1000' . PHP_EOL;
        $assignment .= 'Result:' . $this->multiplesSum([3, 4], 1000) . PHP_EOL . PHP_EOL;
        $assignment .= 'B: Calculate x^y:' . PHP_EOL;
        $assignment .= './Calculate.php power -x=2 -y=2' . PHP_EOL;
        $assignment .= 'Result:' . $this->power(2, 2) . PHP_EOL . PHP_EOL;
        $assignment .= 'C: Fibonacci:' . PHP_EOL;
        $assignment .= './Calculate.php fibonacciRecursion -firstNumber=0  -secondNumber=1 -max=10' . PHP_EOL;
        $assignment .= 'Result:' . $this->fibonacciRecursion(0, 1, 10) . PHP_EOL . PHP_EOL;
        $assignment .= 'C Extra:' . PHP_EOL;
        $assignment .= './Calculate.php fibonacciWithoutRecursion -firstNumber=0  -secondNumber=1 -max=10' . PHP_EOL;
        $assignment .= 'Result:' . $this->fibonacciWithoutRecursion(0, 1, 10) . PHP_EOL;
        echo $this->stringColor($assignment, self::COLOR_WARNING, true) . PHP_EOL;

    }
}