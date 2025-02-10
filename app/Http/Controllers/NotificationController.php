<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use DataTables;
class NotificationController extends Controller{
    public function index(){
        $status = request()->get('status');

        $user = Auth::user();
        $blur = false;
        $notifications = Notification::query()->where('receiver', $user->email)->orderBy('created_at', 'desc');

        if($status == 'read' or $status == 'unread'){
            $notifications = $notifications->where('status', $status);
        }else if($status == 'sent'){
            $blur = true;
            $notifications = Notification::query()->where('sender', $user->email)->where('status', '!=','draft')->orderBy('created_at', 'desc');
        }else if($status == 'draft'){
            $notifications = Notification::query()->where('status', 'draft')->where('sender', $user->email)->orderBy('created_at', 'desc');
        }else{
            $notifications = $notifications->where('status', '!=' ,'draft')->where('status', '!=', 'sent');
        }

        $notifications = $notifications->get();
        $token = '';
        $rows = [];
        $existed_tokens = [];

        // if there are same token then only take the earliest created one and display at table
        foreach($notifications as $n){
            if(!in_array($n->token, $existed_tokens) and $token != $n->token or $token == null){
                $rows[$n->id] = $n;
            }

            $existed_tokens[$n->token] = $n->token;
            $token = $n->token;
        }

        if(request()->ajax()){

            return DataTables::of($rows)
                ->editColumn('sender', function($r) use($blur){
                    if($blur == true){
                        return '<a href="/users/'.$r->user->id.'">'.$r->receiver.'</a>';
                    }
                    return '<a href="/users/'.$r->user->id.'">'.$r->user->email.'</a>';
                })
                ->editColumn('subject', function($r){
                    return '<b>'.$r->subject.'</b>';
                })
                ->editColumn('attachment', function($r){
                    if($r->attachment == null){
                        return 'No Attachment';
                    }
                    return '<a href="/notifications/'.$r->id.'/download_attachment" class="btn btn-warning">Download</a>';
                })
                ->editColumn('status', function($r) use($blur){
                    if($blur == true){
                        return '<span class="badge bg-success">Sent</span>';
                    }
                    if($r->status == 'read'){
                        return '<span class="badge bg-success">'.ucfirst($r->status).'</span>';
                    }else if($r->status == 'draft'){
                        return '<span class="badge bg-warning">'.ucfirst($r->status).'</span>';
                    }
                    return '<span class="badge bg-danger">'.ucfirst($r->status).'</span>';
                })
                ->addColumn('action', function($r) use ($blur){
                    if($blur == false){
                        if($r->status == 'unread'){
                            return '<a href="/notifications/'.$r->id.'" class="btn btn-primary">View</a>
                                        <a href="/notifications/status/'.$r->id.'" class="btn btn-danger">Mark As Read</a>';
                        }else if($r->status == 'draft'){
                            return '<a href="/notifications/'.$r->id.'/send" class="btn btn-success">Send To '.$r->receiver.'</a>';
                        }
                        return '<a href="/notifications/'.$r->id.'" class="btn btn-primary">View</a>';
                    }else{
                        return '<a href="/notifications/'.$r->id.'" class="btn btn-primary">View</a>';
                    }
                })
                ->addColumn('replies', function($r){
                    $replies = Notification::query()->where('token', $r->token)->count();

                    $replies -= 1;

                    if($replies > 0){
                        return $replies;
                    }

                    return 'No Replies';
                })
                ->rawColumns(['action', 'status','sender','attachment', 'replies', 'subject'])
                ->make('true');
        }

        return view('notification.index')->with([
            'user' => $user
        ]);

    }

    public function create($user_id, $status = null){
        $valid_status = ['draft', 'new'];

        if($status !== null and !in_array($status, $valid_status)){
            return back()->withError('Invalid Status');
        }

        $user = User::findOrFail($user_id);

        $emails = User::query()->where('enabled', 1)->get();

        $email = [ null => 'Please select a receiver' ];

        foreach($emails as $e){
            $email[$e->email] = $e->email;
        }

        return view('notification.create')->with([
            'user' => $user,
            'email' => $email,
            'status' => $status
        ]);
    }

    public function store($user_id, $status = null){
        $valid_status = ['draft','new'];

        if($status !== null and !in_array($status, $valid_status)){
            return back()->withError('Invalid Status');
        }

        $valid = request()->validate([
            'receiver' => 'required|email|exists:users,email',
            'subject' => 'required|string|min:3',
            'details' => 'required|string|min:10',
            'attachment' => 'nullable|file'
        ]);

        // all ok
        $user = User::findOrFail($user_id);


        $random_numbers = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $n = 10;

        for ($i = 0; $i < $n; $i++) {
            $index = random_int(0, strlen($random_numbers) - 1);
            $randomString .= $random_numbers[$index];
        }


        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->sender = $user->email;
        $notification->receiver = $valid['receiver'];
        $notification->subject = $valid['subject'];
        $notification->details = $valid['details'];

        if($status == 'draft'){
            $notification->status = $status;
        }else{
            $notification->status = 'unread';
        }

        if(request()->file('attachment') !== null){
            $file = request()->file('attachment');
            $filename = $file->getClientOriginalName(); // get file ori name
            $path = $file->storeAs('attachments', $filename);
            $notification->attachment = $path;
        }

        $notification->datetime = date('Y-m-d H:i:s', strtotime(now()));
        $notification->token = $randomString;
        $notification->save();

        // implement email send here to the receiver
        $data = [
            'user' => $user,
            'notification' => $notification
        ];

        if($notification->status !== 'draft'){
            Mail::send('notification.mail', $data, function($message) use ($notification) {
                $message->to($notification->receiver);
                $message->subject($notification->subject);
            });
        }

        return redirect('/notifications')->withSuccess('Notification Has Been Sent');
    }

    public function read($id){
        $notification = Notification::findOrFail($id);

        if($notification->receiver !== Auth::user()->email){
            return back()->withError('Access Denied!');
        }

        if($notification->status == 'read'){
            return back()->withError('You have already read this email');
        }

        $notification->status = 'read';
        $notification->save();

        return redirect('/notifications')->withSuccess('Marked As Read');
    }

    public function readAll(){
        $notifications = Notification::query()
                ->where('status', 'unread')
                ->where('receiver', Auth::user()->email)
                ->get();

        if($notifications->isEmpty()){
            return back()->withError('No emails are to be read at the moment');
        }

        foreach($notifications as $n){
            $notification = Notification::findOrFail($n->id);

            $notification->status = 'read';
            $notification->save();
        }

        return redirect('/notifications')->withSuccess('All Emails Are Marked As Read');
    }

    public function show($id){
        $notification = Notification::findOrFail($id);

        if($notification->receiver != Auth::user()->email and $notification->sender != Auth::user()->email){
            return back()->withError('Access Denied!');
        }

        // we set to read only if the receiver is viewing
        if(Auth::user()->email == $notification->receiver){
            $notification->status = 'read';
            $notification->save();
        }

        $notifications = Notification::query()
                ->where('token', $notification->token)
                ->orderBy('created_at', 'desc')
                ->get();


        return view('notification.show')->with([
            'notification' => $notification,
            'notifications' => $notifications
        ]);
    }

    public function reply($id){
        $valid = request()->validate([
            'subject' => 'required|string|min:3',
            'details' => 'required|string|min:10',
            'attachment' => 'nullable|file'
        ]);

        $notification = Notification::findOrFail($id);

        if($notification->receiver != Auth::user()->email and $notification->sender != Auth::user()->email){
            return back()->withError('Access Denied!');
        }

        if($notification->token == null){
            // this has no reply yet so create a token
            $random_numbers = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            $n = 10;

            for ($i = 0; $i < $n; $i++) {
                $index = random_int(0, strlen($random_numbers) - 1);
                $randomString .= $random_numbers[$index];
            }


            $notification->token = $randomString;
            $notification->save();
        }

        $reply = new Notification();
        $reply->user_id = Auth::user()->id;
        $reply->sender = Auth::user()->email;
        $reply->receiver = $notification->sender;
        $reply->subject = $valid['subject'];
        $reply->details = $valid['details'];

        if(request()->file('attachment') !== null){
            $file = request()->file('attachment');
            $filename = $file->getClientOriginalName(); // get file ori name
            $path = $file->storeAs('attachments', $filename);
            $reply->attachment = $path;
        }

        $reply->status = 'unread';
        $reply->token = $notification->token;
        $reply->datetime = $notification->datetime = date('Y-m-d H:i:s', strtotime(now()));
        $reply->save();

        return redirect('/notifications/'.$notification->id)->withSuccess('Reply Sent');
    }

    public function send($id){
        $user = Auth::user();

        $notification = Notification::findOrFail($id);

        if($user->email != $notification->sender){
            return back()->withError('Access Denied!');
        }

        if($notification->status != 'draft'){
            return back()->withError('This email has already been sent');
        }

        $notification->status = 'unread';

        $notification->save();

        $data = [
            'user' => $user,
            'notification' => $notification
        ];

        Mail::send('notification.mail', $data, function($message) use ($notification) {
            $message->to($notification->receiver);
            $message->subject($notification->subject);
        });

        return redirect('/notifications?status=draft')->withSuccess('Email Sent successfully');
    }

    public function count(){
        $user = Auth::user();

        $notifications = Notification::query()
                        ->where('receiver', $user->email)
                        ->get();

        $data = [
            'unread' => 0,
            'inbox' => 0,
            'read' => 0,
            'draft' => 0,
            'sent' => 0
        ];

        $count = 1;

        foreach($notifications as $n){
            if($n->status == 'unread'){
                $data['unread'] += $count;
            }else if($n->status == 'read'){
                $data['read'] += $count;
            }
        }

        $data['draft'] = Notification::query()
                ->where('sender', $user->email)
                ->where('status', '=', 'draft')
                ->count();

        $data['sent'] = Notification::query()
                ->where('sender', $user->email)
                ->where('status', '!=', 'draft')
                ->count();

        $data['inbox'] = Notification::query()
                ->where('receiver', $user->email)
                ->where('status','!=', 'draft')
                ->count();

        return response()->json([
            'unread' => $data['unread'],
            'inbox' => $data['inbox'],
            'read' => $data['read'],
            'draft' => $data['draft'],
            'sent' => $data['sent']
        ]);
    }

    public function download($id){
        $notification = Notification::findOrFail($id);

        if ($notification->receiver != Auth::user()->email && $notification->sender != Auth::user()->email) {
            return back()->withError('Access Denied!');
        }

        $filePath = storage_path('app/' . $notification->attachment);

        if (!file_exists($filePath)) {
            return back()->withError('File does not exist.');
        }

        $originalFilename = basename($notification->attachment);

        return response()->download($filePath, $originalFilename);
    }
}
