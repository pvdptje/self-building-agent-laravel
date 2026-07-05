<?php

$toolDefinition_text_acronym = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_acronym',
    'description' => 'Generate an acronym from a phrase. Supports first-letter, first-N-letters, or select-word-by-position modes. Also works in reverse (expand an acronym given a word list).',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'phrase' => 
        array (
          'type' => 'string',
          'description' => 'The phrase to convert (e.g. "As Soon As Possible")',
        ),
        'mode' => 
        array (
          'type' => 'string',
          'description' => "'letters' - first letter of each word, 'caps' - only capital letters, 'each' - first N letters from each word (default: 'letters')",
        ),
        'letters_per_word' => 
        array (
          'type' => 'integer',
          'description' => 'How many letters to take from each word in "each" mode (default: 1)',
        ),
      ),
      'required' => 
      array (
        0 => 'phrase',
      ),
    ),
  ),
);

if (! function_exists('text_acronym')) {
    function text_acronym($phrase, $mode = null, $letters_per_word = null)
    {
        $mode = $mode ?? 'letters';
        $phrase = trim($phrase);
        if ($phrase === '') {
            return json_encode(['error' => 'Phrase cannot be empty']);
        }
        
        $words = preg_split('/\s+/', $phrase);
        $letters = $letters_per_word ?? 1;
        
        switch ($mode) {
            case 'letters':
                $result = '';
                foreach ($words as $w) {
                    $w = trim($w);
                    if ($w !== '') {
                        $result .= mb_strtoupper(mb_substr($w, 0, 1));
                    }
                }
                break;
                
            case 'caps':
                $result = '';
                for ($i = 0; $i < mb_strlen($phrase); $i++) {
                    $ch = mb_substr($phrase, $i, 1);
                    if (mb_strtoupper($ch) !== mb_strtolower($ch) && $ch === mb_strtoupper($ch)) {
                        $result .= $ch;
                    }
                }
                break;
                
            case 'each':
                $result = '';
                foreach ($words as $w) {
                    $w = trim($w);
                    if ($w !== '') {
                        $result .= mb_strtoupper(mb_substr($w, 0, max(1, $letters)));
                    }
                }
                break;
                
            default:
                return json_encode(['error' => "Unknown mode: $mode"]);
        }
        
        return json_encode([
            'phrase' => $phrase,
            'acronym' => $result,
            'word_count' => count($words),
            'mode' => $mode,
            'length' => mb_strlen($result)
        ]);
    }
}
