<?php
declare(strict_types=1);

/**
 * Tijden in de video tonen en invoeren als minuten:seconden ("1:45")
 * in plaats van losse seconden (105). Gebruikt door de site en het CMS.
 */

/** Seconden → "m:ss", bijv. 105 → "1:45" en 10 → "0:10". */
function format_time(float $seconds): string
{
    $s = (int) round(max(0, $seconds));
    return sprintf('%d:%02d', intdiv($s, 60), $s % 60);
}

/**
 * "m:ss" → seconden, bijv. "1:45" → 105.
 * Gewone seconden ("105") mogen ook. Geeft null terug bij een ongeldige invoer.
 */
function parse_time(string $input): ?float
{
    $input = str_replace(',', '.', trim($input));
    if (preg_match('/^(\d{1,3}):([0-5]?\d(?:\.\d+)?)$/', $input, $m)) {
        return (int) $m[1] * 60 + (float) $m[2];
    }
    if (preg_match('/^\d+(?:\.\d+)?$/', $input)) {
        return (float) $input;
    }
    return null;
}
