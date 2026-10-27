<script setup>
import { computed, ref, watch } from 'vue';
import StudentForm from './StudentForm.vue';

const storageKey = 'classroom-student-records';
const courses = [
    { code: 'BSIT', name: 'Information Technology', color: 'violet' },
    { code: 'BSCS', name: 'Computer Science', color: 'blue' },
    { code: 'BSIS', name: 'Information Systems', color: 'mint' },
    { code: 'BSEMC', name: 'Entertainment & Multimedia Computing', color: 'orange' },
];

const sampleStudents = [
    { id: 'sample-1', studentId: '2024-0108', name: 'Maya Chen', course: 'BSIT', yearLevel: 3, score: 94 },
    { id: 'sample-2', studentId: '2023-0241', name: 'Noah Williams', course: 'BSCS', yearLevel: 4, score: 88 },
    { id: 'sample-3', studentId: '2025-0063', name: 'Sofia Reyes', course: 'BSIS', yearLevel: 2, score: 76 },
    { id: 'sample-4', studentId: '2024-0317', name: 'Ethan Park', course: 'BSIT', yearLevel: 3, score: 68 },
    { id: 'sample-5', studentId: '2025-0149', name: 'Ava Thompson', course: 'BSEMC', yearLevel: 2, score: 91 },
];

const storageNotice = ref('');

function loadStudents() {
    const savedStudents = localStorage.getItem(storageKey);
    if (!savedStudents) return sampleStudents;

    try {
        const parsedStudents = JSON.parse(savedStudents);
        if (!Array.isArray(parsedStudents)) throw new TypeError('Saved student data must be a list.');

        return parsedStudents;
    } catch (error) {
        console.error('Unable to read saved student records.', error);
        storageNotice.value = 'Saved records could not be read. The sample roster is shown instead.';

        return sampleStudents;
    }
}

const students = ref(loadStudents());
const searchQuery = ref('');
const activeFilter = ref('All students');
const isFormVisible = ref(true);
const recentlyAdded = ref('');
const filters = ['All students', 'Passing', 'Needs support'];

const averageScore = computed(() => {
    if (!students.value.length) return 0;

    return Math.round(students.value.reduce((total, student) => total + Number(student.score), 0) / students.value.length);
});

const passingStudents = computed(() => students.value.filter((student) => Number(student.score) >= 75));
const passRate = computed(() => students.value.length ? Math.round((passingStudents.value.length / students.value.length) * 100) : 0);
const courseCount = computed(() => new Set(students.value.map((student) => student.course)).size);

const visibleStudents = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return students.value
        .filter((student) => {
            const matchesQuery = !query || [student.name, student.studentId, student.course]
                .some((value) => value.toLowerCase().includes(query));
            const matchesFilter = activeFilter.value === 'All students'
                || (activeFilter.value === 'Passing' && Number(student.score) >= 75)
                || (activeFilter.value === 'Needs support' && Number(student.score) < 75);

            return matchesQuery && matchesFilter;
        })
        .sort((first, second) => first.name.localeCompare(second.name));
});

const courseBreakdown = computed(() => courses
    .map((course) => ({
        ...course,
        count: students.value.filter((student) => student.course === course.code).length,
    }))
    .filter((course) => course.count > 0));

watch(students, (updatedStudents) => {
    try {
        localStorage.setItem(storageKey, JSON.stringify(updatedStudents));
        storageNotice.value = '';
    } catch (error) {
        console.error('Unable to save student records.', error);
        storageNotice.value = 'Changes could not be saved in this browser. Check your storage settings.';
    }
}, { deep: true });

function addStudent(student) {
    students.value.push(student);
    recentlyAdded.value = student.id;
    searchQuery.value = '';
    activeFilter.value = 'All students';
}

function removeStudent(studentId) {
    students.value = students.value.filter((student) => student.id !== studentId);
    if (recentlyAdded.value === studentId) recentlyAdded.value = '';
}

function courseFor(code) {
    return courses.find((course) => course.code === code) ?? { code, name: code, color: 'blue' };
}

function gradeLabel(score) {
    return Number(score) >= 75 ? 'Passing' : 'Needs support';
}

function gradeClass(score) {
    return Number(score) >= 75 ? 'grade-pass' : 'grade-support';
}
</script>

<template>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="#" aria-label="Classroom home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 36 36" fill="none">
                        <rect x="3" y="3" width="30" height="30" rx="10" fill="currentColor" />
                        <path d="M11 12.5h14M11 18h9M11 23.5h6" stroke="white" stroke-width="2.2" stroke-linecap="round" />
                        <circle cx="24" cy="23.5" r="2" fill="#B9F1D4" />
                    </svg>
                </span>
                <span class="brand-name">classroom<span>.</span></span>
            </a>

            <div class="sidebar-label">WORKSPACE</div>
            <nav class="side-nav" aria-label="Main navigation">
                <a class="nav-link active" href="#overview" aria-current="page">
                    <span class="nav-icon" aria-hidden="true">▦</span> Overview
                </a>
                <a class="nav-link" href="#roster">
                    <span class="nav-icon" aria-hidden="true">♧</span> Students
                    <span class="nav-count">{{ students.length }}</span>
                </a>
                <a class="nav-link" href="#programs">
                    <span class="nav-icon" aria-hidden="true">▤</span> Programs
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="term-card">
                    <span class="term-icon" aria-hidden="true">✦</span>
                    <div>
                        <strong>Academic year</strong>
                        <span>2025 — 2026</span>
                    </div>
                    <span class="term-dot" aria-hidden="true"></span>
                </div>
                <div class="profile">
                    <div class="avatar avatar-teacher">JD</div>
                    <div class="profile-copy">
                        <strong>Jordan Davis</strong>
                        <span>Instructor</span>
                    </div>
                    <button class="icon-button profile-menu" type="button" aria-label="Profile options">···</button>
                </div>
            </div>
        </aside>

        <main id="overview" class="main-content">
            <header class="topbar">
                <div class="breadcrumb">Workspace <span>/</span> <strong>Overview</strong></div>
                <div class="topbar-right">
                    <span class="live-status"><i></i> Live workspace</span>
                    <button class="help-button" type="button" aria-label="Help">?</button>
                </div>
            </header>

            <div class="page-content">
                <section class="welcome-row">
                    <div>
                        <div class="date-line"><span class="date-sparkle" aria-hidden="true">✳</span> YOUR CLASSROOM AT A GLANCE</div>
                        <h1>A little progress, <span>every day.</span></h1>
                        <p class="welcome-copy">Keep track of your students and celebrate how far they’ve come.</p>
                    </div>
                    <button class="button button-primary header-add" type="button" @click="isFormVisible = !isFormVisible">
                        <span aria-hidden="true">{{ isFormVisible ? '−' : '＋' }}</span>
                        {{ isFormVisible ? 'Hide form' : 'Add a student' }}
                    </button>
                </section>

                <section class="stats-grid" aria-label="Class summary">
                    <article class="stat-card">
                        <div class="stat-top"><span class="stat-icon stat-icon-lilac" aria-hidden="true">♧</span><span class="stat-trend">This term</span></div>
                        <div class="stat-value">{{ students.length }}<span class="stat-unit"> students</span></div>
                        <div class="stat-caption">on your class roster</div>
                        <div class="mini-avatars" aria-hidden="true">
                            <span class="mini-avatar mini-lilac">M</span><span class="mini-avatar mini-mint">N</span><span class="mini-avatar mini-peach">S</span><span class="mini-avatar mini-count">+{{ Math.max(students.length - 3, 0) }}</span>
                        </div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-top"><span class="stat-icon stat-icon-mint" aria-hidden="true">↗</span><span class="stat-trend">Class average</span></div>
                        <div class="stat-value">{{ averageScore }}<span class="stat-unit"> / 100</span></div>
                        <div class="stat-caption">overall grade performance</div>
                        <div class="stat-meter"><span :style="{ width: `${averageScore}%` }"></span></div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-top"><span class="stat-icon stat-icon-peach" aria-hidden="true">◉</span><span class="stat-trend">Pass rate</span></div>
                        <div class="stat-value">{{ passRate }}<span class="stat-unit">%</span></div>
                        <div class="stat-caption">{{ passingStudents.length }} students meeting the mark</div>
                        <div class="stat-meter meter-peach"><span :style="{ width: `${passRate}%` }"></span></div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-top"><span class="stat-icon stat-icon-blue" aria-hidden="true">▤</span><span class="stat-trend">Programs</span></div>
                        <div class="stat-value">{{ courseCount }}<span class="stat-unit"> active</span></div>
                        <div class="stat-caption">programs represented</div>
                        <div class="program-dots" aria-hidden="true"><i v-for="course in courseBreakdown" :key="course.code" :class="`dot-${course.color}`"></i></div>
                    </article>
                </section>

                <div v-if="storageNotice" class="storage-notice" role="status">{{ storageNotice }}</div>

                <section class="workspace-grid">
                    <div v-show="isFormVisible" class="form-column">
                        <StudentForm :courses="courses" :existing-students="students" @submit-record="addStudent" />
                        <section id="programs" class="program-card">
                            <div class="program-card-heading">
                                <div><p class="eyebrow">By program</p><h2>Class mix</h2></div>
                                <span class="program-total">{{ students.length }} total</span>
                            </div>
                            <div v-if="courseBreakdown.length" class="program-list">
                                <div v-for="course in courseBreakdown" :key="course.code" class="program-row">
                                    <span class="program-color" :class="`dot-${course.color}`"></span>
                                    <div class="program-info"><strong>{{ course.code }}</strong><span>{{ course.name }}</span></div>
                                    <span class="program-students">{{ course.count }}</span>
                                </div>
                            </div>
                            <p v-else class="program-empty">Add students to see your program mix.</p>
                        </section>
                    </div>

                    <section id="roster" class="roster-card">
                        <div class="roster-heading">
                            <div>
                                <p class="eyebrow">The people behind the progress</p>
                                <h2>Student roster <span class="roster-count">{{ students.length }}</span></h2>
                            </div>
                            <label class="search-box">
                                <span aria-hidden="true">⌕</span>
                                <input v-model="searchQuery" type="search" placeholder="Search students..." aria-label="Search students">
                                <kbd>/</kbd>
                            </label>
                        </div>

                        <div class="roster-toolbar">
                            <div class="filter-tabs" aria-label="Filter students">
                                <button
                                    v-for="filter in filters"
                                    :key="filter"
                                    type="button"
                                    class="filter-tab"
                                    :class="{ 'filter-active': activeFilter === filter }"
                                    :aria-pressed="activeFilter === filter"
                                    @click="activeFilter = filter"
                                >
                                    {{ filter }}
                                    <span v-if="filter === 'All students'" class="filter-count">{{ students.length }}</span>
                                </button>
                            </div>
                            <span class="sorted-label"><span aria-hidden="true">↕</span> Name A–Z</span>
                        </div>

                        <div v-if="visibleStudents.length" class="student-list">
                            <article
                                v-for="(student, index) in visibleStudents"
                                :key="student.id"
                                class="student-row"
                                :class="{ 'student-row-new': recentlyAdded === student.id }"
                                :style="{ '--row-delay': `${index * 45}ms` }"
                            >
                                <div class="avatar" :class="`avatar-${courseFor(student.course).color}`">
                                    {{ student.name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() }}
                                </div>
                                <div class="student-identity">
                                    <div class="student-name-line">
                                        <strong>{{ student.name }}</strong>
                                        <span v-if="recentlyAdded === student.id" class="new-tag">Just added</span>
                                    </div>
                                    <span class="student-id">{{ student.studentId }} <i>·</i> Year {{ student.yearLevel }}</span>
                                </div>
                                <div class="student-program">
                                    <span class="program-pill" :class="`pill-${courseFor(student.course).color}`">{{ courseFor(student.course).code }}</span>
                                    <span class="program-full-name">{{ courseFor(student.course).name }}</span>
                                </div>
                                <div class="student-grade">
                                    <div class="grade-value" :class="gradeClass(student.score)">{{ student.score }}<span> / 100</span></div>
                                    <span class="grade-label" :class="gradeClass(student.score)">{{ gradeLabel(student.score) }}</span>
                                </div>
                                <div class="row-actions">
                                    <button class="icon-button delete-button" type="button" :aria-label="`Remove ${student.name}`" @click="removeStudent(student.id)">×</button>
                                </div>
                            </article>
                        </div>
                        <div v-else class="empty-state">
                            <span class="empty-illustration" aria-hidden="true">⌕</span>
                            <h3>{{ students.length ? 'No students found' : 'Your roster is ready' }}</h3>
                            <p>{{ students.length ? 'Try a different search or filter.' : 'Add your first student to start building your class.' }}</p>
                            <button v-if="!students.length" class="text-button" type="button" @click="isFormVisible = true">Add a student <span aria-hidden="true">→</span></button>
                        </div>

                        <footer class="roster-footer">
                            <span>Showing <strong>{{ visibleStudents.length }}</strong> of <strong>{{ students.length }}</strong> students</span>
                            <span class="footer-update"><i></i> Updates automatically</span>
                        </footer>
                    </section>
                </section>
                <footer class="page-footer">
                    <span>Made for better learning <span aria-hidden="true">✳</span></span>
                    <span>Vue.js Fundamentals <i>·</i> ITE 311</span>
                </footer>
            </div>
        </main>
    </div>
</template>
