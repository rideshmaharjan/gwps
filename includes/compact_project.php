<?php
// Safe project compactor
// Usage:
//  php compact_project.php         -> preview only
//  php compact_project.php --apply -> apply changes and create .bak backups

$apply = in_array('--apply', $argv);
$root = dirname(__DIR__);
$skipDirs = ['.git', 'node_modules', 'vendor', 'storage', 'uploads'];
$extHandlers = [
    'php' => 'compact_php',
    'css' => 'compact_css',
    'js'  => 'compact_js',
    'html'=> 'compact_html',
    'htm' => 'compact_html'
];

function shouldSkip($path, $skipDirs) {
    foreach ($skipDirs as $d) {
        if (stripos($path, DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR) !== false) return true;
    }
    return false;
}

function compact_php($src) {
    // Use token_get_all to remove T_COMMENT and T_DOC_COMMENT safely
    $tokens = token_get_all($src);
    $out = '';
    foreach ($tokens as $t) {
        if (is_array($t)) {
            if ($t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT) continue;
            $out .= $t[1];
        } else {
            $out .= $t;
        }
    }
    // normalize line endings and collapse multiple blank lines
    $out = preg_replace("/\r\n|\r|\n/", "\n", $out);
    $out = preg_replace('/\n{3,}/', "\n\n", $out);
    return $out;
}

function compact_css($src) {
    // remove comments and collapse whitespace
    $src = preg_replace('!/\*.*?\*/!s', '', $src);
    $src = preg_replace('/\s+/', ' ', $src);
    $src = preg_replace('/\s*([{};:,])\s*/', '$1', $src);
    $src = trim($src);
    return $src . "\n";
}

function compact_js($src) {
    // naive JS minify: remove /* */ comments and // comments, collapse spaces
    $src = preg_replace('!/\*.*?\*/!s', '', $src);
    $src = preg_replace('/(?<!:)\/\/.*(?=[\n\r])/', '', $src);
    $src = preg_replace('/\s+/', ' ', $src);
    $src = preg_replace('/\s*([{};:,=()+\-<>])\s*/', '$1', $src);
    return trim($src) . "\n";
}

function compact_html($src) {
    // remove HTML comments, collapse multiple spaces and lines
    $src = preg_replace('/<!--(?:.*?--)??>/s', '', $src);
    $src = preg_replace('/\s+/', ' ', $src);
    $src = preg_replace('/>\s+</', '><', $src);
    return trim($src) . "\n";
}

$filesChanged = 0;
$files = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $path = $file->getPathname();
    if (shouldSkip($path, $skipDirs)) continue;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!isset($extHandlers[$ext])) continue;
    $files[] = $path;
}

echo "Found " . count($files) . " files to analyze.\n";

foreach ($files as $file) {
    $orig = file_get_contents($file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $fn = $extHandlers[$ext];
    try {
        $new = $fn($orig);
    } catch (Throwable $e) {
        echo "Skipping (error) : $file\n";
        continue;
    }
    if ($new !== $orig) {
        $filesChanged++;
        echo ($apply ? "Modifying: " : "Would modify: ") . $file . "\n";
        if ($apply) {
            // backup
            copy($file, $file . '.bak');
            file_put_contents($file, $new);
        }
    }
}

echo ($apply ? "Applied changes." : "Preview complete.") . " Files changed: " . $filesChanged . "\n";

?>
