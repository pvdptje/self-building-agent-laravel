<?php

$toolDefinition_text_obfuscate = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_obfuscate',
    'description' => 'Obfuscate or deobfuscate a string using a chain of reversible transforms. Applies ROT13, character shifting, and reversing in sequence. Reversible with the same key.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'input' => 
        array (
          'type' => 'string',
          'description' => 'The string to obfuscate or deobfuscate',
        ),
        'direction' => 
        array (
          'type' => 'string',
          'description' => "'obfuscate' or 'deobfuscate' (default: 'obfuscate')",
        ),
        'shift' => 
        array (
          'type' => 'integer',
          'description' => 'Character shift amount (1-25, default: 5). Used as Caesar shift on letters.',
        ),
      ),
      'required' => 
      array (
        0 => 'input',
      ),
    ),
  ),
);

if (! function_exists('text_obfuscate')) {
    function text_obfuscate($input, $direction = null, $shift = null)
    {
        $dir = $direction ?? 'obfuscate';
        $s = $shift ?? 5;
        $s = max(1, min(25, (int)$s));
        
        if ($dir === 'deobfuscate') {
            // Reverse: undo shift, undo ROT13, undo strrev
            $step3 = str_rot13($input);
            $step2 = '';
            $len = strlen($step3);
            for ($i = 0; $i < $len; $i++) {
                $ch = $step3[$i];
                if (ctype_alpha($ch)) {
                    $base = ctype_upper($ch) ? 'A' : 'a';
                    $ord = ord($ch) - ord($base);
                    $newOrd = ($ord - $s + 26) % 26;
                    $step2 .= chr(ord($base) + $newOrd);
                } else {
                    $step2 .= $ch;
                }
            }
            $result = strrev($step2);
            return json_encode([
                'input' => $input,
                'output' => $result,
                'direction' => 'deobfuscate',
                'shift_used' => $s
            ]);
        } else {
            // Obfuscate: strrev → Caesar shift → ROT13
            $step1 = strrev($input);
            $step2 = '';
            $len = strlen($step1);
            for ($i = 0; $i < $len; $i++) {
                $ch = $step1[$i];
                if (ctype_alpha($ch)) {
                    $base = ctype_upper($ch) ? 'A' : 'a';
                    $ord = ord($ch) - ord($base);
                    $newOrd = ($ord + $s) % 26;
                    $step2 .= chr(ord($base) + $newOrd);
                } else {
                    $step2 .= $ch;
                }
            }
            $result = str_rot13($step2);
            return json_encode([
                'input' => $input,
                'output' => $result,
                'direction' => 'obfuscate',
                'shift_used' => $s
            ]);
        }
    }
}
