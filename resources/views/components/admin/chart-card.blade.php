@props([

    'title',

    'subtitle' => 'Overview of sales performance',

    'chartId',

    'height' => '340',

])

<div
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:shadow-lg">

    {{-- ================= HEADER ================= --}}
    <div
        class="flex flex-col gap-5 border-b border-slate-100 p-6 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <h2
                class="text-xl font-bold text-slate-900">

                {{ $title }}

            </h2>

            <p
                class="mt-1 text-sm text-slate-500">

                {{ $subtitle }}

            </p>

        </div>

        {{-- Filter --}}
        <div
            x-data="{ active:'Monthly' }"
            class="flex rounded-xl bg-slate-100 p-1">

            <button
                @click="active='Weekly'"
                :class="active==='Weekly'
                    ? 'bg-white shadow text-slate-900'
                    : 'text-slate-500'"
                class="rounded-lg px-4 py-2 text-sm font-medium transition">

                Weekly

            </button>

            <button
                @click="active='Monthly'"
                :class="active==='Monthly'
                    ? 'bg-[#AE7C18] text-white shadow'
                    : 'text-slate-500'"
                class="rounded-lg px-4 py-2 text-sm font-medium transition">

                Monthly

            </button>

            <button
                @click="active='Yearly'"
                :class="active==='Yearly'
                    ? 'bg-white shadow text-slate-900'
                    : 'text-slate-500'"
                class="rounded-lg px-4 py-2 text-sm font-medium transition">

                Yearly

            </button>

        </div>

    </div>

    {{-- ================= CHART ================= --}}
    <div
        class="p-6">

        <div
            id="{{ $chartId }}"
            style="height: {{ $height }}px;">

        </div>

    </div>

</div>

{{-- ================= APEX ================= --}}

@once

    {{-- CDN sementara.
         Nanti ketika production kita pindahkan ke npm --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@endonce

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    if(document.querySelector('#{{ $chartId }}')){

        const options = {

            chart: {

                type: 'area',

                height: {{ $height }},

                toolbar: {

                    show: false

                },

                zoom: {

                    enabled: false

                }

            },

            series: [

                {

                    name: 'Sales',

                    data: [

                        120,
                        180,
                        160,
                        220,
                        260,
                        310,
                        280,
                        340,
                        390,
                        430,
                        470,
                        520

                    ]

                }

            ],

            xaxis: {

                categories: [

                    'Jan',

                    'Feb',

                    'Mar',

                    'Apr',

                    'May',

                    'Jun',

                    'Jul',

                    'Aug',

                    'Sep',

                    'Oct',

                    'Nov',

                    'Dec'

                ],

                labels: {

                    style: {

                        colors:'#94A3B8',

                        fontSize:'12px'

                    }

                }

            },

            yaxis: {

                labels: {

                    style: {

                        colors:'#94A3B8'

                    }

                }

            },

            stroke: {

                curve:'smooth',

                width:4

            },

            colors:[

                '#AE7C18'

            ],

            fill:{

                type:'gradient',

                gradient:{

                    shadeIntensity:1,

                    opacityFrom:0.45,

                    opacityTo:0.02,

                    stops:[0,100]

                }

            },

            grid:{

                borderColor:'#E2E8F0',

                strokeDashArray:6

            },

            markers:{

                size:5,

                hover:{

                    size:8

                }

            },

            tooltip:{

                theme:'light'

            },

            dataLabels:{

                enabled:false

            },

            legend:{

                show:false

            }

        };

        new ApexCharts(

            document.querySelector("#{{ $chartId }}"),

            options

        ).render();

    }

});

</script>

@endpush