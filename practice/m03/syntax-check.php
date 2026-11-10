<?php
// Parser-only check; no application files are executed.
$root = dirname(__DIR__, 2);
$files = [];
foreach (['src', 'config', 'templates', 'public'] as $folder) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $folder));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            token_get_all(file_get_contents($file->getPathname()), TOKEN_PARSE);
            $files[] = substr($file->getPathname(), strlen($root) + 1);
        }
    }
}
sort($files);
echo 'PASS: PHP ' . PHP_VERSION . ', ' . count($files) . " files parsed\n";
$tokens = function ($path) {
    $result = [];
    foreach (token_get_all(file_get_contents($path), TOKEN_PARSE) as $token) {
        if (is_array($token)) {
            if ($token[0] === T_WHITESPACE) {
                continue;
            }
            $result[] = [$token[0], $token[1]];
        } else {
            $result[] = $token;
        }
    }
    return $result;
};
if ($tokens(__DIR__ . '/helpers-vscode.txt') !== $tokens(__DIR__ . '/helpers-netbeans.txt')) {
    throw new RuntimeException('Formatter changed PHP tokens');
}
echo "PASS: both formatter outputs have identical PHP tokens except whitespace\n";
