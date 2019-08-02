<?php
declare(strict_types = 1);
namespace Tests\App\Translate;

use App\Translate\TranslatableTrait;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Translation\TranslatorInterface;

class TranslatableTraitTest extends TestCase
{
    public function testTrBasic()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')
            ->with('key')
            ->willReturn('output');

        $trFn = $this->boundTr($translator);

        $this->assertSame('output', $trFn('key'));
    }

    public function testTrSubstitutions()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')
            ->with('key', ['%sub%' => 'ham'])
            ->willReturn('output');

        $trFn = $this->boundTr($translator);

        $this->assertSame('output', $trFn('key', ['%sub%' => 'ham']));
    }

    public function testTrPlurals()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')
            ->with('key %count%', ['%count%' => 2])
            ->willReturn('output');

        $trFn = $this->boundTr($translator);

        $this->assertSame('output', $trFn('key', 2));
    }

    public function testTrSubstitutionsAndPlurals()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('trans')
            ->with('key %count%', ['%sub%' => 'ham', '%count%' => 2])
            ->willReturn('output');

        $trFn = $this->boundTr($translator);

        $this->assertSame('output', $trFn('key', ['%sub%' => 'ham'], 2));
    }

    public function testLocalDateIntl()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->once())->method('getLocale')
            ->willReturn('cy_GB');

        $boundFunction = $this->boundLocalDateIntl($translator);
        $dateTime = new DateTime('2017-08-11 06:00:00');
        $timeZone = new DateTimeZone('Europe/London');
        $result = $boundFunction($dateTime, 'EEE dd MMMM yyyy, HH:mm', $timeZone);
        $this->assertEquals('Gwen 11 Awst 2017, 07:00', $result);
    }

    /**
     * This is funky. It generates a closure that has its scope bound to a
     * mock, which means it has access to call protected functions (i.e. tr).
     * We also need to do some reflection malarkey to set the translateProvider property
     */
    private function boundTr(TranslatorInterface $translator): callable
    {
        $translatable = $this->getMockForTrait(TranslatableTrait::class);

        $reflection = new ReflectionClass($translatable);
        $translatorProperty = $reflection->getProperty('translator');
        $translatorProperty->setAccessible(true);

        $translatorProperty->setValue($translatable, $translator);

        // Define a closure that will call the protected method using "this".
        $barCaller = function (...$args) {
            return $this->tr(...$args);
        };
        // Bind the closure to $translatable's scope.
        return $barCaller->bindTo($translatable, $translatable);
    }

    private function boundLocalDateIntl(TranslatorInterface $translator): callable
    {
        $translatable = $this->getMockForTrait(TranslatableTrait::class);

        $reflection = new ReflectionClass($translatable);
        $translatorProperty = $reflection->getProperty('translator');
        $translatorProperty->setAccessible(true);

        $translatorProperty->setValue($translatable, $translator);

        // Define a closure that will call the protected method using "this".
        $barCaller = function (...$args) {
            return $this->localDateIntl(...$args);
        };
        // Bind the closure to $translatable's scope.
        return $barCaller->bindTo($translatable, $translatable);
    }
}
