<script setup>
import { reactive, ref } from 'vue';

const props = defineProps({
    courses: {
        type: Array,
        required: true,
    },
    existingStudents: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['submit-record']);
const form = reactive({
    studentId: '',
    name: '',
    course: '',
    yearLevel: '',
    score: '',
});
const errors = ref({});

function validateForm() {
    const nextErrors = {};
    const studentId = form.studentId.trim();

    if (!studentId) {
        nextErrors.studentId = 'Enter a student ID.';
    } else if (!/^[a-zA-Z0-9-]{3,12}$/.test(studentId)) {
        nextErrors.studentId = 'Use 3–12 letters, numbers, or hyphens.';
    } else if (props.existingStudents.some((student) => student.studentId.toLowerCase() === studentId.toLowerCase())) {
        nextErrors.studentId = 'That student ID is already on the roster.';
    }

    if (!form.name.trim()) nextErrors.name = 'Enter the student’s full name.';
    if (!form.course) nextErrors.course = 'Choose a program.';
    if (!form.yearLevel) nextErrors.yearLevel = 'Choose a year level.';

    const score = Number(form.score);
    if (form.score === '' || !Number.isFinite(score) || score < 0 || score > 100) {
        nextErrors.score = 'Enter a grade from 0 to 100.';
    }

    errors.value = nextErrors;

    return Object.keys(nextErrors).length === 0;
}

function submitForm() {
    if (!validateForm()) return;

    emit('submit-record', {
        id: `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`,
        studentId: form.studentId.trim(),
        name: form.name.trim(),
        course: form.course,
        yearLevel: form.yearLevel,
        score: Number(form.score),
    });

    Object.assign(form, {
        studentId: '',
        name: '',
        course: '',
        yearLevel: '',
        score: '',
    });
    errors.value = {};
}
</script>

<template>
    <form class="form-card" novalidate @submit.prevent="submitForm">
        <div class="card-heading">
            <div>
                <p class="eyebrow">New record</p>
                <h2>Add a student</h2>
            </div>
            <span class="heading-icon" aria-hidden="true">＋</span>
        </div>

        <label class="field">
            <span>Student ID</span>
            <input
                v-model.trim="form.studentId"
                autocomplete="off"
                placeholder="e.g. 2024-0182"
                aria-describedby="student-id-error"
                :aria-invalid="Boolean(errors.studentId)"
            >
            <small v-if="errors.studentId" id="student-id-error" class="field-error">{{ errors.studentId }}</small>
        </label>

        <label class="field">
            <span>Full name</span>
            <input
                v-model.trim="form.name"
                autocomplete="name"
                placeholder="e.g. Alex Morgan"
                aria-describedby="student-name-error"
                :aria-invalid="Boolean(errors.name)"
            >
            <small v-if="errors.name" id="student-name-error" class="field-error">{{ errors.name }}</small>
        </label>

        <div class="field-row">
            <label class="field">
                <span>Program</span>
                <select v-model="form.course" :aria-invalid="Boolean(errors.course)" aria-describedby="course-error">
                    <option disabled value="">Select program</option>
                    <option v-for="course in courses" :key="course.code" :value="course.code">
                        {{ course.code }}
                    </option>
                </select>
                <small v-if="errors.course" id="course-error" class="field-error">{{ errors.course }}</small>
            </label>

            <label class="field">
                <span>Year level</span>
                <select v-model="form.yearLevel" :aria-invalid="Boolean(errors.yearLevel)" aria-describedby="year-error">
                    <option disabled value="">Year</option>
                    <option v-for="year in [1, 2, 3, 4]" :key="year" :value="year">
                        Year {{ year }}
                    </option>
                </select>
                <small v-if="errors.yearLevel" id="year-error" class="field-error">{{ errors.yearLevel }}</small>
            </label>
        </div>

        <label class="field score-field">
            <span>Current grade <span class="muted-label">/ 100</span></span>
            <div class="score-input">
                <input
                    v-model="form.score"
                    type="number"
                    min="0"
                    max="100"
                    step="0.1"
                    placeholder="85"
                    aria-describedby="score-error"
                    :aria-invalid="Boolean(errors.score)"
                >
                <span>pts</span>
            </div>
            <small v-if="errors.score" id="score-error" class="field-error">{{ errors.score }}</small>
        </label>

        <p class="form-hint"><span aria-hidden="true">✦</span> Passing grade is 75 points or above.</p>
        <button class="button button-primary submit-button" type="submit">
            <span aria-hidden="true">＋</span> Add to roster
        </button>
    </form>
</template>
