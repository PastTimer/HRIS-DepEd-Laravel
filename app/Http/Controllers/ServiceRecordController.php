<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceRecordController extends Controller
{
    private function employeeTypeOptions(): array
    {
        return ['Regular', 'Contractual', 'Substitute'];
    }

    private function redirectToPersonnelServiceTab(int $personnelId, string $message): RedirectResponse
    {
        return redirect()
            ->to(route('personnel.show', $personnelId) . '#service-records')
            ->with('success', $message);
    }

    private function syncPersonnelFromLatestServiceRecord(Personnel $personnel): void
    {
        $latestRecord = $personnel->serviceRecords()
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->first();

        if (!$latestRecord) {
            return;
        }

        $personnel->update([
            'position_id' => $latestRecord->position_id,
            'employee_type' => $latestRecord->status,
            'assigned_school_id' => $latestRecord->school_id,
            'deployed_school_id' => $latestRecord->school_id,
            'salary_actual' => $latestRecord->salary,
            'branch' => $latestRecord->branch,
        ]);
    }

    public function index($personnelId)
    {
        return redirect()->to(route('personnel.show', $personnelId) . '#service-records');
    }

    public function create($personnelId)
    {
        return redirect()->to(route('personnel.show', $personnelId) . '#service-records');
    }

    public function store(Request $request, $personnelId)
    {
        $personnel = Personnel::findOrFail($personnelId);
        $data = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'nullable|date',
            'position_id' => 'required|exists:positions,id',
            'status' => 'required|in:' . implode(',', $this->employeeTypeOptions()),
            'school_id' => 'required|exists:schools,id',
            'salary' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:255',
            'lv_abs_wo_pay' => 'nullable|string|max:255',
        ]);

        // Before creating, close the most recent open record if needed
        $latest = $personnel->serviceRecords()
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->first();

        if ($latest && empty($latest->date_to)) {
            $newFrom = $data['date_from'];
            if ($newFrom >= $latest->date_from) {
                // Set date_to to newFrom if same day, or newFrom - 1 day if after
                $latest->date_to = $newFrom === $latest->date_from
                    ? $newFrom
                    : date('Y-m-d', strtotime($newFrom . ' -1 day'));
                $latest->save();
            }
        }

        $personnel->serviceRecords()->create($data);
        $this->syncPersonnelFromLatestServiceRecord($personnel);

        return $this->redirectToPersonnelServiceTab($personnelId, 'Service record added.');
    }

    public function edit($personnelId, $id)
    {
        return redirect()->to(route('personnel.show', $personnelId) . '#service-records');
    }

    public function update(Request $request, $personnelId, $id)
    {
        $personnel = Personnel::findOrFail($personnelId);
        $serviceRecord = $personnel->serviceRecords()->findOrFail($id);
        $data = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'nullable|date',
            'position_id' => 'required|exists:positions,id',
            'status' => 'required|in:' . implode(',', $this->employeeTypeOptions()),
            'school_id' => 'required|exists:schools,id',
            'salary' => 'nullable|numeric|min:0',
            'branch' => 'nullable|string|max:255',
            'lv_abs_wo_pay' => 'nullable|string|max:255',
        ]);

        $serviceRecord->update($data);
        $this->syncPersonnelFromLatestServiceRecord($personnel);

        return $this->redirectToPersonnelServiceTab($personnelId, 'Service record updated.');
    }

    public function destroy($personnelId, $id)
    {
        $personnel = Personnel::findOrFail($personnelId);
        $serviceRecord = $personnel->serviceRecords()->findOrFail($id);
        // Find the next most recent record (by date_from, id) before deleting
        $nextMostRecent = $personnel->serviceRecords()
            ->where('id', '!=', $serviceRecord->id)
            ->where('date_from', '<=', $serviceRecord->date_from)
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->first();

        $serviceRecord->delete();

        // If the next most recent exists and its date_to was set (closed), open it
        if ($nextMostRecent && $nextMostRecent->date_to) {
            $nextMostRecent->date_to = null;
            $nextMostRecent->save();
        }

        $this->syncPersonnelFromLatestServiceRecord($personnel);

        return $this->redirectToPersonnelServiceTab($personnelId, 'Service record deleted.');
    }
}
