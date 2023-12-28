<?php

namespace App\Traits;

trait HaumanReadable
{
    public function convert($number)
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . ' Billion';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . ' Million';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        } else {
            return $number;
        }
    }
}
