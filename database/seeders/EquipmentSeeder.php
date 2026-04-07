<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get some personnel and school IDs for relations
        $personnelIds = DB::table('personnel')->pluck('id')->all();
        $schoolIds = DB::table('schools')->pluck('id')->all();
        $userIds = DB::table('users')->pluck('id')->all();

        foreach (range(1, 20) as $i) {
            $accountableOfficerId = !empty($personnelIds) ? $faker->randomElement($personnelIds) : null;
            $custodianId = !empty($personnelIds) ? $faker->randomElement($personnelIds) : null;
            $schoolId = !empty($schoolIds) ? $faker->randomElement($schoolIds) : null;
            $createdBy = !empty($userIds) ? $faker->randomElement($userIds) : null;

            $equipmentId = DB::table('equipment')->insertGetId([
                'property_no' => 'PROP-' . $faker->unique()->numberBetween(1000, 9999),
                'old_property_no' => $faker->optional()->numerify('OLD-####'), // optional
                'serial_number' => $faker->unique()->bothify('SN-####??'),
                'qr_code' => $faker->uuid,
                'item' => $faker->randomElement(['Laptop', 'Desktop', 'Printer', 'Projector', 'Tablet']),
                'unit' => $faker->randomElement(['Piece', 'Set (bundled)', 'Lot']),
                'brand_manufacturer' => $faker->randomElement([
                    'Acer', 'Asus', 'Dell', 'HP', 'Lenovo', 'Epson', 'Brother', 'Canon', 'Samsung', 'Apple', 'Other',
                ]),
                'model' => $faker->word,
                'item_description' => $faker->sentence,
                'specifications' => $faker->sentence,
                'is_dcp' => $faker->boolean(30),
                'dcp_package' => $faker->optional()->randomElement([
                    'Batch 35', 'Batch 36', 'Batch 40', 'Batch 42', 'Batch 44', 'Other',
                ]), // optional
                'dcp_year' => $faker->numberBetween(2000, 2099),
                'acquisition_cost' => $faker->randomFloat(2, 10000, 100000),
                'category' => $faker->randomElement(['High-value', 'Low-value']),
                'classification' => $faker->randomElement([
                    'Machinery and Equipment',
                    'Office, ICT Equipment, Furniture And Fixtures',
                    'Other Property, Plant And Equipment',
                ]),
                'estimated_useful_life' => $faker->numberBetween(3, 10),
                'gl_sl_code' => $faker->optional()->bothify('GL-####'), // optional
                'uacs_code' => $faker->optional()->bothify('UACS-####'), // optional
                'mode_acquisition' => $faker->randomElement(['DepEd Purchase', 'Donation', 'Grant']),
                'source_acquisition' => $faker->randomElement([
                    'Central Office',
                    'Regional Office',
                    'SDO',
                    'School',
                    'Local Government Unit (LGU)',
                    'Private Corporation',
                    'National Government Agency (NGA)',
                    'Parent-Teacher Association (PTA)'
                ]),
                'donor' => $faker->optional()->company, // optional
                'source_funds' => $faker->randomElement([
                    'Program Support Funds (PSF)',
                    'General Fund (GF)',
                    'Maintenance and Other Operating Expenses (MOOE)',
                    'Capital Outlay (CO)',
                    'School Education Fund (SEF)'
                ]),
                'allotment_class' => $faker->optional()->randomElement([
                    'Personal Services (PS)',
                    'MOOE',
                    'Capital Outlay (CO)'
                ]), // optional
                'received_date' => $faker->date(),
                'pmp_reference' => $faker->optional()->bothify('PMP-####'), // optional
                'transaction_type' => $faker->randomElement([
                    'Beginning Inventory',
                    'Delivery',
                    'Inspection',
                    'Issuance/Transfer',
                    'Return',
                    'Disposal',
                    'Stock Position'
                ]),
                'supporting_doc_type' => $faker->optional()->randomElement([
                    'Sales Invoice (SI)',
                    'Official Receipt (OR)',
                    'Delivery Receipt (DR)',
                    'Inspection Acceptance Report (IAR)',
                    'Report of Receipt and Stock Position (RRSP)',
                    'Property Acknowledgment Receipt (PAR)',
                    'Inventory Custodian Slip (ICS)',
                    'Return and Receipt of Property/Equipment (RRPE)',
                    'Waste Material Report (WMR)'
                ]), // optional
                'supporting_doc_no' => $faker->optional()->bothify('DOC-####'), // optional
                'accountable_officer_id' => $accountableOfficerId,
                'accountable_date' => $faker->date(),
                'custodian_id' => $custodianId,
                'custodian_date' => $faker->date(),
                'supplier' => $faker->company,
                'supplier_contact' => $faker->phoneNumber,
                'under_warranty' => $faker->boolean(40),
                'warranty_end_date' => $faker->date(),
                'equipment_location' => $faker->city,
                'is_functional' => $faker->boolean(90),
                'equipment_condition' => $faker->randomElement([
                    'Serviceable',
                    'For Repair',
                    'Unserviceable',
                    'Not Applicable'
                ]),
                'disposition_status' => $faker->randomElement([
                    'Normal',
                    'Transferred',
                    'Stolen',
                    'Lost',
                    'Damaged due to calamity',
                    'For Disposal'
                ]),
                'remarks' => $faker->optional()->sentence, // optional
                'school_id' => $schoolId,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add simple movement history for a subset of equipment entries.
            $movementCount = $faker->numberBetween(0, 2);
            $currentHolder = $custodianId ?: $accountableOfficerId;

            for ($m = 0; $m < $movementCount; $m++) {
                if (empty($personnelIds)) {
                    break;
                }

                $nextHolder = $faker->randomElement($personnelIds);
                $fromPersonnelId = $currentHolder;

                if ($nextHolder === $fromPersonnelId && count($personnelIds) > 1) {
                    $nextHolder = $faker->randomElement(array_values(array_filter($personnelIds, fn ($id) => $id !== $fromPersonnelId)));
                }

                DB::table('equipment_movements')->insert([
                    'equipment_id' => $equipmentId,
                    'from_personnel_id' => $fromPersonnelId,
                    'to_personnel_id' => $nextHolder,
                    'movement_date' => $faker->date(),
                    'document_type' => $faker->randomElement([
                        'Property Acknowledgment Receipt (PAR)',
                        'Inventory Custodian Slip (ICS)',
                        'Return and Receipt of Property/Equipment (RRPE)',
                    ]),
                    'document_number' => 'MOV-' . $faker->numerify('####'),
                    'remarks' => $faker->optional()->sentence(),
                    'created_by' => $createdBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $currentHolder = $nextHolder;
            }
        }
    }
}