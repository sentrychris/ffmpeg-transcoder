<?php

namespace Rowles\Console;

class OutputHandler
{
    /**
     * @param array $process
     * @param $console
     * @param $identifier
     */
    public static function handle(array $process, $console, $identifier)
    {
        $console = new OutputFormatter($console);

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