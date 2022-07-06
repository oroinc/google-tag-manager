<?php

namespace Oro\Bundle\GoogleTagManagerBundle\Tests\Unit\Formatter;

use Oro\Bundle\GoogleTagManagerBundle\Formatter\NumberFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter as LocaleNumberFormatter;

class NumberFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var LocaleNumberFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private LocaleNumberFormatter $localeNumberFormatter;

    private NumberFormatter $formatter;

    protected function setUp(): void
    {
        $this->localeNumberFormatter = $this->createMock(LocaleNumberFormatter::class);

        $this->formatter = new NumberFormatter($this->localeNumberFormatter);
    }

    public function testFormatValueWhenRoundingOff(): void
    {
        $this->localeNumberFormatter
            ->expects(self::once())
            ->method('isAllowedToRoundPricesAndAmounts')
            ->willReturn(false);

        $this->localeNumberFormatter
            ->expects(self::never())
            ->method('format');

        self::assertSame(1.2345, $this->formatter->formatPriceValue(1.2345));
    }

    public function testFormatValueWhenRoundingOn(): void
    {
        $this->localeNumberFormatter
            ->expects(self::once())
            ->method('isAllowedToRoundPricesAndAmounts')
            ->willReturn(true);

        $this->localeNumberFormatter
            ->expects(self::exactly(2))
            ->method('getAttribute')
            ->withConsecutive(['min_fraction_digits', 'currency'], ['max_fraction_digits', 'currency'])
            ->willReturnOnConsecutiveCalls(0, 2);

        $value = 1.2345;
        $expected = 1.24;
        $this->localeNumberFormatter
            ->expects(self::once())
            ->method('format')
            ->with(
                $value,
                'decimal',
                [
                    'min_fraction_digits' => 0,
                    'max_fraction_digits' => 2,
                ],
                [],
                [
                    'decimal_separator_symbol' => '.',
                    'grouping_separator_symbol' => '',
                ]
            )
            ->willReturn($expected);

        self::assertSame($expected, $this->formatter->formatPriceValue($value));
    }
}
