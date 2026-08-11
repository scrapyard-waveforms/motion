<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Assets;

/**
 * Workshop sketch slugs for chip-agnostic Magnetometer demos.
 */
enum MagnetometerDemoSketch: string
{
    case OLED = 'magnetometer-oled-demo';
    case CANVAS = 'magnetometer-canvas-demo';
    case UX_ALIAS = 'magnetometer-ux-canvas-demo';
}
