<?php

$toolDefinition_text_random_case = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_random_case',
    'description' => 'Randomly change the case of characters in a string. Can alternate, scramble, or leetspeak-ify text. Useful for playful output, typography experiments, or text decoration.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'input' => 
        array (
          'type' => 'string',
          'description' => 'The string to transform',
        ),
        'mode' => 
        array (
          'type' => 'string',
          'description' => "'alternate' - upper/lower alternating, 'random' - random per char, 'invert' - flip existing case, 'leetspeak' - substitute numbers/symbols for letters (default: 'random')",
        ),
        'seed' => 
        array (
          'type' => 'integer',
          'description' => 'Optional random seed for reproducible results (0 = no seed)',
        ),
      ),
      'required' => 
      array (
        0 => 'input',
      ),
    ),
  ),
);

if (! function_exists('text_random_case')) {
    function text_random_case($input, $mode = null, $seed = null)
    {
        $mode = $mode ?? 'random';
        $s = $seed ?? 0;
        if ($s !== 0) {
            srand($s);
        }
        
        if ($input === '') {
            return json_encode(['error' => 'Input cannot be empty']);
        }
        
        $result = '';
        $leetMap = [
            'a' => '4', 'e' => '3', 'g' => '9', 'i' => '1', 'l' => '1',
            'o' => '0', 's' => '5', 't' => '7', 'z' => '2', 'b' => '8',
        ];
        
        switch ($mode) {
            case 'alternate':
                for ($i = 0; $i < strlen($input); $i++) {
                    if (ctype_alpha($input[$i])) {
                        $result .= ($i % 2 === 0) ? strtoupper($input[$i]) : strtolower($input[$i]);
                    } else {
                        $result .= $input[$i];
                    }
                }
                break;
                
            case 'invert':
                for ($i = 0; $i < strlen($input); $i++) {
                    if (ctype_upper($input[$i])) {
                        $result .= strtolower($input[$i]);
                    } elseif (ctype_lower($input[$i])) {
                        $result .= strtoupper($input[$i]);
                    } else {
                        $result .= $input[$i];
                    }
                }
                break;
                
            case 'leetspeak':
                for ($i = 0; $i < strlen($input); $i++) {
                    $lower = strtolower($input[$i]);
                    if (isset($leetMap[$lower]) && rand(0, 1) === 0) {
                        $result .= $leetMap[$lower];
                    } else {
                        $result .= (rand(0, 1) === 0) ? strtolower($input[$i]) : strtoupper($input[$i]);
                    }
                }
                break;
                
            default: // random
                for ($i = 0; $i < strlen($input); $i++) {
                    if (ctype_alpha($input[$i])) {
                        $result .= (rand(0, 1) === 0) ? strtoupper($input[$i]) : strtolower($input[$i]);
                    } else {
                        $result .= $input[$i];
                    }
                }
                break;
        }
        
        return json_encode([
            'input' => $input,
            'output' => $result,
            'mode' => $mode,
            'length' => strlen($result)
        ]);
    }
}
