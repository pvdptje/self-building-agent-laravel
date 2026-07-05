<?php

namespace App\Agent;

use stdClass;

/**
 * json_encode turns an empty PHP array into JSON [], but JSON Schema expects
 * {} in object positions (properties, items, ...). OpenAI-compatible APIs
 * reject tools whose schema contains [] where an object belongs, which would
 * poison every request once one bad tool exists on disk. This normalizer
 * recursively converts ambiguous empty arrays to empty objects.
 */
class SchemaNormalizer
{
    /** Schema keywords whose values are legitimately JSON arrays. */
    private const ARRAY_KEYS = ['required', 'enum', 'examples', 'allOf', 'anyOf', 'oneOf', 'type', 'default'];

    /**
     * Normalize a tool parameters schema. An entirely empty schema becomes
     * the minimal valid object schema.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    public static function normalize(array $parameters): array
    {
        if ($parameters === []) {
            return ['type' => 'object', 'properties' => new stdClass];
        }

        $parameters = self::walk($parameters);
        $parameters['type'] ??= 'object';
        $parameters['properties'] ??= new stdClass;

        return $parameters;
    }

    /**
     * @param array<array-key, mixed> $node
     * @return array<array-key, mixed>
     */
    private static function walk(array $node): array
    {
        foreach ($node as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if ($value === []) {
                $node[$key] = in_array($key, self::ARRAY_KEYS, true) ? [] : new stdClass;
            } else {
                $node[$key] = self::walk($value);
            }
        }

        return $node;
    }
}
