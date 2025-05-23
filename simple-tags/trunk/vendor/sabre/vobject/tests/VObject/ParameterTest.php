<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
{
    public function testSetup()
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', 'value');
        $this->assertEquals('NAME', $param->name);
        $this->assertEquals('value', $param->getValue());
    }

    public function testSetupNameLess()
    {
        $card = new Component\VCard();

        $param = new Parameter($card, null, 'URL');
        $this->assertEquals('VALUE', $param->name);
        $this->assertEquals('URL', $param->getValue());
        $this->assertTrue($param->noName);
    }

    public function testModify()
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', null);
        $param->addValue(1);
        $this->assertEquals([1], $param->getParts());

        $param->setParts([1, 2]);
        $this->assertEquals([1, 2], $param->getParts());

        $param->addValue(3);
        $this->assertEquals([1, 2, 3], $param->getParts());

        $param->setValue(4);
        $param->addValue(5);
        $this->assertEquals([4, 5], $param->getParts());
    }

    public function testCastToString()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        $this->assertEquals('value', $param->__toString());
        $this->assertEquals('value', (string) $param);
    }

    public function testCastNullToString()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        $this->assertEquals('', $param->__toString());
        $this->assertEquals('', (string) $param);
    }

    public function testSerialize()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'value');
        $this->assertEquals('NAME=value', $param->serialize());
    }

    public function testSerializeEmpty()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', null);
        $this->assertEquals('NAME=', $param->serialize());
    }

    public function testSerializeComplex()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', ['val1', 'val2;', 'val3^', "val4\n", 'val5"']);
        $this->assertEquals('NAME=val1,"val2;","val3^^","val4^n","val5^\'"', $param->serialize());
    }

    /**
     * iCal 7.0 (OSX 10.9) has major issues with the EMAIL property, when the
     * value contains a plus sign, and it's not quoted.
     *
     * So we specifically added support for that.
     */
    public function testSerializePlusSign()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'EMAIL', 'user+something@example.org');
        $this->assertEquals('EMAIL="user+something@example.org"', $param->serialize());
    }

    public function testIterate()
    {
        $cal = new Component\VCalendar();

        $param = new Parameter($cal, 'name', [1, 2, 3, 4]);
        $result = [];

        foreach ($param as $value) {
            $result[] = $value;
        }

        $this->assertEquals([1, 2, 3, 4], $result);
    }

    public function testSerializeColon()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'va:lue');
        $this->assertEquals('NAME="va:lue"', $param->serialize());
    }

    public function testSerializeSemiColon()
    {
        $cal = new Component\VCalendar();
        $param = new Parameter($cal, 'name', 'va;lue');
        $this->assertEquals('NAME="va;lue"', $param->serialize());
    }
}
