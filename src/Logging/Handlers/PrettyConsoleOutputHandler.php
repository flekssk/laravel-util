<?php

namespace FKS\Logging\Handlers;

use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Support\Arr;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

class PrettyConsoleOutputHandler extends AbstractProcessingHandler
{
    public function getLevelMessage($level_name): string
    {
        $map = [
            'emergency' => 'error',
            'alert' => 'error',
            'critical' => 'error',
            'error' => 'error',
            'warning' => 'error',
            'notice' => 'error',
            'info' => 'info',
            'debug' => 'info',
            'log' => 'info',
        ];

        $tag = $map[strtolower($level_name)] ?? 'info';

        return "<$tag>" . $level_name . "</$tag>";
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getCurrentDateTime(): string
    {
        return (new DateTime(timezone: new DateTimeZone('America/Detroit')))->format('Y-m-d H:i:s') . " (New York)";
    }

    protected function write(array|LogRecord $record): void
    {
        $labels = Arr::wrap($record['context']['labels'] ?? null);
        if ($labels) {
            unset($record['context']['labels']);
        }

        $message = $record['message'] ?? null;
        $context = Arr::wrap($record['context'] ?? null);

        $table = new Table(new ConsoleOutput());
        $table->setColumnMaxWidth(0, 60);
        $table->setColumnMaxWidth(1, 150);

        $tableStyle = new TableStyle();

        $table->setStyle($tableStyle);

        $separator = new TableSeparator();
        $rows = [
            [(new TableCell($this->getCurrentDateTime(), [
                'colspan' => 2,
                'style' => (new TableCellStyle(['align' => 'center']))
            ]))],
            $separator,
            [$this->getLevelMessage($record['level_name']), $message],
            $separator,
        ];

        if ($labels) {
            $rows[] = [(new TableCell('LABELS', [
                'colspan' => 2,
                'style' => (new TableCellStyle(['align' => 'center']))
            ]))];
            $rows[] = $separator;

            foreach ($labels as $key => $label) {
                $label = is_array($label) ? implode(',', $label) : $label;
                $rows[] = [
                    $key,
                    "<info>$label</info>"
                ];
            }
        }

        if ($context) {
            $rows = array_merge($rows, [
                $separator,
                [(new TableCell('CONTEXT', [
                    'colspan' => 2,
                    'style' => (new TableCellStyle(['align' => 'center']))
                ]))],
                $separator
            ]);

            foreach ($context as $key => $contextItem) {
                $itemOutput = $contextItem;

                if (is_array($contextItem)) {
                    $itemOutput = json_encode($contextItem, JSON_THROW_ON_ERROR);
                }

                $rows[] = [
                    $key,
                    "<info>$itemOutput</info>"
                ];
            }
        }

        $table->setRows($rows);

        $table->render();
    }
}
