<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter as LocaleNumberFormatter;

/**
 * Formats price according to the application settings.
 */
class NumberFormatter
{
    private LocaleNumberFormatter $localeNumberFormatter;

    public function __construct(LocaleNumberFormatter $localeNumberFormatter)
    {
        $this->localeNumberFormatter = $localeNumberFormatter;
    }

    public function formatPriceValue(float $value): float
    {
        if ($this->localeNumberFormatter->isAllowedToRoundPricesAndAmounts()) {
            return (float)$this->localeNumberFormatter->format(
                $value,
                'decimal',
                [
                    'min_fraction_digits' => $this->localeNumberFormatter
                        ->getAttribute('min_fraction_digits', 'currency'),
                    'max_fraction_digits' => $this->localeNumberFormatter
                        ->getAttribute('max_fraction_digits', 'currency'),
                ],
                [],
                [
                    'decimal_separator_symbol' => '.',
                    'grouping_separator_symbol' => '',
                ]
            );
        }

        return $value;
    }
}
