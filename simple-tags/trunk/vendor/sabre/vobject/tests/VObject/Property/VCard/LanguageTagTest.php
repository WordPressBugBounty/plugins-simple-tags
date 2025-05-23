<?php

namespace Sabre\VObject\Property\VCard;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class LanguageTagTest extends TestCase
{
    public function testMimeDir()
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:nl\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        $this->assertInstanceOf(LanguageTag::class, $result->LANG);

        $this->assertEquals('nl', $result->LANG->getValue());

        $this->assertEquals(
            $input,
            $result->serialize()
        );
    }

    public function testChangeAndSerialize()
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:nl\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        $this->assertInstanceOf(LanguageTag::class, $result->LANG);
        // This replicates what the vcard converter does and triggered a bug in
        // the past.
        $result->LANG->setValue(['de']);

        $this->assertEquals('de', $result->LANG->getValue());

        $expected = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:de\r\nEND:VCARD\r\n";
        $this->assertEquals(
            $expected,
            $result->serialize()
        );
    }
}
