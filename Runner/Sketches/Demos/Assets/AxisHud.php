<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Assets;

use ScrapyardIO\UX\Components\Indicators\ProgressBar;
use ScrapyardIO\UX\Components\Text\Label;
use ScrapyardIO\UX\Core\PaintContext;
use ScrapyardIO\UX\Core\UIComponent;
use ScrapyardIO\UX\Enums\Axis;
use ScrapyardIO\UX\Enums\TextAlign;
use ScrapyardIO\UX\Geometry\Size;
use ScrapyardIO\UX\Support\Theme;

/**
 * Full-canvas 3-axis stage — magnitude + per-axis meters.
 */
class AxisHud extends UIComponent
{
    protected Label $title;

    protected Label $value;

    protected Label $unit;

    protected Label $xLabel;

    protected Label $yLabel;

    protected Label $zLabel;

    protected ProgressBar $xBar;

    protected ProgressBar $yBar;

    protected ProgressBar $zBar;

    protected float $halfScale;

    public function __construct(string $title, string $unit, float $halfScale = 2.0)
    {
        parent::__construct('motion-axis-hud');

        $this->halfScale = max(0.0001, $halfScale);
        $this->title = Label::of($title, Theme::color('muted'))->setAlign(TextAlign::CENTER);
        $this->value = Label::of('0.00', Theme::color('ink'))->setAlign(TextAlign::CENTER);
        $this->unit = Label::of($unit, Theme::color('accent'))->setAlign(TextAlign::CENTER);
        $this->xLabel = Label::of('X +0.00', Theme::color('ink'));
        $this->yLabel = Label::of('Y +0.00', Theme::color('ink'));
        $this->zLabel = Label::of('Z +0.00', Theme::color('ink'));
        $this->xBar = ProgressBar::of(0.0, Axis::HORIZONTAL);
        $this->yBar = ProgressBar::of(0.0, Axis::HORIZONTAL);
        $this->zBar = ProgressBar::of(0.0, Axis::HORIZONTAL);

        foreach ([$this->xBar, $this->yBar, $this->zBar] as $bar) {
            $bar->setColors(Theme::color('accent'), Theme::color('track'));
        }

        foreach ([
            $this->title,
            $this->value,
            $this->unit,
            $this->xLabel,
            $this->yLabel,
            $this->zLabel,
            $this->xBar,
            $this->yBar,
            $this->zBar,
        ] as $child) {
            $this->addChild($child);
        }
    }

    public function sync(float $x, float $y, float $z): void
    {
        $mag = sqrt(($x ** 2) + ($y ** 2) + ($z ** 2));
        $this->value->setText(sprintf('%.2f', $mag));
        $this->xLabel->setText(sprintf('X %+.2f', $x));
        $this->yLabel->setText(sprintf('Y %+.2f', $y));
        $this->zLabel->setText(sprintf('Z %+.2f', $z));
        $this->xBar->setValue($this->axisPercent($x));
        $this->yBar->setValue($this->axisPercent($y));
        $this->zBar->setValue($this->axisPercent($z));
    }

    protected function axisPercent(float $value): float
    {
        return max(0.0, min(1.0, abs($value) / $this->halfScale));
    }

    public function layout(Size $available): void
    {
        $w = max(1, $available->width);
        $h = max(1, $available->height);
        $this->setSize($w, $h);

        $marginX = max(16, (int) round($w * 0.05));
        $marginY = max(12, (int) round($h * 0.04));
        $innerW = max(1, $w - (2 * $marginX));
        $usable = max(1, $h - (2 * $marginY));

        $titleH = (int) round($usable * 0.08);
        $valueH = (int) round($usable * 0.28);
        $unitH = (int) round($usable * 0.07);
        $axisBand = (int) round($usable * 0.48);
        $rowH = max(18, (int) floor($axisBand / 3));

        $y = $marginY;
        $this->fitLabel($this->title, $innerW, $titleH, 1, 4);
        $this->centerChild($this->title, $marginX, $y, $innerW, $titleH);
        $y += $titleH;

        $this->fitLabel($this->value, $innerW, $valueH, 2, 36);
        $this->centerChild($this->value, $marginX, $y, $innerW, $valueH);
        $y += $valueH;

        $this->fitLabel($this->unit, $innerW, $unitH, 1, 5);
        $this->centerChild($this->unit, $marginX, $y, $innerW, $unitH);
        $y += $unitH + max(4, (int) round($usable * 0.02));

        $labelW = max(72, (int) ($innerW * 0.34));
        $barW = max(40, $innerW - $labelW - 8);
        $barH = max(8, min(18, $rowH - 6));

        foreach (
            [
                [$this->xLabel, $this->xBar],
                [$this->yLabel, $this->yBar],
                [$this->zLabel, $this->zBar],
            ] as [$label, $bar]
        ) {
            $this->fitLabel($label, $labelW, $rowH, 1, 3);
            $label->setPosition(
                $marginX,
                $y + max(0, (int) (($rowH - $label->size()->height) / 2)),
            );

            $bar->setThickness($barH);
            $bar->setPosition($marginX + $labelW + 8, $y + max(0, (int) (($rowH - $barH) / 2)));
            $bar->setSize($barW, $barH);
            $bar->layout(new Size($barW, $barH));
            $y += $rowH;
        }
    }

    protected function fitLabel(Label $label, int $maxW, int $maxH, int $min, int $max): void
    {
        $best = $min;
        for ($size = $max; $size >= $min; $size--) {
            $label->setTextSize($size);
            $label->layout($label->size());
            if ($label->size()->width <= $maxW && $label->size()->height <= $maxH) {
                $best = $size;
                break;
            }
        }
        $label->setTextSize($best);
        $label->layout($label->size());
    }

    protected function centerChild(Label $label, int $boxX, int $boxY, int $boxW, int $boxH): void
    {
        $x = $boxX + max(0, (int) (($boxW - $label->size()->width) / 2));
        $y = $boxY + max(0, (int) (($boxH - $label->size()->height) / 2));
        $label->setPosition($x, $y);
    }

    protected function draw(PaintContext $ctx): void
    {
        // children paint
    }
}
