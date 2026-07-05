<?php

namespace App\Agent;

class AgentLogger
{
    public function __construct(private string $directory)
    {
    }

    /**
     * @param array<string, mixed> $entry
     */
    public function promptSwitch(array $entry): void
    {
        $this->append('prompt-lineage.jsonl', $entry);
    }

    /**
     * @param array<string, mixed> $entry
     */
    public function toolChange(array $entry): void
    {
        $this->append('tool-lineage.jsonl', $entry);
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function append(string $file, array $entry): void
    {
        if (! is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        $entry['at'] = date('c');

        file_put_contents(
            $this->directory.'/'.$file,
            json_encode($entry, JSON_UNESCAPED_SLASHES).PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
