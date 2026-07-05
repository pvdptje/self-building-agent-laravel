<?php

$toolDefinition_text_regex_extract = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_regex_extract',
    'description' => 'Extract all matches of a regex pattern from text. Returns matched groups with positions. Safe pure function, no eval.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'text' => 
        array (
          'type' => 'string',
          'description' => 'The text to search in',
        ),
        'pattern' => 
        array (
          'type' => 'string',
          'description' => 'PCRE regex pattern WITHOUT delimiters. E.g. \'\\\\d+\' matches numbers.',
        ),
        'flags' => 
        array (
          'type' => 'string',
          'description' => 'PCRE flags like \'i\' for case-insensitive, \'m\' for multiline (default: \'\')',
        ),
      ),
      'required' => 
      array (
        0 => 'text',
        1 => 'pattern',
      ),
    ),
  ),
);

if (! function_exists('text_regex_extract')) {
    function text_regex_extract($text, $pattern, $flags = null)
    {
        $delimiter = '/';
        $f = $flags ?? '';
        $fullPattern = $delimiter . $pattern . $delimiter . $f;
        $result = preg_match_all($fullPattern, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if ($result === false) {
            $err = preg_last_error_msg();
            return json_encode(['error' => "Regex error: $err", 'pattern' => $fullPattern]);
        }
        $output = [];
        foreach ($matches as $matchIdx => $match) {
            $groups = [];
            foreach ($match as $groupIdx => $group) {
                $groups['group_' . $groupIdx] = [
                    'value' => $group[0],
                    'position' => $group[1]
                ];
            }
            $output[] = $groups;
        }
        return json_encode([
            'match_count' => count($output),
            'matches' => $output,
            'pattern_used' => $fullPattern
        ]);
    }
}
