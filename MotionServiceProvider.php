<?php

namespace ScrapyardIO\Waveforms\Motion;

use Fabricate\NutsAndBolts\MagicAliases\Actuator;
use Fabricate\NutsAndBolts\ServiceProvider;

class MotionServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $actuators = [
            'fan' => Fan::class,
            'positional-servo' => PositionalServo::class,
            'continuous-servo' => ContinuousServo::class,
        ];

        foreach ($actuators as $key => $class) {
            if (config("waveforms.{$key}.enabled", false)) {
                Actuator::addActuator($key, $class);
            }
        }
    }
}
