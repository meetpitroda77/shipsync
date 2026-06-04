<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $addresses = $user->addresses()->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }
}
