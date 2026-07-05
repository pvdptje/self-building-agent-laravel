<?php

$toolDefinition_maze_generator = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'maze_generator',
    'description' => 'Generate random mazes using recursive backtracking algorithm.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'width' => 
        array (
          'type' => 'integer',
          'description' => 'Width in cells (3-50, default: 10)',
        ),
        'height' => 
        array (
          'type' => 'integer',
          'description' => 'Height in cells (3-50, default: 10)',
        ),
        'seed' => 
        array (
          'type' => 'integer',
          'description' => 'Optional random seed (0=random)',
        ),
        'style' => 
        array (
          'type' => 'string',
          'description' => "'ascii', 'unicode', 'compact' (default: 'ascii')",
        ),
      ),
      'required' => 
      array (
      ),
    ),
  ),
);

if (! function_exists('maze_generator')) {
    function maze_generator($width = null, $height = null, $seed = null, $style = null)
    {
        $w = $width ?? 10;
        $h = $height ?? 10;
        $w = max(3, min(50, (int)$w));
        $h = max(3, min(50, (int)$h));
        $style = $style ?? 'ascii';
        $seed = $seed ?? 0;

        $useRand = function($min, $max) use ($seed) {
            static $state;
            if (!isset($state)) {
                $state = $seed > 0 ? $seed : (int)(microtime(true) * 10000);
            }
            // FIXED: cast to int before bitwise AND to prevent float-to-int deprecation
            $state = ((int)($state * 1103515245 + 12345)) & 0x7fffffff;
            return $min + ($state % ($max - $min + 1));
        };

        $rows = $h * 2 + 1;
        $cols = $w * 2 + 1;

        $grid = [];
        for ($r = 0; $r < $rows; $r++) {
            $grid[$r] = [];
            for ($c = 0; $c < $cols; $c++) {
                $grid[$r][$c] = '#';
            }
        }

        for ($r = 0; $r < $h; $r++) {
            for ($c = 0; $c < $w; $c++) {
                $grid[$r * 2 + 1][$c * 2 + 1] = ' ';
            }
        }

        $visited = [];
        $stack = [];
        $visited['0,0'] = true;
        $stack[] = [0, 0];

        while (!empty($stack)) {
            $current = $stack[count($stack) - 1];
            $cr = $current[0];
            $cc = $current[1];
            $neighbors = [];
            $dirs = [[-1, 0, -1, 0], [1, 0, 1, 0], [0, -1, 0, -1], [0, 1, 0, 1]];
            foreach ($dirs as $d) {
                $nr = $cr + $d[0];
                $nc = $cc + $d[1];
                if ($nr >= 0 && $nr < $h && $nc >= 0 && $nc < $w && !isset($visited[$nr . ',' . $nc])) {
                    $wallR = $cr * 2 + 1 + $d[2];
                    $wallC = $cc * 2 + 1 + $d[3];
                    $neighbors[] = [$nr, $nc, $wallR, $wallC];
                }
            }
            if (empty($neighbors)) {
                array_pop($stack);
            } else {
                $idx = $useRand(0, count($neighbors) - 1);
                $next = $neighbors[$idx];
                $grid[$next[2]][$next[3]] = ' ';
                $grid[$next[0] * 2 + 1][$next[1] * 2 + 1] = ' ';
                $visited[$next[0] . ',' . $next[1]] = true;
                $stack[] = [$next[0], $next[1]];
            }
        }

        $grid[1][0] = 'S';
        $grid[$rows - 2][$cols - 1] = 'E';
        $output = [];
        for ($r = 0; $r < $rows; $r++) {
            $line = '';
            for ($c = 0; $c < $cols; $c++) {
                $line .= $grid[$r][$c];
            }
            $output[] = $line;
        }
        $mazeStr = implode("\n", $output);
        $coordinates = [];
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $coordinates[] = ['x' => $c, 'y' => $r, 'token' => $grid[$r][$c]];
            }
        }

        return json_encode([
            'width_cells' => $w,
            'height_cells' => $h,
            'grid_width' => $cols,
            'grid_height' => $rows,
            'maze_ascii' => $mazeStr,
            'coordinates' => $coordinates,
            'solution_hint' => "Start at S (0,1), reach E (" . ($cols-1) . "," . ($rows-2) . ")",
        ]);
    }
}
