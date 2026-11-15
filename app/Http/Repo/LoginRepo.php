<?php

namespace App\Http\Repo;

use App\Models\Cuser;

class LoginRepo
{
    public function getUsers($phoneNumber)
    {
       return Cuser::where('phone_number',$phoneNumber)->first();
    }

    public function createUsers($data)
    {
        return Cuser::query()->create($data);
    }

    public function getUserByMobile($phoneNumber)
    {
        return Cuser::where('phone_number',$phoneNumber)->first();
    }
}
