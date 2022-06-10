<?php
$homepage = file_get_contents("example_data.json");
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

