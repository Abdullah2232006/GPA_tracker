<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPA Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg':       '#F3F4F4',
                        'rose':     '#853953',
                        'plum':     '#612D53',
                        'charcoal': '#2C2C2C',
                    },
                    fontFamily: {
                        'display': ['Playfair Display', 'serif'],
                        'body':    ['DM Sans', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }

        body {
            background-color: #F3F4F4;
            font-family: 'DM Sans', sans-serif;
            color: #2C2C2C;
            min-height: 100vh;
        }

        /* ── decorative header blob ── */
        .header-blob {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #612D53 0%, #853953 60%, #9d4a68 100%);
            clip-path: ellipse(110% 100% at 50% 0%);
            z-index: 0;
        }

        /* ── noise grain overlay ── */
        .grain::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 1;
        }

        /* ── semester card ── */
        .semester-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 20px rgba(44,44,44,0.07), 0 1px 4px rgba(44,44,44,0.04);
            overflow: hidden;
            transition: box-shadow 0.25s ease;
        }
        .semester-card:hover {
            box-shadow: 0 8px 40px rgba(133,57,83,0.12), 0 2px 8px rgba(44,44,44,0.06);
        }

        /* ── course row hover ── */
        .course-row {
            position: relative;
            transition: background 0.18s ease;
        }
        .course-row:hover {
            background: #fdf4f7;
        }
        .course-row .edit-btn {
            opacity: 0;
            transform: translateX(6px);
            transition: opacity 0.18s ease, transform 0.18s ease;
        }
        .course-row:hover .edit-btn {
            opacity: 1;
            transform: translateX(0);
        }

        /* ── add course row ── */
        .add-course-row {
            transition: background 0.18s ease;
            cursor: pointer;
        }
        .add-course-row:hover {
            background: #fdf4f7;
        }

        /* ── pill badge (GPA per semester) ── */
        .gpa-badge {
            background: linear-gradient(135deg, #853953, #612D53);
            color: #fff;
            border-radius: 999px;
            padding: 2px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }

        /* ── add semester button ── */
        .btn-add-semester {
            background: linear-gradient(135deg, #853953 0%, #612D53 100%);
            color: #fff;
            border-radius: 12px;
            padding: 12px 28px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.02em;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 18px rgba(133,57,83,0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }
        .btn-add-semester:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(133,57,83,0.45);
        }
        .btn-add-semester:active {
            transform: translateY(0);
            opacity: 0.9;
        }

        /* ── table header ── */
        th {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #853953;
        }

        /* ── divider between rows ── */
        .course-row + .course-row,
        .add-course-row {
            border-top: 1px solid #f3e8ed;
        }

        /* ── delete semester ghost btn ── */
        .btn-delete-sem {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .semester-card:hover .btn-delete-sem {
            opacity: 1;
        }

        /* ── animated entrance ── */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .slide-up { animation: slideUp 0.45s cubic-bezier(.22,.68,0,1.2) both; }
        .delay-1  { animation-delay: 0.06s; }
        .delay-2  { animation-delay: 0.12s; }
        .delay-3  { animation-delay: 0.18s; }

        /* ── grade color chips ── */
        .grade-chip {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .grade-A  { background:#f0faf0; color:#2e7d32; }
        .grade-B  { background:#e8f4fd; color:#1565c0; }
        .grade-C  { background:#fff8e1; color:#f57f17; }
        .grade-D  { background:#fff3e0; color:#e65100; }
        .grade-F  { background:#fdecea; color:#c62828; }
    </style>
</head>
<body>

{{-- ════════════════ HEADER ════════════════ --}}
<header class="relative overflow-hidden pt-14 pb-20 px-6 grain">
    <div class="header-blob"></div>

    {{-- decorative circles --}}
    <div class="absolute top-[-40px] right-[-40px] w-48 h-48 rounded-full opacity-10 z-10"
         style="background:radial-gradient(circle, #fff 0%, transparent 70%)"></div>
    <div class="absolute bottom-[-20px] left-[8%] w-28 h-28 rounded-full opacity-[0.07] z-10"
         style="background:#fff"></div>

    <div class="relative z-10 max-w-4xl mx-auto">
        {{-- greeting --}}
        <p class="text-white/60 font-body font-medium text-sm tracking-widest uppercase mb-1">Academic Dashboard</p>
        <h1 class="font-display font-black text-white leading-none"
            style="font-size: clamp(2.4rem, 6vw, 4rem);">
            Hello, {{ $user->name ?? 'Sarah' }}.
        </h1>

        {{-- CGPA --}}
        <div class="mt-3 flex items-baseline gap-3">
            <span class="font-display font-bold text-white/90"
                  style="font-size: clamp(1.5rem, 3.5vw, 2.4rem); letter-spacing:-0.01em;">
                CGPA: {{ $cgpa ?? '3.59' }}
            </span>
            <span class="text-white/50 font-body text-sm">/ 4.00</span>
        </div>

        {{-- progress bar --}}
        <div class="mt-4 w-48 h-1.5 rounded-full bg-white/20 overflow-hidden">
            <div class="h-full rounded-full bg-white/80"
                 style="width: {{ (($cgpa ?? 3.59) / 4) * 100 }}%; transition: width 0.8s ease;"></div>
        </div>
    </div>
</header>

{{-- ════════════════ MAIN CONTENT ════════════════ --}}
<main class="max-w-4xl mx-auto px-4 sm:px-6 -mt-8 pb-20">

    {{-- Add Semester button --}}
    <div class="flex justify-between items-center mb-8 slide-up">
        <p class="text-charcoal/50 font-body text-sm">
            {{ count($semesters ?? []) }} semesters &middot; {{ $totalCredits ?? 62 }} credit hours
        </p>
        <button class="btn-add-semester" onclick="addSemester()">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
            </svg>
            Add Semester
        </button>
    </div>

    {{-- ════ SEMESTERS LOOP ════ --}}
    {{-- In a real Laravel view this would be @foreach($semesters as $i => $semester) --}}

    {{-- ── Semester 1 ── --}}
    <div class="semester-card mb-6 slide-up delay-1" id="sem-1">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose/10"
             style="background: linear-gradient(to right, #fdf4f7, #fff)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                     style="background: linear-gradient(135deg,#853953,#612D53)">1</div>
                <div>
                    <h2 class="font-display font-bold text-charcoal text-base leading-tight">Fall 2022</h2>
                    <p class="text-charcoal/40 font-body text-xs">5 courses &middot; 15 credit hours</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="gpa-badge">GPA 3.73</span>
                <button class="btn-delete-sem text-charcoal/30 hover:text-rose transition-colors p-1"
                        onclick="deleteSemester(1)" title="Remove semester">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M2 2l11 11M13 2L2 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-rose/10" style="background:#fafafa">
                    <th class="text-left px-6 py-3">Course</th>
                    <th class="text-center px-4 py-3">Grade</th>
                    <th class="text-center px-4 py-3">Credit Hours</th>
                    <th class="text-center px-4 py-3">Points</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody>

                {{-- course rows --}}
                @php
                $courses1 = [
                    ['name' => 'Calculus I',              'grade' => 'A',  'credits' => 3],
                    ['name' => 'Introduction to CS',      'grade' => 'A',  'credits' => 3],
                    ['name' => 'English Composition',     'grade' => 'B+', 'credits' => 3],
                    ['name' => 'Physics I',               'grade' => 'A-', 'credits' => 3],
                    ['name' => 'Linear Algebra',          'grade' => 'B+', 'credits' => 3],
                ];
                @endphp

                @foreach($courses1 as $ci => $course)
                <tr class="course-row" id="sem1-course-{{ $ci }}">
                    <td class="px-6 py-3 font-body font-medium text-charcoal text-sm">
                        {{ $course['name'] }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $g = strtoupper($course['grade'][0] ?? 'A');
                            $chip = match($g) { 'A' => 'grade-A', 'B' => 'grade-B', 'C' => 'grade-C', 'D' => 'grade-D', default => 'grade-F' };
                        @endphp
                        <span class="grade-chip {{ $chip }}">{{ $course['grade'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-body text-sm text-charcoal/70">{{ $course['credits'] }}</td>
                    <td class="px-4 py-3 text-center font-body text-sm font-semibold text-rose">
                        {{ number_format($course['credits'] * 3.7, 1) }}
                    </td>
                    <td class="px-3 py-3 text-right">
                        <button class="edit-btn text-rose hover:text-plum transition-colors p-1 rounded-lg hover:bg-rose/10"
                                onclick="editCourse(1, {{ $ci }})" title="Edit course">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M9.5 1.5l3 3L4 13H1v-3L9.5 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach

                {{-- add course row --}}
                <tr class="add-course-row" onclick="addCourse(1)">
                    <td colspan="5" class="px-6 py-3">
                        <span class="flex items-center gap-2 text-rose/70 font-body text-sm font-medium">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Add Course
                        </span>
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- ── Semester 2 ── --}}
    <div class="semester-card mb-6 slide-up delay-2" id="sem-2">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose/10"
             style="background: linear-gradient(to right, #fdf4f7, #fff)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                     style="background: linear-gradient(135deg,#853953,#612D53)">2</div>
                <div>
                    <h2 class="font-display font-bold text-charcoal text-base leading-tight">Spring 2023</h2>
                    <p class="text-charcoal/40 font-body text-xs">4 courses &middot; 12 credit hours</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="gpa-badge">GPA 3.50</span>
                <button class="btn-delete-sem text-charcoal/30 hover:text-rose transition-colors p-1"
                        onclick="deleteSemester(2)" title="Remove semester">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M2 2l11 11M13 2L2 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-rose/10" style="background:#fafafa">
                    <th class="text-left px-6 py-3">Course</th>
                    <th class="text-center px-4 py-3">Grade</th>
                    <th class="text-center px-4 py-3">Credit Hours</th>
                    <th class="text-center px-4 py-3">Points</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody>
                @php
                $courses2 = [
                    ['name' => 'Data Structures',         'grade' => 'A',  'credits' => 3],
                    ['name' => 'Discrete Mathematics',    'grade' => 'B+', 'credits' => 3],
                    ['name' => 'Statistics & Probability','grade' => 'B',  'credits' => 3],
                    ['name' => 'Technical Writing',       'grade' => 'A-', 'credits' => 3],
                ];
                @endphp

                @foreach($courses2 as $ci => $course)
                <tr class="course-row" id="sem2-course-{{ $ci }}">
                    <td class="px-6 py-3 font-body font-medium text-charcoal text-sm">{{ $course['name'] }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $g = strtoupper($course['grade'][0] ?? 'A'); $chip = match($g) { 'A' => 'grade-A', 'B' => 'grade-B', 'C' => 'grade-C', 'D' => 'grade-D', default => 'grade-F' }; @endphp
                        <span class="grade-chip {{ $chip }}">{{ $course['grade'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-body text-sm text-charcoal/70">{{ $course['credits'] }}</td>
                    <td class="px-4 py-3 text-center font-body text-sm font-semibold text-rose">
                        {{ number_format($course['credits'] * 3.3, 1) }}
                    </td>
                    <td class="px-3 py-3 text-right">
                        <button class="edit-btn text-rose hover:text-plum transition-colors p-1 rounded-lg hover:bg-rose/10"
                                onclick="editCourse(2, {{ $ci }})" title="Edit course">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M9.5 1.5l3 3L4 13H1v-3L9.5 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach

                <tr class="add-course-row" onclick="addCourse(2)">
                    <td colspan="5" class="px-6 py-3">
                        <span class="flex items-center gap-2 text-rose/70 font-body text-sm font-medium">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Add Course
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ── Semester 3 ── --}}
    <div class="semester-card mb-6 slide-up delay-3" id="sem-3">
        <div class="flex items-center justify-between px-6 py-4 border-b border-rose/10"
             style="background: linear-gradient(to right, #fdf4f7, #fff)">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                     style="background: linear-gradient(135deg,#853953,#612D53)">3</div>
                <div>
                    <h2 class="font-display font-bold text-charcoal text-base leading-tight">Fall 2023</h2>
                    <p class="text-charcoal/40 font-body text-xs">2 courses &middot; 6 credit hours</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="gpa-badge">GPA 3.40</span>
                <button class="btn-delete-sem text-charcoal/30 hover:text-rose transition-colors p-1"
                        onclick="deleteSemester(3)" title="Remove semester">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M2 2l11 11M13 2L2 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="border-b border-rose/10" style="background:#fafafa">
                    <th class="text-left px-6 py-3">Course</th>
                    <th class="text-center px-4 py-3">Grade</th>
                    <th class="text-center px-4 py-3">Credit Hours</th>
                    <th class="text-center px-4 py-3">Points</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody>
                @php
                $courses3 = [
                    ['name' => 'Operating Systems',  'grade' => 'B+', 'credits' => 3],
                    ['name' => 'Database Systems',   'grade' => 'A-', 'credits' => 3],
                ];
                @endphp

                @foreach($courses3 as $ci => $course)
                <tr class="course-row" id="sem3-course-{{ $ci }}">
                    <td class="px-6 py-3 font-body font-medium text-charcoal text-sm">{{ $course['name'] }}</td>
                    <td class="px-4 py-3 text-center">
                        @php $g = strtoupper($course['grade'][0] ?? 'A'); $chip = match($g) { 'A' => 'grade-A', 'B' => 'grade-B', 'C' => 'grade-C', 'D' => 'grade-D', default => 'grade-F' }; @endphp
                        <span class="grade-chip {{ $chip }}">{{ $course['grade'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center font-body text-sm text-charcoal/70">{{ $course['credits'] }}</td>
                    <td class="px-4 py-3 text-center font-body text-sm font-semibold text-rose">
                        {{ number_format($course['credits'] * 3.4, 1) }}
                    </td>
                    <td class="px-3 py-3 text-right">
                        <button class="edit-btn text-rose hover:text-plum transition-colors p-1 rounded-lg hover:bg-rose/10"
                                onclick="editCourse(3, {{ $ci }})" title="Edit course">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M9.5 1.5l3 3L4 13H1v-3L9.5 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </td>
                </tr>
                @endforeach

                <tr class="add-course-row" onclick="addCourse(3)">
                    <td colspan="5" class="px-6 py-3">
                        <span class="flex items-center gap-2 text-rose/70 font-body text-sm font-medium">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                                <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Add Course
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</main>

{{-- ════════════════ MODAL (Add/Edit Course) ════════════════ --}}
<div id="modal-overlay"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none!important; background:rgba(44,44,44,0.5); backdrop-filter:blur(4px);">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden"
         style="animation: slideUp 0.3s cubic-bezier(.22,.68,0,1.2) both;">

        <div class="px-6 py-5 border-b border-rose/10"
             style="background: linear-gradient(to right, #fdf4f7, #fff)">
            <h3 class="font-display font-bold text-charcoal text-lg" id="modal-title">Add Course</h3>
            <p class="text-charcoal/40 font-body text-xs mt-0.5">Fill in the course details below</p>
        </div>

        <div class="px-6 py-5 space-y-4">
            <div>
                <label class="block font-body text-xs font-semibold text-charcoal/50 uppercase tracking-widest mb-1.5">Course Name</label>
                <input type="text" id="input-course-name" placeholder="e.g. Data Structures"
                       class="w-full border border-rose/20 rounded-xl px-4 py-2.5 font-body text-sm text-charcoal placeholder-charcoal/30 outline-none focus:border-rose/60 focus:ring-2 focus:ring-rose/10 transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-body text-xs font-semibold text-charcoal/50 uppercase tracking-widest mb-1.5">Grade</label>
                    <select id="input-grade"
                            class="w-full border border-rose/20 rounded-xl px-4 py-2.5 font-body text-sm text-charcoal outline-none focus:border-rose/60 focus:ring-2 focus:ring-rose/10 transition appearance-none bg-white">
                        <option>A+</option><option>A</option><option>A-</option>
                        <option>B+</option><option>B</option><option>B-</option>
                        <option>C+</option><option>C</option><option>C-</option>
                        <option>D</option><option>F</option>
                    </select>
                </div>
                <div>
                    <label class="block font-body text-xs font-semibold text-charcoal/50 uppercase tracking-widest mb-1.5">Credit Hours</label>
                    <input type="number" id="input-credits" placeholder="3" min="1" max="6"
                           class="w-full border border-rose/20 rounded-xl px-4 py-2.5 font-body text-sm text-charcoal placeholder-charcoal/30 outline-none focus:border-rose/60 focus:ring-2 focus:ring-rose/10 transition">
                </div>
            </div>
        </div>

        <div class="px-6 pb-5 flex justify-end gap-3">
            <button onclick="closeModal()"
                    class="px-5 py-2.5 rounded-xl font-body text-sm font-semibold text-charcoal/50 hover:text-charcoal hover:bg-charcoal/5 transition">
                Cancel
            </button>
            <button onclick="saveModal()"
                    class="btn-add-semester" style="padding: 10px 22px; font-size:0.85rem;">
                Save Course
            </button>
        </div>
    </div>
</div>

<script>
    // ── Simple JS stubs — wire to Laravel routes/AJAX as needed ──

    function addSemester() {
        console.log('→ POST /semesters');
        alert('Add Semester clicked — wire to your Laravel controller!');
    }

    function deleteSemester(id) {
        if (confirm('Remove this semester?')) {
            console.log('→ DELETE /semesters/' + id);
            document.getElementById('sem-' + id)?.remove();
        }
    }

    function addCourse(semId) {
        document.getElementById('modal-title').textContent = 'Add Course';
        document.getElementById('input-course-name').value = '';
        document.getElementById('input-grade').value = 'A';
        document.getElementById('input-credits').value = '';
        document.getElementById('modal-overlay').style.display = 'flex';
        window._editingCourse = { sem: semId, course: null };
    }

    function editCourse(semId, courseId) {
        document.getElementById('modal-title').textContent = 'Edit Course';
        document.getElementById('modal-overlay').style.display = 'flex';
        window._editingCourse = { sem: semId, course: courseId };
    }

    function closeModal() {
        document.getElementById('modal-overlay').style.display = 'none!important';
        document.getElementById('modal-overlay').style.setProperty('display','none','important');
    }

    function saveModal() {
        const name    = document.getElementById('input-course-name').value;
        const grade   = document.getElementById('input-grade').value;
        const credits = document.getElementById('input-credits').value;
        const ctx     = window._editingCourse;
        console.log('→ Saving course', { name, grade, credits, ...ctx });
        // wire to: POST /semesters/{sem}/courses  or  PUT /courses/{id}
        closeModal();
    }

    // close modal on overlay click
    document.getElementById('modal-overlay').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>

</body>
</html>