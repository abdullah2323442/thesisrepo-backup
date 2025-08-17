<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class GroupAssignmentImport implements ToArray
{
    public function array(array $array): array
    {
        return $array;
    }
}