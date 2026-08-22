@props([
    'title',
    'subtitle' => 'Overview of sales performance',
    'chartId',
    'height' => '340',
    'data' => [],
])

<div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:shadow-md sm:rounded-3xl">
    {{-- Card Header --}}
    <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:p-6">
        <div>
            <h2 class="text-base font-bold text-slate-900 sm:text-xl">{{ $title }}</h2>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">{{ $subtitle }}</p>
        </div>

        <div class="flex rounded-lg bg-slate-100 p-1">
            <span class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-slate-900 shadow-sm sm:px-3 sm:py-1.5">
                Bulanan
            </span>
        </div>
    </div>

    {{-- Chart Area --}}
    <div class="p-2 sm:p-6">
        <div id="{{ $chartId }}" class="w-full"></div>
    </div>
</div>

@once
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const element = document.querySelector('#{{ $chartId }}');
    if (!element) return;

    const chartData = @json($data);
    const isMobile = window.innerWidth < 640;

    const options = {
        chart: {
            type: 'area',
            height: isMobile ? 260 : {{ $height }},
            toolbar: { show: false },
            zoom: { enabled: false },
            sparkline: { enabled: false }
        },
        series: [{
            name: 'Penjualan',
            data: chartData.map(item => item.total)
        }],
        xaxis: {
            categories: chartData.map(item => item.label),
            labels: {
                style: {
                    colors: '#94A3B8',
                    fontSize: isMobile ? '10px' : '11px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#94A3B8',
                    fontSize: isMobile ? '10px' : '11px'
                },
                formatter: function (value) {
                    if (value >= 1000000) {
                        return (value / 1000000).toFixed(1) + ' Jt';
                    }
                    if (value >= 1000) {
                        return (value / 1000).toFixed(0) + ' Rb';
                    }
                    return value;
                }
            }
        },
        stroke: {
            curve: 'smooth',
            width: isMobile ? 2.5 : 3
        },
        colors: ['#AE7C18'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        },
        grid: {
            borderColor: '#F1F5F9',
            strokeDashArray: 5,
            padding: {
                left: isMobile ? 0 : 10,
                right: isMobile ? 0 : 10
            }
        },
        markers: {
            size: isMobile ? 3 : 4,
            hover: { size: 6 }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function (value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { show: false }
    };

    new ApexCharts(element, options).render();
});
</script>
@endpush