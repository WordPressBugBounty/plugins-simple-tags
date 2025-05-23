<?php

namespace Sabre\VObject\Parser;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\ParseException;

class JsonTest extends TestCase
{
    public function testRoundTripJCard()
    {
        $input = [
            'vcard',
            [
                [
                    'version',
                    new \stdClass(),
                    'text',
                    '4.0',
                ],
                [
                    'prodid',
                    new \stdClass(),
                    'text',
                    '-//Sabre//Sabre VObject '.VObject\Version::VERSION.'//EN',
                ],
                [
                    'uid',
                    new \stdClass(),
                    'text',
                    'foo',
                ],
                [
                    'bday',
                    new \stdClass(),
                    'date-and-or-time',
                    '1985-04-07',
                ],
                [
                    'bday',
                    (object) [
                        'x-param' => [1, 2],
                    ],
                    'date',
                    '1979-12-25',
                ],
                [
                    'bday',
                    new \stdClass(),
                    'date-time',
                    '1979-12-25T02:00:00',
                ],
                [
                    'rev',
                    new \stdClass(),
                    'timestamp',
                    '1995-10-31T22:27:10Z',
                ],
                [
                    'lang',
                    new \stdClass(),
                    'language-tag',
                    'nl',
                ],
                [
                    'n',
                    new \stdClass(),
                    'text',
                    ['Last', 'First', 'Middle', '', ''],
                ],
                [
                    'tel',
                    (object) [
                        'group' => 'item1',
                    ],
                    'text',
                    '+1 555 123456',
                ],
                [
                    'x-ab-label',
                    (object) [
                        'group' => 'item1',
                    ],
                    'unknown',
                    'Walkie Talkie',
                ],
                [
                    'adr',
                    new \stdClass(),
                    'text',
                        [
                            '',
                            '',
                            ['My Street', 'Left Side', 'Second Shack'],
                            'Hometown',
                            'PA',
                            '18252',
                            'U.S.A',
                        ],
                ],

                [
                    'x-truncated',
                    new \stdClass(),
                    'date',
                    '--12-25',
                ],
                [
                    'x-time-local',
                    new \stdClass(),
                    'time',
                    '12:30:00',
                ],
                [
                    'x-time-utc',
                    new \stdClass(),
                    'time',
                    '12:30:00Z',
                ],
                [
                    'x-time-offset',
                    new \stdClass(),
                    'time',
                    '12:30:00-08:00',
                ],
                [
                    'x-time-reduced',
                    new \stdClass(),
                    'time',
                    '23',
                ],
                [
                    'x-time-truncated',
                    new \stdClass(),
                    'time',
                    '--30',
                ],
                [
                    'x-karma-points',
                    new \stdClass(),
                    'integer',
                    42,
                ],
                [
                    'x-grade',
                    new \stdClass(),
                    'float',
                    1.3,
                ],
                [
                    'tz',
                    new \stdClass(),
                    'utc-offset',
                    '-05:00',
                ],
            ],
        ];

        $parser = new Json(json_encode($input));
        $vobj = $parser->parse();

        $version = VObject\Version::VERSION;

        $result = $vobj->serialize();
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject $version//EN
UID:foo
BDAY:1985-04-07
BDAY;X-PARAM=1,2;VALUE=DATE:1979-12-25
BDAY;VALUE=DATE-TIME:1979-12-25T02:00:00
REV:1995-10-31T22:27:10Z
LANG:nl
N:Last;First;Middle;;
item1.TEL:+1 555 123456
item1.X-AB-LABEL:Walkie Talkie
ADR:;;My Street,Left Side,Second Shack;Hometown;PA;18252;U.S.A
X-TRUNCATED;VALUE=DATE:--12-25
X-TIME-LOCAL;VALUE=TIME:123000
X-TIME-UTC;VALUE=TIME:123000Z
X-TIME-OFFSET;VALUE=TIME:123000-0800
X-TIME-REDUCED;VALUE=TIME:23
X-TIME-TRUNCATED;VALUE=TIME:--30
X-KARMA-POINTS;VALUE=INTEGER:42
X-GRADE;VALUE=FLOAT:1.3
TZ;VALUE=UTC-OFFSET:-0500
END:VCARD

VCF;
        $this->assertEquals($expected, str_replace("\r", '', $result));

        $this->assertEquals(
            $input,
            $vobj->jsonSerialize()
        );
    }

    public function testRoundTripJCal()
    {
        $input = [
            'vcalendar',
            [
                [
                    'version',
                    new \stdClass(),
                    'text',
                    '2.0',
                ],
                [
                    'prodid',
                    new \stdClass(),
                    'text',
                    '-//Sabre//Sabre VObject '.VObject\Version::VERSION.'//EN',
                ],
                [
                    'calscale',
                    new \stdClass(),
                    'text',
                    'GREGORIAN',
                ],
            ],
            [
                ['vevent',
                    [
                        [
                            'uid', new \stdClass(), 'text', 'foo',
                        ],
                        [
                            'dtstart', new \stdClass(), 'date', '2013-05-26',
                        ],
                        [
                            'duration', new \stdClass(), 'duration', 'P1D',
                        ],
                        [
                            'categories', new \stdClass(), 'text', 'home', 'testing',
                        ],
                        [
                            'created', new \stdClass(), 'date-time', '2013-05-26T18:10:00Z',
                        ],
                        [
                            'attach', new \stdClass(), 'binary', base64_encode('attachment'),
                        ],
                        [
                            'attendee', new \stdClass(), 'cal-address', 'mailto:armin@example.org',
                        ],
                        [
                            'attendee',
                            (object) [
                                'cn' => 'Dominik',
                                'partstat' => 'DECLINED',
                            ],
                            'cal-address',
                            'mailto:dominik@example.org',
                        ],
                        [
                            'geo', new \stdClass(), 'float', [51.96668, 7.61876],
                        ],
                        [
                            'sequence', new \stdClass(), 'integer', 5,
                        ],
                        [
                            'freebusy', new \stdClass(), 'period',  ['2013-05-26T21:02:13', 'PT1H'], ['2013-06-26T12:00:00', '2013-06-26T13:00:00'],
                        ],
                        [
                            'url', new \stdClass(), 'uri', 'http://example.org/',
                        ],
                        [
                            'tzoffsetfrom', new \stdClass(), 'utc-offset', '+05:00',
                        ],
                        [
                            'rrule', new \stdClass(), 'recur', [
                                'freq' => 'WEEKLY',
                                'byday' => ['MO', 'TU'],
                            ],
                        ],
                        [
                            'x-bool', new \stdClass(), 'boolean', true,
                        ],
                        [
                            'x-time', new \stdClass(), 'time', '08:00:00',
                        ],
                        [
                            'request-status',
                            new \stdClass(),
                            'text',
                            ['2.0', 'Success'],
                        ],
                        [
                            'request-status',
                            new \stdClass(),
                            'text',
                            ['3.7', 'Invalid Calendar User', 'ATTENDEE:mailto:jsmith@example.org'],
                        ],
                    ],
                    [
                        ['valarm',
                            [
                                [
                                    'action', new \stdClass(), 'text', 'DISPLAY',
                                ],
                            ],
                            [],
                        ],
                    ],
                ],
            ],
        ];

        $parser = new Json(json_encode($input));
        $vobj = $parser->parse();
        $result = $vobj->serialize();

        $version = VObject\Version::VERSION;

        $expected = <<<VCF
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
UID:foo
DTSTART;VALUE=DATE:20130526
DURATION:P1D
CATEGORIES:home,testing
CREATED:20130526T181000Z
ATTACH;VALUE=BINARY:YXR0YWNobWVudA==
ATTENDEE:mailto:armin@example.org
ATTENDEE;CN=Dominik;PARTSTAT=DECLINED:mailto:dominik@example.org
GEO:51.96668;7.61876
SEQUENCE:5
FREEBUSY:20130526T210213/PT1H,20130626T120000/20130626T130000
URL;VALUE=URI:http://example.org/
TZOFFSETFROM:+0500
RRULE:FREQ=WEEKLY;BYDAY=MO,TU
X-BOOL;VALUE=BOOLEAN:TRUE
X-TIME;VALUE=TIME:080000
REQUEST-STATUS:2.0;Success
REQUEST-STATUS:3.7;Invalid Calendar User;ATTENDEE:mailto:jsmith@example.org
BEGIN:VALARM
ACTION:DISPLAY
END:VALARM
END:VEVENT
END:VCALENDAR

VCF;
        $this->assertEquals($expected, str_replace("\r", '', $result));

        $this->assertEquals(
            $input,
            $vobj->jsonSerialize()
        );
    }

    public function testParseStreamArg()
    {
        $input = [
            'vcard',
            [
                [
                    'FN', new \stdClass(), 'text', 'foo',
                ],
            ],
        ];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode($input));
        rewind($stream);

        $result = VObject\Reader::readJson($stream, 0);
        $this->assertEquals('foo', $result->FN->getValue());
    }

    public function testParseInvalidData()
    {
        $this->expectException(ParseException::class);
        $json = new Json();
        $input = [
            'vlist',
            [
                [
                    'FN', new \stdClass(), 'text', 'foo',
                ],
            ],
        ];

        $json->parse(json_encode($input), 0);
    }
}
