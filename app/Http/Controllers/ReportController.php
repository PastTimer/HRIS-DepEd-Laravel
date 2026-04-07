<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\School;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $schools = [];
        if (auth()->user()->hasRole('admin')) {
            $schools = School::where('is_active', true)->orderBy('name')->get();
        }
        return view('report.index', compact('schools'));
    }

    public function generate(Request $request)
    {
        // Permissions
        $user = auth()->user();
        $school_filter = null;
        if ($user->hasRole('school')) {
            $school_filter = $user->school_id;
        } elseif ($user->hasRole('admin') && $request->filled('school_id')) {
            $school_filter = $request->input('school_id');
        }

        // DEBUG: Confirm route is hit
        // Remove this dd() after confirming
        // dd('generate route hit');

        // Query ICT equipment inventory with assigned personnel
        $query = DB::table('equipment as e')
            ->leftJoin('schools as s', 'e.school_id', '=', 's.id')
            ->leftJoin('personnel as p', 'e.accountable_officer_id', '=', 'p.id')
            ->leftJoin('pds_main as d', 'd.personnel_id', '=', 'p.id')
            ->select([
                'e.property_no',
                'e.item',
                'e.brand_manufacturer',
                'e.model',
                'e.serial_number',
                'e.acquisition_cost',
                'e.equipment_condition',
                'e.remarks',
                's.name as school_name',
                'd.last_name as officer_last_name',
                'd.first_name as officer_first_name',
                'd.middle_name as officer_middle_name',
                'd.extension_name as officer_extension_name',
            ]);
        if ($school_filter) {
            $query->where('e.school_id', $school_filter);
        }
        $equipment = $query->orderBy('s.name')->orderBy('e.item')->orderBy('e.property_no')->get();

        // Load template
        $templatePath = resource_path('templates/SchoolInventory.xlsx');
        if (!file_exists($templatePath)) {
            return response()->make('Template file not found: ' . $templatePath, 500);
        }
        try {
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\Exception $e) {
            return response()->make('Error loading template: ' . $e->getMessage(), 500);
        }
        // Find Equipment sheet
        $equipmentSheet = null;
        $equipmentIndex = -1;
        foreach ($spreadsheet->getAllSheets() as $i => $sheet) {
            if (stripos($sheet->getTitle(), 'Equipment') !== false) {
                $equipmentSheet = $sheet;
                $equipmentIndex = $i;
                break;
            }
        }
        if (!$equipmentSheet) {
            return response()->make('Equipment sheet not found in template. Sheet names: ' .
                implode(', ', array_map(fn($s) => $s->getTitle(), $spreadsheet->getAllSheets())), 500);
        }
        $spreadsheet->setActiveSheetIndex($equipmentIndex);
        // Add column headers
        $headers = [
            'No.',
            'Property No.',
            'Item',
            'Brand / Manufacturer',
            'Model',
            'Serial Number',
            'Acquisition Cost',
            'Condition',
            'School',
            'Accountable Officer',
            'Remarks',
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $equipmentSheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Populate data
        $currentRow = 2;
        $counter = 1;
        foreach ($equipment as $row) {
            $equipmentSheet->setCellValue('A' . $currentRow, $counter);
            $equipmentSheet->setCellValue('B' . $currentRow, $row->property_no ?? '');
            $equipmentSheet->setCellValue('C' . $currentRow, $row->item ?? '');
            $equipmentSheet->setCellValue('D' . $currentRow, $row->brand_manufacturer ?? '');
            $equipmentSheet->setCellValue('E' . $currentRow, $row->model ?? '');
            $equipmentSheet->setCellValue('F' . $currentRow, $row->serial_number ?? '');
            $equipmentSheet->setCellValue('G' . $currentRow, $row->acquisition_cost ?? '');
            $equipmentSheet->setCellValue('H' . $currentRow, $row->equipment_condition ?? '');
            $equipmentSheet->setCellValue('I' . $currentRow, $row->school_name ?? '');
            $equipmentSheet->setCellValue('J' . $currentRow, trim(($row->officer_last_name ?? '') . ', ' . ($row->officer_first_name ?? '') . ' ' . ($row->officer_middle_name ?? '') . ' ' . ($row->officer_extension_name ?? '')));
            $equipmentSheet->setCellValue('K' . $currentRow, $row->remarks ?? '');
            $currentRow++;
            $counter++;
        }

        // Autosize columns
        foreach (range('A', 'K') as $col) {
            $equipmentSheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Prepare download
        $filename = 'ICT_Equipment_Inventory_Report_' . now()->format('Y-m-d_His') . '.xlsx';
        if ($school_filter) {
            $school = School::find($school_filter);
            if ($school) {
                $school_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $school->name);
                $filename = 'ICT_Equipment_Inventory_' . $school_name . '_' . now()->format('Y-m-d_His') . '.xlsx';
            }
        }
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Pragma', 'public');
        return $response;
    }
}
