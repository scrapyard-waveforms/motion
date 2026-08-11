<?php

namespace Waveforms\Motion;

use Fabricate\Contracts\Sketches\SketchRegistry;
use Fabricate\NutsAndBolts\ServiceProvider;
use Waveforms\Motion\Runner\Sketches\Demos\Accelerometer\CanvasTestSketch as AccelCanvasTestSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Accelerometer\OLEDTestSketch as AccelOLEDTestSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Accelerometer\UXCanvasTestSketch as AccelUXCanvasTestSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Assets\AccelerometerDemoSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Assets\MagnetometerDemoSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Magnetometer\CanvasTestSketch as MagCanvasTestSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Magnetometer\OLEDTestSketch as MagOLEDTestSketch;
use Waveforms\Motion\Runner\Sketches\Demos\Magnetometer\UXCanvasTestSketch as MagUXCanvasTestSketch;

class MotionSensorServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->registerDemoSketches();
    }

    protected function registerDemoSketches(): void
    {
        if (! $this->container->bound(SketchRegistry::class)) {
            return;
        }

        // Soft tubes dependency.
        if (! class_exists(\ScrapyardIO\Tubes\Core\MagicAliases\Panel::class)) {
            return;
        }

        /** @var SketchRegistry $registry */
        $registry = $this->container->make(SketchRegistry::class);

        if (! $registry->has(AccelerometerDemoSketch::OLED->value)) {
            $registry->registerConvention(AccelerometerDemoSketch::OLED->value, AccelOLEDTestSketch::class);
        }

        if (! $registry->has(AccelerometerDemoSketch::CANVAS->value)) {
            $registry->registerConvention(AccelerometerDemoSketch::CANVAS->value, AccelCanvasTestSketch::class);
        }

        if (! $registry->has(MagnetometerDemoSketch::OLED->value)) {
            $registry->registerConvention(MagnetometerDemoSketch::OLED->value, MagOLEDTestSketch::class);
        }

        if (! $registry->has(MagnetometerDemoSketch::CANVAS->value)) {
            $registry->registerConvention(MagnetometerDemoSketch::CANVAS->value, MagCanvasTestSketch::class);
        }

        if (class_exists(\ScrapyardIO\UX\Core\Scene::class)) {
            $registry->replace(AccelUXCanvasTestSketch::class);
            $registry->replace(MagUXCanvasTestSketch::class);

            if (! $registry->has(AccelerometerDemoSketch::UX_ALIAS->value)) {
                $registry->registerConvention(
                    AccelerometerDemoSketch::UX_ALIAS->value,
                    AccelUXCanvasTestSketch::class,
                );
            }

            if (! $registry->has(MagnetometerDemoSketch::UX_ALIAS->value)) {
                $registry->registerConvention(
                    MagnetometerDemoSketch::UX_ALIAS->value,
                    MagUXCanvasTestSketch::class,
                );
            }
        }
    }
}
