<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternetProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $schools = School::where('is_active', true)
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('school_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->appends(['search' => $search]);

        return view('internet.index', compact('schools'));
    }

    // Show redirects to the survey edit form
    public function show($id)
    {
        $school = School::findOrFail($id);
        $profile = DB::table('school_internet_profile')->where('school_id', $id)->first();
        $isps = DB::table('isp_inventory')->where('school_id', $id)->get();

        return view('internet.edit', compact('school', 'profile', 'isps'));
    }

    public function update(Request $request, $id) 
        {
            // Fetch the school manually to guarantee we have a valid ID
            $school = School::findOrFail($id);

            $data = $request->only([
                'is_provider_available', 'has_mobile_data', 'mobile_data_quality', 
                'total_isps', 'total_cost', 'subscription_purpose', 'rooms_admin_use', 
                'rooms_classroom_use', 'rooms_other_use', 'rooms_covered', 'access_points', 
                'insufficient_bandwidth_reason', 'is_dict_recipient', 'dict_rating', 
                'has_sufficient_bandwidth', 'no_subscription_reason', 'has_electricity', 
                'is_solar_powered', 'frequent_brownouts'
            ]);

            // Handle the 25-question array fields
            $arrayFields = ['available_providers', 'mobile_signals', 'subscribed_providers', 'electricity_sources', 'coverage_areas'];
            foreach ($arrayFields as $field) {
                if ($request->has($field)) {
                    $data[$field] = implode(',', $request->get($field));
                }
            }

            // Force school_id and timestamp into the update
            DB::table('school_internet_profile')->updateOrInsert(
                ['school_id' => $school->id], 
                array_merge($data, [
                    'school_id' => $school->id, 
                    'updated_at' => now()
                ])
            );

            ActivityLog::log('UPDATE', 'InternetProfile', "Updated 25-question survey for: {$school->name}");

            return redirect()->back()->with('success', 'Internet Profile updated successfully.');
        }
        }