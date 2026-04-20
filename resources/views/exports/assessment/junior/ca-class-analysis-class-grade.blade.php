<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th rowspan="3">Grade</th>
            <!-- Individual Grades -->
            <th style="text-align: center;" colspan="3">M</th>
            <th style="text-align: center;" colspan="3">A</th>
            <th style="text-align: center;" colspan="3">B</th>
            <th style="text-align: center;" colspan="3">C</th>
            <th style="text-align: center;" colspan="3">D</th>
            <th style="text-align: center;" colspan="3">E</th>
            <th style="text-align: center;" colspan="3">U</th>
            <!-- Total Column -->
            <th style="text-align: center;" colspan="3">Total</th>
            <!-- Combined Metrics -->
            <th style="text-align: center;" colspan="6">MAB</th>
            <th style="text-align: center;" colspan="6">MABC</th>
            <th style="text-align: center;" colspan="6">MABCD</th>
            <th style="text-align: center;" colspan="6">DEU</th>
            <th style="text-align: center;" colspan="6">X</th>
        </tr>
        <tr>
            <!-- No additional category labels for individual grades -->
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <th colspan="3"></th>
            <!-- Total Column sub-header -->
            <th colspan="3"></th>

            <!-- Combined metrics second-level headers (Raw and %) -->
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
            <th style="text-align: center;" colspan="3">Raw</th>
            <th style="text-align: center;" colspan="3">%</th>
        </tr>
        <tr>
            <!-- Individual Grades (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <!-- Total Column M,F,T -->
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- MAB Raw (M,F,T) and % (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- MABC Raw (M,F,T) and % (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- MABCD Raw (M,F,T) and % (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- DEU Raw (M,F,T) and % (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>

            <!-- X Raw (M,F,T) and % (M,F,T) -->
            <th>M</th>
            <th>F</th>
            <th>T</th>
            <th>M</th>
            <th>F</th>
            <th>T</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total</td>
            <!-- Individual Grades with Totals -->
            <td>{{ $gradeCounts['M']['M'] }}</td>
            <td>{{ $gradeCounts['M']['F'] }}</td>
            <td>{{ $gradeCounts['M']['M'] + $gradeCounts['M']['F'] }}</td>
            <td>{{ $gradeCounts['A']['M'] }}</td>
            <td>{{ $gradeCounts['A']['F'] }}</td>
            <td>{{ $gradeCounts['A']['M'] + $gradeCounts['A']['F'] }}</td>
            <td>{{ $gradeCounts['B']['M'] }}</td>
            <td>{{ $gradeCounts['B']['F'] }}</td>
            <td>{{ $gradeCounts['B']['M'] + $gradeCounts['B']['F'] }}</td>
            <td>{{ $gradeCounts['C']['M'] }}</td>
            <td>{{ $gradeCounts['C']['F'] }}</td>
            <td>{{ $gradeCounts['C']['M'] + $gradeCounts['C']['F'] }}</td>
            <td>{{ $gradeCounts['D']['M'] }}</td>
            <td>{{ $gradeCounts['D']['F'] }}</td>
            <td>{{ $gradeCounts['D']['M'] + $gradeCounts['D']['F'] }}</td>
            <td>{{ $gradeCounts['E']['M'] }}</td>
            <td>{{ $gradeCounts['E']['F'] }}</td>
            <td>{{ $gradeCounts['E']['M'] + $gradeCounts['E']['F'] }}</td>
            <td>{{ $gradeCounts['U']['M'] }}</td>
            <td>{{ $gradeCounts['U']['F'] }}</td>
            <td>{{ $gradeCounts['U']['M'] + $gradeCounts['U']['F'] }}</td>
            
            <!-- Total Column Data -->
            <td>{{ $maleCount }}</td>
            <td>{{ $femaleCount }}</td>
            <td>{{ $totalStudents }}</td>

            <!-- MAB Raw -->
            <td>{{ $mab_M }}</td>
            <td>{{ $mab_F }}</td>
            <td>{{ $mab_T }}</td>
            <!-- MAB % -->
            <td>{{ $mab_M_Percentage }}</td>
            <td>{{ $mab_F_Percentage }}</td>
            <td>{{ $mab_T_percentage }}</td>

            <!-- MABC Raw -->
            <td>{{ $mabc_M }}</td>
            <td>{{ $mabc_F }}</td>
            <td>{{ $mabc_T }}</td>
            <!-- MABC % -->
            <td>{{ $mabc_M_Percentage }}</td>
            <td>{{ $mabc_F_Percentage }}</td>
            <td>{{ $mabc_T_percentage }}</td>

            <!-- MABCD Raw -->
            <td>{{ $mabcd_M }}</td>
            <td>{{ $mabcd_F }}</td>
            <td>{{ $mabcd_T }}</td>
            <!-- MABCD % -->
            <td>{{ $mabcd_M_Percentage }}</td>
            <td>{{ $mabcd_F_Percentage }}</td>
            <td>{{ $mabcd_T_percentage }}</td>

            <!-- DEU Raw -->
            <td>{{ $deu_M }}</td>
            <td>{{ $deu_F }}</td>
            <td>{{ $deu_T }}</td>
            <!-- DEU % -->
            <td>{{ $deu_M_Percentage }}</td>
            <td>{{ $deu_F_Percentage }}</td>
            <td>{{ $deu_T_percentage }}</td>

            <!-- X Raw -->
            <td>{{ $x_M }}</td>
            <td>{{ $x_F }}</td>
            <td>{{ $x_T }}</td>
            <!-- X % -->
            <td>{{ $x_M_Percentage }}</td>
            <td>{{ $x_F_Percentage }}</td>
            <td>{{ $x_T_Percentage }}</td>
        </tr>
    </tbody>
</table>