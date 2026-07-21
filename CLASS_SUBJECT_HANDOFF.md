# Class / Subject / Teacher / Student System — Handoff Notes

This documents the Class → Subject → Book/Teacher/Student system built into this project, for continuing work in a fresh chat session (e.g. no access to prior conversation history).

## The two-project setup

There are **two separate Laravel codebases sharing one Postgres database** (`characterbuilding`, on `127.0.0.1:5432`):

| Project | Path | Role |
|---|---|---|
| `characterbuilding` | `c:\laragon\www\characterbuilding` | Filament 4 **admin panel**. Admin creates classes/subjects/books, assigns teachers/students. |
| `source-code` | `c:\laragon\www\source-code` | **Frontend app** (Livewire) — student portal, teacher portal (`/teacher/dashboard`), storefront. |

Both projects have their **own copies** of the `User`, `Product`, `Subject`, `ClassRoom` models (same DB tables, separate PHP code — a relationship or field added in one project does NOT automatically exist in the other. Check both when making schema-related changes).

## Database schema (current state)

- **`classes`** — `id`, `name`. Model: `ClassRoom` (table name overridden to `classes` since `Class` is a PHP reserved word).
- **`subjects`** — `id`, `name`, `class_id` (FK → `classes`, **one class per subject**, required). Model: `Subject`.
  - Earlier design used a `class_subject` many-to-many pivot (one subject shared across many classes). This was **replaced** with a direct `class_id` column — each subject now belongs to exactly one class. The pivot table and its migration were deleted.
- **`users`** — existing table, gained:
  - `class_id` (FK → `classes`, nullable, `nullOnDelete`) — a student's single assigned class. **One class at a time**, enforced structurally via a plain `belongsTo`/FK column (not a pivot).
  - `teacher_id` (pre-existing) — a *separate*, independent concept: which teacher's dashboard shows this student's book/reading progress. Not derived from subjects.
  - `role` column: `admin` | `teacher` | `student`.
- **`student_subject`** — pivot, `student_id` + `subject_id`. A student can have many subjects (must be subjects offered by their assigned class).
- **`teacher_subject`** — pivot, `teacher_id` + `subject_id`. A teacher can teach many subjects, **not scoped to any particular class** — assigning a teacher to "Chemistry" means they teach Chemistry in every class that offers it.
- **`products`** (books) — gained `subject_id` (FK → `subjects`, nullable, `nullOnDelete`). A "book" **is** a `Product` row (same e-commerce entity — price, PDF, stock, etc.) — there is no separate `Book` model. `product_categories` and `product_packages` tables/features were removed entirely (they were unused).

## Model relationships

**`characterbuilding`** (`app/Models/`):
- `ClassRoom::subjects()` — `hasMany(Subject::class, 'class_id')`
- `ClassRoom::students()` — `hasMany(User::class, 'class_id')->where('role', 'student')`
- `Subject::classRoom()` — `belongsTo(ClassRoom::class, 'class_id')`
- `Subject::students()` — `belongsToMany(User::class, 'student_subject', 'subject_id', 'student_id')`
- `Subject::teachers()` — `belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id')`
- `Subject::books()` — `hasMany(Product::class)`
- `User::classRoom()` — `belongsTo(ClassRoom::class, 'class_id')`
- `User::subjects()` — `belongsToMany(Subject::class, 'student_subject', 'student_id', 'subject_id')` (student's subjects)
- `User::teachingSubjects()` — `belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id')` (teacher's subjects)
- `Product::subject()` — `belongsTo(Subject::class)`

**`source-code`** (`app/Models/`) — mirrors the above (`Subject.php`, `ClassRoom.php` created from scratch; `User.php`/`Product.php` had the relevant relationships added). Keep these in sync if the schema changes again.

## Admin panel flow (`characterbuilding`, Filament)

Resources: `ClassRoomResource`, `SubjectResource`, `UserResource`, `ProductResource` (all under `app/Filament/Resources/`).

1. **Create a Class** — `/admin/classes/create` — just a name.
2. **Create a Subject** — `/admin/subjects/create` — name + required single "Class" dropdown.
3. **Upload a Book** — `/admin/products/create` — has a "Class" filter dropdown (not saved, just narrows the list) + "Subject" dropdown (disabled until a class is picked, shows only that class's subjects).
4. **Assign a Student** — `/admin/users/{id}/edit`, role = student:
   - "Assigned Class" (single select, sets `class_id`)
   - "Assigned Subjects" (multi-select, disabled until a class is picked, options = that class's subjects only)
   - "Assigned Teacher" (single select — filtered to teachers who teach at least one of the student's selected subjects; auto-clears if it becomes invalid when subjects change)
5. **Assign a Teacher** — `/admin/users/{id}/edit`, role = teacher:
   - "Teaching Subjects" (multi-select, all subjects, no class restriction)

The Users list page (`/admin/users`) has All/Admin/Teacher/Student filter buttons built as table `toolbarActions` (not Filament's native `Tab` component — see "Known quirks" below).

## Teacher dashboard flow (`source-code`)

`app/Services/Teacher/TeacherDashboardService.php` → `getData()`:
1. `$teacher->teachingSubjects()->pluck('subjects.id')` — subjects this teacher teaches
2. `Product::whereIn('subject_id', $subjectIds)` — books belonging to those subjects → shown on dashboard
3. For each book, students shown = `User::where('role','student')->whereHas('subjects', ...)` matching that book's subject
4. Reading/unlock mechanics (`TeacherBookProgress`, `StudentBookOverride`, `ReadingProgress`) are **unchanged** — still keyed by `teacher_id`/`student_id`/`product_id`, independent of the subject system; subjects only decide *which* books/students appear, not the progress tracking itself.
5. **Not wired to subjects**: `Product::canViewPage()` — a student still needs a **paid order** to actually view a book's pages. The subject system controls dashboard visibility, not the purchase paywall.

`app/Livewire/Teacher/Dashboard.php` — `updateStudentOverride()` authorizes by checking the student appears in the already-computed subject-scoped `$this->books` list (not `teacher_id` anymore).

## Known quirks / things to be careful about

- **PHP `Class` is reserved** — the class model is named `ClassRoom`, mapped to table `classes`.
- **Postgres, not MySQL** — no `SHOW CREATE TABLE`; use `information_schema` queries instead.
- **Filament 4 namespace changes**: `Get`/`Set` live at `Filament\Schemas\Components\Utilities\{Get,Set}`, not `Filament\Forms\{Get,Set}`.
- **`->bulkActions()` and `->toolbarActions()` on a Table both write to the same underlying array** — calling both in sequence causes the later call to silently wipe out the earlier one. Combine everything into one `->toolbarActions([...])` call if you need both custom buttons and bulk actions.
- **Filament's `->badge()` on Actions always renders as an absolutely-positioned corner overlay** (`fi-btn-badge-ctn`/`fi-link-badge-ctn` both use `position: absolute`), not an inline pill — confirmed via the compiled CSS. If you want an inline count next to a label, put it in the label itself as HTML, don't use `->badge()`.
- Two visually-separate concepts on a student that look similar but aren't: `class_id`/`subjects` (the class/subject system) vs. `teacher_id` (book progress dashboard ownership). Don't assume they're kept in sync automatically.

## If asked to make more changes

- Check **both** projects (`characterbuilding` AND `source-code`) for any model/relationship change — they don't share code, only the database.
- Run `php -l <file>` after any PHP edit to catch syntax errors before reporting done.
- This project's timezone/dates in migration filenames use `2026_07_21` as "today" — check the actual current date rather than assuming.
