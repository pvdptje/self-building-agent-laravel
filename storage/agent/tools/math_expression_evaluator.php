<?php

$toolDefinition_math_expression_evaluator = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'math_expression_evaluator',
    'description' => 'Safely evaluate mathematical expressions using Shunting Yard (no eval).',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'expression' => 
        array (
          'type' => 'string',
          'description' => 'Expression string',
        ),
        'precision' => 
        array (
          'type' => 'integer',
          'description' => 'Decimal places (default: 6)',
        ),
      ),
      'required' => 
      array (
        0 => 'expression',
      ),
    ),
  ),
);

if (! function_exists('math_expression_evaluator')) {
    function math_expression_evaluator($expression, $precision = null)
    {
        $expr = trim($expression);
        $prec = $precision ?? 6;
        if (empty($expr)) return json_encode(['error'=>'Expression required']);

        // Tokenize - FIXED: added ctype_digit for number support
        $tokens = [];
        $buf = '';
        for ($i = 0; $i < strlen($expr); $i++) {
            $ch = $expr[$i];
            if ($ch === ' ') continue;
            if (ctype_alpha($ch) || ctype_digit($ch) || $ch === '.') { $buf .= $ch; continue; }
            if ($buf !== '') { $tokens[] = $buf; $buf = ''; }
            if (strpbrk($ch, '+-*/^%(),')) { $tokens[] = $ch; }
            else return json_encode(['error'=>"Unexpected char: '$ch'"]);
        }
        if ($buf !== '') $tokens[] = $buf;
        if (empty($tokens)) return json_encode(['error'=>'Empty expression']);

        $precMap = ['+'=>2,'-'=>2,'*'=>3,'/'=>3,'%'=>3,'^'=>4];
        $assoc = ['+'=>'L','-'=>'L','*'=>'L','/'=>'L','%'=>'L','^'=>'R'];
        $funcs = ['sqrt'=>1,'sin'=>1,'cos'=>1,'tan'=>1,'log'=>1,'ln'=>1,'abs'=>1,'round'=>1,'floor'=>1,'ceil'=>1,'exp'=>1,'min'=>2,'max'=>2];
        $output = [];
        $stack = [];
        foreach ($tokens as $tok) {
            if (is_numeric($tok)) { $output[] = (float)$tok; }
            elseif ($tok === 'pi') { $output[] = M_PI; }
            elseif ($tok === 'e') { $output[] = M_E; }
            elseif (isset($funcs[$tok])) { $stack[] = ['f', $tok]; }
            elseif ($tok === '(') { $stack[] = ['(', '(']; }
            elseif ($tok === ')') {
                while ($stack && end($stack)[0] !== '(') $output[] = array_pop($stack);
                if (!$stack) return json_encode(['error'=>'Mismatched )']);
                array_pop($stack);
                if ($stack && end($stack)[0] === 'f') $output[] = array_pop($stack);
            } elseif (isset($precMap[$tok])) {
                if ($tok === '-' && (!$output || end($output) === '(')) $output[] = 0;
                while ($stack && ($top = end($stack)) && $top[0] !== '(' && $top[0] !== 'f') {
                    if (($assoc[$tok] === 'L' && $precMap[$tok] <= ($precMap[$top[1]]??0)) || ($assoc[$tok] === 'R' && $precMap[$tok] < ($precMap[$top[1]]??0)))
                        $output[] = array_pop($stack);
                    else break;
                }
                $stack[] = ['o', $tok];
            } else return json_encode(['error'=>"Unknown token: $tok"]);
        }
        while ($stack) {
            $top = array_pop($stack);
            if ($top[0] === '(') return json_encode(['error'=>'Mismatched (']);
            $output[] = $top;
        }
        $eval = [];
        foreach ($output as $tok) {
            if (is_float($tok) || is_int($tok)) { $eval[] = $tok; }
            elseif ($tok[0] === 'o') {
                $b = array_pop($eval); $a = array_pop($eval);
                if ($a === null || $b === null) return json_encode(['error'=>'Invalid expression']);
                $eval[] = match($tok[1]) {
                    '+' => $a + $b, '-' => $a - $b, '*' => $a * $b,
                    '/' => $b == 0 ? NAN : $a / $b,
                    '%' => $a % $b, '^' => pow($a, $b),
                };
            } elseif ($tok[0] === 'f') {
                $fn = $tok[1]; $argc = $funcs[$fn]; $args = [];
                for ($j = 0; $j < $argc; $j++) {
                    $v = array_pop($eval);
                    if ($v === null) return json_encode(['error'=>"$fn needs $argc args"]);
                    $args[] = $v;
                }
                $args = array_reverse($args);
                $eval[] = match($fn) {
                    'sqrt' => sqrt($args[0]), 'sin' => sin($args[0]), 'cos' => cos($args[0]),
                    'tan' => tan($args[0]), 'log' => log10($args[0]), 'ln' => log($args[0]),
                    'abs' => abs($args[0]), 'round' => round($args[0]),
                    'floor' => floor($args[0]), 'ceil' => ceil($args[0]), 'exp' => exp($args[0]),
                    'min' => min($args[0], $args[1]), 'max' => max($args[0], $args[1]),
                };
            }
        }
        $result = end($eval);
        if (is_nan($result) || is_infinite($result)) return json_encode(['error'=>'NaN or infinite']);
        $result = round($result, $prec > 0 ? $prec : 10);
        if ($result == (int)$result) $result = (int)$result;
        return json_encode(['expression' => $expression, 'result' => $result, 'type' => is_int($result) ? 'integer' : 'float']);
    }
}
