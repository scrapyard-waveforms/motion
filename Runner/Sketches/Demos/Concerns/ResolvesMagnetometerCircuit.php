<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Concerns;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;
use Waveforms\Motion\Magnetometer;

/**
 * Require a circuits.php profile argument and open {@see Magnetometer}.
 *
 * @mixin \Fabricate\Sketches\Sketch
 */
trait ResolvesMagnetometerCircuit
{
    protected ?string $circuitProfile = null;

    protected ?Magnetometer $magnetometer = null;

    protected bool $stopRequested = false;

    protected function configureMagnetometerProfileArgument(Command $command): void
    {
        $command->addArgument(
            'profile',
            InputArgument::REQUIRED,
            'circuits.php profile name (ic must MeasureMagneticFields)',
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
    protected function bootMagnetometer(): bool
    {
        $requested = $this->argument('profile');
        if (! is_string($requested) || trim($requested) === '') {
            $this->error('Profile argument is required.');

            return false;
        }

        $this->circuitProfile = trim($requested);

        try {
            $this->magnetometer = Magnetometer::circuit($this->circuitProfile);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->magnetometer = null;
            $this->circuitProfile = null;

            return false;
        }

        return true;
    }

    protected function closeMagnetometer(): void
    {
        $this->magnetometer = null;
        $this->circuitProfile = null;
    }
}
