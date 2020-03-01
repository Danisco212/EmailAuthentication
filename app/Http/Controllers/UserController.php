<?php

namespace App\Http\Controllers;

use Auth;
use App\Http\Resources\QuestionResource;
use App\Question;
use App\Answer;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // fill in the data of the question
        $question = new Question([
            'user_id'=>Auth::user()->id,
            'category_id'=>$request->input('category_id'),
            'body'=>$request->input('body'),
        ]);

        $question->save();
        
    }

    public function createAnswer(Request $request)
    {
        $answer = new Answer([
            'user_id'=>Auth::user()->id,
            'question_id'=>$request->input('question_id'),
            'rating'=>0,
            'deleted'=>0,
            'body'=>$request->input('body'),
        ]);

        $answer->save();
    }

    public function profile($id)
    {
        $user = User::find($id);
        return $user;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $questions = Question::find('user_id', $id);
        $question = DB::table('questions')->where('user_id', $id)->orderBy('created_at', 'DESC')->get();
        $user = Question::find($id)->user()->get();

        return response()->json([
            'questions'=>$question,
            'user'=>$user
            ]);
    }

    public function showAll(){
        $question = DB::table('questions')
        ->join('users', 'questions.user_id', '=', 'users.id')
        ->join('categories', 'questions.category_id', '=', 'categories.id')
        ->select('users.name as user_name', 'questions.id', 'questions.body', 'categories.name as category_name')
        ->orderBy('questions.created_at', 'DESC')
        ->paginate(20);

        return $question;
    }

    public function getAnswers($q_id){
        $answers = Answer::find($q_id)->paginate(20);
        return response()->json([
            'answers'=>$answers,
        ]);
    }

    public function getQuestion($q_id){
        $answers = Question::find($q_id)->answers()->paginate(20);
        return $answers;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $answer = Answer::find($id);
        $user = $answer->user;
        $user->rating=$user->rating+1;
        $rating = $answer->rating;
        $answer->rating=$rating+1;
        $user->update();
        $answer->update();
    }

    public function downVote($id)
    {
        $answer = Answer::find($id);
        $user = $answer->user;
        $user->rating=$user->rating-1;
        $rating = $answer->rating;
        $answer->rating=$rating-1;
        $user->update();
        $answer->update();
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

    public function search(Request $request, $term){
        $searchTerms = explode(" ", $term);
        $test = $request->input('test');
        $terms = '%'.$term.'%';

        // SQL query
        $question = DB::table('questions')
        ->join('users', 'questions.user_id', '=', 'users.id')
        ->join('categories', 'questions.category_id', '=', 'categories.id')
        ->select('users.name as user_name', 'questions.id', 'questions.body', 'categories.name as category_name')
        ->where('body','like', '%'.$term.'%')
        ->orderBy('questions.created_at', 'DESC')
        ->paginate(20);

        return $question;
    }

    public function category($category){
        $question = DB::table('questions')
        ->join('users', 'questions.user_id', '=', 'users.id')
        ->join('categories', 'questions.category_id', '=', 'categories.id')
        ->select('users.name as user_name', 'questions.id', 'questions.body', 'categories.name as category_name')
        ->where('categories.name','=', ''.$category.'')
        ->orderBy('questions.created_at', 'DESC')
        ->paginate(20);

        return $question;
    }
}
