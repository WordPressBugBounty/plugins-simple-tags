<?php

declare(strict_types=1);

namespace Sabre\Xml\Deserializer;

use PHPUnit\Framework\TestCase;
use Sabre\Xml\Service;

class RepeatingElementsTest extends TestCase
{
    public function testRead(): void
    {
        $service = new Service();
        $service->elementMap['{urn:test}collection'] = function ($reader) {
            return repeatingElements($reader, '{urn:test}item');
        };

        $xml = <<<XML
<?xml version="1.0"?>
<collection xmlns="urn:test">
    <item>foo</item>
    <item>bar</item>
</collection>
XML;

        $result = $service->parse($xml);

        $expected = [
            'foo',
            'bar',
        ];

        self::assertEquals($expected, $result);
    }
}
