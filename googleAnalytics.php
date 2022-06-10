<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once $_SERVER['DOCUMENT_ROOT'] . "/" . "data/main_vars.php";
include_once PATH_TO_ANALYTICS_LOADER;
$analytics = load_analytics_by_id(1);

$eshop = load_eshop(233);
$view_id = "" . $eshop["view_id"];
$day = "2022-04-15";

$output_array = [];

$dim_array = []; // provide dimensions
$met_array = []; // provide metrics

$response = getReport($analytics, $view_id, $day, $dim_array, $met_array);
$output_array = convertData($output_array, $response);

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
                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        if (count($output_array[$entry->getName()]) == 0) {
                            $output_array[$entry->getName()] = [$values[$k]];
                        } else {
                            array_push($output_array[$entry->getName()], $values[$k]);
                        }
                    }
                }
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
