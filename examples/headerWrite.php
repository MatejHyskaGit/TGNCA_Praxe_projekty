<?php
$url = 'https://feeds.mergado.com/tropicliberec-cz-google-nakupy-cz-3005fb225d53c620d3954c9f8fadee19.xml';

writeHeader($url);

function writeHeader($url)
{
    $file = fopen($url, "r");

    $index = 0;
    while ((($line = fgets($file)) !== false) && ($index++ < 20)) {
        echo htmlspecialchars($line);
    }

    fclose($file);
}
