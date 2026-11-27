import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
);

const PERIODS = [
    { key: 'day', label: 'Hari' },
    { key: 'week', label: 'Minggu' },
    { key: 'month', label: 'Bulan' },
    { key: 'year', label: 'Tahun' },
];

const EMPTY_SERIES = {
    labels: [],
    tooltips: [],
    values: [],
    total: 0,
    transactions: 0,
    peak_label: null,
    peak_value: 0,
    range_label: '',
};

const FONT_FAMILY = "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif";

export default function revenueChart(seriesData = {}) {
    // Keep Chart.js outside Alpine's reactive Proxy to avoid internal recursion errors.
    let chartInstance = null;

    return {
        series: seriesData,
        period: 'day',
        periods: PERIODS,
        ready: false,
        chartError: '',

        get activeSeries() {
            return this.series[this.period] ?? EMPTY_SERIES;
        },

        formatRupiah(value) {
            return 'Rp ' + Math.round(Number(value) || 0).toLocaleString('id-ID');
        },

        formatAxisTick(value) {
            const numeric = Number(value) || 0;
            if (numeric === 0) {
                return 'Rp 0';
            }
            if (numeric >= 1_000_000) {
                return 'Rp ' + (numeric / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
            }
            if (numeric >= 1_000) {
                return 'Rp ' + (numeric / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
            }

            return this.formatRupiah(numeric);
        },

        init() {
            this.$nextTick(() => this.mountChart());
        },

        mountChart() {
            try {
                const canvas = this.$refs.canvas;
                if (!canvas) {
                    throw new Error('Canvas grafik tidak ditemukan.');
                }

                const context = canvas.getContext('2d');
                if (!context) {
                    throw new Error('Konteks 2D canvas tidak tersedia.');
                }

                const reducedMotion = Boolean(
                    window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                );

                const current = this.activeSeries;
                const component = this;

                chartInstance = new Chart(context, {
                    type: 'bar',
                    data: {
                        labels: current.labels,
                        datasets: [
                            {
                                label: 'Pendapatan',
                                data: current.values,
                                backgroundColor: '#9A3412',
                                hoverBackgroundColor: '#7C2D12',
                                borderRadius: {
                                    topLeft: 4,
                                    topRight: 4,
                                },
                                borderSkipped: false,
                                maxBarThickness: 36,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: reducedMotion ? false : { duration: 220 },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: '#1C1917',
                                titleColor: '#FFFFFF',
                                bodyColor: '#FFFFFF',
                                borderColor: '#E2DDD5',
                                borderWidth: 1,
                                padding: 10,
                                cornerRadius: 6,
                                displayColors: false,
                                titleFont: {
                                    family: FONT_FAMILY,
                                    size: 12,
                                    weight: '600',
                                },
                                bodyFont: {
                                    family: FONT_FAMILY,
                                    size: 13,
                                    weight: '700',
                                },
                                callbacks: {
                                    title(items) {
                                        if (!items || items.length === 0) {
                                            return '';
                                        }
                                        const index = items[0].dataIndex;
                                        return component.activeSeries.tooltips[index] ?? items[0].label;
                                    },
                                    label(ctx) {
                                        return component.formatRupiah(ctx.parsed.y);
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                                border: {
                                    display: false,
                                },
                                ticks: {
                                    color: '#57534E',
                                    font: {
                                        family: FONT_FAMILY,
                                        size: 11,
                                        weight: '500',
                                    },
                                    autoSkip: true,
                                    maxRotation: 0,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                border: {
                                    display: false,
                                },
                                grid: {
                                    color: '#E2DDD5',
                                    lineWidth: 1,
                                },
                                ticks: {
                                    color: '#57534E',
                                    font: {
                                        family: FONT_FAMILY,
                                        size: 11,
                                        weight: '500',
                                    },
                                    maxTicksLimit: 5,
                                    callback(value) {
                                        return component.formatAxisTick(value);
                                    },
                                },
                            },
                        },
                    },
                });

                this.ready = true;
            } catch (error) {
                this.chartError = 'Grafik pendapatan gagal dimuat. Muat ulang halaman untuk mencoba lagi.';
                this.ready = true;
            }
        },

        selectPeriod(key) {
            if (!this.series[key] || this.period === key) {
                return;
            }

            this.period = key;

            if (chartInstance) {
                chartInstance.data.labels = this.activeSeries.labels;
                chartInstance.data.datasets[0].data = this.activeSeries.values;
                chartInstance.update();
            }
        },

        onTabKeydown(event, index) {
            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                return;
            }

            event.preventDefault();
            const step = event.key === 'ArrowRight' ? 1 : -1;
            const nextIndex = (index + step + this.periods.length) % this.periods.length;
            const nextKey = this.periods[nextIndex].key;

            this.selectPeriod(nextKey);
            this.$nextTick(() => {
                this.$refs['tab_' + nextKey]?.focus();
            });
        },

        destroy() {
            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }
        },
    };
}
