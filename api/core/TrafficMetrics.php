<?php

namespace LogicLeap\StockManagement\core;

class TrafficMetrics
{
    private static int $peakRequestsInSecond;
    private static int $requestsInSecond;
    private static int $peakRequestsInMinute;
    private static int $requestsInMinute;

    public static function getMemoryUsage(): array
    {
        $mem_usage = memory_get_usage(true);

        if ($mem_usage < 1024)
            $mem = $mem_usage . " bytes";
        elseif ($mem_usage < 1048576)
            $mem = round($mem_usage / 1024, 2) . " kilobytes";
        else
            $mem = round($mem_usage / 1048576, 2) . " megabytes";
        return [
            "raw" => $mem_usage,
            "detail" => $mem
        ];
    }

    public static function getCpuUsage(): bool|array
    {
        if (!function_exists('sys_getloadavg'))
            return false;

        $cpu = sys_getloadavg();
        return [
            "1min" => $cpu[0],
            "5min" => $cpu[1],
            "15min" => $cpu[2]
        ];
    }
}