# Classroom — Student Gradebook

A Laravel 13 and Vue 3 application for the ITE 311 Vue.js Fundamentals laboratory. The dashboard lets an instructor add and manage student grade records, search and filter the roster, and see class performance update as records change.

## Run locally

Requirements: PHP 8.3+, Composer, and Node.js with npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
```

Run the Laravel server and Vite in separate terminals:

```powershell
php artisan serve
```

```powershell
npm run dev
```

Open the URL printed by `php artisan serve` (usually `http://127.0.0.1:8000`). For a production asset build, run `npm run build`.

## Using the gradebook

- Add a student with an ID, name, program, year level, and grade from 0–100.
- IDs must be unique; grades under 75 are marked **Needs support**.
- Search by name, student ID, or program, and filter the roster by grade status.
- Remove a record with its row action. Summary cards and program counts recalculate automatically.
- Records are saved in the browser’s local storage for this site; they are not sent to or stored by the Laravel backend.

## Vue concepts demonstrated

| Concept | Example in the application |
| --- | --- |
| `ref()` and reactive data | Roster, search, filters, form visibility, and notices |
| `v-model` and interpolation | Student form fields, roster search, and live summaries |
| `v-if` / `v-else` and `v-show` | Validation feedback, empty states, and collapsible form |
| Event handling and methods | Submit, remove, filter, and toggle actions |
| `computed()` and reactive calculations | Class average, pass rate, course mix, and filtered roster |
| `v-for`, arrays, and objects | Course options, filter tabs, student rows, and statistics |
| Form validation | Required fields, grade bounds, and duplicate ID checks |
| Dynamic class and style binding | Grade status, course colors, progress meters, and row animation |
| Watchers | Persist roster changes to local storage |
| Components | `StudentGradebook.vue` uses the reusable `StudentForm.vue` component |

## Project entry points

- `routes/web.php` serves the gradebook page.
- `resources/views/students.blade.php` provides Laravel’s Blade/Vite mount point.
- `resources/js/components/StudentGradebook.vue` contains the dashboard and reactive roster.
- `resources/js/components/StudentForm.vue` contains the student form and validation.
- `resources/css/app.css` contains the responsive dashboard styles.

Run the Laravel feature tests with `php artisan test`.
