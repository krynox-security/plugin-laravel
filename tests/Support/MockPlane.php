<?php

namespace Krynox\Captcha\Tests\Support;

use RuntimeException;

/**
 * Spawns a real local HTTP mock of the Krynox data plane (`php -S` + router.php)
 * so the plugin's Http client is exercised end-to-end over the wire.
 */
final class MockPlane
{
    /** @var resource */
    private $proc;

    private function __construct(
        public readonly string $url,
        private readonly string $stateDir,
        $proc,
    ) {
        $this->proc = $proc;
    }

    public static function start(): self
    {
        $stateDir = sys_get_temp_dir().'/krynox-mock-'.bin2hex(random_bytes(6));
        if (! mkdir($stateDir, 0777, true)) {
            throw new RuntimeException("cannot create state dir $stateDir");
        }

        $port = self::freePort();
        $proc = proc_open(
            [PHP_BINARY, '-S', "127.0.0.1:$port", __DIR__.'/router.php'],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', $stateDir.'/server.log', 'w']],
            $pipes,
            null,
            ['KRYNOX_MOCK_STATE' => $stateDir, 'PATH' => (string) getenv('PATH')]
        );
        if (! is_resource($proc)) {
            throw new RuntimeException('failed to spawn php -S mock plane');
        }

        $plane = new self("http://127.0.0.1:$port", $stateDir, $proc);
        $plane->awaitReady();

        return $plane;
    }

    /** Clear the request log and any scripted response queue. */
    public function reset(): void
    {
        @unlink($this->stateDir.'/requests.log');
        @unlink($this->stateDir.'/queue.json');
    }

    /** @param array<int,array{status:int,body:mixed}> $responses */
    public function queue(array $responses): void
    {
        file_put_contents($this->stateDir.'/queue.json', json_encode($responses), LOCK_EX);
    }

    /** @return array<int,array{method:string,path:string,body:?array}> */
    public function requests(): array
    {
        $log = @file_get_contents($this->stateDir.'/requests.log');
        if ($log === false || $log === '') {
            return [];
        }

        return array_map(
            static fn (string $line): array => json_decode($line, true),
            array_values(array_filter(explode("\n", trim($log))))
        );
    }

    public function stop(): void
    {
        if (is_resource($this->proc)) {
            proc_terminate($this->proc);
            proc_close($this->proc);
        }
        array_map('unlink', glob($this->stateDir.'/*') ?: []);
        @rmdir($this->stateDir);
    }

    private static function freePort(): int
    {
        $sock = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        if ($sock === false) {
            throw new RuntimeException("cannot find free port: $errstr");
        }
        $name = stream_socket_get_name($sock, false);
        fclose($sock);

        return (int) substr((string) strrchr($name, ':'), 1);
    }

    private function awaitReady(): void
    {
        $deadline = microtime(true) + 10.0;
        while (microtime(true) < $deadline) {
            $conn = @fsockopen(parse_url($this->url, PHP_URL_HOST), (int) parse_url($this->url, PHP_URL_PORT), $e, $m, 0.25);
            if ($conn !== false) {
                fclose($conn);

                return;
            }
            usleep(50_000);
        }

        throw new RuntimeException('mock plane did not become ready within 10s');
    }
}
