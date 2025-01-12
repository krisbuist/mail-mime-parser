<?php

namespace ZBateson\MailMimeParser\Message;

use PHPUnit\Framework\TestCase;

/**
 * PartFilterTest
 *
 * @group PartFilter
 * @group Message
 * @covers ZBateson\MailMimeParser\PartFilter
 * @author Zaahid Bateson
 */
class PartFilterTest extends TestCase
{
    public function testAttachmentFilter() : void
    {
        $callback = PartFilter::fromAttachmentFilter();

        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMessagePart::class);
        $part->method('getContentType')->willReturnOnConsecutiveCalls('text/plain', 'text/plain', 'text/html', 'text/html', 'blah');
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('inline', 'attachment', 'inline', 'attachment', 'blah');

        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));

        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('getContentType')->willReturnOnConsecutiveCalls('text/plain', 'text/html', 'blah');
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('attachment', 'attachment', 'blah');
        $part->method('isMultiPart')->willReturnOnConsecutiveCalls(true, false, false);
        $part->method('isSignaturePart')->willReturnOnConsecutiveCalls(true, false);
        $this->assertFalse($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
    }

    public function testHeaderValueFilterWithMessagePart() : void
    {
        $callback = PartFilter::fromHeaderValue('detective', 'peralta');
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMessagePart::class);
        $this->assertFalse($callback($part));
    }

    public function testHeaderValueFilterWithSignaturePart() : void
    {
        $callback = PartFilter::fromHeaderValue('detective', 'peralta');
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->expects($this->once())->method('isSignaturePart')->willReturn(true);
        $part->expects($this->never())->method('getHeaderValue');
        $this->assertFalse($callback($part));
    }

    public function testHeaderValueFilterWithMimePart() : void
    {
        $callback = PartFilter::fromHeaderValue('detective', 'peralta');
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('isSignaturePart')->willReturnOnConsecutiveCalls(false, false, false, true, false, true);
        $part->method('getHeaderValue')->with('detective')->willReturnOnConsecutiveCalls(
            'PERAlta',
            'peralta',
            'HOLT!',
            'PERAlta',
            'peralta',
            'HOLT!'
        );
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));

        $callback = PartFilter::fromHeaderValue('detective', 'peralta', false);
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
    }

    public function testContentTypeFilter() : void
    {
        $callback = PartFilter::fromContentType('text/plain');

        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMessagePart::class);
        $part->method('getContentType')->willReturnOnConsecutiveCalls('text/plain', 'text/html', 'text/plain', 'blah');
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertFalse($callback($part));
    }

    public function testInlineContentTypeFilter() : void
    {
        $callback = PartFilter::fromInlineContentType('text/plain');

        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMessagePart::class);
        $part->method('getContentType')->willReturnOnConsecutiveCalls('text/plain', 'text/html', 'text/plain', 'blah');
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('inline', 'attachment', 'attoochment', 'attachment', 'blah');
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertFalse($callback($part));
    }

    public function testDispositionFilter() : void
    {
        $callback = PartFilter::fromDisposition('needy');
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMessagePart::class);
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('inline', 'noodly', 'NEEDY', 'attachment', 'needy');
        $this->assertFalse($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
    }

    public function testDispositionFilterNoMultiOrSignedParts() : void
    {
        $callback = PartFilter::fromDisposition('needy');
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('needy', 'needy', 'needy');
        $part->method('isMultiPart')->willReturnOnConsecutiveCalls(true, false, false);
        $part->method('isSignaturePart')->willReturnOnConsecutiveCalls(true, false);
        $this->assertFalse($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
    }

    public function testDispositionFilterWithMultiParts() : void
    {
        $callback = PartFilter::fromDisposition('greedy', true);
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('greedy', 'greedy', 'greedy');
        $part->expects($this->never())->method('isMultiPart');
        $part->method('isSignaturePart')->willReturnOnConsecutiveCalls(false, true, false);
        $this->assertTrue($callback($part));
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
    }

    public function testDispositionFilterWithSignatureParts() : void
    {
        $callback = PartFilter::fromDisposition('seedy', false, true);
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('seedy', 'seedy', 'seedy');
        $part->method('isMultiPart')->willReturnOnConsecutiveCalls(true, false, false);
        $part->expects($this->never())->method('isSignaturePart');
        $this->assertFalse($callback($part));
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));
    }

    public function testDispositionFilterWithMultiAndSignatureParts() : void
    {
        $callback = PartFilter::fromDisposition('seedy', true, true);
        $part = $this->getMockForAbstractClass(\ZBateson\MailMimeParser\Message\IMimePart::class);
        $part->method('getContentDisposition')->willReturnOnConsecutiveCalls('seedy', 'seedy', 'seedy');
        $part->expects($this->never())->method('isMultiPart');
        $part->expects($this->never())->method('isSignaturePart');
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));
        $this->assertTrue($callback($part));
    }
}
