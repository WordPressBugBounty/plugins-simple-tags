<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class EmptyParameterTest extends TestCase
{
    public function testRead()
    {
        $input = <<<VCF
BEGIN:VCARD
VERSION:2.1
N:Doe;Jon;;;
FN:Jon Doe
EMAIL;X-INTERN:foo@example.org
UID:foo
END:VCARD
VCF;

        $vcard = Reader::read($input);

        $this->assertInstanceOf(Component\VCard::class, $vcard);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD30);
        $vcard = $vcard->serialize();

        $converted = Reader::read($vcard);
        $converted->validate();

        $this->assertTrue(isset($converted->EMAIL['X-INTERN']));

        $version = Version::VERSION;

        $expected = <<<VCF
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject $version//EN
N:Doe;Jon;;;
FN:Jon Doe
EMAIL;X-INTERN=:foo@example.org
UID:foo
END:VCARD

VCF;

        $this->assertEquals($expected, str_replace("\r", '', $vcard));
    }

    public function testVCard21Parameter()
    {
        $vcard = new Component\VCard([], false);
        $vcard->VERSION = '2.1';
        $vcard->PHOTO = 'random_stuff';
        $vcard->PHOTO->add(null, 'BASE64');
        $vcard->UID = 'foo-bar';

        $result = $vcard->serialize();
        $expected = [
            'BEGIN:VCARD',
            'VERSION:2.1',
            'PHOTO;BASE64:'.base64_encode('random_stuff'),
            'UID:foo-bar',
            'END:VCARD',
            '',
        ];

        $this->assertEquals(implode("\r\n", $expected), $result);
    }
}
