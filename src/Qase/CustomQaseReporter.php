<?php
declare(strict_types=1);

namespace Xentral\LaravelTesting\Qase;

use Illuminate\Support\Str;
use PHPUnit\Event\Code\TestMethod;
use Qase\PhpCommons\Interfaces\ReporterInterface;
use Qase\PhpCommons\Models\Attachment;
use Qase\PhpCommons\Models\Result;
use Qase\PhpCommons\Utils\Signature;
use Qase\PHPUnitReporter\Attributes\AttributeParserInterface;
use Qase\PHPUnitReporter\QaseReporterInterface;

class CustomQaseReporter implements QaseReporterInterface
{
    private static ?CustomQaseReporter $instance;

    private array $testResults = [];

    private AttributeParserInterface $attributeParser;

    private ReporterInterface $reporter;

    private ?string $currentKey = null;

    private function __construct(AttributeParserInterface $attributeParser, ReporterInterface $reporter)
    {
        $this->attributeParser = $attributeParser;
        $this->reporter = $reporter;
    }

    public static function getInstance(AttributeParserInterface $attributeParser, ReporterInterface $reporter): CustomQaseReporter
    {
        if (! isset(self::$instance)) {
            self::$instance = new CustomQaseReporter($attributeParser, $reporter);
        }

        return self::$instance;
    }

    public static function getInstanceWithoutInit(): ?CustomQaseReporter
    {
        return self::$instance;
    }

    public static function title(string $title): void
    {
        self::$instance?->updateTitle($title);
    }

    public static function comment(string $comment): void
    {
        self::$instance?->addComment($comment);
    }

    public static function attachScenario(string $value): void
    {
        self::$instance?->addAttachment(Attachment::createContentAttachment('scenario', $value, 'text/plain'));
    }

    public function startTestRun(): void
    {
        $this->reporter->startRun();
    }

    public function completeTestRun(): void
    {
        $this->reporter->completeRun();
    }

    public function startTest(TestMethod $test): void
    {
        $key = $this->getTestKey($test);

        $metadata = $this->attributeParser->parseAttribute($test->className(), $test->methodName());

        $testResult = new Result;

        if (! empty($metadata->qaseIds)) {
            $testResult->testOpsIds = $metadata->qaseIds;
        }

        if (empty($metadata->suites)) {
            return;
        }

        foreach ($metadata->suites as $suite) {
            $testResult->relations->addSuite($suite);
        }

        $testResult->fields = $metadata->fields;
        $testResult->params = $metadata->parameters;
        $testResult->signature = $this->createSignature($test, $metadata->qaseIds, $metadata->suites, $metadata->parameters);
        $testResult->execution->setThread($this->getThread());

        $testResult->title = $metadata->title ?? Str::of($test->methodName())->after('test')->headline()->toString();

        $this->currentKey = $key;
        $this->testResults[$key] = $testResult;
    }

    public function updateStatus(TestMethod $test, string $status, ?string $message = null, ?string $stackTrace = null): void
    {
        $key = $this->getTestKey($test);
        if (! $result = $this->getResult($this->getTestKey($test))) {
            return;
        }

        $result->execution->setStatus($status);
        $this->handleMessage($key, $message);

        if ($stackTrace) {
            $result->execution->setStackTrace($stackTrace);
        }
    }

    public function completeTest(TestMethod $test): void
    {
        if (! $result = $this->getResult($this->getTestKey($test))) {
            return;
        }
        $result->execution->finish();

        $this->reporter->addResult($result);
        $this->currentKey = null;
    }

    public function addComment(string $message): void
    {
        if (! $result = $this->getResult($this->currentKey)) {
            return;
        }

        $result->message = $result->message.$message."\n";
    }

    public function addField(string $name, string $value): void
    {
        if (! $result = $this->getResult($this->currentKey)) {
            return;
        }

        $result->fields[$name] = $value;
    }

    public function updateTitle(string $title): void
    {
        if (! $result = $this->getResult($this->currentKey)) {
            return;
        }

        $result->title = $title;
    }

    public function addAttachment(mixed $input): void
    {
        if (! $result = $this->getResult($this->currentKey)) {
            return;
        }
        if (is_array($input)) {
            foreach ($input as $item) {
                $this->addAttachment($item);
            }

            return;
        }
        if (is_string($input) && file_exists($input)) {
            $result->attachments[] = Attachment::createFileAttachment($input);

            return;
        }
        if ($input instanceof Attachment) {
            $result->attachments[] = $input;

            return;
        }
        if (is_object($input)) {
            $data = (array) $input;
            $this->testResults[$this->currentKey]->attachments[] = Attachment::createContentAttachment(
                $data['title'] ?? 'attachment',
                $data['content'] ?? null,
                $data['mime'] ?? null
            );
        }
    }

    protected function getResult(?string $key = null): ?Result
    {
        if (! $key) {
            return null;
        }
        if (! isset($this->testResults[$key])) {
            return null;
        }
        if (! $this->testResults[$key] instanceof Result) {
            return null;
        }

        return $this->testResults[$key];
    }

    private function handleMessage(string $key, ?string $message): void
    {
        if (! isset($this->testResults[$key])) {
            return;
        }
        if ($message) {
            $this->testResults[$key]->message = $this->testResults[$key]->message."\n".$message."\n";
        }
    }

    private function getTestKey(TestMethod $test): string
    {
        return $test->className().'::'.$test->methodName().':'.$test->line();
    }

    private function createSignature(TestMethod $test, ?array $ids = null, ?array $suites = null, ?array $params = null): string
    {
        $finalSuites = [];
        if ($suites) {
            $finalSuites = $suites;
        } else {
            $suites = explode('\\', $test->className());
            foreach ($suites as $suite) {
                $finalSuites[] = $suite;
            }
        }

        return Signature::generateSignature($ids, $finalSuites, $params);
    }

    private function getThread(): string
    {
        return $_ENV['TEST_TOKEN'] ?? 'default';
    }
}
