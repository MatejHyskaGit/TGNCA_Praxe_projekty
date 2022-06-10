<?php
// $data = [
//     "labels" => ["2021-07-10", "2021-07-11", "2021-07-12", "2021-07-13", "2021-07-14", "2021-07-15", "2021-07-16", "2021-07-17", "2021-07-18", "2021-07-19", "2021-07-20", "2021-07-21", "2021-07-22", "2021-07-23", "2021-07-24", "2021-07-25", "2021-07-26", "2021-07-27", "2021-07-28", "2021-07-29", "2021-07-30", "2021-07-31", "2021-08-01", "2021-08-02", "2021-08-03"], "expenses" => ["222.28", "236.22", "514.93", "291.40", "274.26", "300.76", "509.79", "170.21", "139.12", "884.61", "561.26", "501.82", "300.37", "234.05", "147.69", "162.86", "306.72", "204.41", "57.04", "352.34", "189.11", "193.83", "328.22", "468.30", "1163.49"], "revenue" => ["0.00", "3980.16", "6618.33", "0.00", "2830.69", "3782.49", "5697.19", "812.67", "1702.51", "9594.38", "5161.41", "4677.70", "3024.60", "390.57", "170.00", "104.78", "66.30", "2903.50", "2262.60", "3153.69", "2468.24", "0.00", "3226.88", "0.00", "10392.08"], "users" => ["42", "55", "91", "76", "73", "69", "64", "46", "39", "72", "77", "79", "56", "57", "31", "37", "67", "38", "17", "28", "50", "40", "56", "92", "95"], "orders" => ["0", "1", "7", "0", "3", "3", "4", "2", "1", "4", "6", "3", "3", "1", "1", "1", "1", "2", "2", "1", "1", "0", "3", "0", "6"], "currency" => "CZK"
// ];

$homepage = file_get_contents("https://backend.tanganica.cz/report_data.php");
$data = [];
$input = json_decode($homepage, true);
$datas = json_decode(json_encode($input["data"]), true);
$data["labels"] = $datas["labels"];
$data["expenses"] = $datas["expenses"];
$data["revenue"] = $datas["revenue"];
$data["users"] = $datas["users"];
$data["orders"] = $datas["orders"];
$data["assisted_revenues"] = $datas["assisted_revenues"];
$data["currency"] = $input["currency"];



$keys = array_keys($data);
$pno = [];
for ($i = 0; $i < count($data[$keys[0]]); $i++) {
    $expense = $data[$keys[1]][$i];
    $revenue = $data[$keys[2]][$i];
    if ($revenue == 0.00) {
        $revenue = INF;
    }
    array_push($pno, round(($expense / $revenue) * 100, 2) . "%");
}
$data["pno"] = $pno;

$export = new Export($data);
$export->generate_xls_report($data);
echo "<br> <br>";
$export->generate_csv_report($data);


class Export
{
    function generate_xls_report(array $values)
    {
        $filename = "website_data_" . date('Ymd') . ".xls";
        $xls_content = "";
        $keys = array_keys($values);
        $titles = false;
        if (!$titles) {
            $xls_content .= implode("\t", $keys) . "\n";
            $titles = true;
        }
        for ($i = 0; $i < count($values["labels"]); $i++) {
            for ($j = 0; $j < count($keys); $j++) {
                if ($keys[$j] == "currency") {
                    $xls_content .= $values["currency"] . "\t";
                } else {
                    $xls_content .= $values[$keys[$j]][$i] . "\t";
                    if ($j == count($keys) - 1) {
                        $xls_content .= "\n";
                    }
                }
            }
        }
        echo $xls_content;
        $this->create_file($filename, $xls_content);
        if (file_exists($filename) && $this->create_file($filename, $xls_content)) {
            return true;
        } else {
            return false;
        }
    }

    function create_file($name, $content)
    {
        try {
            $fh = fopen($name, "w");
            fwrite($fh, $content);
            fclose($fh);
            return true;
        } catch (Exception $e) {
            //echo "Caught exception: " . $e->getMessage(), "\n";
            return false;
        }
    }

    function create_csv_file($name, $content)
    {
        try {
            $fh = fopen($name, "w");
            $content_list = explode("\n", $content);
            $content_list_2 = [];
            foreach ($content_list as $row) {
                array_push($content_list_2, explode(";", $row));
            }
            foreach ($content_list_2 as $row) {
                fputcsv($fh, $row, ";");
            }
            fclose($fh);
            return true;
        } catch (Exception $e) {
            //echo "Caught exception: " . $e->getMessage(), "\n";
            return false;
        }
    }

    function generate_csv_report(array $values)
    {
        $filename = "csv_data_" . date("Ymd") . ".csv";
        $csv_content = "";
        $keys = array_keys($values);
        $titles = false;
        if (!$titles) {
            $csv_content .= implode(";", $keys) . "\n";
            $titles = true;
        }
        for ($i = 0; $i < count($values["labels"]); $i++) {
            for ($j = 0; $j < count($keys); $j++) {
                if ($keys[$j] == "currency") {
                    $csv_content .= $values["currency"] . ";";
                } else {
                    $csv_content .= $values[$keys[$j]][$i] . ";";
                    if ($j == count($keys) - 1) {
                        $csv_content .= "\n";
                    }
                }
            }
        }
        echo $csv_content;
        $this->create_csv_file($filename, $csv_content);
        if (file_exists($filename) && $this->create_csv_file($filename, $csv_content)) {
            return true;
        } else {
            return false;
        }
    }
}

