<?php

namespace App\Http\Controllers\Api;

use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = User::findOrFail($id);
        $userFavorite = $user->favorites()->with('user', 'product')->get();
        return $this->sendResponse('userFavorite', $userFavorite);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $userFavorite = $user = User::findOrFail($request->user_id)->favorites;
        $productExist = Favorite::where('product_id', $request->product_id)->where('user_id', $request->user_id)->get();
        if(count($productExist) != 0) {
            try{
                $productExist->each->delete();
                return $this->sendSuccess('delete favorite successfully');
            }
            catch(\Exception $ex) {
                return $this->sendError($ex->getMessage());
            }
        }
        $rules = [
            'product_id'    => 'required',
            'user_id'       => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        try {
            Favorite::create($request->all());
                return $this->sendSuccess('added item successfully');
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $favorite = Favorite::findOrFail($id);
        if(empty($favorite)) {
            return $this->sendError('Favorite Not Found');
        }
        try{
            $favorite->delete();
            return $this->sendSuccess('delete favorite successfully');
        }
        catch(\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
}
