<?php

namespace ZBateson\MailMimeParser\Header\Part;

use PHPUnit\Framework\TestCase;
use ZBateson\MbWrapper\MbWrapper;

/**
 * Description of ParameterTest
 *
 * @group HeaderParts
 * @group ParameterPart
 * @covers ZBateson\MailMimeParser\Header\Part\ParameterPart
 * @covers ZBateson\MailMimeParser\Header\Part\HeaderPart
 * @author Zaahid Bateson
 */
class ParameterPartTest extends TestCase
{
    // @phpstan-ignore-next-line
    private $charsetConverter;

    protected function setUp() : void
    {
        $this->charsetConverter = new MbWrapper();
    }

    public function testBasicNameValuePair() : void
    {
        $part = new ParameterPart($this->charsetConverter, 'Name', 'Value');
        $this->assertEquals('Name', $part->getName());
        $this->assertEquals('Value', $part->getValue());
    }

    public function testMimeValue() : void
    {
        $part = new ParameterPart($this->charsetConverter, 'name', '=?US-ASCII?Q?Kilgore_Trout?=');
        $this->assertEquals('name', $part->getName());
        $this->assertEquals('Kilgore Trout', $part->getValue());
    }

    public function testMimeName() : void
    {
        $part = new ParameterPart($this->charsetConverter, '=?US-ASCII?Q?name?=', 'Kilgore');
        $this->assertEquals('name', $part->getName());
        $this->assertEquals('Kilgore', $part->getValue());
    }

    public function testNameValueNotDecodedWithLanguage() : void
    {
        $part = new ParameterPart($this->charsetConverter, '=?US-ASCII?Q?name?=', '=?US-ASCII?Q?Kilgore_Trout?=', 'Kurty');
        $this->assertEquals('=?US-ASCII?Q?name?=', $part->getName());
        $this->assertEquals('=?US-ASCII?Q?Kilgore_Trout?=', $part->getValue());
    }

    public function testGetLanguage() : void
    {
        $part = new ParameterPart($this->charsetConverter, 'name', 'Drogo', 'Dothraki');
        $this->assertEquals('Dothraki', $part->getLanguage());
    }
}
