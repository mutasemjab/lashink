<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $rows, private array $headings)
    {
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
