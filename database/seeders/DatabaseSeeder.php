<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users
        DB::table('users')->insert([
            [
                'name' => 'Carlos (Pasajero)',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pasajero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'María (Pasajero)',
                'email' => 'maria@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pasajero',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Juan (Conductor)',
                'email' => 'juan@example.com',
                'password' => Hash::make('password123'),
                'role' => 'conductor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pedro (Conductor)',
                'email' => 'pedro@example.com',
                'password' => Hash::make('password123'),
                'role' => 'conductor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ana (Admin)',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Luis (Conductor)',
                'email' => 'luis@example.com',
                'password' => Hash::make('password123'),
                'role' => 'conductor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Create driver profiles for conductors
        DB::table('driver_profiles')->insert([
            [
                'user_id' => 3, // Juan
                'document_number' => '1234567890',
                'license_number' => 'LIC123456',
                'vehicle_plate' => 'ABC-123',
                'vehicle_type' => 'auto',
                'status' => 'verified',
                'rating' => 4.8,
                'total_trips' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4, // Pedro
                'document_number' => '0987654321',
                'license_number' => 'LIC654321',
                'vehicle_plate' => 'XYZ-789',
                'vehicle_type' => 'auto',
                'status' => 'verified',
                'rating' => 4.9,
                'total_trips' => 78,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6, // Luis
                'document_number' => '5555555555',
                'license_number' => 'LIC555555',
                'vehicle_plate' => 'MNO-456',
                'vehicle_type' => 'moto',
                'status' => 'verified',
                'rating' => 4.7,
                'total_trips' => 32,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
