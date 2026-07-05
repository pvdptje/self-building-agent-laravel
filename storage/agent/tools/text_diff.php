<?php

$toolDefinition_text_diff = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_diff',
    'description' => 'Compare two strings line-by-line and report additions, removals, and unchanged lines (like a minimal diff). Useful for comparing file versions, tool code changes, or text edits.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'old_text' => 
        array (
          'type' => 'string',
          'description' => 'The original text to compare from',
        ),
        'new_text' => 
        array (
          'type' => 'string',
          'description' => 'The new text to compare against',
        ),
      ),
      'required' => 
      array (
        0 => 'old_text',
        1 => 'new_text',
      ),
    ),
  ),
);

if (! function_exists('text_diff')) {
    function text_diff($old_text, $new_text)
    {
        $oldLines = explode("\n", $old_text);
        $newLines = explode("\n", $new_text);
        
        $oldMax = count($oldLines);
        $newMax = count($newLines);
        
        // Build LCS table — each row must be independently allocated
        $dp = [];
        for ($i = 0; $i <= $oldMax; $i++) {
            $dp[$i] = [];
            for ($j = 0; $j <= $newMax; $j++) {
                $dp[$i][$j] = 0;
            }
        }
        
        for ($i = 1; $i <= $oldMax; $i++) {
            for ($j = 1; $j <= $newMax; $j++) {
                if ($oldLines[$i-1] === $newLines[$j-1]) {
                    $dp[$i][$j] = $dp[$i-1][$j-1] + 1;
                } else {
                    $dp[$i][$j] = max($dp[$i-1][$j], $dp[$i][$j-1]);
                }
            }
        }
        
        // Backtrack to find the diff
        $i = $oldMax;
        $j = $newMax;
        $diff = [];
        
        while ($i > 0 || $j > 0) {
            if ($i > 0 && $j > 0 && $oldLines[$i-1] === $newLines[$j-1]) {
                array_unshift($diff, ['type' => 'same', 'old_line' => $i, 'new_line' => $j, 'content' => $oldLines[$i-1]]);
                $i--;
                $j--;
            } elseif ($j > 0 && ($i === 0 || $dp[$i][$j-1] >= $dp[$i-1][$j])) {
                array_unshift($diff, ['type' => 'added', 'new_line' => $j, 'content' => $newLines[$j-1]]);
                $j--;
            } elseif ($i > 0) {
                array_unshift($diff, ['type' => 'removed', 'old_line' => $i, 'content' => $oldLines[$i-1]]);
                $i--;
            }
        }
        
        $added = 0;
        $removed = 0;
        $same = 0;
        foreach ($diff as $d) {
            if ($d['type'] === 'added') $added++;
            elseif ($d['type'] === 'removed') $removed++;
            else $same++;
        }
        
        return json_encode([
            'diff' => $diff,
            'stats' => [
                'same' => $same,
                'added' => $added,
                'removed' => $removed,
                'old_lines' => $oldMax,
                'new_lines' => $newMax
            ]
        ]);
    }
}
