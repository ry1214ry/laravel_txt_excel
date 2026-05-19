<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TxtExport implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    private array $rows;
    private bool  $hasHeader;
    private string $sheetName;

    public function __construct(array $rows, bool $hasHeader = true, string $sheetName = 'Sheet1')
    {
        $this->rows      = $rows;
        $this->hasHeader = $hasHeader;
        $this->sheetName = $sheetName;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        // Excel sheet names max 31 chars, no special chars
        return mb_substr(preg_replace('/[\/\\\?\*\[\]:]+/', '_', $this->sheetName), 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow    = $sheet->getHighestDataRow();
        $lastCol    = $sheet->getHighestDataColumn();
        $dataRange  = 'A1:' . $lastCol . $lastRow;

        // ── Default: thin borders on all data cells ──────────────────────────
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFD1D5DB'],
                ],
            ],
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
            ],
        ]);

        if ($this->hasHeader && $lastRow >= 1) {
            $headerRange = 'A1:' . $lastCol . '1';

            // ── Header row styling ───────────────────────────────────────────
            $sheet->getStyle($headerRange)->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A5F'],
                ],
                'font' => [
                    'name'  => 'Calibri',
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Freeze top row
            $sheet->freezePane('A2');

            // Zebra-stripe data rows
            if ($lastRow > 1) {
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F4F8'],
                            ],
                        ]);
                    }
                }
            }
        }

        return [];
    }
}