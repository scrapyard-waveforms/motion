<?php

namespace Waveforms\Motion\Runner\Sketches\Demos\Concerns;

use ScrapyardIO\Tubes\Canvas\Canvas;
use ScrapyardIO\Tubes\Rendering\Renderer2D;

/**
 * 3-axis stage — magnitude hero + bipolar X/Y/Z meters.
 */
trait PaintsTubesAxisHud
{
    /** Half-scale for bipolar bars (accel ±2 g, mag ±100 µT by default). */
    protected float $axisHalfScale = 2.0;

    protected function paintAxisHud(
        Renderer2D $renderer,
        Canvas $canvas,
        string $title,
        string $unit,
        float $x,
        float $y,
        float $z,
    ): void {
        $w = max(1, $canvas->width());
        $h = max(1, $canvas->height());
        $bg = 0x0A0C10FF;
        $fg = 0xF2F5F8FF;
        $muted = 0x8B93A1FF;
        $accent = 0x3DDC97FF;
        $track = 0x1E2430FF;
        $neg = 0xFF6B6BFF;

        $fb = $canvas->framebuffer();
        $renderer->setFramebuffer($fb);
        $renderer->fill($bg);
        $renderer->setFont(null)->setTextWrap(false);

        $mag = sqrt(($x ** 2) + ($y ** 2) + ($z ** 2));

        if ($h < 100 || $w < 160) {
            $this->paintCompactAxisHud($renderer, $w, $h, $title, $unit, $x, $y, $z, $mag, $fg, $bg, $muted, $accent, $track, $neg);

            return;
        }

        $this->paintStageAxisHud($renderer, $w, $h, $title, $unit, $x, $y, $z, $mag, $fg, $bg, $muted, $accent, $track, $neg);
    }

    protected function paintStageAxisHud(
        Renderer2D $renderer,
        int $w,
        int $h,
        string $title,
        string $unit,
        float $x,
        float $y,
        float $z,
        float $mag,
        int $fg,
        int $bg,
        int $muted,
        int $accent,
        int $track,
        int $neg,
    ): void {
        $marginX = max(16, (int) round($w * 0.05));
        $marginY = max(12, (int) round($h * 0.04));
        $innerW = max(1, $w - (2 * $marginX));
        $usable = max(1, $h - (2 * $marginY));

        $titleH = (int) round($usable * 0.08);
        $valueH = (int) round($usable * 0.28);
        $unitH = (int) round($usable * 0.07);
        $axisBand = (int) round($usable * 0.48);

        $yPos = $marginY;
        $this->paintFittedCentered($renderer, $title, $marginX, $yPos, $innerW, $titleH, $muted, $bg, 1, 4);
        $yPos += $titleH;

        $this->paintFittedCentered(
            $renderer,
            sprintf('%.2f', $mag),
            $marginX,
            $yPos,
            $innerW,
            $valueH,
            $fg,
            $bg,
            2,
            36,
        );
        $yPos += $valueH;

        $this->paintFittedCentered($renderer, $unit, $marginX, $yPos, $innerW, $unitH, $accent, $bg, 1, 5);
        $yPos += $unitH + max(4, (int) round($usable * 0.02));

        $rowH = max(18, (int) floor($axisBand / 3));
        foreach ([['X', $x], ['Y', $y], ['Z', $z]] as [$label, $value]) {
            $this->paintAxisRow(
                $renderer,
                $marginX,
                $yPos,
                $innerW,
                $rowH,
                $label,
                $value,
                $fg,
                $bg,
                $muted,
                $accent,
                $track,
                $neg,
            );
            $yPos += $rowH;
        }

        $renderer->setFont(null);
    }

    protected function paintCompactAxisHud(
        Renderer2D $renderer,
        int $w,
        int $h,
        string $title,
        string $unit,
        float $x,
        float $y,
        float $z,
        float $mag,
        int $fg,
        int $bg,
        int $muted,
        int $accent,
        int $track,
        int $neg,
    ): void {
        $pad = 2;
        $titleH = max(8, (int) ($h * 0.14));
        $this->paintFittedCentered($renderer, $title, $pad, $pad, $w - (2 * $pad), $titleH, $muted, $bg, 1, 2);

        $magH = max(10, (int) ($h * 0.22));
        $this->paintFittedCentered(
            $renderer,
            sprintf('|%.2f| %s', $mag, $unit),
            $pad,
            $pad + $titleH,
            $w - (2 * $pad),
            $magH,
            $fg,
            $bg,
            1,
            3,
        );

        $rowTop = $pad + $titleH + $magH;
        $rowH = max(10, (int) floor(($h - $rowTop - $pad) / 3));
        foreach ([['X', $x], ['Y', $y], ['Z', $z]] as [$label, $value]) {
            $this->paintAxisRow(
                $renderer,
                $pad,
                $rowTop,
                $w - (2 * $pad),
                $rowH,
                $label,
                $value,
                $fg,
                $bg,
                $muted,
                $accent,
                $track,
                $neg,
            );
            $rowTop += $rowH;
        }

        $renderer->setFont(null);
    }

    protected function paintAxisRow(
        Renderer2D $renderer,
        int $x,
        int $y,
        int $w,
        int $h,
        string $label,
        float $value,
        int $fg,
        int $bg,
        int $muted,
        int $accent,
        int $track,
        int $neg,
    ): void {
        $labelW = max(10, (int) ($w * 0.10));
        $valueW = max(36, (int) ($w * 0.28));
        $barW = max(20, $w - $labelW - $valueW - 4);
        $barH = max(4, min(14, $h - 4));
        $barY = $y + max(0, (int) (($h - $barH) / 2));

        $this->paintFittedLeft($renderer, $label, $x, $y, $labelW, $h, $muted, $bg, 1, 3);
        $this->paintFittedLeft(
            $renderer,
            sprintf('%+.2f', $value),
            $x + $labelW,
            $y,
            $valueW,
            $h,
            $fg,
            $bg,
            1,
            3,
        );

        $barX = $x + $labelW + $valueW;
        $renderer->fillRect($barX, $barY, $barW, $barH, $track);

        $mid = $barX + (int) ($barW / 2);
        $renderer->fillRect($mid, $barY, 1, $barH, $muted);

        $half = max(0.0001, $this->axisHalfScale);
        $norm = max(-1.0, min(1.0, $value / $half));
        $fillW = max(1, (int) round(abs($norm) * ($barW / 2)));
        $color = $norm >= 0 ? $accent : $neg;

        if ($norm >= 0) {
            $renderer->fillRect($mid, $barY, $fillW, $barH, $color);
        } else {
            $renderer->fillRect($mid - $fillW, $barY, $fillW, $barH, $color);
        }
    }

    protected function paintFittedCentered(
        Renderer2D $renderer,
        string $text,
        int $boxX,
        int $boxY,
        int $boxW,
        int $boxH,
        int $fg,
        int $bg,
        int $minSize,
        int $maxSize,
    ): void {
        if ($text === '' || $boxW < 1 || $boxH < 1) {
            return;
        }

        $size = $this->fitTextSize($renderer, $text, $boxW, $boxH, $minSize, $maxSize);
        [$textW, $textH] = $this->measureText($renderer, $text, $size);
        $drawX = $boxX + max(0, (int) (($boxW - $textW) / 2));
        $drawY = $boxY + max(0, (int) (($boxH - $textH) / 2));

        $renderer->setTextSize($size)
            ->setTextColor($fg, $bg)
            ->setCursor($drawX, $drawY)
            ->println($text);
    }

    protected function paintFittedLeft(
        Renderer2D $renderer,
        string $text,
        int $boxX,
        int $boxY,
        int $boxW,
        int $boxH,
        int $fg,
        int $bg,
        int $minSize,
        int $maxSize,
    ): void {
        if ($text === '' || $boxW < 1 || $boxH < 1) {
            return;
        }

        $size = $this->fitTextSize($renderer, $text, $boxW, $boxH, $minSize, $maxSize);
        [, $textH] = $this->measureText($renderer, $text, $size);
        $drawY = $boxY + max(0, (int) (($boxH - $textH) / 2));

        $renderer->setTextSize($size)
            ->setTextColor($fg, $bg)
            ->setCursor($boxX, $drawY)
            ->println($text);
    }

    protected function fitTextSize(
        Renderer2D $renderer,
        string $text,
        int $maxW,
        int $maxH,
        int $min,
        int $max,
    ): int {
        $min = max(1, $min);
        $max = max($min, $max);
        $lo = $min;
        $hi = $max;
        $best = $min;

        while ($lo <= $hi) {
            $mid = (int) (($lo + $hi) / 2);
            [$tw, $th] = $this->measureText($renderer, $text, $mid);
            if ($tw <= $maxW && $th <= $maxH) {
                $best = $mid;
                $lo = $mid + 1;
            } else {
                $hi = $mid - 1;
            }
        }

        return $best;
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function measureText(Renderer2D $renderer, string $text, int $size): array
    {
        $renderer->setTextSize($size)->setTextWrap(false);

        return [
            max(1, strlen($text) * 6 * $size),
            max(1, 8 * $size),
        ];
    }
}
