<?php

namespace App\Agent;

class PromptRepository
{
    public function __construct(private string $path)
    {
    }

    /**
     * @return array<int, array{id: string, title: string, tags: array<int, string>, file: string}>
     */
    public function all(): array
    {
        $prompts = [];

        foreach (glob($this->path.'/*.md') ?: [] as $file) {
            $prompt = $this->parse($file);

            if ($prompt !== null) {
                unset($prompt['body']);
                $prompts[] = $prompt;
            }
        }

        return $prompts;
    }

    /**
     * @return array{id: string, title: string, tags: array<int, string>, file: string, body: string}|null
     */
    public function find(string $id): ?array
    {
        foreach (glob($this->path.'/*.md') ?: [] as $file) {
            $prompt = $this->parse($file);

            if ($prompt !== null && $prompt['id'] === $id) {
                return $prompt;
            }
        }

        return null;
    }

    /**
     * Search prompt ids, titles, tags, and content for a case-insensitive term.
     *
     * @return array<int, array{id: string, title: string, tags: array<int, string>, file: string}>
     */
    public function search(string $query): array
    {
        $query = mb_strtolower(trim($query));
        $matches = [];

        foreach (glob($this->path.'/*.md') ?: [] as $file) {
            $prompt = $this->parse($file);

            if ($prompt === null) {
                continue;
            }

            $haystack = mb_strtolower(implode("\n", [
                $prompt['id'],
                $prompt['title'],
                implode(' ', $prompt['tags']),
                $prompt['body'],
            ]));

            if ($query === '' || str_contains($haystack, $query)) {
                unset($prompt['body']);
                $matches[] = $prompt;
            }
        }

        return $matches;
    }

    /**
     * @return array{id: string, title: string, tags: array<int, string>, file: string, body: string}|null
     */
    private function parse(string $file): ?array
    {
        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $contents = str_replace("\r\n", "\n", $contents);

        if (! preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', $contents, $matches)) {
            return null;
        }

        $meta = ['id' => null, 'title' => null, 'tags' => []];

        foreach (explode("\n", $matches[1]) as $line) {
            if (! str_contains($line, ':')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode(':', $line, 2));

            if ($key === 'tags') {
                $meta['tags'] = array_values(array_filter(array_map('trim', explode(',', trim($value, '[] ')))));
            } elseif ($key === 'id' || $key === 'title') {
                $meta[$key] = $value;
            }
        }

        if ($meta['id'] === null) {
            return null;
        }

        return [
            'id' => $meta['id'],
            'title' => $meta['title'] ?? $meta['id'],
            'tags' => $meta['tags'],
            'file' => basename($file),
            'body' => trim($matches[2]),
        ];
    }
}
