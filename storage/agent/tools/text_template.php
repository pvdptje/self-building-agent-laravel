<?php

$toolDefinition_text_template = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_template',
    'description' => 'Perform simple variable substitution on a text template. Replaces {{variable}} placeholders with provided values. Useful for generating messages, reports, and code snippets.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'template' => 
        array (
          'type' => 'string',
          'description' => 'Template string with {{variable}} placeholders',
        ),
        'values' => 
        array (
          'type' => 'object',
          'description' => 'Object/dictionary of variable names to values',
        ),
      ),
      'required' => 
      array (
        0 => 'template',
        1 => 'values',
      ),
    ),
  ),
);

if (! function_exists('text_template')) {
    function text_template($template, $values)
    {
        if (!is_array($values)) {
            return json_encode(['error' => 'Values must be an associative array (object)']);
        }
        $result = preg_replace_callback(
            '/\{\{(\w+)\}\}/',
            function($matches) use ($values) {
                $key = $matches[1];
                return isset($values[$key]) ? (string)$values[$key] : $matches[0];
            },
            $template
        );
        $unresolved = [];
        preg_match_all('/\{\{(\w+)\}\}/', $result, $unresolvedMatches);
        return json_encode([
            'result' => $result,
            'resolved_count' => count($values),
            'unresolved_placeholders' => $unresolvedMatches[1] ?? []
        ]);
    }
}
