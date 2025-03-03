<?php

$fonts = [
    'OpenSans-Bold.ttf' => 'https://github.com/googlefonts/opensans/raw/main/fonts/ttf/OpenSans-Bold.ttf',
    'Roboto-Bold.ttf' => 'https://github.com/googlefonts/roboto/raw/main/src/hinted/Roboto-Bold.ttf'
];

$fontDir = __DIR__ . '/asset/fonts/';

if (!file_exists($fontDir)) {
    mkdir($fontDir, 0777, true);
}

foreach ($fonts as $fontName => $url) {
    $destination = $fontDir . $fontName;
    if (!file_exists($destination)) {
        echo "Downloading {$fontName}...\n";
        $fontContent = file_get_contents($url);
        if ($fontContent !== false) {
            file_put_contents($destination, $fontContent);
            echo "Successfully downloaded {$fontName}\n";
        } else {
            echo "Failed to download {$fontName}\n";
        }
    } else {
        echo "{$fontName} already exists\n";
    }
}

echo "Done!\n";
?> 