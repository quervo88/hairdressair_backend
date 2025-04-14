<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;

class MigrateAdminUsersToEmployees extends Command
{
    protected $signature = 'migrate:admins';
    protected $description = 'Átmásolja az admin felhasználókat az employees táblába';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Az admin felhasználók lekérdezése
        $admins = User::where('admin', 1)->get();

        foreach ($admins as $admin) {
            // Átmásoljuk az admin felhasználókat az employees táblába
            Employee::create([
                'name' => $admin->name,
                'email' => $admin->email,
                'position' => 'admin',  // Ha van pozíció, itt állítható
            ]);
        }

        $this->info('Admin felhasználók sikeresen átmásolva az employees táblába.');
    }
}