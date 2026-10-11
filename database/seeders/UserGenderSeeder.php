<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserGenderSeeder extends Seeder
{
    /**
     * Run the database seeds to populate gender for existing users.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No users found in database.');
            }
            return;
        }

        // Comprehensive list of female indicators / names
        $femaleKeywords = [
            'maria', 'mary', 'ma.', 'engr. maria', 'prof. maria',
            'kristalyn', 'krista', 'vicente',
            'aubrey', 'dela pena',
            'ana', 'anna', 'ann', 'anne',
            'jane', 'sarah', 'sara',
            'grace', 'joy', 'rose', 'santos',
            'cristine', 'christine', 'kristine',
            'angela', 'angel', 'angelica',
            'patricia', 'jennifer', 'jessica',
            'karen', 'catherine', 'kathleen',
            'michelle', 'fatima', 'bea',
            'clarisse', 'princess', 'rochelle',
            'maricel', 'rowena', 'divina',
            'chona', 'lourdes', 'teresa', 'theresa',
            'elizabeth', 'florence', 'joan', 'joanna',
            'judy', 'judith', 'claire', 'diana',
        ];

        $updatedCount = 0;

        foreach ($users as $user) {
            $nameLower = strtolower($user->name ?? '');

            $isFemale = false;
            foreach ($femaleKeywords as $kw) {
                if (str_contains($nameLower, $kw)) {
                    $isFemale = true;
                    break;
                }
            }

            $gender = $isFemale ? 'female' : 'male';

            // Update user gender
            $user->update([
                'gender' => $gender,
            ]);

            $updatedCount++;

            if ($this->command) {
                $icon = $gender === 'female' ? '👩 [FEMALE]' : '👨 [MALE]';
                $this->command->info("{$icon} {$user->name} ({$user->email}) -> {$gender}");
            }
        }

        if ($this->command) {
            $this->command->info(" Successfully updated gender for {$updatedCount} user(s).");
        }
    }
}
