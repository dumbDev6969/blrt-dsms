# Web-based Intelligent Driving School Management and Enrollment System (BLRT-DSMS)

BLRT-DSMS is a role-based operations platform for managing the full lifecycle of a driving school — from student and instructor onboarding, through document verification and enrollment approval, to scheduled driving sessions, formal skills assessment, and grading. The system's "intelligence" comes from two places: an automated instructor-matching engine that assigns the best-fit instructor to an approved enrollment based on skill set, vehicle/transmission compatibility, weekly availability, and current workload, and a Google Dialogflow-powered chatbot that gives prospective and enrolled users conversational access to information about the school. The application is built entirely on the TALL stack, using Livewire 4's single-file component syntax for every interactive page — there is no separate REST/JSON API layer; all client-server interaction happens over Livewire's wire protocol.

![Performance analytics dashboard showing school-wide metrics and enrollment data](docs/images/system_ui/school_admin/performance_analytics.png)

## Tech Stack

- **[Laravel 12](https://laravel.com)** — Application framework (PHP ^8.2)
- **[Livewire 4](https://livewire.laravel.com)** — Full-stack reactive components, using single-file `.blade.php` components (routed via `Route::livewire()`)
- **[Livewire Flux 2.9](https://fluxui.dev)** — UI component library (buttons, modals, navlists, inputs, etc.)
- **[Alpine.js](https://alpinejs.dev)** — Client-side interactivity (bundled with Livewire)
- **[Tailwind CSS 4](https://tailwindcss.com)** — Utility-first styling, compiled via the Vite plugin
- **[Laravel Fortify 1.30](https://laravel.com/docs/fortify)** — Authentication scaffolding (registration, password reset, two-factor auth)
- **[Spatie Laravel-Permission 6.24](https://spatie.be/docs/laravel-permission)** — Role- and permission-based access control (`Admin`, `Instructor`, `Student`, `Staff`)
- **[Google Cloud Dialogflow 2.4](https://cloud.google.com/dialogflow)** — Conversational AI chatbot integration
- **SQLite** — Local, file-based database engine
- **[Laravel Herd](https://herd.laravel.com)** — Local development environment (serves the app at `http://blrt-dsms-main.test`)
- **Pest 3 / PHPUnit** — Testing framework
- **Laravel Pint** — Code style enforcement

## Core System Features

Every feature below was verified directly against the routes, Livewire components, and service classes in this repository — nothing here is generic boilerplate.

- **Role-Based Multi-Portal Access** — Four distinct roles (`Admin`, `Instructor`, `Student`, `Staff`) are seeded via Spatie Permission, each gated to its own set of routes through `can:` middleware (e.g. `can:user.view`, `can:enrollment.view_any`, `can:student.view_any`, `can:instructor.view_own`).
- **Guided Profile Onboarding** — New Students and Instructors are redirected to a mandatory profile-completion wizard (`student/onboard`, `instructor/onboard`) by the custom `EnsureProfileIsComplete` middleware before they can reach any other authenticated page.

  ![Online enrollment form for new student registration](docs/images/system_ui/school_admin/online_enrollment.png)
- **Multi-Step Enrollment Form Pipeline** — Students submit an `EnrollmentForm` (draft → submitted → approved/rejected) capturing package type (TDC/PDC/Refresher), vehicle category, transmission preference, and JSON-stored personal info and schedule preferences. Staff review and approve/reject forms with a recorded rejection reason and reviewer audit trail.

  ![Enrollment details view showing package type, vehicle category, and approval status](docs/images/system_ui/admin/enrollment_details.png)
- **Automated Instructor Matching Engine** (`EnrollmentService::findBestInstructor`) — On enrollment approval, the system filters active/verified instructors by required skill (theoretical vs. practical), transmission type, vehicle category, and weekly schedule overlap with the student's preferred days, then ranks remaining candidates by current active-enrollment workload to balance instructor load automatically. Students with no available match are placed on a `waiting_list`.

  ![Instructor-student coordination panel showing matched assignments](docs/images/system_ui/staff/instructor_student_coordination.png)
- **Document Upload & Verification Pipeline** — Students upload required documents (birth certificate, medical certificate, ADL form, valid ID, TDC certificate, TIN ID, passport, etc.) which enter a `pending → verified/rejected` review queue for Admin/staff, with private file storage served through an authorized streaming route (`document/serve/{document}`).

  ![Document review management queue with pending, verified, and rejected status badges](docs/images/system_ui/admin/document_review_management.png)
- **Instructor Availability Engine** (`InstructroAvailabilityService`) — Computes a rolling 30-day availability calendar per instructor by cross-referencing their stored weekly schedule against existing booking sessions.
- **Booking Session & Assessment Tracking** — Tracks individual lecture/driving/assessment sessions, including score, pass/fail outcome, student ratings, per-skill JSON progress tracking, and a full change-log audit trail.
- **Formal Driving Assessment Forms** — A structured, section-by-section practical assessment (pre-drive checklist, immediate-fail criteria, driving skills rating, traffic rule observance, learner-type classification) modeled directly on the physical LTO assessment form, complete with instructor and admin sign-off fields.

  ![Instructor performance evaluation and grading form](docs/images/system_ui/instructor/performance_eval_grading.png)
- **Instructor Grading & Metrics** (`InstructorGradingService`, `InstructorMetricService`) — Finalizes course grades transactionally, auto-creates the corresponding theoretical assessment record, closes out related booking sessions, and rolls results into monthly per-instructor performance metrics (pass rate, average rating, sessions completed).
- **Live Analytics** (`SystemMetric::syncToday`) — Daily aggregate snapshot of revenue, new student signups, active enrollments, completed courses, and total bookings.

  ![Enrollment oversight dashboard displaying enrollment counts, statuses, and trends](docs/images/system_ui/admin/enrollment_oversight.png)
- **AI Chatbot Assistant** — A Livewire-powered chat widget backed by Google Dialogflow's `SessionsClient`, with per-session conversation history stored server-side and graceful fallback messaging if the Dialogflow API call fails.

  ![Dialogflow-powered chatbot widget open with a sample conversation](docs/images/system_ui/student/chatbot.png)
- **Vehicle Fleet Management** — Tracks vehicle type, transmission, maintenance history (JSON), and next scheduled maintenance date, with live status (`available`, `maintenance`, `in-use`).

  ![Vehicle management table listing plates, transmission types, and maintenance status](docs/images/system_ui/admin/vehicle_management.png)
- **LTO-Accredited Clinic Directory** — Maintains accredited third-party clinic records (for medical certificates, etc.) with accreditation expiry tracking.
- **Two-Factor Authentication & Email Verification** — Full Fortify-driven 2FA (with recovery codes) and mandatory email verification gating access to the dashboard.
- **PWA Support** — Includes a service worker, web manifest, and a dedicated offline fallback route (`/offline`) that is intentionally excluded from all auth middleware to avoid redirect loops when the user is offline.

## System Requirements & Initialization

The application enforces several runtime safeguards in `app/Providers/AppServiceProvider.php` that any developer or deployer must be aware of:

- **Immutable Dates by Default** — `Date::use(CarbonImmutable::class)` is set globally, meaning every date/time helper (`now()`, Eloquent date casts, etc.) throughout the app returns a `CarbonImmutable` instance rather than mutable `Carbon`. Code that expects to mutate a date object in place (`$date->addDay()`) will not work as it might in a default Laravel app — always reassign the returned value.
- **Destructive Command Protection in Production** — `DB::prohibitDestructiveCommands(app()->isProduction())` blocks destructive Artisan commands (e.g. `migrate:fresh`, `db:wipe`) whenever `APP_ENV=production`. This is a hard safety rail, not a suggestion — it cannot be bypassed with `--force`.
- **Stepped-Up Password Policy in Production** — `Password::defaults()` enforces a minimum 12-character password with mixed case, letters, numbers, symbols, and a "not previously compromised" check (via the Have I Been Pwned API) automatically once the app runs in production. In non-production environments, no default policy is enforced.
- **Mandatory Dialogflow Credential Validation on Boot** — `configureDialogflow()` runs on every non-console boot (console/artisan commands and unit tests are exempted) and throws a `RuntimeException` immediately if the file at `services.dialogflow.credentials_json` is missing or the config value is blank. **The application will not boot in a browser context without a valid Dialogflow service-account JSON file present at the configured path.** See the setup steps below to avoid this on first run.

## Local Installation & Setup

> Prerequisites: PHP ^8.2, Composer, Node.js, and [Laravel Herd](https://herd.laravel.com) (or any local server capable of serving `.test` domains).

1. **Clone the repository and install dependencies**
   ```bash
   git clone https://github.com/dumbDev6969/blrt-dsms.git blrt-dsms
   cd blrt-dsms
   composer install
   npm install
   ```

2. **Create your environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure `APP_URL` and the database connection** in `.env`:
   ```env
   APP_URL=http://blrt-dsms-main.test
   DB_CONNECTION=sqlite
   ```
   (`DB_CONNECTION=sqlite` is already the default in `.env.example` — just confirm it wasn't overridden.)

4. **Configure the Dialogflow credentials — required before the app will boot.** Add the following to `.env` (these keys are not present in `.env.example` by default and must be added manually):
   ```env
   DIALOGFLOW_PROJECT_ID=your-gcp-project-id
   DIALOGFLOW_CREDENTIALS=app/google-auth.json
   ```
   Place your Google Cloud service-account JSON key file at `storage/app/google-auth.json` (the path is resolved relative to `storage_path()`). Without this file present at boot, every non-console request will throw a `RuntimeException` from `AppServiceProvider::configureDialogflow()`.

5. **Create the SQLite database file:**
   ```bash
   # Git Bash / macOS / Linux
   touch database/database.sqlite

   # PowerShell
   New-Item -ItemType File -Path database/database.sqlite -Force
   ```

6. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```
   This seeds roles/permissions (`Admin`, `Instructor`, `Student`, `Staff`) along with one demo account per role, all using the password `password`:
   | Role | Email |
   |---|---|
   | Admin | `admin@blrt.com` |
   | Instructor | `instructor@blrt.com` |
   | Student | `student@blrt.com` |
   | Staff | `staff@blrt.com` |

7. **Compile frontend assets and start the dev server:**
   ```bash
   npm run dev
   ```
   Or run the server, queue listener, and Vite dev server concurrently with a single command:
   ```bash
   composer run dev
   ```

8. Visit `http://blrt-dsms-main.test` (via Herd) and log in with one of the seeded demo accounts above.

![Login page with email and password fields](docs/images/system_ui/login.png)

## Directory Structure

```
blrt-dsms-main/
├── app/
│   ├── Actions/Fortify/          # Fortify auth actions (CreateNewUser, ResetUserPassword)
│   ├── Concerns/                 # Shared validation rule traits (Profile, Password)
│   ├── Http/
│   │   └── Middleware/
│   │       └── EnsureProfileIsComplete.php   # Forces onboarding wizard before dashboard access
│   ├── Livewire/                 # Standalone Livewire classes (Courses, Logout, grading widgets)
│   ├── Models/                   # Eloquent models — see schema table below
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Immutable dates, prod safeguards, Dialogflow boot check
│   └── Services/
│       ├── EnrollmentService.php            # Approval workflow + automated instructor matching
│       ├── InstructorGradingService.php     # Final grade submission + assessment sync
│       ├── InstructorMetricService.php      # Monthly per-instructor performance rollups
│       ├── InstructorPerformanceService.php # Aggregate review/rating analytics
│       ├── InstructroAvailabilityService.php # 30-day instructor availability calendar
│       └── AssessmentAnalyticsService.php   # Static labels for practical assessment criteria
├── database/
│   ├── factories/                # Model factories for all domain entities
│   ├── migrations/                # Full relational schema (see table below)
│   └── seeders/                  # RolesAndPermissionsSeeder, demo data seeders per entity
├── resources/
│   └── views/
│       ├── pages/                 # Route-mapped Livewire full-page components ("pages::" namespace)
│       │   ├── admin/              # Registrations, courses, documents, vehicles, users, clinics
│       │   ├── auth/               # Fortify-driven login/register/2FA/reset views
│       │   ├── instructor/         # Onboarding, schedule, students, assessment, evaluations
│       │   ├── settings/           # Profile, password, appearance, 2FA settings
│       │   ├── staff/               # Enrollment management, waiting list, approvals
│       │   └── student/            # Onboarding, enrollment form, schedule, records, documents
│       ├── components/             # Reusable Blade/Livewire components (incl. the AI chatbot)
│       ├── layouts/                 # App and auth shell layouts
│       └── flux/                    # Published/customized Flux UI component overrides
├── routes/
│   ├── web.php                     # All application routes (guest, auth, role-gated groups)
│   └── settings.php                 # User settings routes (profile, password, 2FA, appearance)
└── tests/
    ├── Feature/                     # Auth flows, settings, enrollment-blocking behavior
    └── Unit/
```

> Note: Files under `resources/views/pages/**` and several components use Livewire 4's single-file component convention, prefixed with a `⚡` symbol in the filename (e.g. `⚡onboard.blade.php`) to denote a class-and-markup-combined component.

## Database Schema Overview

| Entity | Key Fields | Relationships |
|---|---|---|
| **users** | `name`, `email`, `password`, `status` (active/pending/rejected), 2FA columns | `hasOne` StudentProfile, `hasOne` InstructorProfile, `hasMany` Document; roles via Spatie Permission |
| **student_profiles** | `birth_date`, `contact_number`, `address`, `nationality`, `is_minor`, `civil_status`, `sex`, `ltms_client_id`, `meta_details` (JSON) | `belongsTo` User; `hasMany` EnrollmentForm, Enrollment, Assessment, InstructorPerformance |
| **instructor_profiles** | `license_number`, `license_expiry`, `skills` (JSON), `vehicle_types` (JSON), `weekly_schedule` (JSON), `status` (pending/verified/...), `is_active` | `belongsTo` User; `hasMany` Enrollment, BookingSession, InstructorMetric, Assessment, InstructorPerformance |
| **courses** | `code` (auto-generated `BLRT-XXXXXX`), `title`, `price`, `duration_hours`, `type` (theoretical/practical), `prerequisites` (JSON) | `hasMany` EnrollmentForm, Enrollment |
| **enrollment_forms** | `control_number` (auto-generated), `package_type` (TDC/PDC/Refresher), `vehicle_category`, `transmission`, `status` (draft/submitted/approved/rejected), `personal_info` (JSON), `course_preferences` (JSON) | `belongsTo` StudentProfile, Course; `hasOne` Enrollment; `belongsTo` User as reviewer |
| **enrollments** | `code` (unique), `status` (waiting_list/pending/active/completed/dropped), `progress_percent`, `final_grade`, `final_result`, financials (`total_amount`, `amount_paid`, `balance`), TDC/PDC hour tracking | `belongsTo` StudentProfile, Course, InstructorProfile, EnrollmentForm; `hasMany` BookingSession, Assessment, InstructorPerformance |
| **booking_sessions** | `start_time`, `end_time`, `type` (lecture/driving/assessment), `status`, `score`, `is_passed`, `rating`, `skill_progress` (JSON), `change_log` (JSON) | `belongsTo` Enrollment, InstructorProfile, Vehicle; `hasMany` Assessment; `hasOne` InstructorPerformance |
| **assessments** | Structured LTO-style practical exam: `pre_drive_checklist`, `immediate_fails`, `driving_skills`, `traffic_rules` (all JSON with counts/ratings), `learner_type`, `is_passed`, sign-off fields | `belongsTo` Enrollment, StudentProfile, InstructorProfile, BookingSession; `belongsTo` User as `notedBy` |
| **instructor_performances** | `rating`, `performance_criteria` (JSON), `feedback_comment`, `areas_of_strength`, `areas_for_improvement`, `evaluation_date` | `belongsTo` InstructorProfile, StudentProfile, Enrollment, BookingSession |
| **instructor_metrics** | Monthly rollup: `total_sessions`, `completed_sessions`, `total_hours`, `avg_rating`, `students_taught`, `students_passed`, `pass_rate` | `belongsTo` InstructorProfile |
| **documents** | `type` (enum: birth_cert, medical, adl_form, valid_id, marriage_contract, tdc_certificate, tin_id, passport), `file_path`, `status` (pending/verified/rejected), `metadata` (JSON) | `belongsTo` User; `belongsTo` User as `verifiedBy` |
| **vehicles** | `model`, `plate_number` (unique), `transmission`, `type` (motorcycle/automobile/tricycle), `status`, `maintenance_history` (JSON), `next_maintenance_date` | `hasMany` BookingSession |
| **lto_clinics** | `clinic_name`, `accreditation_number` (unique), `address`, `accreditation_expiry`, `is_active` | Standalone reference table |
| **system_metrics** | Daily snapshot: `new_students`, `active_enrollments`, `completed_courses`, `total_bookings`, `revenue`, `additional_data` (JSON) | Standalone, populated via `SystemMetric::syncToday()` |

**Role/Permission tables** (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) are provided by `spatie/laravel-permission` and back the four application roles: `Admin`, `Instructor`, `Student`, `Staff`.
