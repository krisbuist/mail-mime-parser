<?php

namespace ZBateson\MailMimeParser\Header\Consumer;

use PHPUnit\Framework\TestCase;

/**
 * Description of AddressEmailConsumerTest
 *
 * @group Consumers
 * @group AddressConsumer
 * @covers ZBateson\MailMimeParser\Header\Consumer\AddressConsumer
 * @covers ZBateson\MailMimeParser\Header\Consumer\AbstractConsumer
 * @author Zaahid Bateson
 */
class AddressConsumerTest extends TestCase
{
    // @phpstan-ignore-next-line
    private $addressConsumer;

    protected function setUp() : void
    {
        $charsetConverter = $this->getMockBuilder(\ZBateson\MbWrapper\MbWrapper::class)
            ->setMethods(['__toString'])
            ->getMock();
        $pf = $this->getMockBuilder(\ZBateson\MailMimeParser\Header\Part\HeaderPartFactory::class)
            ->setConstructorArgs([$charsetConverter])
            ->setMethods(['__toString'])
            ->getMock();
        $mlpf = $this->getMockBuilder(\ZBateson\MailMimeParser\Header\Part\MimeLiteralPartFactory::class)
            ->setConstructorArgs([$charsetConverter])
            ->setMethods(['__toString'])
            ->getMock();
        $cs = $this->getMockBuilder(\ZBateson\MailMimeParser\Header\Consumer\ConsumerService::class)
            ->setConstructorArgs([$pf, $mlpf])
            ->setMethods(['__toString'])
            ->getMock();
        $this->addressConsumer = new AddressConsumer($cs, $pf);
    }

    public function testConsumeEmail() : void
    {
        $email = 'Max.Payne@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $address = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressPart::class, $address);
        $this->assertEquals('', $address->getName());
        $this->assertEquals($email, $address->getEmail());
    }

    public function testConsumeEmailWithSpaces() : void
    {
        $email = "Max\n\t  .Payne@AddressUnknown.com";
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertEquals('Max.Payne@AddressUnknown.com', $ret[0]->getEmail());
    }

    public function testConsumeEmailName() : void
    {
        $email = 'Max Payne <Max.Payne@AddressUnknown.com>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $address = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressPart::class, $address);
        $this->assertEquals('Max.Payne@AddressUnknown.com', $address->getEmail());
        $this->assertEquals('Max Payne', $address->getName());
    }

    public function testConsumeMimeEncodedName() : void
    {
        $email = '=?US-ASCII?Q?Kilgore_Trout?= <Kilgore.Trout@Iliyum.ny>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $address = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressPart::class, $address);
        $this->assertEquals('Kilgore.Trout@Iliyum.ny', $address->getEmail());
        $this->assertEquals('Kilgore Trout', $address->getName());
    }

    public function testConsumeEmailWithComments() : void
    {
        // can't remember any longer if this is how it should be handled
        // need to review RFC
        $email = 'Max(imum).Payne (comment)@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $address = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressPart::class, $address);
        $this->assertEquals('Max.Payne@AddressUnknown.com', $address->getEmail());
    }

    public function testConsumeEmailWithQuotes() : void
    {
        $email = 'Max"(imum)..Payne (not a comment)"@AddressUnknown.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $address = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressPart::class, $address);
        $this->assertEquals('Max"(imum)..Payne (not a comment)"@AddressUnknown.com', $address->getEmail());
    }

    public function testConsumeQuotedEmailLocalPartWithSpaces() : void
    {
        $email = "\"Max\n\t  .Payne\"@AddressUnknown.com";
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertEquals("\"Max\t  .Payne\"@AddressUnknown.com", $ret[0]->getEmail());
    }

    public function testConsumeVeryStrangeQuotedEmailLocalPart() : void
    {
        $email = '"very.(),:;<>[]\"  .VERY.\"very@\\\\ \"very\".unusual"@strange.example.com';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertEquals($email, $ret[0]->getEmail());
    }

    public function testConsumeAddressGroup() : void
    {
        $email = 'Senate: Caesar@Dictator.com,Cicero@Philosophy.com, Marc Antony <MarcAntony@imawesome.it>';
        $ret = $this->addressConsumer->__invoke($email);
        $this->assertNotEmpty($ret);
        $this->assertCount(1, $ret);

        $addressGroup = $ret[0];
        $this->assertInstanceOf('\\' . \ZBateson\MailMimeParser\Header\Part\AddressGroupPart::class, $addressGroup);
        $this->assertEquals('Senate', $addressGroup->getName());
    }
}
