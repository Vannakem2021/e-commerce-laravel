<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:assign-roles {--force : Force assignment even if user already has roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign default user role to users who don\'t have any roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        
        // Ensure the user role exists
        $userRole = Role::firstOrCreate(['name' => 'user']);
        
        // Get users without roles or all users if force is used
        if ($force) {
            $users = User::all();
            $this->info('Force mode: Processing all users...');
        } else {
            $users = User::doesntHave('roles')->get();
            $this->info('Processing users without roles...');
        }
        
        if ($users->isEmpty()) {
            $this->info('No users found to process.');
            return;
        }
        
        $count = 0;
        foreach ($users as $user) {
            if ($force || !$user->hasAnyRole()) {
                if ($force && $user->hasAnyRole()) {
                    $user->syncRoles(['user']); // Replace existing roles
                    $this->line("Updated roles for user: {$user->email}");
                } else {
                    $user->assignRole('user');
                    $this->line("Assigned user role to: {$user->email}");
                }
                $count++;
            }
        }
        
        $this->info("Successfully processed {$count} users.");
        
        // Clear permission cache
        $this->call('permission:cache-reset');
        $this->info('Permission cache cleared.');
    }
}
