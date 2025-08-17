<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class GroupTemplateExport implements FromArray
{
    private $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function array(): array
    {
        return $this->data;
    }
}