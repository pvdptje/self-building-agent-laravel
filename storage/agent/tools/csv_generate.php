<?php

$toolDefinition_csv_generate = array (
  'type' => 'function',
  'function' => 
  array (
    'name' => 'csv_generate',
    'description' => 'Generate a CSV string from an array of associative rows. The inverse of csv_table. Takes an array of objects (rows) and outputs CSV content.',
    'parameters' => 
    array (
      'type' => 'object',
      'properties' => 
      array (
        'rows' => 
        array (
          'type' => 'array',
          'items' => array('type' => 'object'),
          'description' => 'Array of associative arrays (rows) to convert to CSV',
        ),
        'delimiter' => 
        array (
          'type' => 'string',
          'description' => 'CSV delimiter character (default: \',\')',
        ),
        'include_headers' => 
        array (
          'type' => 'boolean',
          'description' => 'If true, first row contains column headers (default: true)',
        ),
      ),
      'required' => 
      array (
        0 => 'rows',
      ),
    ),
  ),
);

if (! function_exists('csv_generate')) {
    function csv_generate($rows, $delimiter = null, $include_headers = null)
    {
        if (empty($rows)) {
            return json_encode(['error' => 'Cannot generate CSV from empty rows']);
        }
        
        $delim = $delimiter ?? ',';
        if (strlen($delim) !== 1) {
            return json_encode(['error' => 'Delimiter must be a single character']);
        }
        
        $showHeaders = $include_headers ?? true;
        $headers = array_keys((array)$rows[0]);
        $csv = '';
        
        if ($showHeaders) {
            $escapedHeaders = array_map(function($h) use ($delim) {
                $s = str_replace('"', '""', (string)$h);
                if (strpos($s, $delim) !== false || strpos($s, '"') !== false || strpos($s, "\n") !== false) {
                    $s = '"' . $s . '"';
                }
                return $s;
            }, $headers);
            $csv .= implode($delim, $escapedHeaders) . "\n";
        }
        
        foreach ($rows as $row) {
            $row = (array)$row;
            $values = [];
            foreach ($headers as $h) {
                $val = isset($row[$h]) ? (string)$row[$h] : '';
                $val = str_replace('"', '""', $val);
                if (strpos($val, $delim) !== false || strpos($val, '"') !== false || strpos($val, "\n") !== false) {
                    $val = '"' . $val . '"';
                }
                $values[] = $val;
            }
            $csv .= implode($delim, $values) . "\n";
        }
        
        return json_encode([
            'csv' => $csv,
            'row_count' => count($rows),
            'columns' => count($headers),
            'delimiter' => $delim,
            'headers' => $headers
        ]);
    }
}
