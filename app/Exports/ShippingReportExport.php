<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ShippingReportExport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows[] = [
            'Tracking', 'Date', 'Sender', 'Origin', 'Status',
            'Weight', 'Subtotal', 'Insurance', 'Tax', 'Total'
        ];

        foreach ($this->data['report'] as $row) {
            $rows[] = [
                $row['tracking'],
                $row['date'],
                $row['sender'],
                $row['origin'],
                $row['status'],
                $row['weight'],
                $row['subtotal'],
                $row['insurance'],
                $row['tax'],
                $row['total'],
            ];
        }

        $rows[] = [
            'Total', '', '', '', '',
            $this->data['totals']['weight'],
            $this->data['totals']['subtotal'],
            $this->data['totals']['insurance'],
            $this->data['totals']['tax'],
            $this->data['totals']['total'],
        ];

        return $rows;
    }
}