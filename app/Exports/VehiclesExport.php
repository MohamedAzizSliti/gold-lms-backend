<?php

namespace App\Exports;

use App\Helpers\Helpers;
use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Enums\RoleEnum;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VehiclesExport implements FromCollection, WithMapping, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Vehicle::notDeleted()
        ->byRole(Helpers::getCurrentRoleName(), Helpers::getCurrentUserId())
        ->get();
    }

    public function columns(): array
    {
        return [
            "id",
            "title",
            "Immatriculation",

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Add first header row
                $sheet->setCellValue('A1', ''); // Set header content
                $sheet->mergeCells('B1:C1'); // Merge cells for header
                $sheet->setCellValue('B1', 'Voiture'); // Set header content
                $sheet->mergeCells('D1:F1'); // Merge cells for header
                $sheet->setCellValue('D1', 'Conducteur'); // Set header content
                $sheet->mergeCells('G1:J1'); // Merge cells for header
                $sheet->setCellValue('G1', 'Appareil GPS'); // Set header content
                $sheet->mergeCells('K1');
                $sheet->setCellValue('K1', ''); // Set header content
                // Add second header row
                $sheet->setCellValue('A2', 'Id');
                $sheet->setCellValue('B2', 'Titre');
                $sheet->setCellValue('C2', 'Immatriculation');
                $sheet->setCellValue('D2', 'Nom');
                $sheet->setCellValue('E2', 'Téléphone');
                $sheet->setCellValue('F2', 'Email');
                $sheet->setCellValue('G2', 'IMEI');
                $sheet->setCellValue('H2', 'Téléphone');
                $sheet->setCellValue('I2', 'Opérateur');
                $sheet->setCellValue('J2', 'Protocol');
                $sheet->setCellValue('K2', 'Date d\'ajout');

                // Apply styles to the headers
                $sheet->getStyle('A1:K2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'd1a744'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'], // White border color
                        ],
                    ],
                ]);

                // Adjust column widths
                $sheet->getColumnDimension('A')->setWidth(5); // Adjust Name column
                $sheet->getColumnDimension('B')->setWidth(40); // Adjust Email column
                $sheet->getColumnDimension('C')->setWidth(30); // Adjust Date column
                $sheet->getColumnDimension('D')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('E')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('F')->setWidth(25); // Adjust Date column
                $sheet->getColumnDimension('G')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('H')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('I')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('J')->setWidth(20); // Adjust Date column
                $sheet->getColumnDimension('K')->setWidth(20); // Adjust Date column

                // Align all content (data rows) to the left
                $sheet->getStyle('A3:K100')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }

    public function map($product): array
    {
        $driver = $product->driver;
        $device = $product->device;

        return [
            $product->id,
            $product->title,
            $product->immatriculation,
            $driver ?->name,
            $driver ?->phone,
            $driver ?->email,
            $device ?->imei,
            $device ?->phone,
            $device ?->operator,
            '', // Protocol
            $product->created_at
        ];
    }




}
