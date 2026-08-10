@props([
    'title',
    'subtitle' => 'Overview of sales performance',
    'chartId',
    'height' => '340',
])

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-4 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <h2 class="text-lg font-bold text-slate-900 sm:text-xl">{{ $title }}</h2>
            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">{{ $subtitle }}</p>
        </div>

        {{-- Filter --}}
        <div x-data="{ active: 'Monthly' }" class="flex w-full rounded-xl bg-slate-100 p-1 sm:w-auto">
            <button @click="active='Mingguan'" 
                :class="active==='Mingguan' ? 'bg-white shadow text-slate-900' : 'text-slate-500'" 
                class="flex-1 rounded-lg px-3 py-1.5 text-xs font-medium transition sm:flex-none sm:px-4 sm:py-2 sm:text-sm">
                Mingguan
            </button>
            <button @click="active='Bulanan'" 
                :class="active==='Bulanan' ? 'bg-[#AE7C18] text-white shadow' : 'text-slate-500'" 
                class="flex-1 rounded-lg px-3 py-1.5 text-xs font-medium transition sm:flex-none sm:px-4 sm:py-2 sm:text-sm">
                Bulanan
            </button>
            <button @click="active='Tahunan'" 
                :class="active==='Tahunan' ? 'bg-white shadow text-slate-900' : 'text-slate-500'" 
                class="flex-1 rounded-lg px-3 py-1.5 text-xs font-medium transition sm:flex-none sm:px-4 sm:py-2 sm:text-sm">
                Tahunan
            </button>
        </div>
    </div>

    {{-- ================= CHART ================= --}}
    <div class="p-3 sm:p-6">
        <div id="{{ $chartId }}" style="height: {{ $height }}px;"></div>
    </div>

</div>

{{-- ================= APEX ================= --}}
@once
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endonce

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('#{{ $chartId }}')) {
        const options = {
            chart: {
                type: 'area',
                height: {{ $height }},
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            series: [{
                name: 'Sales',
                data: [120, 180, 160, 220, 260, 310, 280, 340, 390, 430, 470, 520]
            }],
            xaxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                labels: {
                    style: { colors: '#94A3B8', fontSize: '11px' }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#94A3B8', fontSize: '11px' }
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#AE7C18'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.02,
                    stops: [0, 100]
                }
            },
            grid: {
                borderColor: '#E2E8F0',
                strokeDashArray: 6
            },
            markers: {
                size: 4,
                hover: { size: 6 }
            },
            tooltip: { theme: 'light' },
            dataLabels: { enabled: false },
            legend: { show: false }
        };

        new ApexCharts(
            document.querySelector("#{{ $chartId }}"),
            options
        ).render();
    }
});
</script>
@endpush