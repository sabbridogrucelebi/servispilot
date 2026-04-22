<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Herkese açık kayıt kapatıldı.
     * Yeni firma ve kullanıcı sadece Super Admin tarafından oluşturulabilir.
     */
    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }
}