<?php

namespace ScrapyardIO\Waveforms\Motion;

use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\ContinuousServo as ContinuousServoCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class ContinuousServo extends PositionalServo
{
    public function __construct(ContinuousServoCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function clockwise(int $speed = 100): void
    {
        $this->continuousServo()->clockwise($speed);
    }

    public function counterClockwise(int $speed = 100): void
    {
        $this->continuousServo()->counterClockwise($speed);
    }

    public function cw(int $speed = 100): void
    {
        $this->continuousServo()->cw($speed);
    }

    public function ccw(int $speed = 100): void
    {
        $this->continuousServo()->ccw($speed);
    }

    public function stop(): void
    {
        $this->continuousServo()->stop();
    }

    public function deadband(int $lower, int $upper): static
    {
        $this->continuousServo()->deadband($lower, $upper);

        return $this;
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof ContinuousServoCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a ContinuousServo.");
    }

    private function continuousServo(): ContinuousServoCircuit
    {
        /** @var ContinuousServoCircuit */
        return $this->circuit;
    }
}
