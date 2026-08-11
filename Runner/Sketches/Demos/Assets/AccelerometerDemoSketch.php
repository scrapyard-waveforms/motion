<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Assets;

/**
 * Workshop sketch slugs for chip-agnostic Accelerometer demos.
 */
enum AccelerometerDemoSketch: string
{
    case OLED = 'accelerometer-oled-demo';
    case CANVAS = 'accelerometer-canvas-demo';
    case UX_ALIAS = 'accelerometer-ux-canvas-demo';
}
