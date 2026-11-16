<?php
$content = file_get_contents('resources/views/admin_panel/vochers/unified_create.blade.php');
preg_match_all('/<div\b/i', $content, $open);
preg_match_all('/<\/div>/i', $content, $close);
echo "Open: " . count($open[0]) . " Close: " . count($close[0]) . PHP_EOL;
