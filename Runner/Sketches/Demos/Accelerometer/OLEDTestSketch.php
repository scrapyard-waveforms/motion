<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Accelerometer;

use Fabricate\Contracts\Sketches\Attributes\Sketch as SketchAttribute;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\Sketches\Sketch;
use ScrapyardIO\Tubes\Panels\MonochromePanel;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\OpensDefaultTubesCanvas;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\PaintsTubesAxisHud;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\ResolvesAccelerometerCircuit;

/**
 * Accelerometer on a MonochromePanel (SSD1306 / SH1106).
 *
 *   ./runner accelerometer-oled-demo msa311
 *
 * Requires tubes.defaults.canvas → a MonochromePanel.
 */
#[SketchAttribute('accelerometer-oled-demo')]
class OLEDTestSketch extends Sketch
{
    use ResolvesAccelerometerCircuit;
    use OpensDefaultTubesCanvas;
    use PaintsTubesAxisHud;

    protected string $description = 'Accelerometer X/Y/Z + magnitude on a MonochromePanel (Ctrl-C to stop)';

    protected bool $announced = false;

    protected int $lastSampleNs = 0;

    public function configureCommand(Command $command): void
    {
        $this->configureAccelerometerProfileArgument($command);
    }

    public function boot(): void
    {
        $this->installStopHandlers();

        if (! $this->bootAccelerometer()) {
            return;
        }

        if (! $this->bootDefaultTubesCanvas()) {
            return;
        }

        if (! $this->canvas instanceof MonochromePanel) {
            $kind = $this->canvas::class;
            $this->error(
                "OLED demo requires a MonochromePanel; tubes.defaults.canvas [{$this->canvasProfile}] opened {$kind}."
            );
            $this->closeDefaultTubesCanvas();
            $this->closeAccelerometer();
        }
    }

    public function loop(): SketchLoopResult
    {
        if ($this->stopRequested || $this->defaultCanvasShouldStop()) {
            $this->info('Accelerometer OLED demo stopped.');

            return SketchLoopResult::STOP;
        }

        if (is_null($this->accelerometer) || is_null($this->canvas) || ! $this->canvas instanceof MonochromePanel) {
            return SketchLoopResult::STOP;
        }

        if (! $this->announced) {
            $this->info(
                "Accelerometer OLED via Accelerometer::circuit('{$this->circuitProfile}') → canvas [{$this->canvasProfile}]"
            );
            $this->line('  X/Y/Z g + magnitude — Ctrl-C to end.');
            $this->announced = true;
        }

        $now = hrtime(true);
        if ($this->lastSampleNs !== 0 && ($now - $this->lastSampleNs) < 300_000_000) {
            usleep(5_000);

            return SketchLoopResult::CONTINUE;
        }

        try {
            $x = $this->accelerometer->x();
            $y = $this->accelerometer->y();
            $z = $this->accelerometer->z();
            $renderer = $this->canvasRenderer();
            $this->paintAxisHud($renderer, $this->canvas, 'ACCELEROMETER', 'g', $x, $y, $z);
            $this->canvas->present();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return SketchLoopResult::STOP;
        }

        $this->lastSampleNs = $now;

        return SketchLoopResult::CONTINUE;
    }

    public function shutdown(): void
    {
        $this->closeDefaultTubesCanvas();
        $this->closeAccelerometer();
    }
}
