@extends('layouts.master')
@section('title')
    House Analysis Report
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
        }

        .table th {
            color: rgb(75, 75, 75);
            font-weight: bold;
            white-space: nowrap;
        }

        .table-sm {
            font-size: 12px;
        }

        .house-row td {
            font-weight: bold;
            text-align: left;
            padding-left: 10px;
        }

        .class-row td {
            text-align: center;
        }

        .house-total {
            color: #333;
        }

        .house-total td {
            font-weight: bold;
        }

        .house-total td:nth-child(2) {
            text-align: left;
            padding-left: 10px;
        }

        .grand-total td {
            color: #333;
        }

        .grand-total td:nth-child(1),
        .grand-total td:nth-child(2) {
            text-align: left;
            padding-left: 10px;
        }

        .gender-row {
            background-color: #f8f9fa;
            font-size: 11px;
        }

        .empty-house-cell {
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            border-bottom: none;
            background-color: white;
        }

        .percent-high {
            background-color: #d4edda;
            color: #333;
        }

        .percent-medium {
            background-color: #fff3cd;
            color: #333;
        }

        .percent-low {
            background-color: #f8d7da;
            color: #333;
        }

        .table-light {
            background-color: #f2f2f2;
        }
        
        .chart-container {
            height: 350px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .chart-section {
            margin-top: 30px;
        }
        
        .section-divider {
            margin-top: 30px;
            margin-bottom: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .male-cell {
            background-color: rgba(54, 162, 235, 0.1);
        }
        
        .female-cell {
            background-color: rgba(255, 99, 132, 0.1);
        }
        
        .total-cell {
            background-color: rgba(75, 192, 192, 0.1);
        }

        @media print {
            @page {
                size: landscape;
                margin: 10mm;
            }

            body {
                font-size: 10pt;
            }

            .no-print {
                display: none !important;
            }

            .card {
                box-shadow: none;
                border: none;
            }

            .table th {
                background-color: #343a40 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .house-total {
                color: rgb(88, 84, 84) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .grand-total {
                background-color: #FFC107 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .percent-high {
                background-color: #d4edda !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .percent-medium {
                background-color: #fff3cd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .percent-low {
                background-color: #f8d7da !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .chart-container {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            .male-cell {
                background-color: rgba(54, 162, 235, 0.1) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .female-cell {
                background-color: rgba(255, 99, 132, 0.1) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .total-cell {
                background-color: rgba(75, 192, 192, 0.1) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            House Analysis Report
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="text-muted">
                <i style="font-size:20px;" class="bx bx-download me-2"></i>
            </a>
            <a href="#" class="me-2 text-muted" onclick="printContent()">
                <i style="font-size:20px;" class="bx bx-printer"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex flex-column">
                                <h5 class="mb-0">{{ $school_data->school_name }}</h5>
                                <p class="mb-0">{{ $school_data->physical_address }}</p>
                                <p class="mb-0">{{ $school_data->postal_address }}</p>
                                <p class="mb-0">Tel: {{ $school_data->telephone }} Fax: {{ $school_data->fax }}</p>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="text-start mb-3">House Classes Analysis Report - {{ ucfirst($type) }}</h4>
                    <h6 class="text-start mb-4">Term {{ $currentTerm->term }}, {{ $currentTerm->year }}</h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">House</th>
                                    <th rowspan="2">Class</th>
                                    @foreach (['A','B','C','D','E','U'] as $g)
                                        <th colspan="3">{{ $g }}</th>
                                    @endforeach
                                    <th colspan="3">Total</th>
                                    <th colspan="3">ABC %</th>
                                    <th colspan="3">ABCD %</th>
                                </tr>
                                <tr class="gender-row">
                                    @foreach (['A','B','C','D','E','U', 'Total', 'ABC %', 'ABCD %'] as $g)
                                        <th class="male-cell">M</th>
                                        <th class="female-cell">F</th>
                                        <th class="total-cell">T</th>
                                    @endforeach
                                </tr>
                            </thead>
                        
                            <tbody>
                                @php $stripe = false; @endphp
                        
                                @foreach ($houseAnalysis as $house)
                                    @php $first = true; @endphp
                        
                                    @foreach ($house['classes'] as $cls)
                                        <tr @class(['table-light'=>$stripe])>
                                            @if ($first)
                                                <td rowspan="{{ count($house['classes'])+1 }}">{{ $house['name'] }}</td>
                                                @php $first = false; @endphp
                                            @endif
                        
                                            <td>{{ $cls['class_name'] }}</td>
                                            @foreach (['A','B','C','D','E','U'] as $g)
                                                <td class="male-cell">{{ $cls['grades'][$g]['M'] }}</td>
                                                <td class="female-cell">{{ $cls['grades'][$g]['F'] }}</td>
                                                <td class="total-cell">{{ $cls['grades'][$g]['M'] + $cls['grades'][$g]['F'] }}</td>
                                            @endforeach
                                            
                                            <!-- Totals -->
                                            <td class="male-cell">{{ $cls['male_count'] }}</td>
                                            <td class="female-cell">{{ $cls['female_count'] }}</td>
                                            <td class="total-cell">{{ $cls['total'] }}</td>
                                            
                                            <!-- ABC Percentages -->
                                            <td class="male-cell {{ isset($cls['male_abc_percentage']) && $cls['male_abc_percentage'] >= 50 ? 'percent-high' : (isset($cls['male_abc_percentage']) && $cls['male_abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                                {{ isset($cls['male_abc_percentage']) ? $cls['male_abc_percentage'] : 0 }}
                                            </td>
                                            <td class="female-cell {{ isset($cls['female_abc_percentage']) && $cls['female_abc_percentage'] >= 50 ? 'percent-high' : (isset($cls['female_abc_percentage']) && $cls['female_abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                                {{ isset($cls['female_abc_percentage']) ? $cls['female_abc_percentage'] : 0 }}
                                            </td>
                                            <td class="total-cell {{ $cls['abc_percentage'] >= 50 ? 'percent-high' : ($cls['abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                                {{ $cls['abc_percentage'] }}
                                            </td>
                                            
                                            <!-- ABCD Percentages -->
                                            <td class="male-cell {{ isset($cls['male_abcd_percentage']) && $cls['male_abcd_percentage'] >= 70 ? 'percent-high' : (isset($cls['male_abcd_percentage']) && $cls['male_abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                                {{ isset($cls['male_abcd_percentage']) ? $cls['male_abcd_percentage'] : 0 }}
                                            </td>
                                            <td class="female-cell {{ isset($cls['female_abcd_percentage']) && $cls['female_abcd_percentage'] >= 70 ? 'percent-high' : (isset($cls['female_abcd_percentage']) && $cls['female_abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                                {{ isset($cls['female_abcd_percentage']) ? $cls['female_abcd_percentage'] : 0 }}
                                            </td>
                                            <td class="total-cell {{ $cls['abcd_percentage'] >= 70 ? 'percent-high' : ($cls['abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                                {{ $cls['abcd_percentage'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                        
                                    {{-- House total row --}}
                                    <tr class="house-total">
                                        <td>Total</td>
                                        @foreach (['A','B','C','D','E','U'] as $g)
                                            <td class="male-cell">{{ $house['totals'][$g]['M'] }}</td>
                                            <td class="female-cell">{{ $house['totals'][$g]['F'] }}</td>
                                            <td class="total-cell">{{ $house['totals'][$g]['M'] + $house['totals'][$g]['F'] }}</td>
                                        @endforeach
                                        
                                        <!-- House Totals -->
                                        <td class="male-cell">{{ $house['totals']['male_count'] }}</td>
                                        <td class="female-cell">{{ $house['totals']['female_count'] }}</td>
                                        <td class="total-cell">{{ $house['totals']['total'] }}</td>
                                        
                                        <!-- House Percentages -->
                                        <td class="male-cell {{ isset($house['totals']['male_abc_percentage']) && $house['totals']['male_abc_percentage'] >= 50 ? 'percent-high' : (isset($house['totals']['male_abc_percentage']) && $house['totals']['male_abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                            {{ isset($house['totals']['male_abc_percentage']) ? $house['totals']['male_abc_percentage'] : 0 }}
                                        </td>
                                        <td class="female-cell {{ isset($house['totals']['female_abc_percentage']) && $house['totals']['female_abc_percentage'] >= 50 ? 'percent-high' : (isset($house['totals']['female_abc_percentage']) && $house['totals']['female_abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                            {{ isset($house['totals']['female_abc_percentage']) ? $house['totals']['female_abc_percentage'] : 0 }}
                                        </td>
                                        <td class="total-cell {{ $house['totals']['abc_percentage'] >= 50 ? 'percent-high' : ($house['totals']['abc_percentage'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                            {{ $house['totals']['abc_percentage'] }}
                                        </td>
                                        
                                        <td class="male-cell {{ isset($house['totals']['male_abcd_percentage']) && $house['totals']['male_abcd_percentage'] >= 70 ? 'percent-high' : (isset($house['totals']['male_abcd_percentage']) && $house['totals']['male_abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                            {{ isset($house['totals']['male_abcd_percentage']) ? $house['totals']['male_abcd_percentage'] : 0 }}
                                        </td>
                                        <td class="female-cell {{ isset($house['totals']['female_abcd_percentage']) && $house['totals']['female_abcd_percentage'] >= 70 ? 'percent-high' : (isset($house['totals']['female_abcd_percentage']) && $house['totals']['female_abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                            {{ isset($house['totals']['female_abcd_percentage']) ? $house['totals']['female_abcd_percentage'] : 0 }}
                                        </td>
                                        <td class="total-cell {{ $house['totals']['abcd_percentage'] >= 70 ? 'percent-high' : ($house['totals']['abcd_percentage'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                            {{ $house['totals']['abcd_percentage'] }}
                                        </td>
                                    </tr>
                        
                                    @php $stripe = ! $stripe; @endphp
                                @endforeach
                        
                                {{-- Grand totals row --}}
                                <tr class="grand-total">
                                    <td colspan="2">Grand Total</td>
                                    @foreach (['A','B','C','D','E','U'] as $g)  
                                        <td class="male-cell">{{ $totalGrades[$g]['M'] }}</td>
                                        <td class="female-cell">{{ $totalGrades[$g]['F'] }}</td>
                                        <td class="total-cell">{{ $overallTotals[$g] }}</td>
                                    @endforeach
                                    
                                    <!-- Overall Totals -->
                                    <td class="male-cell">{{ $totalMaleCount }}</td>
                                    <td class="female-cell">{{ $totalFemaleCount }}</td>
                                    <td class="total-cell">{{ $overallTotals['T'] }}</td>
                                    
                                    <!-- Overall Percentages -->
                                    <td class="male-cell {{ isset($overallTotals['ABC_M']) && $overallTotals['ABC_M'] >= 50 ? 'percent-high' : (isset($overallTotals['ABC_M']) && $overallTotals['ABC_M'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                        {{ isset($overallTotals['ABC_M']) ? $overallTotals['ABC_M'] : 0 }}
                                    </td>
                                    <td class="female-cell {{ isset($overallTotals['ABC_F']) && $overallTotals['ABC_F'] >= 50 ? 'percent-high' : (isset($overallTotals['ABC_F']) && $overallTotals['ABC_F'] >= 30 ? 'percent-medium' : 'percent-low') }}">
                                        {{ isset($overallTotals['ABC_F']) ? $overallTotals['ABC_F'] : 0 }}
                                    </td>
                                    <td class="total-cell {{ $overallABCPercentage >= 50 ? 'percent-high' : ($overallABCPercentage >= 30 ? 'percent-medium' : 'percent-low') }}">
                                        {{ $overallABCPercentage }}
                                    </td>
                                    
                                    <td class="male-cell {{ isset($overallTotals['ABCD_M']) && $overallTotals['ABCD_M'] >= 70 ? 'percent-high' : (isset($overallTotals['ABCD_M']) && $overallTotals['ABCD_M'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                        {{ isset($overallTotals['ABCD_M']) ? $overallTotals['ABCD_M'] : 0 }}
                                    </td>
                                    <td class="female-cell {{ isset($overallTotals['ABCD_F']) && $overallTotals['ABCD_F'] >= 70 ? 'percent-high' : (isset($overallTotals['ABCD_F']) && $overallTotals['ABCD_F'] >= 50 ? 'percent-medium' : 'percent-low') }}">
                                        {{ isset($overallTotals['ABCD_F']) ? $overallTotals['ABCD_F'] : 0 }}
                                    </td>
                                    <td class="total-cell {{ $overallABCDPercentage >= 70 ? 'percent-high' : ($overallABCDPercentage >= 50 ? 'percent-medium' : 'percent-low') }}">
                                        {{ $overallABCDPercentage }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Charts Section -->
                    <div class="section-divider"></div>
                    <div class="row chart-section">
                        <div class="col-md-12">
                            <h5 class="text-center mb-3">House Performance Comparison</h5>
                            {{-- Corrected HTML for the chart container --}}
                            <div id="housePerformanceChart" class="chart-container"></div> 
                        </div>
                    </div>
                    
                    <div class="section-divider"></div>
                    <div class="row chart-section">
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Grade Distribution by House</h5>
                             {{-- Corrected HTML for the chart container --}}
                            <div id="gradeDistributionChart" class="chart-container"></div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Gender Distribution by House</h5>
                             {{-- Corrected HTML for the chart container --}}
                             <div id="genderDistributionChart" class="chart-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    
    <script>
        function printContent() {
            window.print();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            window.echartsInstances = {};
            const houseAnalysis = @json($houseAnalysis ?? []); 

            const houseNames = [];
            const abcPercentages = [];
            const abcdPercentages = [];
            const maleCountsByHouse = [];
            const femaleCountsByHouse = [];
            const gradesForChart = ['A', 'B', 'C', 'D', 'E', 'U'];
            const gradesByHouse = {};

            gradesForChart.forEach(grade => {
                gradesByHouse[grade] = [];
            });

            let dataIsValid = false;
            if (houseAnalysis && Object.keys(houseAnalysis).length > 0) {
                
                const houses = Array.isArray(houseAnalysis) ? houseAnalysis : Object.values(houseAnalysis);

                houses.forEach((house, index) => {
                    if (!house || typeof house !== 'object') {
                         console.warn(`Skipping invalid house data at index/key ${index}:`, house);
                         return;
                    }

                    houseNames.push(house.name || `House ${index + 1}`); 
                    abcPercentages.push(house.totals?.abc_percentage ?? 0);
                    abcdPercentages.push(house.totals?.abcd_percentage ?? 0);
                    maleCountsByHouse.push(house.totals?.male_count ?? 0);
                    femaleCountsByHouse.push(house.totals?.female_count ?? 0);
                    
                    gradesForChart.forEach(grade => {
                        const maleGradeCount = house.totals?.[grade]?.['M'] ?? 0;
                        const femaleGradeCount = house.totals?.[grade]?.['F'] ?? 0;
                        gradesByHouse[grade].push(maleGradeCount + femaleGradeCount);
                    });
                });

                 if (houseNames.length > 0) {
                    dataIsValid = true;
                 } else {
                    console.warn("No valid house data found after processing.");
                 }

            } else {
                console.warn("Initial house analysis data is empty or not iterable.");
            }
            
            if (!dataIsValid) {
                 console.log("Stopping chart initialization due to invalid/empty data.");
                 const perfChartDom = document.getElementById('housePerformanceChart');
                 if (perfChartDom) perfChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No data available for House Performance chart.</p>';
                 
                 const gradeDistChartDom = document.getElementById('gradeDistributionChart');
                 if (gradeDistChartDom) gradeDistChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No data available for Grade Distribution chart.</p>';
                 
                 const genderDistChartDom = document.getElementById('genderDistributionChart');
                 if (genderDistChartDom) genderDistChartDom.innerHTML = '<p style="text-align:center; padding:20px;">No data available for Gender Distribution chart.</p>';
                 
                 return; 
            }

            const echartsColors = {
                male: '#5470c6', female: '#ee6666',
                abcPercentage: '#73c0de', abcdPercentage: '#9a60b4',
                gradeA: '#91cc75', gradeB: '#5470c6', gradeC: '#fac858',
                gradeD: '#fc8452', gradeE: '#ee6666', gradeU: '#909399'
            };

            function initChart(domId, option) {
                const chartDom = document.getElementById(domId);
                if (chartDom) { 
                    try {

                        const existingInstance = echarts.getInstanceByDom(chartDom);
                        if (existingInstance) {
                             existingInstance.dispose();
                        }
                        const chart = echarts.init(chartDom);
                        chart.setOption(option);
                        window.echartsInstances[domId] = chart; 
                        window.addEventListener('resize', () => { if(chart && !chart.isDisposed()){ console.log(`Resizing chart: ${domId}`); chart.resize(); } });
                        return chart;
                    } catch (e) {
                        console.error(`Error initializing or setting options for chart #${domId}:`, e);
                        chartDom.innerHTML = `<p style="text-align:center; padding:20px; color: red;">Error loading chart #${domId}.</p>`;
                    }
                } else {
                    console.warn(`Chart container #${domId} not found.`);
                }
                return null;
            }

            const housePerfOption = {
                title: { text: 'House Performance Comparison', left: 'center'},
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' }, valueFormatter: val => val + '%' },
                legend: { data: ['ABC %', 'ABCD %'], top: 30 },
                grid: { top: 70, bottom: 30, left: '3%', right: '4%', containLabel: true },
                xAxis: { type: 'category', data: houseNames },
                yAxis: { type: 'value', name: 'Percentage (%)', min: 0, max: 100, axisLabel: { formatter: '{value}%' } },
                series: [
                    { name: 'ABC %', type: 'bar', data: abcPercentages, color: echartsColors.abcPercentage, label: { show: true, position: 'top', formatter: '{c}%' } },
                    { name: 'ABCD %', type: 'bar', data: abcdPercentages, color: echartsColors.abcdPercentage, label: { show: true, position: 'top', formatter: '{c}%' } }
                ],
                toolbox: { right: 20, feature: { saveAsImage: {}, dataView: {}, magicType: {type: ['line', 'bar']}, restore: {} } }
            };
            initChart('housePerformanceChart', housePerfOption);

            const gradeDistSeries = gradesForChart.map(grade => ({
                name: grade,
                type: 'bar',
                stack: 'total',
                emphasis: { focus: 'series' },
                color: echartsColors['grade' + grade] || '#ccc',
                data: gradesByHouse[grade]
            }));
            
            const gradeDistOption = {
                title: { text: 'Grade Distribution by House', left: 'center' },
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                legend: { data: gradesForChart, top: 30, type: 'scroll' },
                grid: { top: 70, bottom: 30, left: '3%', right: '4%', containLabel: true },
                xAxis: { type: 'category', data: houseNames },
                yAxis: { type: 'value', name: 'Number of Students' },
                series: gradeDistSeries,
                toolbox: { right: 20, feature: { saveAsImage: {}, dataView: {}, magicType: {type: ['line', 'bar', 'stack']}, restore: {} } } 
            };
            initChart('gradeDistributionChart', gradeDistOption);

            const genderDistOption = {
                 title: { text: 'Gender Distribution by House', left: 'center' },
                 tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                 legend: { data: ['Male', 'Female'], top: 30 },
                 grid: { top: 70, bottom: 30, left: '3%', right: '4%', containLabel: true },
                 xAxis: { type: 'category', data: houseNames },
                 yAxis: { type: 'value', name: 'Number of Students' },
                 series: [
                    { name: 'Male', type: 'bar', stack: 'total', data: maleCountsByHouse, color: echartsColors.male, emphasis: { focus: 'series' } },
                    { name: 'Female', type: 'bar', stack: 'total', data: femaleCountsByHouse, color: echartsColors.female, emphasis: { focus: 'series' } }
                 ],
                 toolbox: { right: 20, feature: { saveAsImage: {}, dataView: {}, magicType: {type: ['line', 'bar', 'stack']}, restore: {} } }
            };
            initChart('genderDistributionChart', genderDistOption);

        }); 
    </script>
@endsection