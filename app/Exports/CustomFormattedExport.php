<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomFormattedExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $title;
    protected $subtitle;
    protected $headings;
    protected $data;
    protected $hasTotalRow;

    public function __construct(string $title, string $subtitle, array $headings, array $data, bool $hasTotalRow = false)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->headings = $headings;
        $this->data = collect($data);
        $this->hasTotalRow = $hasTotalRow;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            [$this->title],
            [$this->subtitle],
            $this->headings
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow    = $sheet->getHighestRow();

        $sheet->mergeCells("A1:{$highestColumn}1");
        $sheet->mergeCells("A2:{$highestColumn}2");

        $styles = [
            1 => [
                'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            ],
            2 => [
                'font'      => ['italic' => true, 'color' => ['rgb' => '333333']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            ],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
            ],
        ];

        if ($this->hasTotalRow && $highestRow > 3) {
            $styles[$highestRow] = [
                'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                'borders' => [
                    'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F172A']],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F172A']],
                ],
            ];
        }

        return $styles;
    }
}
