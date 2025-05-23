<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class FloatTest extends TestCase
{
    public function testMimeDir()
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nX-FLOAT;VALUE=FLOAT:0.234;1.245\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        $this->assertInstanceOf(FloatValue::class, $result->{'X-FLOAT'});

        $this->assertEquals([
            0.234,
            1.245,
        ], $result->{'X-FLOAT'}->getParts());

        $this->assertEquals(
            $input,
            $result->serialize()
        );
    }
}
