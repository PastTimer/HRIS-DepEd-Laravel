<?php

namespace App\Exports;

use App\Models\Personnel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class ServiceRecordFormatExport implements WithEvents, WithTitle
{
    protected $personnel;

    public function __construct(Personnel $personnel)
    {
        $this->personnel = $personnel;
    }

    public function title(): string
    {
        return 'Service Record';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->getDelegate()->getColumnDimension('A')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('B')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('C')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('D')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('E')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('F')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('G')->setAutoSize(true);
                $sheet->getDelegate()->getColumnDimension('H')->setAutoSize(true);

                // Set default font to Arial, size 10 for the whole sheet
                $sheet->getDelegate()->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

                // --- HEADER ---
                // SERVICE RECORD 
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'S E R V I C E  R E C O R D');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setUnderline(true)->setSize(11)->setName('Arial');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Subtitle
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', '(To be Accomplished by Employer)');
                $sheet->getStyle('A2')->getFont()->setSize(10);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // NAME Row
                $sheet->setCellValue('A4', 'NAME:');
                $sheet->setCellValue('B4', $this->personnel->pdsMain->last_name);
                $sheet->setCellValue('C4', $this->personnel->pdsMain->first_name);
                $sheet->mergeCells('C4:D4');
                $sheet->setCellValue('E4', $this->personnel->pdsMain->middle_name);
                $sheet->mergeCells('E4:F4');

                $sheet->setCellValue('B5', '(Surname)');
                $sheet->setCellValue('C5', '(Given Name)');
                $sheet->mergeCells('C5:D5');
                $sheet->setCellValue('E5', '(Middle Name)'); 
                $sheet->mergeCells('E5:F5');

                $sheet->setCellValue('G4', '(If married woman, give also maiden name.) Date herein should be checked from Baptismal Certificate or some reliable documents.');
                $sheet->mergeCells('G4:H8');
                $sheet->getStyle('G4')->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // BIRTH row
                $sheet->setCellValue('A7', 'BIRTH:');
                $sheet->setCellValue('B7', optional($this->personnel->pdsMain)->birth_date);
                $sheet->mergeCells('B7:C7');
                $sheet->setCellValue('D7', optional($this->personnel->pdsMain)->birth_place);
                $sheet->mergeCells('D7:F7');

                $sheet->setCellValue('B8', '(Date of Birth)');
                $sheet->mergeCells('B8:C8');
                $sheet->setCellValue('D8', '(Place of Birth)');
                $sheet->mergeCells('D8:F8');

                $sheet->getStyle('B4:F8')->getAlignment()->setHorizontal('center');

                // Certification Statement
                $sheet->setCellValue('A11', '              This is to certify that the employee named herein above actually rendered services in this Office as shown by the record below each line of which is supported by appointment other papers actually issued by the Office and approved by the authorities concerned.');
                $sheet->mergeCells('A11:H13');
                $sheet->getStyle('A11')->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);



                // --- TABLE HEADER ---
                $sheet->mergeCells('A15:H15');
                $sheet->setCellValue('A15', 'S E R V I C E  O F  A P P O I N T M E N T');
                $sheet->getStyle('A15')->getFont()->setBold(true)->setSize(11)->setName('Arial');
                $sheet->getStyle('A15')->getAlignment()->setHorizontal('center');

                // Table Column Headers
                $sheet->setCellValue('A16', '(INCLUSIVE DATES)');
                $sheet->mergeCells('A16:B16');
                $sheet->mergeCells('C16:C17');
                $sheet->mergeCells('D16:D17');
                $sheet->mergeCells('E16:E17');
                $sheet->mergeCells('F16:F17');
                $sheet->mergeCells('G16:G17');
                $sheet->mergeCells('H16:H17');
                $sheet->setCellValue('C16', "DESIGNATION\n(1)");
                $sheet->setCellValue('D16', "STATUS\n(2)");
                $sheet->setCellValue('E16', "SALARY\n(3)");
                $sheet->setCellValue('F16', "STATION\n(4)");
                $sheet->setCellValue('G16', 'BRCH');
                $sheet->setCellValue('H16', 'L/V ABS w/out pay');

                // Center align all table header and subheader rows (A15:H17)
                $sheet->getStyle('A15:H17')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A15:H17')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // Table Subheaders
                $sheet->setCellValue('A17', 'From');
                $sheet->setCellValue('B17', 'To');
                $sheet->setCellValue('G17', '');
                $sheet->setCellValue('H17', '');

                // Enable wrap text for merged header cells
                $sheet->getStyle('C16')->getAlignment()->setWrapText(true);
                $sheet->getStyle('D16')->getAlignment()->setWrapText(true);
                $sheet->getStyle('E16')->getAlignment()->setWrapText(true);
                $sheet->getStyle('F16')->getAlignment()->setWrapText(true);

                // Add thin borders to the table header and subheader rows (A15:H17)
                $headerRange = 'A15:H17';
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];
                $sheet->getStyle($headerRange)->applyFromArray($borderStyle);

                // --- TABLE DATA ---
                $row = 18;
                foreach ($this->personnel->serviceRecords()->orderByDesc('date_from')->get() as $record) {
                    $sheet->setCellValue('A' . $row, $record->date_from);
                    $sheet->setCellValue('B' . $row, $record->date_to);
                    $sheet->setCellValue('C' . $row, $record->position->title ?? '');
                    $sheet->setCellValue('D' . $row, $record->status);
                    $sheet->setCellValue('E' . $row, $record->salary);
                    $sheet->setCellValue('F' . $row, $record->school->name ?? '');
                    $sheet->setCellValue('G' . $row, $record->branch);
                    $sheet->setCellValue('H' . $row, '');
                    $row++;
                }
                
                // x-x-x line
                $row += 2;
                $mergeXRange = 'A' . $row . ':H' . $row;
                $sheet->mergeCells($mergeXRange);
                // Fill the merged cell with x-x-x pattern to cover all columns
                $xPattern = str_repeat('x-', 100); // Large enough to fill A-H
                $sheet->setCellValue('A' . $row, $xPattern);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal('center');
                $row++;

                // Purpose
                $sheet->setCellValue('A' . $row, 'Purpose:');
                $sheet->setCellValue('B' . $row, 'For Employment');
                $sheet->getStyle('B' . $row)->getFont()->setItalic(true);

                // Compliance
                $row += 6;
                $mergeRange = 'A' . $row . ':H' . ($row + 1);
                $sheet->mergeCells($mergeRange);
                $sheet->setCellValue('A' . $row, '              Issued in compliance with Executive Order No. 54, dated August 10, 1954 and in accordance with Circular No. 58, dated August 10, 1954 of the System.');
                $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)->setHorizontal('left');
                // Add top border to the merged compliance cell
                $sheet->getStyle($mergeRange)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                
                // Certification
                $row += 3;
                $sheet->mergeCells('E' . $row . ':F' . $row);
                $sheet->setCellValue('E' . $row, 'CERTIFIED CORRECT:');
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal('center');
                
                // Date
                $row += 2;
                $sheet->setCellValue('B' . $row, now()->format('Y-m-d'));
                $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal('center');
                $sheet->getStyle('B' . $row)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->setCellValue('B' . ($row + 1), 'Date');
                $sheet->getStyle('B' . ($row + 1))->getAlignment()->setHorizontal('center');

                // Signature
                $sheet->mergeCells('F' . $row . ':H' . $row);
                $sheet->setCellValue('F' . $row, 'ANALLE G. BUÑAG');
                $sheet->getStyle('F' . $row)->getFont()->setBold(true);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal('center');
                $sheet->mergeCells('F' . ($row + 1) . ':H' . ($row + 1));
                $sheet->setCellValue('F' . ($row + 1), 'Administrative Officer V');
                $sheet->getStyle('F' . ($row + 1))->getAlignment()->setHorizontal('center');
            }
        ];
    }
}
