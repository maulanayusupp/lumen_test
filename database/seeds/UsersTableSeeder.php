<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@mail.com';
        $user->password = app('hash')->make('asdfasdf');
        $user->role = 'admin';
        $user->save();

        $user = new User();
        $user->name = 'Member';
        $user->email = 'member@mail.com';
        $user->password = app('hash')->make('asdfasdf');
        $user->role = 'member';
        $user->save();
    }
}
