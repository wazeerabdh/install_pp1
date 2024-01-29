@extends('layouts.branch.app')

@section('title', translate('Dashboard'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="d-flex">{{translate('dashboard')}}</h2>
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
                    @include('branch-views.partials._dashboard-order-stats',['data'=>$data])
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-6">
                        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
                            <img width="20" src="{{asset('public/assets/admin/img/icons/earning_statictics.png')}}" alt="Earning Statistics">
                            {{ translate('Earning_statistics') }}
                        </h4>
                    </div>
                    <div class="col-md-6 d-flex justify-content-md-end">
                        <ul class="option-select-btn mb-0">
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden checked>
                                    <span data-earn-type="yearEarn"
                                          onclick="earningStatisticsUpdate(this)">{{ translate('This Year') }}</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden="">
                                    <span data-earn-type="MonthEarn"
                                          onclick="earningStatisticsUpdate(this)">{{ translate('This Month') }}</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden="">
                                    <span data-earn-type="WeekEarn"
                                          onclick="earningStatisticsUpdate(this)">{{ translate('This Week') }}</span>
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
    </div>
@endsection

@push('script')
    <script src="{{asset('public/assets/admin/vendor/chart.js/dist/Chart.min.js')}}"></script>
    <script src="{{asset('public/assets/admin/vendor/chart.js.extensions/chartjs-extensions.js')}}"></script>
    <script src="{{asset('public/assets/admin/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js')}}"></script>
@endpush


@push('script_2')
    <script>
        Chart.plugins.unregister(ChartDataLabels);
        let updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));
    </script>

    <script>
        function order_stats_update(type) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('branch.order-stats')}}",
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
        function earningStatisticsUpdate(t) {
            let value = $(t).attr('data-earn-type');
            $.ajax({
                url: '{{route('branch.dashboard.earning-statistics')}}',
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
