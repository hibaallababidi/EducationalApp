<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4CAF50;
        }

        p {
            color: #555;
        }

        .success-icon {
            font-size: 50px;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="success-icon">✔</div>
    <h1>Subscription Successful</h1>
    <p>Your subscription has been successfully processed!</p>
    {{--    <a href="javascript:void(0);" class="btn">Return to the App</a>--}}
</div>
</body>
</html>


{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0">--}}
{{--    <title>Subscription Successful</title>--}}
{{--</head>--}}
{{--<body>--}}
{{--<h1>Subscription Successful</h1>--}}
{{--<p>Your subscription is being processed...</p>--}}

{{--<form method="POST" action="{{ route('process.subscription', ['course_id' => $course_id, 'student_id' => $student_id]) }}">--}}
{{--    @csrf--}}
{{--    <button type="submit">Submit</button>--}}
{{--</form>--}}

{{--</body>--}}
{{--</html>--}}
