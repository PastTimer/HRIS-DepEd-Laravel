<?php

namespace Database\Seeders;

use App\Models\IspInventory;
use App\Models\IspSpeedtest;
use App\Models\School;
use App\Models\SchoolInternetProfile;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class InternetAndIspSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $schools = School::where('is_active', true)->get();
        if ($schools->isEmpty()) {
            return;
        }

        $seedUserId = User::where('username', 'admin')->value('id') ?? User::value('id');

        $providerPool = ['PLDT', 'Globe', 'Smart', 'Converge', 'Starlink', 'SkyCable', 'DITO'];
        $coveragePool = ['Building/Office-wide', 'School-wide', 'Faculty area', 'ICT Room', 'Library'];
        $purposePool = ['Administrative use', 'Classroom instruction', 'Both admin and classroom'];
        $electricityPool = ['Grid', 'Generator', 'Solar'];

        foreach ($schools as $school) {
            $availableProviders = collect($providerPool)->random($faker->numberBetween(2, 4))->values()->all();
            $mobileSignals = collect(['3G', 'LTE', '5G'])->random($faker->numberBetween(1, 3))->values()->all();
            $coverageAreas = collect($coveragePool)->random($faker->numberBetween(2, 4))->values()->all();
            $subscriptionPurpose = collect($purposePool)->random($faker->numberBetween(1, 2))->values()->all();
            $electricitySources = collect($electricityPool)->random($faker->numberBetween(1, 2))->values()->all();

            $ispCount = $faker->numberBetween(1, 2);
            $hasSubscription = $ispCount > 0;
            $subscribedProviders = [];
            $totalCost = 0;

            for ($i = 0; $i < $ispCount; $i++) {
                $provider = $faker->randomElement($providerPool);
                $monthlyCost = $faker->randomFloat(2, 1200, 6500);
                $planSpeed = (string) $faker->randomElement([20, 35, 50, 100, 200]);
                $minSpeed = (string) max(5, ((int) $planSpeed) - $faker->numberBetween(5, 20));

                $isp = IspInventory::create([
                    'school_id' => $school->id,
                    'provider' => $provider,
                    'account_no' => 'ACC-' . $faker->unique()->numerify('######'),
                    'internet_type' => $faker->randomElement(['Fiber', 'Wireless/LTE', 'Satellite', 'DSL']),
                    'subscription_type' => $faker->randomElement(['Postpaid', 'Prepaid']),
                    'status' => $faker->randomElement(['Active', 'Active', 'Inactive', 'Pending']),
                    'purpose' => $faker->randomElement($purposePool),
                    'acquisition_mode' => $faker->randomElement(['DepEd Purchase', 'Donation', 'Grant']),
                    'donor' => $faker->optional(0.2)->company(),
                    'fund_source' => $faker->randomElement(['MOOE', 'SEF', 'General Fund', 'PSF']),
                    'monthly_mrc' => $monthlyCost,
                    'plan_speed' => $planSpeed,
                    'min_speed' => $minSpeed,
                    'area_coverage' => $faker->randomElement(['Admin Office', 'Classrooms', 'Whole School']),
                    'package_inclusion' => 'Router, modem, and basic support package',
                    'installation_date' => $faker->dateTimeBetween('-2 years', '-2 months')->format('Y-m-d'),
                    'contract_end_date' => $faker->dateTimeBetween('+1 months', '+2 years')->format('Y-m-d'),
                    'ip_type' => $faker->randomElement(['Dynamic', 'Static']),
                    'public_ip' => $faker->optional(0.35)->ipv4(),
                    'remarks' => $faker->optional(0.6)->sentence(),

                    'access_points_count' => $faker->numberBetween(1, 8),
                    'access_points_loc' => 'Admin Office, ICT Room',
                    'admin_rooms_covered' => $faker->numberBetween(1, 5),
                    'classrooms_covered' => $faker->numberBetween(1, 12),
                    'admin_connectivity_rating' => $faker->numberBetween(2, 5),
                    'classroom_connectivity_rating' => $faker->numberBetween(2, 5),
                    'signal_quality' => $faker->randomElement(['Excellent', 'Good', 'Fair']),
                    'isp_service_rating' => $faker->numberBetween(2, 5),
                    'active_isp_counter' => $faker->numberBetween(1, 10),
                    'active_custom_counter_2' => $faker->numberBetween(1, 15),
                    'active_custom_counter_3' => $faker->numberBetween(1, 30),
                    'created_by' => $seedUserId,
                    'updated_by' => $seedUserId,
                ]);

                $subscribedProviders[] = $provider;
                $totalCost += $monthlyCost;

                $speedTests = $faker->numberBetween(1, 3);
                for ($t = 0; $t < $speedTests; $t++) {
                    $testDate = $faker->dateTimeBetween('-120 days', 'now');
                    $download = $faker->randomFloat(2, 8, (float) $planSpeed);
                    $upload = $faker->randomFloat(2, 4, max(6, (float) $minSpeed));

                    IspSpeedtest::create([
                        'isp_id' => $isp->id,
                        'test_date' => $testDate->format('Y-m-d H:i:s'),
                        'download_mbps' => $download,
                        'upload_mbps' => $upload,
                        'ping_ms' => $faker->numberBetween(8, 90),
                        'tested_by' => 'Seed Script',
                        'remarks_speed' => $faker->optional(0.6)->sentence(),
                    ]);
                }
            }

            SchoolInternetProfile::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'is_provider_available' => 'Yes',
                    'available_providers' => implode(',', $availableProviders),
                    'mobile_signals' => implode(',', $mobileSignals),
                    'has_mobile_data' => $faker->randomElement(['Yes', 'No']),
                    'mobile_data_quality' => $faker->randomElement(['Excellent', 'Good', 'Intermittent', 'Poor']),

                    'is_subscribed' => $hasSubscription ? 'Yes' : 'No',
                    'subscribed_providers' => !empty($subscribedProviders) ? implode(',', array_unique($subscribedProviders)) : null,
                    'total_isps' => $ispCount,
                    'total_cost' => round($totalCost, 2),

                    'subscription_purpose' => implode(',', $subscriptionPurpose),
                    'rooms_admin_use' => $faker->numberBetween(1, 8),
                    'rooms_classroom_use' => $faker->numberBetween(2, 25),
                    'rooms_other_use' => $faker->numberBetween(0, 4),
                    'rooms_covered' => $faker->numberBetween(1, 30),
                    'access_points' => $faker->numberBetween(1, 10),
                    'insufficient_bandwidth_reason' => $faker->optional(0.45)->sentence(),
                    'coverage_areas' => implode(',', $coverageAreas),

                    'is_dict_recipient' => $faker->randomElement(['Yes', 'No']),
                    'dict_rating' => (string) $faker->numberBetween(1, 5),
                    'has_sufficient_bandwidth' => $faker->randomElement(['Yes', 'No']),
                    'no_subscription_reason' => $hasSubscription ? null : 'Budget constraints / no reliable provider contract yet.',

                    'has_electricity' => $faker->randomElement(['Yes', 'No']),
                    'electricity_sources' => implode(',', $electricitySources),
                    'is_solar_powered' => $faker->randomElement(['Yes', 'No']),
                    'frequent_brownouts' => $faker->randomElement(['Yes', 'No']),
                ]
            );
        }
    }
}
