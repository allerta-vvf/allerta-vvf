<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->name = 'admin';
        $user->surname = 'User';
        $user->username = 'admin';
        $user->password = Hash::make('admin');
        $user->email = 'u1@example.com';
        $user->save();
        $user->addRole('superadmin');

        /*
        Create 10 users:
        - 1 chief and with role 'admin'
        - other 2 chief and driver
        - other 2 chief
        - other 2 driver
        - other 3 normal user
        */
        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->name = ''.$i;
            $user->surname = 'User';
            $user->username = 'user' . $i;
            $user->password = Hash::make('user' . $i);
            $user->email = 'u' . $i+1 . '@example.com';
            $user->save();

            if ($i === 1) {
                $user->addRole('admin');
                $user->update(['chief' => true]);
            } elseif ($i === 2 || $i === 3) {
                $user->addRole('chief');
                $user->update(['chief' => true, 'driver' => true]);
            } elseif ($i === 4 || $i === 5) {
                $user->addRole('chief');
            } elseif ($i === 6 || $i === 7) {
                $user->update(['driver' => true]);
            }
        }
    }
}
