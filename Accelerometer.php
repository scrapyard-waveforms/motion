<?php

namespace Waveforms\Motion;

use GeneralPurposeIO\Core\MagicAliases\Circuit;
use Waveforms\Contracts\Motion\MeasuresAcceleration;
use Waveforms\Contracts\Sensors\Enums\AxisOrientation;
use Waveforms\Contracts\Sensors\SensorException;
use Waveforms\PhysicalDevices\AbstractSensor;

/**
 * @property-read float $pitch
 * @property-read float $roll
 * @property-read float $x
 * @property-read float $y
 * @property-read float $z
 * @property-read float $acceleration
 * @property-read float $inclination
 * @property-read AxisOrientation $orientation
 */
class Accelerometer extends AbstractSensor
{
    protected bool $enabled = true;

    public function __construct(
        protected MeasuresAcceleration $sensor,
    ) {}

    public function __get(string $name): mixed
    {
        return match ($name) {
            'pitch' => $this->pitch(),
            'roll' => $this->roll(),
            'x' => $this->x(),
            'y' => $this->y(),
            'z' => $this->z(),
            'acceleration' => $this->acceleration(),
            'inclination' => $this->inclination(),
            'orientation' => $this->orientation(),
            default => throw SensorException::invalidProperty($name, static::class),
        };
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function x(): float
    {
        $this->ensureEnabled();

        return $this->sensor->x();
    }

    public function y(): float
    {
        $this->ensureEnabled();

        return $this->sensor->y();
    }

    public function z(): float
    {
        $this->ensureEnabled();

        return $this->sensor->z();
    }

    public function acceleration(): float
    {
        $x = $this->x();
        $y = $this->y();
        $z = $this->z();

        return sqrt($x ** 2 + $y ** 2 + $z ** 2);
    }

    public function pitch(): float
    {
        return rad2deg(atan2($this->x(), hypot($this->y(), $this->z())));
    }

    public function roll(): float
    {
        return rad2deg(atan2($this->y(), hypot($this->x(), $this->z())));
    }

    public function inclination(): float
    {
        return rad2deg(atan2($this->y(), $this->x()));
    }

    public function orientation(): AxisOrientation
    {
        $x = $this->x();
        $y = $this->y();
        $z = $this->z();

        $magnitudes = ['x' => abs($x), 'y' => abs($y), 'z' => abs($z)];
        [$smallest_axis] = array_keys($magnitudes, min($magnitudes));

        return match ($smallest_axis) {
            'x' => $x >= 0 ? AxisOrientation::X : AxisOrientation::X_INVERTED,
            'y' => $y >= 0 ? AxisOrientation::Y : AxisOrientation::Y_INVERTED,
            'z' => $z >= 0 ? AxisOrientation::Z : AxisOrientation::Z_INVERTED,
        };
    }

    protected function ensureEnabled(): void
    {
        if (! $this->enabled) {
            throw SensorException::disabled(static::class);
        }
    }

    public static function circuit(string $driver): static
    {
        $circuit = Circuit::profile($driver);

        if ($circuit instanceof MeasuresAcceleration) {
            return new static($circuit);
        }

        throw new SensorException("Circuit [{$driver}] does not Measure Acceleration.");
    }
}
