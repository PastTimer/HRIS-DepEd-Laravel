<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolInternetProfile;
use App\Models\IspInventory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternetProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = School::with(['district:id,name', 'internetProfile'])
            ->where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('school_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc');

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id)) {
            $query->where('id', Auth::user()->school_id);
        }

        $schools = $query->paginate(15)->appends(['search' => $search]);

        return view('internet.index', compact('schools'));
    }

    public function show($id)
    {
        $school = School::findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $school->id) {
            abort(403);
        }

        $profile = SchoolInternetProfile::firstOrNew(['school_id' => $school->id]);
        $isps = IspInventory::where('school_id', $id)->latest()->get();

        return view('internet.edit', compact('school', 'profile', 'isps'));
    }

    public function update(Request $request, $id)
    {
        $school = School::findOrFail($id);

        if (Auth::check() && Auth::user()->hasRole('school') && !empty(Auth::user()->school_id) && (int) Auth::user()->school_id !== (int) $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'is_provider_available' => 'nullable|in:Yes,No',
            'available_providers' => 'nullable|array',
            'available_providers.*' => 'nullable|string|max:100',
            'available_providers_others' => 'nullable|string|max:255',
            'mobile_signals' => 'nullable|array',
            'mobile_signals.*' => 'nullable|string|max:100',
            'has_mobile_data' => 'nullable|in:Yes,No',
            'mobile_data_quality' => 'nullable|string|max:255',

            'is_subscribed' => 'nullable|in:Yes,No',
            'subscribed_providers' => 'nullable|array',
            'subscribed_providers.*' => 'nullable|string|max:100',
            'subscribed_providers_others' => 'nullable|string|max:255',
            'total_isps' => 'nullable|integer|min:0',
            'total_cost' => 'nullable|numeric|min:0',

            'subscription_purpose' => 'nullable|array',
            'subscription_purpose.*' => 'nullable|string|max:255',
            'rooms_admin_use' => 'nullable|integer|min:0',
            'rooms_classroom_use' => 'nullable|integer|min:0',
            'rooms_other_use' => 'nullable|integer|min:0',
            'rooms_covered' => 'nullable|integer|min:0',
            'access_points' => 'nullable|integer|min:0',
            'insufficient_bandwidth_reason' => 'nullable|string',
            'coverage_areas' => 'nullable|array',
            'coverage_areas.*' => 'nullable|string|max:255',

            'is_dict_recipient' => 'nullable|in:Yes,No',
            'dict_rating' => 'nullable|string|max:255',
            'has_sufficient_bandwidth' => 'nullable|in:Yes,No',
            'no_subscription_reason' => 'nullable|string',

            'has_electricity' => 'nullable|in:Yes,No',
            'electricity_sources' => 'nullable|array',
            'electricity_sources.*' => 'nullable|string|max:100',
            'electricity_sources_others' => 'nullable|string|max:255',
            'is_solar_powered' => 'nullable|in:Yes,No',
            'frequent_brownouts' => 'nullable|in:Yes,No',
        ]);

        $data = [
            'school_id' => $school->id,
            'is_provider_available' => $validated['is_provider_available'] ?? null,
            'available_providers' => $this->joinMultiSelect($request, 'available_providers', 'available_providers_others'),
            'mobile_signals' => $this->joinMultiSelect($request, 'mobile_signals'),
            'has_mobile_data' => $validated['has_mobile_data'] ?? null,
            'mobile_data_quality' => $validated['mobile_data_quality'] ?? null,

            'is_subscribed' => $validated['is_subscribed'] ?? null,
            'subscribed_providers' => $this->joinMultiSelect($request, 'subscribed_providers', 'subscribed_providers_others'),
            'total_isps' => (int) ($validated['total_isps'] ?? 0),
            'total_cost' => (float) ($validated['total_cost'] ?? 0),

            'subscription_purpose' => $this->joinMultiSelect($request, 'subscription_purpose'),
            'rooms_admin_use' => (int) ($validated['rooms_admin_use'] ?? 0),
            'rooms_classroom_use' => (int) ($validated['rooms_classroom_use'] ?? 0),
            'rooms_other_use' => (int) ($validated['rooms_other_use'] ?? 0),
            'rooms_covered' => (int) ($validated['rooms_covered'] ?? 0),
            'access_points' => (int) ($validated['access_points'] ?? 0),
            'insufficient_bandwidth_reason' => $validated['insufficient_bandwidth_reason'] ?? null,
            'coverage_areas' => $this->joinMultiSelect($request, 'coverage_areas'),

            'is_dict_recipient' => $validated['is_dict_recipient'] ?? null,
            'dict_rating' => $validated['dict_rating'] ?? null,
            'has_sufficient_bandwidth' => $validated['has_sufficient_bandwidth'] ?? null,
            'no_subscription_reason' => $validated['no_subscription_reason'] ?? null,

            'has_electricity' => $validated['has_electricity'] ?? null,
            'electricity_sources' => $this->joinMultiSelect($request, 'electricity_sources', 'electricity_sources_others'),
            'is_solar_powered' => $validated['is_solar_powered'] ?? null,
            'frequent_brownouts' => $validated['frequent_brownouts'] ?? null,
        ];

        SchoolInternetProfile::updateOrCreate(
            ['school_id' => $school->id],
            $data
        );

        ActivityLog::log('UPDATE', 'InternetProfile', "Updated 25-question survey for: {$school->name}");

        return redirect()->route('internet.show', $school->id)->with('success', 'Internet Profile updated successfully.');
    }

    private function joinMultiSelect(Request $request, string $field, ?string $othersField = null): ?string
    {
        $values = $request->input($field, []);
        if (!is_array($values)) {
            $values = [];
        }

        if ($othersField !== null && in_array('Others', $values, true)) {
            $others = trim((string) $request->input($othersField, ''));
            if ($others !== '') {
                $values = array_values(array_filter($values, fn ($v) => $v !== 'Others'));
                $values[] = 'Others: ' . $others;
            }
        }

        return empty($values) ? null : implode(',', $values);
    }
}