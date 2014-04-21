<?hh //strict
namespace HackUnit\Core;

abstract class TestCase
{
    public function __construct(protected string $name)
    {
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function expect<T>(T $context): Expectation<T>
    {
        return new Expectation($context);
    }

    public function expectCallable((function(): void) $callable): CallableExpectation
    {
        return new CallableExpectation($callable);
    }

    public function run(TestResult $result): TestResult
    {
        $result->testStarted();
        $this->setUp();
        $class = get_class($this);
        try {
            hphp_invoke_method($this, $class, $this->name, []);
        } catch (\Exception $e) {
            $result->testFailed();
        }
        $this->tearDown();
        return $result;
    }
}