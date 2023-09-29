<?php

namespace App\Http\Controllers;

use App\Models\CustomResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    private int $status = 1;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::table('users')->get();

        return response()->json([
            'data'=>$users
        ],200);
    }

    public function searchUsersByEmailOrUsername(Request $request):JsonResponse{
        try {
            $text = $request->query('text');

            if(!$text){
                $r = CustomResponse::ok([]);
                return response()->json($r);
            }

            $users = User::where(function($query) use($text){
                    $query->where('username', 'LIKE', '%' . $text . '%')
                    ->orWhere('email', 'LIKE', '%' . $text . '%');
                })
                ->where("status", $this->status)
                ->get();

            $r = CustomResponse::ok($users);
            return response()->json($r);
        }catch (\Exception $e){
            $r = CustomResponse::intertalServerError("Ocurrió un error en el sevidor");
            return response()->json($r, $r->code);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
