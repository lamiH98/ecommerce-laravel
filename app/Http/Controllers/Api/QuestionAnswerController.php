<?php

namespace App\Http\Controllers\Api;

use App\Models\QuestionAnswer;
use Illuminate\Http\Request;
use App\Http\Requests\QuestionAnswerRequest;
use App\Http\Controllers\Controller;
use App\Traits\GeneralTrait;

class QuestionAnswerController extends Controller
{

    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questionAnswer = QuestionAnswer::all();
        return $this->sendResponse('questionAnswer', $questionAnswer, 'All Question Answer');
    }

}
