<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Magnetometer;

use Fabricate\Contracts\Sketches\Attributes\Sketch as SketchAttribute;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\Sketches\Sketch;
use ScrapyardIO\Tubes\Panels\MonochromePanel;
use Symfony\Component\Console\Command\Command;
use Throwable;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\OpensDefaultTubesCanvas;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\PaintsTubesAxisHud;
use Waveforms\Motion\Runner\Sketches\Demos\Concerns\ResolvesMagnetometerCircuit;

/**
 * Magnetometer on tubes.defaults.canvas (window or non-mono panel).
 *
 *   ./runner magnetometer-canvas-demo lis3mdl
 *
 * When scrapyard-io/ux is installed, {@see UXCanvasTestSketch} replaces this slug.
 * MonochromePanel is rejected — use magnetometer-oled-demo instead.
 */
#[SketchAttribute('magnetometer-canvas-demo')]
class CanvasTestSketch extends Sketch
{
    use ResolvesMagnetometerCircuit;
    use OpensDefaultTubesCanvas;
    use PaintsTubesAxisHud;

    protected string $description = 'Magnetometer X/Y/Z + magnitude on tubes.defaults.canvas (Ctrl-C to stop)';

    protected bool $announced = false;

    protected int $lastSampleNs = 0;

    public function configureCommand(Command $command): void
    {
        $this->configureMagnetometerProfileArgument($command);
    }

    public function boot(): void
    {
        $this->axisHalfScale = 100.0;
        $this->installStopHandlers();

        if (! $this->bootMagnetometer()) {
            return;
        }

        if (! $this->bootDefaultTubesCanvas()) {
            return;
        }

        if ($this->canvas instanceof MonochromePanel) {
            $this->error(
                "Canvas demo rejects MonochromePanel [{$this->canvasProfile}]. "
                .'Use magnetometer-oled-demo instead.'
            );
            $this->closeDefaultTubesCanvas();
            $this->closeMagnetometer();
        }
    }

    public function loop(): SketchLoopResult
    {
        if ($this->stopRequested || $this->defaultCanvasShouldStop()) {
            $this->info('Magnetometer canvas demo stopped.');

            return SketchLoopResult::STOP;
        }

        if (is_null($this->magnetometer) || is_null($this->canvas) || $this->canvas instanceof MonochromePanel) {
            return SketchLoopResult::STOP;
        }

        if (! $this->announced) {
            $this->info(
                "Magnetometer canvas via Magnetometer::circuit('{$this->circuitProfile}') → canvas [{$this->canvasProfile}]"
            );
            $this->line('  X/Y/Z uT + magnitude — Ctrl-C to end.');
            $this->announced = true;
        }

        $now = hrtime(true);
        if ($this->lastSampleNs !== 0 && ($now - $this->lastSampleNs) < 300_000_000) {
            usleep(2_000);

            return SketchLoopResult::CONTINUE;
        }

        try {
            $x = $this->magnetometer->x();
            $y = $this->magnetometer->y();
            $z = $this->magnetometer->z();
            $renderer = $this->canvasRenderer();
            $this->paintAxisHud($renderer, $this->canvas, 'MAGNETOMETER', 'uT', $x, $y, $z);
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
        $this->closeMagnetometer();
    }
}
