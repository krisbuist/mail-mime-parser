<?php

namespace ZBateson\MailMimeParser\Header\Part;

use PHPUnit\Framework\TestCase;
use ZBateson\MbWrapper\MbWrapper;

/**
 * Description of ParameterTest
 *
 * @group HeaderParts
 * @group SplitParameterToken
 * @covers ZBateson\MailMimeParser\Header\Part\SplitParameterToken
 * @covers ZBateson\MailMimeParser\Header\Part\HeaderPart
 * @author Zaahid Bateson
 */
class SplitParameterTokenTest extends TestCase
{
    // @phpstan-ignore-next-line
    private $charsetConverter;

    protected function setUp() : void
    {
        $this->charsetConverter = new MbWrapper();
    }

    public function testGetNameAndNullLanguage() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, '  Drogo  ');
        $this->assertEquals('Drogo', $part->getName());
        $this->assertNull($part->getLanguage());
    }

    public function testLanguageIsSetBeforeGetValue() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, '  Drogo  ');
        $part->addPart('unknown\'Dothraki\'blah', true, '');
        $this->assertEquals('Dothraki', $part->getLanguage());
    }

    public function testLanguageIsNullForEmptyEncodedLanguage() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('unknown\'\'Khal%20Drogo,%20', true, 0);
        $this->assertNull($part->getLanguage());
    }

    public function testAddLiteralPart() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khal Drogo', false, 0);
        $this->assertEquals('Khal Drogo', $part->getValue());
    }

    public function testAddMultipleLiteralParts() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khal ', false, 0);
        $part->addPart('Drogo', false, 1);
        $this->assertEquals('Khal Drogo', $part->getValue());
    }

    public function testAddUnsortedMultipleLiteralParts() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Dro', false, 1);
        $part->addPart('Khal ', false, 0);
        $part->addPart('go', false, 2);
        $this->assertEquals('Khal Drogo', $part->getValue());
    }

    public function testAddEncodedPart() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khal%20Drogo', true, 0);
        $this->assertEquals('Khal Drogo', $part->getValue());
    }

    public function testAddMultiEncodedPart() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khal%20Drogo,%20', true, 0);
        $part->addPart('Ruler%20of%20', true, 1);
        $part->addPart('his%20', true, 2);
        $part->addPart('Khalisar', true, 3);
        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
    }

    public function testAddUnsortedMultiEncodedPart() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khalisar', true, 3);
        $part->addPart('Khal%20Drogo,%20', true, 0);
        $part->addPart('his%20', true, 2);
        $part->addPart('Ruler%20of%20', true, 1);
        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
    }

    public function testAddUnsortedMultiEncodedPartWithLanguage() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khalisar', true, 3);
        $part->addPart('us-ascii\'dothraki-LHAZ\'Khal%20Drogo,%20', true, 0);
        $part->addPart('his%20', true, 2);
        $part->addPart('Ruler%20of%20', true, 1);
        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
        $this->assertEquals('dothraki-LHAZ', $part->getLanguage());
    }

    public function testLanguageNotSetOnNonZeroPart() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khalisar', true, 3);
        $part->addPart('us-ascii\'dothraki-LHAZ\'Khal%20Drogo,%20', true, 0);
        $part->addPart('his%20', true, 2);
        $part->addPart('charset\'other-lang\'Ruler%20of%20', true, 1);
        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
        $this->assertEquals('dothraki-LHAZ', $part->getLanguage());
    }

    public function testAddMixedEncodedAndNonEncodedCombinesCharsetConversion() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('us-ascii\'dothraki-LHAZ\'Khal%20Drogo,%20', true, 0);
        $part->addPart('Ruler%20of%20', true, 1);
        $part->addPart('his ', false, 2);
        $part->addPart('Khalisar', true, 3);

        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
    }

    public function testAddUnsortedMixedEncodedAndNonEncodedCombinesCharsetConversion() : void
    {
        $part = new SplitParameterToken($this->charsetConverter, 'name');
        $part->addPart('Khalisar', true, 3);
        $part->addPart('Ruler%20of%20', true, 1);
        $part->addPart('us-ascii\'dothraki-LHAZ\'Khal%20Drogo,%20', true, 0);
        $part->addPart('his ', false, 2);

        $this->assertEquals('Khal Drogo, Ruler of his Khalisar', $part->getValue());
    }
}
