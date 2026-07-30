<?php

namespace ScrapyardIO\Waveforms\Motion;

use Fabricate\Actuation\Actuator;
use Fabricate\Contracts\Actuation\ActuatorException;
use Fabricate\Contracts\Actuation\Interfaces\Fan as FanCircuit;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;

class Fan extends Actuator
{
    public function __construct(FanCircuit $circuit)
    {
        parent::__construct($circuit);
    }

    public function on(): void
    {
        $this->fan()->on();
    }

    public function off(): void
    {
        $this->fan()->off();
    }

    public function speed(?int $percent = null): int
    {
        return $this->fan()->speed($percent);
    }

    public function frequency(?int $hz = null): int
    {
        return $this->fan()->frequency($hz);
    }

    public function hasTachometer(): bool
    {
        return method_exists($this->fan(), 'rpm');
    }

    public function rpm(int $sample_ms = 500, int $pulses_per_revolution = 2): float
    {
        $fan = $this->fan();

        if (! method_exists($fan, 'rpm')) {
            throw new ActuatorException('The wrapped fan does not provide tachometer readings.');
        }

        return $fan->rpm($sample_ms, $pulses_per_revolution);
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::driver($driver);

        if ($circuit instanceof FanCircuit) {
            return new static($circuit);
        }

        throw new ActuatorException("Circuit [{$driver}] is not a Fan.");
    }

    private function fan(): FanCircuit
    {
        /** @var FanCircuit */
        return $this->circuit;
    }
}
