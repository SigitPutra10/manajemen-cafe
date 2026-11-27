<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Order::query()->whereDate('created_at', today());

        $topProduct = OrderItem::query()
            ->selectRaw('product_id, SUM(quantity) as total_qty')
            ->whereHas('order', fn (Builder $q) => $q->whereDate('created_at', today()))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product:id,name')
            ->first();

        return view('admin.dashboard', [
            'revenue' => (int) (clone $today)->sum('total_price'),
            'transactionCount' => (clone $today)->count(),
            'activeMenuCount' => Product::query()->where('is_available', true)->count(),
            'topProduct' => $topProduct?->product?->name,
            'topProductQty' => (int) ($topProduct->total_qty ?? 0),
            'recentOrders' => (clone $today)->latest()->limit(5)->get(),
            'revenueChart' => $this->buildRevenueChartSeries(),
        ]);
    }

    /**
     * Aggregate valid order totals across day, week, month, and year buckets in a single pass.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildRevenueChartSeries(): array
    {
        $now = now();
        $todayKey = $now->format('Y-m-d');
        $weekStart = $now->copy()->subDays(6)->startOfDay();
        $yearStart = $now->copy()->startOfYear();
        $yearEnd = $now->copy()->endOfYear();
        $queryStart = $weekStart->lt($yearStart) ? $weekStart : $yearStart;

        $shortDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $shortMonths = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $fullMonths = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        $dayLabels = [];
        $dayTooltips = [];
        $dayValues = array_fill(0, 24, 0);
        for ($hour = 0; $hour < 24; $hour++) {
            $hourPrefix = str_pad((string) $hour, 2, '0', STR_PAD_LEFT);
            $dayLabels[] = "{$hourPrefix}:00";
            $dayTooltips[] = "{$hourPrefix}:00 - {$hourPrefix}:59";
        }

        $weekLabels = [];
        $weekTooltips = [];
        $weekValues = array_fill(0, 7, 0);
        $weekIndexByDate = [];
        for ($offset = 0; $offset < 7; $offset++) {
            $date = $weekStart->copy()->addDays($offset);
            $weekIndexByDate[$date->format('Y-m-d')] = $offset;
            $weekLabels[] = "{$date->day} {$shortMonths[$date->month]}";
            $weekTooltips[] = "{$shortDays[$date->dayOfWeek]}, {$date->day} {$shortMonths[$date->month]} {$date->year}";
        }

        $daysInMonth = $now->daysInMonth;
        $monthLabels = [];
        $monthTooltips = [];
        $monthValues = array_fill(0, $daysInMonth, 0);
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $monthLabels[] = (string) $day;
            $monthTooltips[] = "{$day} {$shortMonths[$now->month]} {$now->year}";
        }

        $yearLabels = [];
        $yearTooltips = [];
        $yearValues = array_fill(0, 12, 0);
        for ($month = 1; $month <= 12; $month++) {
            $yearLabels[] = $shortMonths[$month];
            $yearTooltips[] = "{$fullMonths[$month]} {$now->year}";
        }

        $dayTransactions = 0;
        $weekTransactions = 0;
        $monthTransactions = 0;
        $yearTransactions = 0;

        $orders = Order::query()
            ->select(['total_price', 'created_at'])
            ->where('created_at', '>=', $queryStart)
            ->where('created_at', '<=', $yearEnd)
            ->cursor();

        foreach ($orders as $order) {
            $createdAt = $order->created_at;
            if (! $createdAt) {
                continue;
            }

            $amount = (int) $order->total_price;
            $dateKey = $createdAt->format('Y-m-d');

            if ($dateKey === $todayKey) {
                $dayValues[(int) $createdAt->format('G')] += $amount;
                $dayTransactions++;
            }

            if (isset($weekIndexByDate[$dateKey])) {
                $weekValues[$weekIndexByDate[$dateKey]] += $amount;
                $weekTransactions++;
            }

            if ($createdAt->year === $now->year && $createdAt->month === $now->month) {
                $monthValues[$createdAt->day - 1] += $amount;
                $monthTransactions++;
            }

            if ($createdAt->year === $now->year) {
                $yearValues[$createdAt->month - 1] += $amount;
                $yearTransactions++;
            }
        }

        return [
            'day' => $this->formatSeries(
                $dayLabels,
                $dayTooltips,
                $dayValues,
                $dayTransactions,
                "Per jam hari ini, {$now->day} {$shortMonths[$now->month]} {$now->year}",
            ),
            'week' => $this->formatSeries(
                $weekLabels,
                $weekTooltips,
                $weekValues,
                $weekTransactions,
                "7 hari terakhir ({$weekStart->day} {$shortMonths[$weekStart->month]} s.d. {$now->day} {$shortMonths[$now->month]} {$now->year})",
            ),
            'month' => $this->formatSeries(
                $monthLabels,
                $monthTooltips,
                $monthValues,
                $monthTransactions,
                "Per tanggal pada {$fullMonths[$now->month]} {$now->year}",
            ),
            'year' => $this->formatSeries(
                $yearLabels,
                $yearTooltips,
                $yearValues,
                $yearTransactions,
                "Per bulan sepanjang tahun {$now->year}",
            ),
        ];
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, string>  $tooltips
     * @param  array<int, int>  $values
     * @return array<string, mixed>
     */
    private function formatSeries(
        array $labels,
        array $tooltips,
        array $values,
        int $transactions,
        string $rangeLabel,
    ): array {
        $total = array_sum($values);
        $peakLabel = null;
        $peakValue = 0;

        if ($total > 0) {
            $peakValue = max($values);
            $peakIndex = array_search($peakValue, $values, true);
            $peakLabel = $peakIndex !== false ? ($labels[$peakIndex] ?? null) : null;
        }

        return [
            'labels' => $labels,
            'tooltips' => $tooltips,
            'values' => $values,
            'total' => $total,
            'transactions' => $transactions,
            'peak_label' => $peakLabel,
            'peak_value' => $peakValue,
            'range_label' => $rangeLabel,
        ];
    }
}
