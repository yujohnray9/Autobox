<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('gender', 20)->nullable()->after('name');
            });
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

        // Automatically assign gender to existing users without needing migrate:fresh
        $users = DB::table('users')->whereNull('gender')->orWhere('gender', '')->get();

        foreach ($users as $user) {
            $nameLower = strtolower($user->name ?? '');

            $isFemale = false;
            foreach ($femaleKeywords as $kw) {
                if (str_contains($nameLower, $kw)) {
                    $isFemale = true;
                    break;
                }
            }

            DB::table('users')->where('id', $user->id)->update([
                'gender' => $isFemale ? 'female' : 'male',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'gender')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('gender');
            });
        }
    }
};
