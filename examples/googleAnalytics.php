<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once $_SERVER['DOCUMENT_ROOT'] . "/" . "data/main_vars.php";
include_once PATH_TO_ANALYTICS_LOADER;
$analytics = load_analytics_by_id(1);

// $eshops = get_all_clients_from_DB();
$eshop = load_eshop(233);
$view_id = "" . $eshop["view_id"];
$day = "2022-04-15";

$output_array = [];

$dim_array = ["ga:productName","ga:campaign"];
$met_array = ["ga:itemRevenue","ga:itemQuantity","ga:uniquePurchases","ga:itemsPerPurchase"];

$response = getReport($analytics, $view_id, $day, $dim_array, $met_array);
$output_array = convertData($output_array, $response);
var_dump($output_array);

function convertData($output_array, $response)
{
    for ($reportIndex = 0; $reportIndex < count($response); $reportIndex++) {
        $report = $response[$reportIndex];
        $header = $report->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $report->getData()->getRows();

        for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[$rowIndex];
            $dimensions = $row->getDimensions();
            $metrics = $row->getMetrics();
            $text = "";

            //Getting Dimensions
            for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                $text .= $dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n";
                if (count($output_array[$dimensionHeaders[$i]]) == 0) {
                    $output_array[$dimensionHeaders[$i]] = [$dimensions[$i]];
                } else {
                    array_push($output_array[$dimensionHeaders[$i]], $dimensions[$i]);
                }
            }

            //Getting Metrics
            if ($text != "break") {
                print($text);
                print("<br>");
                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        print($entry->getName() . ": " . $values[$k] . "\n");
                        if (count($output_array[$entry->getName()]) == 0) {
                            $output_array[$entry->getName()] = [$values[$k]];
                        } else {
                            array_push($output_array[$entry->getName()], $values[$k]);
                        }
                    }
                }
                print("<br>");
                print("<br>");
                print("<br>");
                // $camp_id = get_campaign_id_from_utm_campaign($camp_name, $eshop["id"]);
                // //if not exist
                // $date = date("Y-m-d", strtotime($date_time));

                // $campaign_day = do_query("SELECT DISTINCT * FROM campaign_days WHERE id_cam='" . $camp_id . "' AND id_day='" . $date . "'");
                // $row = $campaign_day->fetch();

                // if ($eshop["currency_analytics"] == "EUR" && $eshop["currency_ads"] == "CZ") {
                //     //echo "převádím";
                //     $curr_rate = load_curr_rate_on_day($date);
                //     $revenue = $revenue * $curr_rate;
                // }
                // if ($eshop["currency_analytics"] == "CZ" && $eshop["currency_ads"] == "EUR") {
                //     //echo "převádím";
                //     $curr_rate = load_curr_rate_on_day($date);
                //     $revenue = $revenue / $curr_rate;
                // }
                // $found = false;
                // for ($x = 0; $x <= sizeof($campaigns_info); $x++) {
                //     //foreach ($campaigns_info as $campaigns_info_val) {
                //     if ($campaigns_info[$x]["camp_id"] == $camp_id) {

                //         $convs = $ecom_conv_rate * $sessions / 100;
                //         $convs_old = $campaigns_info[$x]["conv_rate"] * $campaigns_info[$x]["sessions"] / 100;
                //         $campaigns_info[$x]["conv_rate"] = ($convs + $convs_old) / ($sessions + $campaigns_info[$x]["sessions"]) * 100;

                //         $campaigns_info[$x]["users"] += $users;
                //         $campaigns_info[$x]["sessions"] += $sessions;
                //         $campaigns_info[$x]["revenue"] += $revenue;
                //         $campaigns_info[$x]["transactions"] += $transactions;
                //         $campaigns_info[$x]["clicks"] += $clicks;
                //         $campaigns_info[$x]["costPerConversion"] += $cost_per_conv;
                //         $campaigns_info[$x]["CPC"] += $CPC;
                //         $campaigns_info[$x]["RPC"] += $RPC;

                //         $found = true;
                //         break;
                //     }
                // }
                // if (!$found) {
                //     array_push($campaigns_info, ["date_time" => $date_time, "camp_id" => $camp_id, "users" => $users, "sessions" => $sessions, "revenue" => $revenue, "conv_rate" => $ecom_conv_rate, "transactions" => $transactions, "clicks" => $clicks, "ga:goalConversionRateAll" => $cost_per_conv, "CPC" => $CPC, "RPC" => $RPC]);
                // }
            }
        }
    }
    return $output_array;
}



function getReport($analytics, $VIEW_ID, $day = "today", $dim_array, $met_array)
{
    // Replace with your view ID, for example XXXX.
    // Create the DateRange object.
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate($day);
    $dateRange->setEndDate($day);

    // Creating Dimensions
    function newGADimension($ga_name)
    {
        $ga_temp = new Google_Service_AnalyticsReporting_Dimension();
        $ga_temp->setName($ga_name);
        return $ga_temp;
    }

    $dimensions = [];

    foreach ($dim_array as $dimension) {
        $dimensions[$dimension] = newGADimension($dimension, $dimension);
    }

    // Create the Metrics objects.

    $metrics = [];

    foreach ($met_array as $metric) {
        $metrics[$metric] = newMetric($metric, $metric);
    }

    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($VIEW_ID);
    $request->setDateRanges($dateRange);
    $request->setDimensions(array_values($dimensions));
    $request->setMetrics(array_values($metrics));

    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests(array($request));
    return $analytics->reports->batchGet($body);
}







// function getReport_Delete($analytics, $VIEW_ID, $day = "today")
// {
//     // Replace with your view ID, for example XXXX.
//     // Create the DateRange object.
//     $dateRange = new Google_Service_AnalyticsReporting_DateRange();
//     $dateRange->setStartDate($day);
//     $dateRange->setEndDate($day);

//     // Creating Dimensions
//     function newGADimension($ga_name)
//     {
//         $ga_temp = new Google_Service_AnalyticsReporting_Dimension();
//         $ga_temp->setName($ga_name);
//         return $ga_temp;
//     }


//     $keyword = newGADimension("ga:keyword");

//     $campaign = newGADimension("ga:campaign");

//     $source = newGADimension("ga:source");

//     $medium = newGADimension("ga:medium");

//     $date_time = newGADimension("ga:date");

//     // Create the Metrics objects.
//     $sessions = new Google_Service_AnalyticsReporting_Metric();
//     $sessions->setExpression("ga:sessions");
//     $sessions->setAlias("sessions");

//     $users = newMetric("ga:users", "users");
//     $revenue = newMetric("ga:transactionRevenue", "revenue");
//     $transactions = newMetric("ga:transactions", "transactions");
//     $conv_rate = newMetric("ga:transactionsPerSession", "conv_rate");
//     $clicks = newMetric("ga:adClicks", "clicks");
//     $cost_per_conv = newMetric("ga:costPerConversion", "cost_per_conv");
//     $CPC = newMetric("ga:CPC", "CPC");
//     $RPC = newMetric("ga:RPC", "RPC");

//     // Create the ReportRequest object.
//     $request = new Google_Service_AnalyticsReporting_ReportRequest();
//     $request->setViewId($VIEW_ID);
//     $request->setDateRanges($dateRange);
//     $request->setDimensions(array($source, $medium, $campaign/* , $keyword */, $date_time));
//     $request->setMetrics(array($sessions, $users, $revenue, $transactions, $conv_rate, $clicks, $cost_per_conv, $CPC, $RPC));

//     $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
//     $body->setReportRequests(array($request));
//     return $analytics->reports->batchGet($body);
// }
