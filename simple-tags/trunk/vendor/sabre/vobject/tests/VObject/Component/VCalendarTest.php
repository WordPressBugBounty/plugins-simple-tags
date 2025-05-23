<?php

namespace Sabre\VObject\Component;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\InvalidDataException;

class VCalendarTest extends TestCase
{
    use VObject\PHPUnitAssertions;

    /**
     * @dataProvider expandData
     */
    public function testExpand($input, $output, $timeZone = 'UTC', $start = '2011-12-01', $end = '2011-12-31')
    {
        $vcal = VObject\Reader::read($input);

        $timeZone = new DateTimeZone($timeZone);

        $vcal = $vcal->expand(
            new \DateTime($start),
            new \DateTime($end),
            $timeZone
        );

        // This will normalize the output
        $output = VObject\Reader::read($output)->serialize();

        $this->assertVObjectEqualsVObject($output, $vcal->serialize());
    }

    public function expandData()
    {
        $tests = [];

        // No data
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
END:VCALENDAR
';

        $output = $input;
        $tests[] = [$input, $output];

        // Simple events
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla
SUMMARY:InExpand
DTSTART;VALUE=DATE:20111202
END:VEVENT
BEGIN:VEVENT
UID:bla2
SUMMARY:NotInExpand
DTSTART;VALUE=DATE:20120101
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla
SUMMARY:InExpand
DTSTART;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Removing timezone info
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Paris
END:VTIMEZONE
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART;TZID=Europe/Paris:20111203T130102
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART:20111203T120102Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Removing timezone info from sub-components. See Issue #278
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Paris
END:VTIMEZONE
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART;TZID=Europe/Paris:20111203T130102
BEGIN:VALARM
TRIGGER;VALUE=DATE-TIME;TZID=America/New_York:20151209T133200
END:VALARM
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART:20111203T120102Z
BEGIN:VALARM
TRIGGER;VALUE=DATE-TIME:20151209T183200Z
END:VALARM
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Recurrence rule
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111202T120000Z
DTEND:20111202T130000Z
RECURRENCE-ID:20111202T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111209T120000Z
DTEND:20111209T130000Z
RECURRENCE-ID:20111209T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111216T120000Z
DTEND:20111216T130000Z
RECURRENCE-ID:20111216T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111223T120000Z
DTEND:20111223T130000Z
RECURRENCE-ID:20111223T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111230T120000Z
DTEND:20111230T130000Z
RECURRENCE-ID:20111230T120000Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Recurrence rule + override
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY
END:VEVENT
BEGIN:VEVENT
UID:bla6
RECURRENCE-ID:20111209T120000Z
DTSTART:20111209T140000Z
DTEND:20111209T150000Z
SUMMARY:Override!
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111202T120000Z
DTEND:20111202T130000Z
RECURRENCE-ID:20111202T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
RECURRENCE-ID:20111209T120000Z
DTSTART:20111209T140000Z
DTEND:20111209T150000Z
SUMMARY:Override!
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111216T120000Z
DTEND:20111216T130000Z
RECURRENCE-ID:20111216T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111223T120000Z
DTEND:20111223T130000Z
RECURRENCE-ID:20111223T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111230T120000Z
DTEND:20111230T130000Z
RECURRENCE-ID:20111230T120000Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Floating dates and times.
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:bla1
DTSTART:20141112T195000
END:VEVENT
BEGIN:VEVENT
UID:bla2
DTSTART;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141112
RRULE:FREQ=DAILY;COUNT=2
END:VEVENT
END:VCALENDAR
ICS;

        $output = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:bla1
DTSTART:20141112T225000Z
END:VEVENT
BEGIN:VEVENT
UID:bla2
DTSTART;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141112
RECURRENCE-ID;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141113
RECURRENCE-ID;VALUE=DATE:20141113
END:VEVENT
END:VCALENDAR
ICS;

        $tests[] = [$input, $output, 'America/Argentina/Buenos_Aires', '2014-01-01', '2015-01-01'];

        // Recurrence rule with no valid instances
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule3
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY;COUNT=1
EXDATE:20111125T120000Z
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
END:VCALENDAR
';

        $tests[] = [$input, $output];

        return $tests;
    }

    public function testBrokenEventExpand()
    {
        $this->expectException(InvalidDataException::class);
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
RRULE:FREQ=WEEKLY
DTSTART;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';
        $vcal = VObject\Reader::read($input);
        $vcal->expand(
            new \DateTime('2011-12-01'),
            new \DateTime('2011-12-31')
        );
    }

    /**
     * This test used to induce an infinite loop.
     * The "medium" annotation means that phpunit will fail the
     * test if it takes longer than a default of 10 seconds.
     *
     * @medium
     */
    public function testEventExpandYearly()
    {
        $input = 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:1a093f1012086078fdd3d9df5ff4d7d0
DTSTART;TZID=UTC:20210203T130000
DTEND;TZID=UTC:20210203T140000
RRULE:FREQ=YEARLY;COUNT=7;WKST=MO;BYDAY=MO;BYWEEKNO=13,15,50
END:VEVENT
END:VCALENDAR
';
        $vcal = VObject\Reader::read($input);
        $events = $vcal->expand(
            new \DateTime('2021-01-01'),
            new \DateTime('2023-01-01')
        );

        $this->assertCount(7, $events->VEVENT);
    }

    public function testGetDocumentType()
    {
        $vcard = new VCalendar();
        $vcard->VERSION = '2.0';
        $this->assertEquals(VCalendar::ICALENDAR20, $vcard->getDocumentType());
    }

    public function testValidateCorrect()
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
DTSTAMP:20140122T233226Z
UID:foo
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals([], $vcal->validate(), 'Got an error');
    }

    public function testValidateNoVersion()
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateWrongVersion()
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:3.0
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateNoProdId()
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateDoubleCalScale()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
CALSCALE:GREGORIAN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateDoubleMethod()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateTwoMasterEvents()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(1, count($vcal->validate()));
    }

    public function testValidateOneMasterEvent()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        $this->assertEquals(0, count($vcal->validate()));
    }

    public function testGetBaseComponent()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);

        $result = $vcal->getBaseComponent();
        $this->assertEquals('test', $result->SUMMARY->getValue());
    }

    public function testGetBaseComponentNoResult()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
RECURRENCE-ID;VALUE=DATE:20111202
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);

        $result = $vcal->getBaseComponent();
        $this->assertNull($result);
    }

    public function testGetBaseComponentWithFilter()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);

        $result = $vcal->getBaseComponent('VEVENT');
        $this->assertEquals('test', $result->SUMMARY->getValue());
    }

    public function testGetBaseComponentWithFilterNoResult()
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VTODO
SUMMARY:test
UID:foo
DTSTAMP:20140122T234434Z
END:VTODO
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);

        $result = $vcal->getBaseComponent('VEVENT');
        $this->assertNull($result);
    }

    public function testNoComponents()
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
END:VCALENDAR
ICS;

        $this->assertValidate(
            $input,
            0,
            3,
           'An iCalendar object must have at least 1 component.'
        );
    }

    public function testCalDAVNoComponents()
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VTIMEZONE
TZID:America/Toronto
END:VTIMEZONE
END:VCALENDAR
ICS;

        $this->assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
           'A calendar object on a CalDAV server must have at least 1 component (VTODO, VEVENT, VJOURNAL).'
        );
    }

    public function testCalDAVMultiUID()
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VEVENT
UID:foo
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
BEGIN:VEVENT
UID:bar
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
           'A calendar object on a CalDAV server may only have components with the same UID.'
        );
    }

    public function testCalDAVMultiComponent()
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VEVENT
UID:foo
RECURRENCE-ID:20150109T185200Z
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
BEGIN:VTODO
UID:foo
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VTODO
END:VCALENDAR
ICS;

        $this->assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
           'A calendar object on a CalDAV server may only have 1 type of component (VEVENT, VTODO or VJOURNAL).'
        );
    }

    public function testCalDAVMETHOD()
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:vobject
BEGIN:VEVENT
UID:foo
RECURRENCE-ID:20150109T185200Z
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
           'A calendar object on a CalDAV server MUST NOT have a METHOD property.'
        );
    }

    public function testNodeInValidationErrorHasLineIndexAndLineStringProps(): void
    {
        $defectiveInput = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:vobject
BEGIN:VEVENT
UID:foo
CLASS:PUBLIC
DTSTART;VALUE=DATE:19931231
DTSTAMP:20240422T070855Z
CREATED:
LAST-MODIFIED:
DESCRIPTION:bar
END:VEVENT
ICS;

        $vcal = VObject\Reader::read($defectiveInput);
        $result = $vcal->validate();
        $warningMessages = [];
        foreach ($result as $error) {
            $warningMessages[] = $error['message'];
        }
        self::assertCount(2, $result, 'We expected exactly 2 validation messages, instead we got '.count($result).' results:'.implode(', ', $warningMessages));
        foreach ($result as $idx => $warning) {
            self::assertArrayHasKey('node', $warning);
            self::assertInstanceOf(VObject\Property\ICalendar\DateTime::class, $warning['node']);
            if (method_exists($this, 'assertObjectHasProperty')) {
                self::assertObjectHasProperty('lineIndex', $warning['node']);
                self::assertObjectHasProperty('lineString', $warning['node']);
            } else {
                // Fall back to the older method name known to older versions of phpunit.
                self::assertObjectHasAttribute('lineIndex', $warning['node']);
                self::assertObjectHasAttribute('lineString', $warning['node']);
            }
            switch ($idx) {
                case 0:
                    self::assertEquals('10', $warning['node']->lineIndex);
                    self::assertEquals('CREATED:', $warning['node']->lineString);
                    break;
                case 1:
                    self::assertEquals('11', $warning['node']->lineIndex);
                    self::assertEquals('LAST-MODIFIED:', $warning['node']->lineString);
                    break;
            }
        }
    }

    public function assertValidate($ics, $options, $expectedLevel, $expectedMessage = null)
    {
        $vcal = VObject\Reader::read($ics);
        $result = $vcal->validate($options);

        $this->assertValidateResult($result, $expectedLevel, $expectedMessage);
    }

    public function assertValidateResult($input, $expectedLevel, $expectedMessage = null)
    {
        $messages = [];
        foreach ($input as $warning) {
            $messages[] = $warning['message'];
        }

        if (0 === $expectedLevel) {
            $this->assertEquals(0, count($input), 'No validation messages were expected. We got: '.implode(', ', $messages));
        } else {
            $this->assertEquals(1, count($input), 'We expected exactly 1 validation message, We got: '.implode(', ', $messages));

            $this->assertEquals($expectedMessage, $input[0]['message']);
            $this->assertEquals($expectedLevel, $input[0]['level']);
        }
    }
}
