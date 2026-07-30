<?php

namespace ScrapyardIO\Waveforms\Motion;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\PositionalServo as PositionalServoCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class PositionalServo extends Actuator
{
    public function __construct(PositionalServoCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function to(int $degrees, int $ms = 0, int $rate = 0): void
    {
        $this->servo()->to($degrees, $ms, $rate);
    }

    public function pulse(?int $ns = null): int
    {
        return $this->servo()->pulse($ns);
    }

    public function calibrate(int $min, int $max, ?int $stop = null): static
    {
        $this->servo()->calibrate($min, $max, $stop);

        return $this;
    }

    public function center(int $ms = 0, int $rate = 0): void
    {
        $this->servo()->center($ms, $rate);
    }

    public function home(): void
    {
        $this->servo()->home();
    }

    public function min(): void
    {
        $this->servo()->min();
    }

    public function max(): void
    {
        $this->servo()->max();
    }

    /**
     * @param array{0?: int, 1?: int} $range
     */
    public function sweep(
        int $low = 0,
        int $high = 180,
        array $range = [],
        int $interval_of_half_sweep = 1000,
        int $step_of_each_degree = 10,
    ): void {
        $this->servo()->sweep($low, $high, $range, $interval_of_half_sweep, $step_of_each_degree);
    }

    public function getPosition(): int
    {
        return $this->servo()->getPosition();
    }

    public function enable(): void
    {
        $this->servo()->enable();
    }

    public function disable(): void
    {
        $this->servo()->disable();
    }

    public function enabled(): bool
    {
        return $this->servo()->enabled();
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof PositionalServoCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a PositionalServo.");
    }

    protected function servo(): PositionalServoCircuit
    {
        /** @var PositionalServoCircuit */
        return $this->circuit;
    }
}
