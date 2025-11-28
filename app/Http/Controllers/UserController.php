<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function index()
    {
        $Users = [
            ["id" => 1, "Name" => "Mahmoud"],
            ["id" => 2, "Name" => "Ahmad"],
            ["id" => 3, "Name" => "Maher"]
        ];

        //foreach ($Users as $User) {
        //  echo $User['id'] . ',' . $User['Name'] . "\n" ;
        //}
        return response()->json($Users);

    }

    public function CheckUser($id)
    {

        if ($id > 10) {
            return response()->json(['Message' => 'Accesse Denied']);
        } else {
            return response()->json(['Message'=>'Your id is Valid']);
        }
    }

}
