<?php


namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;


class ImportTest implements ToArray
{
    public function Array(Array $tables)
    {
        return $tables;
    }

}
