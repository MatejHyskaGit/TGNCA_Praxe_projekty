<?php
function writeHeader($url)//give url of web page or file
{
    $file = fopen($url, "r");

    $index = 0;
    while ((($line = fgets($file)) !== false) && ($index++ < 20)) {
        echo htmlspecialchars($line);
    }

    fclose($file);
}
