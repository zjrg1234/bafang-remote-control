<?php

namespace App\Http\Repo;

use App\Models\Cuser;

class LoginRepo
{
    //判断用户是否存在
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
