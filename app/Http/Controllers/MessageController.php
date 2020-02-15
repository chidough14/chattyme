<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function users()
    {
        $users = User::latest()->where('id', '!=', auth()->user()->id)->get();

        /* if (\Request::ajax()) {
            return response()->json($users, 200);
        }
        return abort(404); */
        return response()->json($users, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function userMessages($id = null)
    {
        /* if (!\Request::ajax()) {
            return abort(404);
        }
 */
        $user = User::findOrFail($id);

        $messages = $this->message_by_user_id($id);

        return response()->json([
            'messages'=> $messages,
            'user'=> $user
        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function send_message(Request $request)
    {

            $message = Message::create([
                'message'=> $request->message,
                'from'=> auth()->user()->id,
                'to'=> $request->user_id,
                'type'=> 0
            ]);

            $message = Message::create([
                'message'=> $request->message,
                'from'=> auth()->user()->id,
                'to'=> $request->user_id,
                'type'=> 1
            ]);

            broadcast(new MessageSent($message));

            return response()->json($message, 200);

    }

    public function send_image (Request $request, $id) {
        $filename = request('file')->store('pchat');

        $message = Message::create([
            'message'=> $request->message,
            'image'=> $filename,
            'from'=> auth()->user()->id,
            'to'=> $id,
            'type'=> 0
        ]);

        $message = Message::create([
            'message'=> $request->message,
            'image'=> $filename,
            'from'=> auth()->user()->id,
            'to'=> $id,
            'type'=> 1
        ]);

        broadcast(new MessageSent($message));

        return response()->json($message, 200);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function delete_message($messageId = null)
    {
        /* if (!\Request::ajax()) {
            return abort(404);
        } */

        Message::findOrFail($messageId)->delete();

        return response()->json('deleted', 200);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function delete_all_message($id = null)
    {
        $messages = $this->message_by_user_id($id);

        foreach ($messages as $message) {
            Message::findOrFail($message->id)->delete();
        }

        return response()->json('Messages Deleted', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
    public function message_by_user_id($id)
    {
        $messages = Message::where(function($q) use($id) {
            $q->where('from', auth()->user()->id);
            $q->where('to', $id);
            $q->where('type', 0);
        })->orWhere(function($q) use($id) {
            $q->where('from', $id);
            $q->where('to', auth()->user()->id);
            $q->where('type', 1);
        })->with('user')->get();

        return $messages;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Message  $message
     * @return \Illuminate\Http\Response
     */
}
