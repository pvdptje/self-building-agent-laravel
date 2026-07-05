<?php
$toolDefinition_ascii_canvas = array (
  'type' => 'function',
  'function' => array (
    'name' => 'ascii_canvas',
    'description' => 'Generate ASCII art patterns and shapes on a character grid. Supports checkerboard, diamond, cross, border, stripes, grid, wave, spiral, triangle, sine. Pure function.',
    'parameters' => array (
      'type' => 'object',
      'properties' => array (
        'width' => array ( 'type' => 'integer', 'description' => 'Width (1-120)' ),
        'height' => array ( 'type' => 'integer', 'description' => 'Height (1-80)' ),
        'pattern' => array ( 'type' => 'string', 'description' => "'empty','checkerboard','diamond','cross','border','stripes','grid','wave','spiral','triangle','sine'" ),
        'fill_char' => array ( 'type' => 'string', 'description' => "Fill char (default: '#')" ),
        'bg_char' => array ( 'type' => 'string', 'description' => "Background char (default: ' ')" ),
      ),
      'required' => array ( 0 => 'width', 1 => 'height', 2 => 'pattern' ),
    ),
  ),
);
if (! function_exists('ascii_canvas')) {
    function ascii_canvas($width, $height, $pattern, $fill_char = null, $bg_char = null) {
        $w = max(1, min(120, (int)$width));
        $h = max(1, min(80, (int)$height));
        $fill = isset($fill_char) && $fill_char !== '' ? $fill_char[0] : '#';
        $bg = isset($bg_char) && $bg_char !== '' ? $bg_char[0] : ' ';
        $grid = array_fill(0, $h, array_fill(0, $w, $bg));
        switch ($pattern) {
            case 'checkerboard':
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        $grid[$y][$x] = (($x + $y) % 2 === 0) ? $fill : $bg;
                break;
            case 'diamond':
                $cx = ($w - 1) / 2; $cy = ($h - 1) / 2;
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if (abs($x - $cx) / max(1, $cx) + abs($y - $cy) / max(1, $cy) <= 1.0)
                            $grid[$y][$x] = $fill;
                break;
            case 'cross':
                $cx = (int)(($w - 1) / 2); $cy = (int)(($h - 1) / 2);
                $aw = max(1, (int)($w / 6)); $ah = max(1, (int)($h / 6));
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if (($x >= $cx - $aw && $x <= $cx + $aw) || ($y >= $cy - $ah && $y <= $cy + $ah))
                            $grid[$y][$x] = $fill;
                break;
            case 'border':
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if ($y === 0 || $y === $h - 1 || $x === 0 || $x === $w - 1)
                            $grid[$y][$x] = $fill;
                break;
            case 'stripes':
                $sw = max(1, (int)($w / 5));
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if ((int)($x / $sw) % 2 === 0) $grid[$y][$x] = $fill;
                break;
            case 'grid':
                $cw = max(1, (int)($w / 5)); $ch = max(1, (int)($h / 5));
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if ($x % $cw === 0 || $y % $ch === 0 || $x === $w - 1 || $y === $h - 1)
                            $grid[$y][$x] = $fill;
                break;
            case 'wave':
                for ($x = 0; $x < $w; $x++) {
                    $yp = (int)(($h - 1) / 2 + sin($x * 4 * M_PI / $w) * ($h - 1) / 3);
                    $grid[max(0, min($h - 1, $yp))][$x] = $fill;
                }
                break;
            case 'sine':
                for ($x = 0; $x < $w; $x++) {
                    $yp = (int)(($h - 1) / 2 + sin($x * 4 * M_PI / $w) * ($h - 1) / 3);
                    $yp = max(0, min($h - 1, $yp));
                    for ($y = 0; $y <= $yp; $y++) $grid[$y][$x] = $fill;
                }
                break;
            case 'spiral':
                $cx = ($w - 1) / 2; $cy = ($h - 1) / 2; $mr = max($cx, $cy);
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if ((int)(sqrt(pow($x - $cx, 2) + pow($y - $cy, 2)) / max(1, $mr) * 5) % 2 === 0)
                            $grid[$y][$x] = $fill;
                break;
            case 'triangle':
                $slope = (float)$h / max(1, $w);
                for ($y = 0; $y < $h; $y++)
                    for ($x = 0; $x < $w; $x++)
                        if ($y >= $h - 1 - ($x * $slope)) $grid[$y][$x] = $fill;
                break;
            case 'empty':
            default: break;
        }
        $lines = array_map(function($r) { return implode('', $r); }, $grid);
        return json_encode(['canvas' => implode("\n", $lines), 'width' => $w, 'height' => $h, 'pattern' => $pattern, 'lines' => $h, 'characters' => $w * $h]);
    }
}
