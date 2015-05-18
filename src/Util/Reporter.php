<?hh // strict

namespace HackPack\HackUnit\Util;

use HackPack\HackUnit\Event\Failure;
use HackPack\HackUnit\Event\Skip;
use HackPack\HackUnit\Event\Success;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\Format\StyleGroup;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\Format\Style;

class Reporter
{
    private bool $colors = false;
    private ?float $starttime = null;

    private Vector<Failure> $failEvents = Vector{};
    private Vector<Skip> $skipEvents = Vector{};

    private int $assertCount = 0;
    private int $successCount = 0;
    private int $passCount = 0;
    private int $testCount = 0;

    public function __construct(private \kilahm\Clio\Clio $clio)
    {
    }

    public function identifyPackage() : void
    {
        $message = 'HackUnit by HackPack version ' . Options::VERSION;
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::info());
        }
        $this->clio->line('');
        $this->clio->line($message);
        $this->clio->line('');
    }

    public function startTiming() : void
    {
        $this->starttime = microtime(true);
    }

    public function withColor() : void
    {
        $this->colors = true;
    }

    public function reportFailure(Failure $event) : void
    {
        $this->failEvents->add($event);
        $this->testCount++;
        $this->assertCount++;
        $message = 'F';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::error());
        }
        $this->clio->show($message);
    }

    public function reportSkip(Skip $event) : void
    {
        $this->skipEvents->add($event);
        $this->testCount++;
    }

    public function reportSuccess() : void
    {
        $this->successCount++;
        $this->assertCount++;
    }

    public function reportPass() : void
    {
        $message = '.';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::success());
        }
        $this->clio->show($message);
        $this->testCount++;
        $this->passCount++;
    }

    public function reportUntestedException(\Exception $e) : void
    {
        $message = 'Fatal exception thrown in ' . $e->getFile() . ' on line ' . $e->getLine() . '.';
        if($this->colors) {
            $message = $this->clio->style($message)->with(Style::error());
        }

        $this->clio->line(PHP_EOL);
        $this->clio->line($message);
        $this->clio->line('Exception message:');
        $this->clio->line($e->getMessage());
    }

    public function displaySummary() : void
    {
        // Blank line between the dots and the summary
        $this->clio->line(PHP_EOL);
        $this->clio->line($this->timeReport());
        $this->clio->line($this->testSummary());
        $this->clio->show($this->errorReport());
    }

    public function testSummary() : string
    {
        $successCount = (string)$this->successCount;
        $passCount = (string)$this->passCount;
        $failedCount = (string)$this->failEvents->count();
        $skipCount = (string)$this->skipEvents->count();

        if($this->colors) {
            $successCount = $this->clio->style($successCount)->with(Style::success());
            $passCount = $this->clio->style($passCount)->with(Style::success());

            $failedStyle = $failedCount === '0' ? Style::success() : Style::error();
            $skipStyle = $skipCount === '0' ? Style::success() : Style::warn();

            $failedCount = $this->clio->style($failedCount)->with($failedStyle);
            $skipCount = $this->clio->style($skipCount)->with($skipStyle);
        }

        return sprintf(
            'Assertions: %s/%d Tests: %s/%d Failed: %s Skipped %s',
            $successCount,
            $this->assertCount,
            $passCount,
            $this->testCount,
            $failedCount,
            $skipCount,
        );
    }

    public function errorReport() : string
    {
        return 'Report HERE';
    }

    private function timeReport() : string
    {
        if($this->starttime !== null) {
            $start = $this->starttime;
            $message = sprintf('Finished testing in %.2f seconds.', (float)(microtime(true) - $start));
        } else {
            $message = 'Finished testing.';
        }

        if($this->colors) {
            return $this->clio->style($message)->fg(TextColor::blue)->render();
        }
        return $message;
    }
}