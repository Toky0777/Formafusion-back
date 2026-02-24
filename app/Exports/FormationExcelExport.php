<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FormationExcelExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            "Matricule",
            "Formation",
            "Nom",
            "Prénom",
            "Fonction",
            "Salle du formation",
            "Quartier du formation",
            "Statut",
            "Type de formation",
            "Nom de l'entreprise",
            "Nom du centre de formation",
            "Date de debut",
            "Date de fin",
            "Durer du formation en heure",
            "Taux de presence"
        ];
    }

 public function styles(Worksheet $sheet)
{
    return [
        1 => [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F81BD'], // Bleu pro
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ],
        'A2:O1000' => [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_DOTTED,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ],
    ];
}

public function registerEvents(): array
{
    return [
        AfterSheet::class => function(AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();

            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Supprimer ces 2 lignes pour ne plus aligner à droite
            // $sheet->getStyle('N2:N1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            // $sheet->getStyle('O2:O1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle('L2:M1000')->getNumberFormat()->setFormatCode('DD/MM/YYYY');

            $highestRow = $sheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                $cell = 'O' . $row;
                $value = $sheet->getCell($cell)->getValue();

                if (is_numeric($value)) {
                    if ($value < 75) {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('FF0000'); // rouge
                    } else {
                        $sheet->getStyle($cell)->getFont()->getColor()->setRGB('008000'); // vert
                    }
                }
            }
        },
    ];
}

}
