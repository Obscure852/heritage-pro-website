<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

abstract class BaseExport implements FromView{
    protected $viewName;
    protected $data;

    public function __construct($viewName, $data){
        $this->viewName = $viewName;
        $this->data = $data;
    }

    public function view(): View{
        return view($this->viewName, ['data' => $this->data]);
    }
}