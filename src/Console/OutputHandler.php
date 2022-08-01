<?php

namespace Rowles\Console;

use Illuminate\Console\OutputStyle;

class OutputHandler
{
    /**
     * @param array $process
     * @param OutputStyle $output
     * @param string $identifier
     */
    public static function handle(array $process, OutputStyle $output, string $identifier)
    {
        $console = new OutputFormatter($output);

        if ($process['status'] === 'error') {
            if ($process['errors'][$identifier] > 0) {
                $console->error('failed to generate ' . $process['errors'][$identifier] . ' ' . $identifier);
            } else {
                $console->error('unspecified error.');
            }
        } else {
            $console->success($identifier . ' successfully generated.');
        }
    }
}