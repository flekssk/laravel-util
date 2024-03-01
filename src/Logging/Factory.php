<?php

declare(strict_types=1);

namespace FKS\Logging;

use Illuminate\Support\Env;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use FKS\Logging\Formatters\LogJsonFormatter;
use FKS\Logging\Handlers\PrettyConsoleOutputHandler;
use FKS\Logging\Processors\GkeGeneralInfoProcessor;
use FKS\Logging\Processors\GkeWebProcessor;

class Factory
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger($config['name'] ?? 'gke-log');

        if (Env::get('TERM') === 'xterm') {
            $logger->pushHandler(new PrettyConsoleOutputHandler());
            return $logger;
        }

        $streamHandler = new StreamHandler('php://stdout');
        $streamHandler->setFormatter(new LogJsonFormatter(
            batchMode: JsonFormatter::BATCH_MODE_JSON,
            appendNewline: true,
            ignoreEmptyContextAndExtra: true,
            includeStacktraces: true,
            prependNewline: true,
        ));
        $logger->pushHandler($streamHandler);

        $logger->pushProcessor(app(GkeWebProcessor::class));
        $logger->pushProcessor(app(GkeGeneralInfoProcessor::class));

        return $logger;
    }
}
