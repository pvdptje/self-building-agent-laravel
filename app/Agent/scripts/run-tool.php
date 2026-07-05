<?php

// Executes one generated tool in an isolated child process, so a runaway
// tool (infinite loop, memory exhaustion, fatal error) cannot kill the
// agent loop. Invoked by ToolRegistry::executeGenerated().
//
// argv: [script, toolFile, functionName, base64(json arguments)]

[, $toolFile, $function, $encodedArguments] = $argv + [null, null, null, ''];

$arguments = json_decode(base64_decode($encodedArguments), true);
$arguments = is_array($arguments) ? $arguments : [];

require $toolFile;

if (! function_exists($function)) {
    echo json_encode(['ok' => false, 'error' => "Tool file did not define function [{$function}]."]);
    exit(0);
}

try {
    $reflection = new ReflectionFunction($function);
    $ordered = [];

    foreach ($reflection->getParameters() as $parameter) {
        if (array_key_exists($parameter->getName(), $arguments)) {
            $ordered[] = $arguments[$parameter->getName()];
        } elseif ($parameter->isDefaultValueAvailable()) {
            $ordered[] = $parameter->getDefaultValue();
        } else {
            $ordered[] = null;
        }
    }

    $output = json_encode(['ok' => true, 'result' => $reflection->invokeArgs($ordered)]);

    if ($output === false) {
        $output = json_encode(['ok' => false, 'error' => 'Tool result could not be JSON-encoded: '.json_last_error_msg()]);
    }

    echo $output;
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => get_class($e).': '.$e->getMessage()]);
}
