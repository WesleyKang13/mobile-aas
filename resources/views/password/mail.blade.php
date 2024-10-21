<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Current Password</title>
</head>
<body>
    <p>
        Dear {{$user->firstname. ' '. $user->lastname}},</br></br>
            Please proceed to click the link <a href="{{env('FORGOT_PASSWORD_URL')}}/{{$user->id}}">HERE IF YOU WISH TO CONTINUE</a>.</br>
            Please ignore this email if you did not generate this request! </br>

            If you have any questions or enquiries,
            please do not hesitate to contact <a href="mailto:admin@example.com">admin@example.com</a></br></br>

        Best Regards,</br>

        Support Team


    </p>
</body>
</html>
