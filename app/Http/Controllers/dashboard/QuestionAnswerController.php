<?php

namespace App\Http\Controllers\dashboard;

use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use App\Http\Requests\QuestionAnswerRequest;
use App\Http\Controllers\Controller;

class QuestionAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questionAnswer = QuestionAnswer::all();
        return view('dashboard.questionAnswer.index' , compact('questionAnswer'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.questionAnswer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuestionAnswerRequest $request)
    {
        QuestionAnswer::create($request->all());
            return redirect()->route('questionAnswer.index')->with('success' , __('message.add_success'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $questionAnswer = QuestionAnswer::findOrFail($id);
        return view('dashboard.questionAnswer.edit' , compact('questionAnswer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(QuestionAnswerRequest $request, $id)
    {
        $questionAnswer = QuestionAnswer::findOrFail($id);
        $questionAnswer->fill($request->all());
        $questionAnswer->update();
            return redirect()->route('questionAnswer.index')->with('success' , __('message.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $questionAnswer = QuestionAnswer::findOrFail($id);
            $questionAnswer->delete();
            return redirect()->back()->with('success' ,  __('message.delete_success'));
        }
        catch(ModelNotFoundException $ModelNotFoundException) {

            return redirect()->route('size.index')->with('error', __('message.not_found'));
        }
    }

    public function Multidestroy(Request $request) {

        try{
            $multiDeletes = $request->input('MultDelete');
            if($multiDeletes == null) {
                return redirect()->back()->with('error', __('message.select_item'));
            }
            $count = count($multiDeletes);
            QuestionAnswer::whereIn('id' , $multiDeletes)->delete();
            return redirect()->back()->with('success', __('message.multi_delete') . $count);
        }
        catch(ModelNotFoundException $ModelNotFoundException) {

            return redirect()->route('questionAnswer.index')->with('error', __('message.not_found'));
        }
    }
}
