<?php

$toolDefinition_data_histogram = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'data_histogram',
    'description' => 'Create a simple text-based histogram from an array of numbers. Shows distribution visually with horizontal bars using Unicode block characters.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'numbers' => 
        array (
          'type' => 'array',
          'items' => array('type' => 'number'),
          'description' => 'Array of numbers to visualize',
        ),
        'buckets' => 
        array (
          'type' => 'integer',
          'description' => 'Number of buckets/ranges (default: 5)',
        ),
        'bar_width' => 
        array (
          'type' => 'integer',
          'description' => 'Width of the histogram in characters (default: 40)',
        ),
      ),
      'required' => 
      array (
        0 => 'numbers',
      ),
    ),
  ),
);

if (! function_exists('data_histogram')) {
    function data_histogram($numbers, $buckets = null, $bar_width = null)
    {
        if (empty($numbers)) {
            return json_encode(['error' => 'Cannot create histogram from empty array']);
        }
        $buckets = isset($buckets) ? max(2, min(50, (int)$buckets)) : 5;
        $width = isset($bar_width) ? max(10, min(100, (int)$bar_width)) : 40;
        $nums = array_map('floatval', $numbers);
        $min = min($nums);
        $max = max($nums);
        if ($min === $max) {
            $label = number_format($min, 2);
            $bar = str_repeat('█', $width);
            return json_encode([
                'histogram' => $label . ': ' . $bar . ' (' . count($nums) . ')',
                'buckets' => [['label' => $label, 'count' => count($nums), 'bar' => $bar]],
                'min' => $min, 'max' => $max, 'total_values' => count($nums), 'bucket_count' => $buckets
            ]);
        }
        $range = $max - $min;
        $histLines = [];
        $bucketsArr = [];
        for ($i = 0; $i < $buckets; $i++) {
            $lower = $min + ($i * $range / $buckets);
            $upper = $min + (($i + 1) * $range / $buckets);
            $mid = ($lower + $upper) / 2;
            $label = number_format($mid, 2);
            $count = 0;
            foreach ($nums as $n) {
                if ($n >= $lower && ($i === $buckets - 1 ? $n <= $upper : $n < $upper)) {
                    $count++;
                }
            }
            $barLen = $count > 0 ? max(1, (int)round(($count / count($nums)) * $width)) : 0;
            $bar = str_repeat('█', $barLen);
            $bucketsArr[] = ['label' => $label, 'count' => $count, 'bar' => $bar];
            $histLines[] = $label . ': ' . $bar . ' (' . $count . ')';
        }
        return json_encode([
            'histogram' => implode("\n", $histLines),
            'buckets' => $bucketsArr,
            'min' => $min,
            'max' => $max,
            'total_values' => count($nums),
            'bucket_count' => $buckets
        ]);
    }
}
