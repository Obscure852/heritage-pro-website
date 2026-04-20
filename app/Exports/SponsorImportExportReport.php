<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class SponsorImportExportReport implements FromArray{
    protected $data;
    public function __construct(array $data){
        $this->data = $data;
    }

    public function array(): array{
        $header = [
            'Connect ID',
            'Title',
            'First Name',
            'Last Name',
            'Email',
            'Gender',
            'Date of Birth',
            'Nationality',
            'Relation',
            'Status',
            'ID Number',
            'Phone',
            'Profession',
            'Work Place',
            'Year',
        ];

        $rows = [];
        foreach ($this->data as $sponsor) {
            $rows[] = [
                $sponsor['connect_id'],
                $sponsor['title'],
                $sponsor['first_name'],
                $sponsor['last_name'],
                $sponsor['email'],
                $sponsor['gender'],
                $sponsor['date_of_birth'],
                $sponsor['nationality'],
                $sponsor['relation'],
                $sponsor['status'],
                $sponsor['id_number'],
                $sponsor['phone'],
                $sponsor['profession'],
                $sponsor['work_place'],
                $sponsor['year'],
            ];
        }
        return array_merge([$header], $rows);
    }
}
