<?php

$toolDefinition_text_morse_code = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_morse_code',
    'description' => 'Encode text into Morse code or decode Morse code back to text. Supports A-Z, 0-9, and basic punctuation.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'input' => 
        array (
          'type' => 'string',
          'description' => 'Text to encode or Morse to decode',
        ),
        'direction' => 
        array (
          'type' => 'string',
          'description' => "'encode' (text to morse) or 'decode' (morse to text) (default: 'encode')",
        ),
        'separator' => 
        array (
          'type' => 'string',
          'description' => 'Character between morse letters (default: \' \')',
        ),
        'word_separator' => 
        array (
          'type' => 'string',
          'description' => 'Character between words (default: \' / \')',
        ),
      ),
      'required' => 
      array (
        0 => 'input',
      ),
    ),
  ),
);

if (! function_exists('text_morse_code')) {
    function text_morse_code($input, $direction = null, $separator = null, $word_separator = null)
    {
        $morse = [
            'A' => '.-', 'B' => '-...', 'C' => '-.-.', 'D' => '-..', 'E' => '.',
            'F' => '..-.', 'G' => '--.', 'H' => '....', 'I' => '..', 'J' => '.---',
            'K' => '-.-', 'L' => '.-..', 'M' => '--', 'N' => '-.', 'O' => '---',
            'P' => '.--.', 'Q' => '--.-', 'R' => '.-.', 'S' => '...', 'T' => '-',
            'U' => '..-', 'V' => '...-', 'W' => '.--', 'X' => '-..-', 'Y' => '-.--',
            'Z' => '--..', '0' => '-----', '1' => '.----', '2' => '..---', '3' => '...--',
            '4' => '....-', '5' => '.....', '6' => '-....', '7' => '--...', '8' => '---..',
            '9' => '----.', '.' => '.-.-.-', ',' => '--..--', '?' => '..--..',
            "'" => '.----.', '!' => '-.-.--', '/' => '-..-.', '(' => '-.--.', ')' => '-.--.-',
            '&' => '.-...', ':' => '---...', ';' => '-.-.-.', '=' => '-...-',
            '+' => '.-.-.', '-' => '-....-', '_' => '..--.-', '"' => '.-..-.',
            '@' => '.--.-.', ' ' => '/'
        ];
        $dir = $direction ?? 'encode';
        $sep = $separator ?? ' ';
        $wordSep = $word_separator ?? ' / ';
        
        if ($dir === 'decode') {
            $revMorse = array_flip($morse);
            $words = explode($wordSep, $input);
            $result = [];
            foreach ($words as $word) {
                $letters = explode($sep, trim($word));
                $decoded = '';
                foreach ($letters as $m) {
                    if (isset($revMorse[$m])) {
                        $decoded .= $revMorse[$m];
                    } elseif ($m === '') {
                        continue;
                    } else {
                        $decoded .= '?';
                    }
                }
                $result[] = $decoded;
            }
            return json_encode(['direction' => 'decode', 'input' => $input, 'output' => implode(' ', $result)]);
        } else {
            $chars = str_split(strtoupper($input));
            $output = [];
            foreach ($chars as $ch) {
                if (isset($morse[$ch])) {
                    $output[] = $morse[$ch];
                } elseif ($ch === ' ') {
                    $output[] = '/';
                } else {
                    $output[] = '?';
                }
            }
            $encoded = implode($sep, $output);
            return json_encode(['direction' => 'encode', 'input' => $input, 'output' => $encoded]);
        }
    }
}
