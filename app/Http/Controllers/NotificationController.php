<?php /** @noinspection PhpUndefinedMethodInspection */

/** @noinspection PhpMissingReturnTypeInspection */

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public function sendNotification($device_key, $body, $title)
    {
        // Path to your service account JSON file
        $serviceAccountPath = 'json\file.json'; // In server

        // Get the OAuth 2.0 token
        $accessToken = $this->getAccessToken($serviceAccountPath);

        // The new FCM HTTP v1 URL
        $URL = 'https://fcm.googleapis.com/v1/projects/sila-78fef/messages:send';

        // The new payload structure
        $post_data = [
            "message" => [
                "token" => $device_key,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        // Sending the request using Laravel's Http client
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($URL, $post_data);

        // Check if the request was successful
        if ($response->successful()) {
            return $response->json();
        } else {
            // Log the error or handle it appropriately
            $error = $response->body();
            // Handle or log the error as needed
            return response()->json([
                'message' => 'Notification sending failed',
                'error' => $error
            ], $response->status());
        }
    }

    private function getAccessToken($serviceAccountPath)
    {
        // Construct the full path using storage_path with forward slashes
        $fullPath = public_path('json/file.json'); // Ensure all slashes are forward slashes

        // Debugging: Output the path to verify it's correct
//        echo "Looking for file at: " . $fullPath;

        // Ensure the file exists before trying to open it
        if (!file_exists($fullPath)) {
            throw new \Exception("The service account file does not exist at path: " . $fullPath);
        }

        // Read the file contents
        $credentials = file_get_contents($fullPath);

        if ($credentials === false) {
            throw new \Exception("Failed to read the service account file.");
        }

        // Decode the JSON file contents into an associative array
        $credentials = json_decode($credentials, true);

        if ($credentials === null) {
            throw new \Exception("Failed to decode the service account file. The file may be improperly formatted.");
        }

        // Initialize the Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($credentials);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();

        // Return the access token
        return $client->getAccessToken()['access_token'];
    }

    public function sendLessonReminders()
    {
        try {
//            Log::info('sendLessonReminders: Task started');

            // Set the reminder time to exactly 15 minutes before the lesson starts
            $reminderTime = now()->addMinutes(15);

            // Fetch lessons happening exactly 15 minutes from now
            $lessons = DB::table('private_lessons')
                ->where('lesson_date', '=', $reminderTime)
                ->get();

            foreach ($lessons as $lesson) {
                // Fetch the teacher and student data
                $teacher = DB::table('teachers')->where('id', $lesson->teacher_id)->first();
                $student = DB::table('students')->where('id', $lesson->student_id)->first();

                // Prepare notification data
                $title = "Upcoming Lesson Reminder";
                $body = "You have a lesson scheduled at " . Carbon::parse($lesson->lesson_date)->format('H:i');

                // Send notification to teacher
                if ($teacher && $teacher->device_token) {
                    $this->sendNotification($teacher->device_token, $body, $title);
                }

                // Send notification to student
                if ($student && $student->device_token) {
                    $this->sendNotification($student->device_token, $body, $title);
                    Log::info('Notification sent for student lesson id: ' . $lesson->id);
                }

                // Save the notification to the database
//                Notification::create([
//                    'type' => 'student',
//                    'user_id' => $student->id,
//                    'body' => json_encode($body)
//                ]);

//                Log::info('Notification sent for lesson id: ' . $lesson->id);
            }

//            Log::info('sendLessonReminders: Task completed successfully');
        } catch (\Exception $e) {
            Log::error('sendLessonReminders: Task failed with error - ' . $e->getMessage());
        }
    }

    public function getNotificationCount()
    {
        $count = Notification::where('user_id', Auth::id())->get()->count();
        return response()->json([
            'status' => true,
            'message' => trans('messages.notification_count'),
            'data' => $count
        ]);
    }

    public function notificationListTeacher()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('type', 'teacher')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'title',
                'body',
                'actor_id',
                'created_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => trans('messages.notifications'),
            'data' => $notifications
        ]);
    }

    public function notificationListStudent()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->where('type', 'student')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'title',
                'body',
                'actor_id',
                'created_at'
            ]);

        return response()->json([
            'status' => true,
            'message' => trans('messages.notifications'),
            'data' => $notifications
        ]);
    }


}
