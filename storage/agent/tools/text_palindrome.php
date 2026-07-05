<?php

$toolDefinition_text_palindrome = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'text_palindrome',
    'description' => 'Check if a string is a palindrome (reads the same forwards and backwards). Can ignore case, spaces, and punctuation. Also finds the longest palindromic substring.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'text' => 
        array (
          'type' => 'string',
          'description' => 'The string to analyze',
        ),
        'strict' => 
        array (
          'type' => 'boolean',
          'description' => 'If true, exact character match (case-sensitive, includes spaces/punctuation). If false, ignores case, spaces, and non-alphanumeric characters (default: false)',
        ),
        'find_longest' => 
        array (
          'type' => 'boolean',
          'description' => 'If true, also find the longest palindromic substring (default: false)',
        ),
      ),
      'required' => 
      array (
        0 => 'text',
      ),
    ),
  ),
);

if (! function_exists('text_palindrome')) {
    function text_palindrome($text, $strict = null, $find_longest = null)
    {
        $strictMode = $strict ?? false;
        $findLongest = $find_longest ?? false;
        
        if ($text === '') {
            return json_encode(['is_palindrome' => false, 'error' => 'Empty string cannot be a palindrome']);
        }
        
        if ($strictMode) {
            $clean = $text;
            $reversed = strrev($clean);
            $isPalindrome = $clean === $reversed;
        } else {
            $clean = preg_replace('/[^a-zA-Z0-9]/', '', $text);
            $clean = mb_strtolower($clean);
            $reversed = strrev($clean);
            $isPalindrome = $clean === $reversed;
        }
        
        $result = [
            'text' => $text,
            'is_palindrome' => $isPalindrome,
            'strict' => $strictMode,
            'normalized' => $clean,
            'reversed' => $reversed,
            'length' => strlen($text),
        ];
        
        if ($findLongest && strlen($text) > 0) {
            $longest = '';
            $len = strlen($text);
            $searchText = $strictMode ? $text : preg_replace('/[^a-zA-Z0-9]/', '', mb_strtolower($text));
            
            for ($i = 0; $i < strlen($searchText); $i++) {
                // Odd length
                $l = $i; $r = $i;
                while ($l >= 0 && $r < strlen($searchText) && $searchText[$l] === $searchText[$r]) {
                    $sub = substr($searchText, $l, $r - $l + 1);
                    if (strlen($sub) > strlen($longest)) {
                        $longest = $sub;
                    }
                    $l--; $r++;
                }
                // Even length
                $l = $i; $r = $i + 1;
                while ($l >= 0 && $r < strlen($searchText) && $searchText[$l] === $searchText[$r]) {
                    $sub = substr($searchText, $l, $r - $l + 1);
                    if (strlen($sub) > strlen($longest)) {
                        $longest = $sub;
                    }
                    $l--; $r++;
                }
            }
            $result['longest_palindromic_substring'] = $longest;
        }
        
        return json_encode($result);
    }
}
