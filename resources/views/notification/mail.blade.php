<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>New Notification from {{$notification->user->firstname. ' '.$notification->user->lastname}}</title>
</head>
<body>
    <p>
        You have received a new notification from {{$notification->user->firstname. ' '.$notification->user->lastname}}.
        </br>Please login to your account to view the notification

        @if($receiver->role !== 'admin')
            <a href="https://wesleytus.com" class="btn btn-primary">View Notification</a>
        @else
            <a href="https://backend.wesleytus.com">View Notification</a>
        @endif
    </p>
</body>
</html>
