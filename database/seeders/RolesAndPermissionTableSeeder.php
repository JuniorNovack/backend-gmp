<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $manager = Role::create(['name' => 'manager']);

        $createCompany = Permission::create(['name' => 'create_companies']);
        $editCompany = Permission::create(['name' => 'edit_companies']);
        $deleteCompany = Permission::create(['name' => 'delete_companies']);
        $viewCompany = Permission::create(['name' => 'view_companies']);

        $admin->givePermissionTo([$createCompany, $editCompany, $deleteCompany, $viewCompany]);
        $manager->givePermissionTo([$viewCompany, $editCompany]);
        $customer->givePermissionTo([$viewCompany]);

        $userAdmin = User::factory()->create([
            'name' => 'Test admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password')
        ]);
        $userAdmin->assignRole($admin->name);


        if (config('app.debug')) {

            $usereEditor =  User::factory()->create([
                'name' => 'Test editor',
                'email' => 'editor@example.com',
                'password' => Hash::make('password')
            ]);
            $usereEditor->assignRole($editor->name);

            $userCustomer = User::factory()->create([
                'name' => 'Test customer',
                'email' => 'customer@example.com',
                'password' => Hash::make('password')
            ]);
            $userCustomer->assignRole($customer->name);

            $manager = User::factory()->create([
                'name' => 'Test Manager',
                'email' => 'manager@example.com',
                'password' => Hash::make('password')
            ]);
            $manager->assignRole($customer->name);
        }
    }
}
