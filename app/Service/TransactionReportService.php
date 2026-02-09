<?php

namespace App\Service;

use Carbon\Carbon;

class TransactionReportService
{
    public function getData(array $params): array
    {
        $type = $params['type'] ?? null;
        $query = \App\Models\ProductTransaction::query();
        $rangeLabel = '-';

        if (
            $type === 'weekly'
            && !empty($params['start_week_date'])
            && !empty($params['end_week_date'])
        ) {
            $start = Carbon::parse($params['start_week_date'])->startOfDay();
            $end   = Carbon::parse($params['end_week_date'])->endOfDay();

            $query->whereBetween('created_at', [$start, $end]);

            $rangeLabel = $start->format('d M Y') . ' - ' . $end->format('d M Y');
        }

        if (
            $type === 'monthly'
            && !empty($params['start_month'])
            && !empty($params['end_month'])
        ) {
            $start = Carbon::parse($params['start_month'])->startOfMonth();
            $end   = Carbon::parse($params['end_month'])->endOfMonth();

            $query->whereBetween('created_at', [$start, $end]);

            $rangeLabel = $start->translatedFormat('F Y')
                . ' - ' .
                $end->translatedFormat('F Y');
        }

        return [
            'transactions' => $query->get(),
            'type'         => $type,
            'rangeLabel'   => $rangeLabel,
        ];
    }
}
