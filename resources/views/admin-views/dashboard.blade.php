@extends('layouts.admin.app')

@section('title', translate('Dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div>
            <h2 class="mb-1 text--primary">{{translate('welcome')}}, {{optional(auth('admin'))->user()->f_name}}.</h2>
            <p class="text-dark fs-12">{{translate('welcome')}} {{translate('admin')}}, {{translate('_here_is_your_business_statistics')}}.</p>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row justify-content-between align-items-center g-2 mb-3">
                    <div class="col-auto">
                        <h4 class="d-flex align-items-center gap-10 mb-0">
                            <img width="20" src="{{asset('public/assets/admin/img/icons/business_analytics.png')}}" alt="{{ translate('Business Analytics') }}">
                            {{translate('Business_Analytics')}}
                        </h4>
                    </div>
                    <div class="col-auto">
                        <select class="custom-select mn-w200" name="statistics_type" onchange="order_stats_update(this.value)">
                            <option value="overall" {{session()->has('statistics_type') && session('statistics_type') == 'overall'?'selected':''}}>
                                {{ translate('Overall Statistics') }}
                            </option>
                            <option value="today" {{session()->has('statistics_type') && session('statistics_type') == 'today'?'selected':''}}>
                                {{ translate("Today's Statistics") }}
                            </option>
                            <option value="this_month" {{session()->has('statistics_type') && session('statistics_type') == 'this_month'?'selected':''}}>
                                {{ translate("This Month's Statistics") }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row g-2" id="order_stats">
                    @include('admin-views.partials._dashboard-order-stats',['data'=>$data])
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img width="20" src="{{asset('public/assets/admin/img/icons/earning_statictics.png')}}" alt="{{ translate('Earning Statistics') }}">
                            {{ translate('Earning_statistics') }}
                        </h4>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <ul class="option-select-btn mb-0">
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden checked>
                                    <span data-earn-type="yearEarn"
                                          onclick="earningStatisticsUpdate(this)">{{ translate('this_year') }}</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden="">
                                    <span data-earn-type="MonthEarn"
                                    onclick="earningStatisticsUpdate(this)">{{ translate('this_month') }}</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden="">
                                    <span data-earn-type="WeekEarn"
                                          onclick="earningStatisticsUpdate(this)">{{ translate('this_week') }}</span>
                                </label>
                            </li>
                        </ul>
                    </div>

                </div>

                <div class="chartjs-custom height-20rem" id="set-new-graph">
                    <canvas id="updatingData"
                            data-hs-chartjs-options='{
                    "type": "bar",
                    "data": {
                        "labels": ["Jan","Feb","Mar","April","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
                        "datasets": [
                        {
                            "data": [{{$earning[1]}},{{$earning[2]}},{{$earning[3]}},{{$earning[4]}},{{$earning[5]}},{{$earning[6]}},{{$earning[7]}},{{$earning[8]}},{{$earning[9]}},{{$earning[10]}},{{$earning[11]}},{{$earning[12]}}],
                            "backgroundColor": "#673ab7",
                            "borderColor": "#673ab7"
                        }
                        ]
                    },
                    "options": {
                        "legend": {
                            "display": false,
                            "position": "top",
                            "align": "center",
                            "labels": {
                                "fontColor": "#758590",
                                "fontSize": 14
                            }
                        },
                        "scales": {
                            "yAxes": [{
                                "gridLines": {
                                    "color": "rgba(180, 208, 224, 0.3)",
                                    "borderDash": [8, 4],
                                    "drawBorder": false,
                                    "zeroLineColor": "rgba(180, 208, 224, 0.3)"
                                },
                                "ticks": {
                                    "beginAtZero": true,
                                    "fontSize": 12,
                                    "fontColor": "#5B6777",
                                    "padding": 10,
                                    "postfix": "{{ Helpers::currency_symbol() }}"
                                }
                            }],
                            "xAxes": [{
                                "gridLines": {
                                    "color": "rgba(180, 208, 224, 0.3)",
                                    "display": true,
                                    "drawBorder": true,
                                    "zeroLineColor": "rgba(180, 208, 224, 0.3)"
                                },
                                "ticks": {
                                    "fontSize": 12,
                                    "fontColor": "#5B6777",
                                    "fontFamily": "Open Sans, sans-serif",
                                    "padding": 5
                                },
                                "categoryPercentage": 0.5,
                                "maxBarThickness": "7"
                            }]
                        },
                        "cornerRadius": 3,
                        "tooltips": {
                            "prefix": " ",
                            "hasIndicator": true,
                            "mode": "index",
                            "intersect": false
                        },
                        "hover": {
                            "mode": "nearest",
                            "intersect": true
                        }
                    }
                    }'></canvas>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img width="20" src="{{asset('public/assets/admin/img/icons/business_overview.png')}}" alt="{{ translate('business overview') }}">
                            {{ translate('Total Business Overview') }}
                        </h4>
                    </div>

                    <div class="card-body" id="business-overview-board">
                        <div class="chartjs-custom position-relative h-400">
                            <canvas id="business-overview"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    @include('admin-views.partials._top-selling-products',['top_sell'=>$data['top_sell']])
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    @include('admin-views.partials._most-rated-products',['most_rated_products'=>$data['most_rated_products']])
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    @include('admin-views.partials._top-customer',['top_customer'=>$data['top_customer']])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin/vendor/chart.js/dist/Chart.min.js')}}"></script>
    <script src="{{asset('public/assets/admin/vendor/chart.js.extensions/chartjs-extensions.js')}}"></script>
    <script src="{{asset('public/assets/admin/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js')}}"></script>
@endpush


@push('script_2')
    <script>
        'use strict';

        let ctx = document.getElementById('business-overview');
        let myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    '{{translate("Customer")}} ( {{$data['customer']}} )',
                    '{{translate("Product")}} ( {{$data['product']}} )',
                    '{{translate("Order")}} ( {{$data['order']}} )',
                    '{{translate("Category")}} ( {{$data['category']}} )',
                    '{{translate("Branch")}} ( {{$data['branch']}} )',
                ],
                datasets: [{
                    label: 'Business',
                    data: ['{{$data['customer']}}', '{{$data['product']}}', '{{$data['order']}}', '{{$data['category']}}', '{{$data['branch']}}'],
                    backgroundColor: [
                        '#673ab7',
                        '#346751',
                        '#343A40',
                        '#7D5A50',
                        '#C84B31',
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                "legend": {
                    "display": true,
                    "position": "bottom",
                    "align": "center",
                    "labels": {
                        "fontColor": "#758590",
                        "fontSize": 14,
                        padding: 20
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    },
                }
            }
        });
    </script>
    <script>
        function order_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('admin.order-stats')}}",
                type: "post",
                data: {
                    statistics_type: type,
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (data) {
                    $('#order_stats').html(data.view)
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }
    </script>

    <script>
        Chart.plugins.unregister(ChartDataLabels);

        $('.js-chart').each(function () {
            $.HSCore.components.HSChartJS.init($(this));
        });

        let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

        $('[data-toggle="chart-bar"]').click(function (e) {
            let keyDataset = $(e.currentTarget).attr('data-datasets')

            if (keyDataset === 'lastWeek') {
                updatingChart.data.labels = ["Apr 22", "Apr 23", "Apr 24", "Apr 25", "Apr 26", "Apr 27", "Apr 28", "Apr 29", "Apr 30", "Apr 31"];
                updatingChart.data.datasets = [
                    {
                        "data": [120, 250, 300, 200, 300, 290, 350, 100, 125, 320],
                        "backgroundColor": "#377dff",
                        "hoverBackgroundColor": "#377dff",
                        "borderColor": "#377dff"
                    },
                    {
                        "data": [250, 130, 322, 144, 129, 300, 260, 120, 260, 245, 110],
                        "backgroundColor": "#e7eaf3",
                        "borderColor": "#e7eaf3"
                    }
                ];
                updatingChart.update();
            } else {
                updatingChart.data.labels = ["May 1", "May 2", "May 3", "May 4", "May 5", "May 6", "May 7", "May 8", "May 9", "May 10"];
                updatingChart.data.datasets = [
                    {
                        "data": [200, 300, 290, 350, 150, 350, 300, 100, 125, 220],
                        "backgroundColor": "#377dff",
                        "hoverBackgroundColor": "#377dff",
                        "borderColor": "#377dff"
                    },
                    {
                        "data": [150, 230, 382, 204, 169, 290, 300, 100, 300, 225, 120],
                        "backgroundColor": "#e7eaf3",
                        "borderColor": "#e7eaf3"
                    }
                ]
                updatingChart.update();
            }
        })


        $('.js-chart-datalabels').each(function () {
            $.HSCore.components.HSChartJS.init($(this), {
                plugins: [ChartDataLabels],
                options: {
                    plugins: {
                        datalabels: {
                            anchor: function (context) {
                                let value = context.dataset.data[context.dataIndex];
                                return value.r < 20 ? 'end' : 'center';
                            },
                            align: function (context) {
                                let value = context.dataset.data[context.dataIndex];
                                return value.r < 20 ? 'end' : 'center';
                            },
                            color: function (context) {
                                let value = context.dataset.data[context.dataIndex];
                                return value.r < 20 ? context.dataset.backgroundColor : context.dataset.color;
                            },
                            font: function (context) {
                                let value = context.dataset.data[context.dataIndex],
                                    fontSize = 25;

                                if (value.r > 50) {
                                    fontSize = 35;
                                }

                                if (value.r > 70) {
                                    fontSize = 55;
                                }

                                return {
                                    weight: 'lighter',
                                    size: fontSize
                                };
                            },
                            offset: 2,
                            padding: 0
                        }
                    }
                },
            });
        });

    </script>

    <script>
        function earningStatisticsUpdate(t) {
            let value = $(t).attr('data-earn-type');
            $.ajax({
                url: '{{route('admin.dashboard.earning-statistics')}}',
                type: 'GET',
                data: {
                    type: value
                },
                beforeSend: function () {
                    $('#loading').show()
                },
                success: function (response_data) {
                    document.getElementById("updatingData").remove();
                    let graph = document.createElement('canvas');
                    graph.setAttribute("id", "updatingData");
                    document.getElementById("set-new-graph").appendChild(graph);
                    let ctx = document.getElementById("updatingData").getContext("2d");

                    let options = {
                        responsive: true,
                        bezierCurve: false,
                         maintainAspectRatio: false,
                        legend: {
                            display: false,
                            position: "top",
                            align: "center",
                            labels: {
                                fontColor: "#758590",
                                fontSize: 14
                            }
                        },
                        scales: {
                            yAxes: [{
                                gridLines: {
                                    color: "rgba(180, 208, 224, 0.3)",
                                    borderDash: [8, 4],
                                    drawBorder: false,
                                    zeroLineColor: "rgba(180, 208, 224, 0.3)"
                                },
                                ticks: {
                                    beginAtZero: true,
                                    fontSize: 12,
                                    fontColor: "#5B6777",
                                    padding: 10,
                                    postfix: "{{ Helpers::currency_symbol() }}"
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    color: "rgba(180, 208, 224, 0.3)",
                                    display: true,
                                    drawBorder: true,
                                    zeroLineColor: "rgba(180, 208, 224, 0.3)"
                                },
                                ticks: {
                                    fontSize: 12,
                                    fontColor: "#5B6777",
                                    fontFamily: "Open Sans, sans-serif",
                                    padding: 5
                                },
                                categoryPercentage: 0.5,
                                maxBarThickness: "7"
                            }]
                        },
                        cornerRadius: 3,
                        tooltips: {
                            prefix: " ",
                            hasIndicator: true,
                            mode: "index",
                            intersect: false
                        },
                        hover: {
                            mode: "nearest",
                            intersect: true
                        }
                    };
                    let myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [
                                {
                                    label: "{{translate('Earning')}}",
                                    data: [],
                                    backgroundColor: "#673ab7",
                                    borderColor: "#673ab7"
                                }
                            ]
                        },
                        options: options
                    });
                    myChart.data.labels = response_data.earning_label;
                    myChart.data.datasets[0].data = response_data.earning;
                    myChart.update();
                },
                complete: function () {
                    $('#loading').hide()
                }
            });
        }
    </script>

@endpush
