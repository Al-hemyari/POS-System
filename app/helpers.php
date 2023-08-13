<?php

if (!function_exists('imap_open')) {
    
}

function currency_format(float $number): string
{
    return str_replace('.00', '', number_format($number, 2)). "$";
}
