<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Concerns;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;
use Waveforms\Motion\Accelerometer;

/**
 * Require a circuits.php profile argument and open {@see Accelerometer}.
 *
 * @mixin \Fabricate\Sketches\Sketch
 */
trait ResolvesAccelerometerCircuit
{
    protected ?string $circuitProfile = null;

    protected ?Accelerometer $accelerometer = null;

    protected bool $stopRequested = false;

    protected function configureAccelerometerProfileArgument(Command $command): void
    {
        $command->addArgument(
            'profile',
            InputArgument::REQUIRED,
            'circuits.php profile name (ic must MeasureAcceleration)',
        );
    }

    protected function installStopHandlers(): void
    {
        if (! extension_loaded('pcntl')) {
            return;
        }

        pcntl_async_signals(true);
        $stop = function (): void {
            $this->stopRequested = true;
        };
        pcntl_signal(SIGINT, $stop);
        pcntl_signal(SIGTERM, $stop);
    }

    /**
     * @return bool false when the sketch should quit (errors already printed)
     */
    protected function bootAccelerometer(): bool
    {
        $requested = $this->argument('profile');
        if (! is_string($requested) || trim($requested) === '') {
            $this->error('Profile argument is required.');

            return false;
        }

        $this->circuitProfile = trim($requested);

        try {
            $this->accelerometer = Accelerometer::circuit($this->circuitProfile);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->accelerometer = null;
            $this->circuitProfile = null;

            return false;
        }

        return true;
    }

    protected function closeAccelerometer(): void
    {
        $this->accelerometer = null;
        $this->circuitProfile = null;
    }
}
