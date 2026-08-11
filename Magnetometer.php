<?php

namespace Waveforms\Motion;

use GeneralPurposeIO\Core\MagicAliases\Circuit;
use Waveforms\Contracts\Motion\MeasuresMagneticFields;
use Waveforms\Contracts\Sensors\SensorException;
use Waveforms\PhysicalDevices\AbstractSensor;

/**
 * Magnetic field wrapper — axis samples in µT.
 *
 * @property-read float $x
 * @property-read float $y
 * @property-read float $z
 * @property-read float $magnitude
 */
class Magnetometer extends AbstractSensor
{
    public function __construct(
        protected MeasuresMagneticFields $sensor,
    ) {}

    public function __get(string $name): float
    {
        return match ($name) {
            'x' => $this->x(),
            'y' => $this->y(),
            'z' => $this->z(),
            'magnitude' => $this->magnitude(),
            default => throw SensorException::invalidProperty($name, static::class),
        };
    }

    public function x(): float
    {
        return $this->sensor->x();
    }

    public function y(): float
    {
        return $this->sensor->y();
    }

    public function z(): float
    {
        return $this->sensor->z();
    }

    public function magnitude(): float
    {
        $x = $this->x();
        $y = $this->y();
        $z = $this->z();

        return sqrt($x ** 2 + $y ** 2 + $z ** 2);
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::profile($driver);

        if ($circuit instanceof MeasuresMagneticFields) {
            return new static($circuit);
        }

        throw new SensorException("Circuit [{$driver}] does not Measure Magnetic Fields.");
    }
}
