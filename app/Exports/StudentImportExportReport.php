<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class StudentImportExportReport implements FromArray{
    protected $data;

    public function __construct(array $data){
         $this->data = $data;
    }

    public function array(): array{
         $header = [
              'Connect ID',
              'First Name',
              'Last Name',
              'Middle Name',
              'Gender',
              'Date of Birth',
              'Nationality',
              'ID Number',
              'Status',
              'Type',
              'Grade',
              'Class',
              'Year',
              'Parent First Name',
              'Parent Last Name',
              'Parent Gender',
              'Parent Date of Birth',
              'Parent ID Number',
              'Parent Relation',
              'Parent Status',
              'Parent Phone',
              'Parent Profession',
              'Boarding',
         ];

         $rows = [];
         foreach ($this->data as $student) {
              $rows[] = [
                  $student['connect_id'],
                  $student['first_name'],
                  $student['last_name'],
                  $student['middle_name'],
                  $student['gender'],
                  $student['date_of_birth'],
                  $student['nationality'],
                  $student['id_number'],
                  $student['status'],
                  $student['type'],
                  $student['grade'],
                  $student['class'],
                  $student['year'],
                  $student['parent_first_name'],
                  $student['parent_last_name'],
                  $student['parent_gender'],
                  $student['parent_date_of_birth'],
                  $student['parent_id_number'],
                  $student['parent_relation'],
                  $student['parent_status'],
                  $student['parent_phone'],
                  $student['parent_profession'],
              ];
         }

         return array_merge([$header], $rows);
    }
}
