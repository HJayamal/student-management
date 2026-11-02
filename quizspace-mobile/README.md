# QuizSpace Mobile (Expo + React Native)

This is a separate React Native mobile client for the Laravel API in the `student-management` project. It does not replace or modify the existing web frontend.

## Included mobile features

### Student
- Email/password login using the existing `/api/student/login` endpoint
- Academic profile from `/api/student/dashboard`
- Assigned quizzes from `/api/student/quizzes`
- Server-timed quiz start/resume from `/api/student/quizzes/{id}/start`
- Countdown based on the server's saved `deadline_at`
- Per-answer server persistence through `/api/student/quizzes/{id}/attempt/answer`
- Automatic submit when the deadline expires
- Marks and results from `/api/student/quizzes/{id}/submit` and `/api/student/results`
- Summary cards for attempts, average percentage, and best percentage
- Token storage using Expo SecureStore

### Teacher
- Email/password login using `/api/teacher/login`
- Teacher profile/dashboard
- Teacher's quiz list
- Create and publish a quiz with questions through `/api/teacher/quizzes`
- View student submissions through `/api/teacher/results`

The admin Blade pages (student/admission/course/degree/batch management) remain in the web app. Those web pages do not all have matching JSON API endpoints yet, so the first mobile client covers the student and teacher API workflows supported by this repository.

## Requirements

- Node.js 22.13 or newer is recommended for Expo SDK 57.
- A phone and development computer on the same Wi-Fi network, or an Android emulator.
- The Laravel app and database must be running.

## Run the mobile app

From a terminal:

```powershell
cd path\to\quizspace-mobile
npm install
npx expo start
```

Install **Expo Go** on an Android phone, scan the QR code, and open the app. On an iPhone, use Expo Go only with a compatible SDK build.

## Connect it to Laravel

The login screen contains a **Laravel API address** field. Enter the correct base address for the machine running Laravel.

### Physical phone on the same Wi-Fi

1. On Windows, run `ipconfig` and find the computer's Wi-Fi **IPv4 Address**, for example `192.168.1.20`.
2. Start Laravel so it listens on the local network:

   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```

3. In the mobile app, enter `http://192.168.1.20:8000/api`, replacing the sample IP with your computer's actual IPv4 address.
4. Allow PHP/Laravel through Windows Defender Firewall on a **Private network** if requested.
5. Keep the phone and computer connected to the same Wi-Fi network.

### Android emulator

Use:

```text
http://10.0.2.2:8000/api
```

### Important

Do not use `http://127.0.0.1:8000/api` on a physical phone. On a phone, `127.0.0.1` points to the phone itself, not your computer. For production, use an HTTPS API URL.

## Quiz timing behavior

The server remains the source of truth for attempt deadlines. Starting or reopening a quiz requests the existing attempt session, and the app uses its saved deadline rather than starting a fresh duration. Each selected answer is sent to the server before submit. When the timer reaches zero, the app sends the auto-submit request; the backend grades the options previously saved in the session.

If the phone has no network when the deadline expires, the app cannot transmit the submit request until connectivity returns. When automatic submission fails, the app displays an error and a retry button. The server-side deadline still prevents answers arriving after the deadline from being accepted as saved answers.

## Teacher results score fields

The current repository's `TeacherResultsController` returns the submitted answers and student/quiz details but does not select the score columns. The optional `backend-patch/app/Http/Controllers/TeacherResultsController.php` file in this project adds `marks_obtained`, `total_marks`, `percentage`, and `graded_at` to that response. To show scores in the teacher mobile results screen, back up the Laravel controller and copy this patch to:

```text
app/Http/Controllers/TeacherResultsController.php
```

Then check it with:

```powershell
php -l app/Http/Controllers/TeacherResultsController.php
php artisan optimize:clear
```

## Existing Laravel API routes used

- `POST /api/student/login`
- `POST /api/teacher/login`
- `GET /api/student/dashboard`
- `GET /api/student/quizzes`
- `GET /api/student/quizzes/{quizId}`
- `POST /api/student/quizzes/{quizId}/start`
- `PUT /api/student/quizzes/{quizId}/attempt/answer`
- `POST /api/student/quizzes/{quizId}/submit`
- `GET /api/student/results`
- `GET /api/teacher/dashboard`
- `GET /api/teacher/quizzes`
- `POST /api/teacher/quizzes`
- `GET /api/teacher/results`
- `POST /api/student/logout`
- `POST /api/teacher/logout`

## Notes

- API tokens are stored in the device's secure storage via `expo-secure-store`.
- Quiz creation currently uses text fields for course, degree, batch, and subject. Enter values that match the existing academic setup.
- The current Laravel create-quiz endpoint requires `available_at` to be after the server's current time, and it publishes the quiz when it is created.
- The mobile app is a working source implementation; it has not been run against your personal Windows/MySQL environment from this workspace. Test your API URL and actual test accounts locally.
