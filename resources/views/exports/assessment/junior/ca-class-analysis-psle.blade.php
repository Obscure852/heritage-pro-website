<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th rowspan="3">Grade</th>
            <!-- Individual Grades -->
            <th style="text-align: center;" colspan="3">A</th>
            <th style="text-align: center;" colspan="3">B</th>
            <th style="text-align: center;" colspan="3">C</th>
            <th style="text-align: center;" colspan="3">D</th>
            <th style="text-align: center;" colspan="3">E</th>
            <th style="text-align: center;" colspan="3">U</th>
            <!-- Total Column -->
            <th style="text-align: center;" colspan="3">Total</th>
            <!-- Combined Metrics -->
            <th style="text-align: center;" colspan="6">AB</th>
            <th style="text-align: center;" colspan="6">ABC</th>
            <th style="text-align: center;" colspan="6">ABCD</th>
            <th style="text-align: center;" colspan="6">DEU</th>
        </tr>
        <tr>
            <!-- Individual grades have no sub-headers other than M,F,T -->
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <!-- Total Column sub-header -->
            <th colspan="3"></th>

            <!-- AB: Raw and % -->
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <!-- ABC -->
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <!-- ABCD -->
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <!-- DEU -->
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
        </tr>
        <tr>
            <!-- Individual Grades -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- A -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- B -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- C -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- D -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- E -->
            <th>M</th>
            <th>F</th>
            <th>T</th> <!-- U -->
            <!-- Total Column M,F,T -->
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- AB Raw -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <!-- AB % -->
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- ABC Raw -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <!-- ABC % -->
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- ABCD Raw -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <!-- ABCD % -->
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- DEU Raw -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <!-- DEU % -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total</td>
            <!-- Individual Grades with Totals -->
            <td>{{ $psleGradeCounts['A']['M'] }}</td>
            <td>{{ $psleGradeCounts['A']['F'] }}</td>
            <td>{{ $psleGradeCounts['A']['M'] + $psleGradeCounts['A']['F'] }}</td>
            <td>{{ $psleGradeCounts['B']['M'] }}</td>
            <td>{{ $psleGradeCounts['B']['F'] }}</td>
            <td>{{ $psleGradeCounts['B']['M'] + $psleGradeCounts['B']['F'] }}</td>
            <td>{{ $psleGradeCounts['C']['M'] }}</td>
            <td>{{ $psleGradeCounts['C']['F'] }}</td>
            <td>{{ $psleGradeCounts['C']['M'] + $psleGradeCounts['C']['F'] }}</td>
            <td>{{ $psleGradeCounts['D']['M'] }}</td>
            <td>{{ $psleGradeCounts['D']['F'] }}</td>
            <td>{{ $psleGradeCounts['D']['M'] + $psleGradeCounts['D']['F'] }}</td>
            <td>{{ $psleGradeCounts['E']['M'] }}</td>
            <td>{{ $psleGradeCounts['E']['F'] }}</td>
            <td>{{ $psleGradeCounts['E']['M'] + $psleGradeCounts['E']['F'] }}</td>
            <td>{{ $psleGradeCounts['U']['M'] }}</td>
            <td>{{ $psleGradeCounts['U']['F'] }}</td>
            <td>{{ $psleGradeCounts['U']['M'] + $psleGradeCounts['U']['F'] }}</td>
            
            <!-- Total Column Data -->
            <td>{{ $psleTotalM }}</td>
            <td>{{ $psleTotalF }}</td>
            <td>{{ $psleTotalM + $psleTotalF }}</td>

            <!-- AB Raw -->
            <td>{{ $psleAB_M }}</td>
            <td>{{ $psleAB_F }}</td>
            <td>{{ $psleAB_T }}</td>
            <!-- AB % -->
            <td>{{ $psleAB_M_Percentage }}</td>
            <td>{{ $psleAB_F_Percentage }}</td>
            <td>{{ $psleAB_T_Percentage ?? '' }}</td>

            <!-- ABC Raw -->
            <td>{{ $psleABC_M }}</td>
            <td>{{ $psleABC_F }}</td>
            <td>{{ $psleABC_T }}</td>
            <!-- ABC % -->
            <td>{{ $psleABC_M_Percentage }}</td>
            <td>{{ $psleABC_F_Percentage }}</td>
            <td>{{ $psleABC_T_Percentage }}</td>

            <!-- ABCD Raw -->
            <td>{{ $psleABCD_M }}</td>
            <td>{{ $psleABCD_F }}</td>
            <td>{{ $psleABCD_T }}</td>
            <!-- ABCD % -->
            <td>{{ $psleABCD_M_Percentage }}</td>
            <td>{{ $psleABCD_F_Percentage }}</td>
            <td>{{ $psleABCD_T_Percentage }}</td>

            <!-- DEU Raw -->
            <td>{{ $psleDEU_M }}</td>
            <td>{{ $psleDEU_F }}</td>
            <td>{{ $psleDEU_T }}</td>
            <!-- DEU % -->
            <td>{{ $psleDEU_M_Percentage }}</td>
            <td>{{ $psleDEU_F_Percentage }}</td>
            <td>{{ $psleDEU_T_Percentage }}</td>
        </tr>
    </tbody>
</table>