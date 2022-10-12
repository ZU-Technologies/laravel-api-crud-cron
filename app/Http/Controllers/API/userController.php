<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class userController extends Controller
{
    public function Post(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'description' => 'required|string'
        //    ]);
        $data=array ('name'=>"Ahmed",'description'=>"hiii alll",'created_at'=>Carbon::now());
        DB::table('user_data')->insert($data);
        echo "Data Store";
    }

    public function getPost(){
        $post= DB::table('user_data')->paginate(12);
        $users_post=array();
        foreach ($post as $sub_post ) {
            $single_user=array();
            $single_user['id']=$sub_post->id;
            $single_user['name']=$sub_post->name;
            $single_user['description']=$sub_post->description;
            array_push($users_post,$single_user);
        }
         return response([
             'data' => $users_post,
         ], 200);
    }
}
