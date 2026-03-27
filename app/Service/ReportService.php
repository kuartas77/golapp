<?php

namespace App\Service;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public static function paymentByGroupReport($year = null, $schoolId = null, $groupId = null)
    {
        return collect(DB::select('CALL sp_group_payment_report(?, ?, ?)', [
            $year, $schoolId, $groupId
        ]));
    }

    public static function generalReport($year = null, $schoolId = null)
    {
        return collect(DB::select('CALL sp_general_payment_report(?, ?)', [
            $year, $schoolId
        ]));
    }

    public static function monthlyReport($year = null, $schoolId = null, $groupId = null)
    {
        return collect(DB::select('CALL sp_monthly_payment_report(?, ?, ?)', [
            $year, $schoolId, $groupId
        ]));
    }

    public static function assistsPercentagesReport($year = null, $month = null, $groupId = null, $schoolId = null)
    {
        return collect(DB::select('CALL sp_get_assists_report_with_percentages(?, ?, ?, ?)', [
            $year, $month, $groupId, $schoolId
        ]));
    }
}
