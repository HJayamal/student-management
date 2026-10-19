import { useEffect, useState } from "react";
import api from "./api";
import "./App.css";

const emptyQuestion = () => ({
    question_text: "",
    option_a: "",
    option_b: "",
    option_c: "",
    option_d: "",
    correct_option: "A",
    marks: 1,
});

const initialQuiz = () => ({
    title: "",
    grade: "6",
    subject: "",
    description: "",
    duration_minutes: 15,
    available_at: "",
});

// Convert the saved UTC timestamp into the browser's local datetime-local value.
const toLocalDateTimeInput = (value) => {
    if (!value) return "";

    const text = String(value).trim();
    const normalized = text.includes("T") ? text : text.replace(" ", "T");
    const hasTimezone = /(?:Z|[+-]\d{2}:?\d{2})$/i.test(normalized);
    const date = new Date(hasTimezone ? normalized : `${normalized}Z`);

    if (Number.isNaN(date.getTime())) return "";

    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return localDate.toISOString().slice(0, 16);
};

// A published quiz with a future availability time is scheduled, not yet available.
const parseAvailabilityDate = (value) => {
    if (!value) return null;

    // Laravel may return a SQL timestamp without a timezone; treat that as UTC.
    const text = String(value).trim();
    const normalized = text.includes("T") ? text : text.replace(" ", "T");
    const hasTimezone = /(?:Z|[+-]\d{2}:?\d{2})$/i.test(normalized);
    const date = new Date(hasTimezone ? normalized : `${normalized}Z`);

    return Number.isNaN(date.getTime()) ? null : date;
};

const isFutureScheduledQuiz = (quiz) => {
    if (quiz?.status !== "published" || !quiz?.available_at) return false;

    const availableAt = parseAvailabilityDate(quiz.available_at);
    return Boolean(availableAt && availableAt.getTime() > Date.now());
};

const formatAvailabilityDateTime = (value) => {
    const date = parseAvailabilityDate(value);
    if (!date) return "Not set";

    return date.toLocaleString(undefined, {
        dateStyle: "medium",
        timeStyle: "short",
    });
};

const formatSubmissionDateTime = (value) => {
    if (!value) return "Not available";

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);

    return date.toLocaleString(undefined, {
        dateStyle: "medium",
        timeStyle: "short",
    });
};

function App() {
    const [teacher, setTeacher] = useState(() => {
        try {
            return JSON.parse(localStorage.getItem("teacher_info")) || null;
        } catch {
            return null;
        }
    });

    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [dataLoading, setDataLoading] = useState(false);
    const [savingQuiz, setSavingQuiz] = useState(false);
    const [error, setError] = useState("");
    const [notice, setNotice] = useState("");
    const [quizzes, setQuizzes] = useState([]);
    const [results, setResults] = useState([]);
    const [resultsLoading, setResultsLoading] = useState(false);
    const [selectedResult, setSelectedResult] = useState(null);
    const [subjects, setSubjects] = useState([]);
    const [subjectsLoading, setSubjectsLoading] = useState(false);
    const [subjectsError, setSubjectsError] = useState("");
    const [view, setView] = useState("dashboard");
    const [quizSearch, setQuizSearch] = useState("");
    const [quizStatusFilter, setQuizStatusFilter] = useState("all");
    const [, setClockTick] = useState(0);

    // Refresh time-based statuses while the teacher portal remains open.
    useEffect(() => {
        const timer = window.setInterval(() => {
            setClockTick((tick) => tick + 1);
        }, 30000);

        return () => window.clearInterval(timer);
    }, []);

    const [quizForm, setQuizForm] = useState(initialQuiz);
    const [questions, setQuestions] = useState([emptyQuestion()]);
    const [editingQuizId, setEditingQuizId] = useState(null);
    const [previewQuiz, setPreviewQuiz] = useState(null);
    const [previewLoading, setPreviewLoading] = useState(false);
    const [deletingQuizId, setDeletingQuizId] = useState(null);

    useEffect(() => {
        if (!teacher || !localStorage.getItem("teacher_token")) return;

        let cancelled = false;

        async function loadDashboard() {
            setDataLoading(true);
            setError("");

            try {
                const [dashboardResponse, quizzesResponse] = await Promise.all([
                    api.get("/teacher/dashboard"),
                    api.get("/teacher/quizzes"),
                ]);

                if (cancelled) return;

                const currentTeacher = dashboardResponse.data.teacher;

                setTeacher(currentTeacher);
                localStorage.setItem(
                    "teacher_info",
                    JSON.stringify(currentTeacher)
                );
                setQuizzes(quizzesResponse.data.quizzes || []);
            } catch (err) {
                if (cancelled) return;

                setError(
                    err.response?.data?.message ||
                    "Unable to load dashboard. Please check your connection."
                );
            } finally {
                if (!cancelled) setDataLoading(false);
            }
        }

        async function loadSubjects() {
            setSubjectsLoading(true);
            setSubjectsError("");

            try {
                const response = await api.get("/teacher/subjects");

                if (cancelled) return;

                // The API returns a list of subjects from the existing database table.
                setSubjects(Array.isArray(response.data) ? response.data : []);
            } catch (err) {
                if (cancelled) return;

                setSubjects([]);
                setSubjectsError(
                    err.response?.data?.message ||
                    "Unable to load subjects. Please try again."
                );
            } finally {
                if (!cancelled) setSubjectsLoading(false);
            }
        }

        loadDashboard();
        loadSubjects();

        return () => {
            cancelled = true;
        };
    }, [teacher?.id]);

    const handleLogin = async (event) => {
        event.preventDefault();
        setError("");
        setNotice("");
        setLoading(true);

        try {
            const response = await api.post("/teacher/login", {
                email,
                password,
            });

            localStorage.setItem("teacher_token", response.data.token);
            localStorage.setItem(
                "teacher_info",
                JSON.stringify(response.data.teacher)
            );

            setTeacher(response.data.teacher);
            setPassword("");
        } catch (err) {
            setError(
                err.response?.data?.message ||
                "Login failed. Please check your email and password."
            );
        } finally {
            setLoading(false);
        }
    };

    const handlePublish = async (quiz) => {
        if (quiz.status !== "draft") return;

        if (
            !window.confirm(
                `Publish "${quiz.title}" for Grade ${quiz.grade}?`
            )
        ) {
            return;
        }

        setError("");
        setNotice("");

        try {
            const response = await api.post(
                `/teacher/quizzes/${quiz.id}/publish`
            );

            setNotice(response.data.message);

            if (previewQuiz?.id === quiz.id) {
                setPreviewQuiz((current) => current ? {
                    ...current,
                    status: response.data.quiz?.status || "published",
                    published_at: response.data.quiz?.published_at || current.published_at,
                } : current);
            }

            const quizzesResponse = await api.get("/teacher/quizzes");
            setQuizzes(quizzesResponse.data.quizzes || []);
        } catch (err) {
            setError(
                err.response?.data?.message || "Unable to publish this quiz."
            );
        }
    };

    const handlePreviewQuiz = async (quiz) => {
        setError("");
        setNotice("");
        setPreviewLoading(true);

        try {
            const response = await api.get(`/teacher/quizzes/${quiz.id}`);
            setPreviewQuiz(response.data.quiz);
            setView("preview");
        } catch (err) {
            setError(
                err.response?.data?.message || "Unable to load quiz details."
            );
        } finally {
            setPreviewLoading(false);
        }
    };

    const handleEditQuiz = async (quiz) => {
        if (quiz.status !== "draft" && !isFutureScheduledQuiz(quiz)) {
            setError("Only draft or not-yet-available scheduled quizzes can be edited.");
            return;
        }

        setError("");
        setNotice("");

        try {
            const response = await api.get(`/teacher/quizzes/${quiz.id}`);
            const data = response.data.quiz;

            setQuizForm({
                title: data.title,
                grade: String(data.grade),
                subject: data.subject,
                description: data.description || "",
                duration_minutes: String(data.duration_minutes),
                available_at: toLocalDateTimeInput(data.available_at),
            });

            setQuestions(
                (data.questions || []).map((question) => ({
                    question_text: question.question_text,
                    option_a: question.option_a,
                    option_b: question.option_b,
                    option_c: question.option_c,
                    option_d: question.option_d,
                    correct_option: question.correct_option,
                    marks: String(question.marks),
                }))
            );

            setEditingQuizId(data.id);
            setView("create");
        } catch (err) {
            setError(
                err.response?.data?.message || "Unable to load quiz for editing."
            );
        }
    };

    const handleDeleteQuiz = async (quiz) => {
        if (
            !window.confirm(
                `Are you sure you want to delete "${quiz.title}"?`
            )
        ) {
            return;
        }

        setError("");
        setNotice("");
        setDeletingQuizId(quiz.id);

        try {
            const response = await api.delete(`/teacher/quizzes/${quiz.id}`);

            const quizzesResponse = await api.get("/teacher/quizzes");
            setQuizzes(quizzesResponse.data.quizzes || []);
            setNotice(response.data.message);
        } catch (err) {
            setError(
                err.response?.data?.message || "Unable to delete this quiz."
            );
        } finally {
            setDeletingQuizId(null);
        }
    };

    const handleQuizFieldChange = (event) => {
        const { name, value } = event.target;

        setQuizForm((current) => ({
            ...current,
            [name]: value,
        }));

        if (name === "subject") {
            setSubjectsError("");
        }
    };

    const handleQuestionChange = (index, field, value) => {
        setQuestions((current) =>
            current.map((question, questionIndex) =>
                questionIndex === index
                    ? { ...question, [field]: value }
                    : question
            )
        );
    };

    const addQuestion = () => {
        setQuestions((current) => [...current, emptyQuestion()]);
    };

    const removeQuestion = (index) => {
        setQuestions((current) =>
            current.filter((_, questionIndex) => questionIndex !== index)
        );
    };

    const handleCreateQuiz = async (event) => {
        event.preventDefault();
        setError("");
        setNotice("");
        setSavingQuiz(true);

        const isEditing = editingQuizId !== null;

        try {
            const selectedSubject = subjects.find(
                (subject) => subject.subject_name === quizForm.subject
            );

            if (!selectedSubject) {
                throw new Error(
                    "Please select a subject registered by the administrator."
                );
            }

            const payload = {
                ...quizForm,
                subject: selectedSubject.subject_name,
                // Convert the selected local time to UTC before sending it to Laravel.
                available_at: new Date(quizForm.available_at).toISOString(),
                grade: Number(quizForm.grade),
                duration_minutes: Number(quizForm.duration_minutes),
                questions: questions.map((question) => ({
                    ...question,
                    marks: Number(question.marks),
                })),
            };

            const response = isEditing
                ? await api.put(`/teacher/quizzes/${editingQuizId}`, payload)
                : await api.post("/teacher/quizzes", payload);

            setNotice(
                isEditing
                    ? "Quiz schedule updated successfully."
                    : (response.data.message || "Quiz scheduled successfully.")
            );

            setQuizForm(initialQuiz());
            setQuestions([emptyQuestion()]);
            setEditingQuizId(null);

            const quizzesResponse = await api.get("/teacher/quizzes");
            setQuizzes(quizzesResponse.data.quizzes || []);

            setView("quizzes");
        } catch (err) {
            const validationErrors = err.response?.data?.errors;
            const firstValidationError = validationErrors
                ? Object.values(validationErrors).flat()[0]
                : null;

            setError(
                firstValidationError ||
                err.response?.data?.message ||
                err.message ||
                "Unable to save quiz. Please try again."
            );
        } finally {
            setSavingQuiz(false);
        }
    };

    const handleLoadResults = async () => {
        setView("results");
        setError("");
        setNotice("");
        setResultsLoading(true);
        setSelectedResult(null);

        try {
            const response = await api.get("/teacher/results");
            setResults(response.data.submissions || []);
        } catch (err) {
            setResults([]);
            setError(
                err.response?.data?.message ||
                "Unable to load student results. Please try again."
            );
        } finally {
            setResultsLoading(false);
        }
    };

    const handleLogout = async () => {
        try {
            // Revoke the current token on the Laravel backend
            await api.post("/teacher/logout");
        } catch (err) {
            console.error("Teacher logout request failed:", err);
        } finally {
            // Clear local session even if the request fails
            localStorage.removeItem("teacher_token");
            localStorage.removeItem("teacher_info");

            setTeacher(null);
            setQuizzes([]);
            setResults([]);
            setSelectedResult(null);
            setSubjects([]);
            setSubjectsError("");
            setSubjectsLoading(false);
            setView("dashboard");
            setEmail("");
            setPassword("");
            setError("");
            setNotice("");
        }
    };

    const publishedCount = quizzes.filter(
        (quiz) => quiz.status === "published" && !isFutureScheduledQuiz(quiz)
    ).length;

    const draftCount = quizzes.filter(
        (quiz) => quiz.status === "draft"
    ).length;

    const questionCount = quizzes.reduce(
        (total, quiz) => total + Number(quiz.questions_count || 0),
        0
    );

    if (!teacher) {
        return (
            <main className="login-page">
                <section className="login-layout">
                    <div className="welcome-panel">
                        <div className="brand">
                            <div className="brand-icon">Q</div>
                            <span>QuizSpace</span>
                        </div>

                        <div className="welcome-content">
                            <span className="welcome-label">TEACHER PORTAL</span>
                            <h1>Inspire learning.<br />Track progress.</h1>
                            <p>
                                Create quizzes, manage questions, and help students
                                reach their full potential.
                            </p>

                            <div className="feature-list">
                                <div>✓ &nbsp; Create grade-based quizzes</div>
                                <div>✓ &nbsp; Manage questions and subjects</div>
                                <div>✓ &nbsp; Publish quizzes for students</div>
                            </div>
                        </div>

                        <p className="panel-footer">
                            Your classroom, one step ahead.
                        </p>
                    </div>

                    <div className="form-panel">
                        <div className="form-heading">
                            <div className="teacher-icon">♙</div>
                            <p className="eyebrow">WELCOME BACK</p>
                            <h2>Teacher Login</h2>
                            <p>Enter your account details to continue.</p>
                        </div>

                        <form onSubmit={handleLogin}>
                            <div className="form-group">
                                <label htmlFor="email">Email address</label>
                                <input
                                    id="email"
                                    type="email"
                                    value={email}
                                    onChange={(event) => setEmail(event.target.value)}
                                    placeholder="Enter your email"
                                    autoComplete="username"
                                    required
                                />
                            </div>

                            <div className="form-group">
                                <label htmlFor="password">Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    value={password}
                                    onChange={(event) => setPassword(event.target.value)}
                                    placeholder="Enter your password"
                                    autoComplete="current-password"
                                    required
                                />
                            </div>

                            {error && (
                                <div className="error-message" role="alert">
                                    {error}
                                </div>
                            )}

                            <button className="login-button" disabled={loading}>
                                {loading ? "Signing in..." : "Sign in to Dashboard →"}
                            </button>
                        </form>
                    </div>
                </section>
            </main>
        );
    }

    const scheduledQuizzes = quizzes
        .filter(isFutureScheduledQuiz)
        .sort((left, right) => {
            const leftDate = parseAvailabilityDate(left.available_at)?.getTime() ?? 0;
            const rightDate = parseAvailabilityDate(right.available_at)?.getTime() ?? 0;
            return leftDate - rightDate;
        });

    const filteredMyQuizzes = quizzes.filter((quiz) => {
        const searchTerm = quizSearch.trim().toLowerCase();
        const searchableText = [quiz.title, quiz.subject, `grade ${quiz.grade}`]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();
        const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
        const isScheduled = isFutureScheduledQuiz(quiz);

        const matchesStatus =
            quizStatusFilter === "all" ||
            (quizStatusFilter === "scheduled" && isScheduled) ||
            (quizStatusFilter === "available" && quiz.status === "published" && !isScheduled) ||
            (quizStatusFilter === "draft" && quiz.status === "draft");

        return matchesSearch && matchesStatus;
    });

    const displayedQuizzes =
        view === "dashboard"
            ? quizzes.slice(0, 3)
            : view === "scheduled"
                ? scheduledQuizzes
                : filteredMyQuizzes;

    return (
        <div className="dashboard-layout">
            <aside className="sidebar">
                <div className="brand sidebar-brand">
                    <div className="brand-icon">Q</div>
                    <span>QuizSpace</span>
                </div>

                <p className="sidebar-label">WORKSPACE</p>

                <button
                    className={`nav-item ${view === "dashboard" ? "active" : ""}`}
                    onClick={() => {
                        setView("dashboard");
                        setError("");
                        setNotice("");
                    }}
                >
                    <span>▦</span> Dashboard
                </button>

                <button
                    className={`nav-item ${view === "quizzes" || view === "preview" ? "active" : ""}`}
                    onClick={() => {
                        setView("quizzes");
                        setError("");
                        setNotice("");
                    }}
                >
                    <span>☷</span> My Quizzes
                </button>

                <button
                    className={`nav-item ${view === "scheduled" ? "active" : ""}`}
                    onClick={() => {
                        setView("scheduled");
                        setError("");
                        setNotice("");
                    }}
                >
                    <span>◷</span> Scheduled Quizzes
                </button>

                <button
                    className={`nav-item ${view === "results" ? "active" : ""}`}
                    onClick={handleLoadResults}
                    disabled={resultsLoading}
                >
                    <span>▥</span> Student Results
                </button>

                <button
                    className={`nav-item ${view === "create" ? "active" : ""}`}
                    onClick={() => {
                        setQuizForm(initialQuiz());
                        setQuestions([emptyQuestion()]);
                        setEditingQuizId(null);
                        setPreviewQuiz(null);
                        setView("create");
                        setError("");
                        setNotice("");
                    }}
                >
                    <span>＋</span> Create Quiz
                </button>

                <div className="sidebar-bottom">
                    <div className="sidebar-teacher">
                        <div className="avatar">
                            {teacher.name?.charAt(0)?.toUpperCase() || "T"}
                        </div>
                        <div className="teacher-summary">
                            <strong>{teacher.name}</strong>
                            <span>Teacher</span>
                        </div>
                    </div>

                    <button className="logout-button" onClick={handleLogout}>
                        ↪ &nbsp; Sign out
                    </button>
                </div>
            </aside>

            <main className="dashboard-main">
                <header className="dashboard-header">
                    <div>
                        <p className="eyebrow">TEACHER PORTAL</p>
                        <h1>
                            {view === "dashboard"
                                ? "Dashboard"
                                : view === "create"
                                    ? (editingQuizId !== null ? "Edit Quiz" : "Create Quiz")
                                    : view === "preview"
                                        ? "Quiz Preview"
                                        : view === "scheduled"
                                            ? "Scheduled Quizzes"
                                            : view === "results"
                                                ? "Student Results"
                                                : "My Quizzes"}
                        </h1>
                        <p className="header-subtitle">
                            {view === "dashboard"
                                ? "Here is an overview of your quiz activity."
                                : view === "create"
                                    ? (editingQuizId !== null
                                        ? "Update your quiz details and availability time."
                                        : "Build a new quiz and choose when it becomes available.")
                                    : view === "preview"
                                        ? "Review the quiz details and questions before using it."
                                        : view === "scheduled"
                                            ? "Manage quizzes that are waiting for their availability date and time."
                                            : view === "results"
                                                ? "Review student submissions for quizzes you created."
                                                : "Manage your quizzes and publication status."}
                        </p>
                    </div>

                    <div className="header-profile">
                        <div className="avatar">
                            {teacher.name?.charAt(0)?.toUpperCase() || "T"}
                        </div>
                        <div>
                            <strong>{teacher.name}</strong>
                            <span>{teacher.subject || "Teacher"}</span>
                        </div>
                    </div>
                </header>

                {error && (
                    <div className="notice notice-error" role="alert">
                        {error}
                    </div>
                )}

                {notice && (
                    <div className="notice notice-success" role="status">
                        {notice}
                    </div>
                )}

                {(view === "dashboard" || view === "scheduled") && (
                    <section className="stats-grid">
                        <article className="stat-card">
                            <div className="stat-icon purple">▤</div>
                            <p>Total quizzes</p>
                            <strong>{quizzes.length}</strong>
                            <span>Created by you</span>
                        </article>

                        <article className="stat-card">
                            <div className="stat-icon green">✓</div>
                            <p>Published</p>
                            <strong>{publishedCount}</strong>
                            <span>Available now</span>
                        </article>

                        <article className="stat-card">
                            <div className="stat-icon orange">◷</div>
                            <p>Drafts</p>
                            <strong>{draftCount}</strong>
                            <span>Not yet published</span>
                        </article>

                        <article className="stat-card">
                            <div className="stat-icon blue">☷</div>
                            <p>Total questions</p>
                            <strong>{questionCount}</strong>
                            <span>Across your quizzes</span>
                        </article>
                    </section>
                )}

                {view === "create" ? (
                    <section className="content-card quiz-builder">
                        <div className="content-card-header">
                            <div>
                                <h2>{editingQuizId !== null ? "Edit quiz information" : "Quiz information"}</h2>
                                <p>Complete the details and add your questions.</p>
                            </div>
                        </div>

                        <form className="quiz-form" onSubmit={handleCreateQuiz}>
                            <div className="builder-grid">
                                <div className="builder-field builder-field-full">
                                    <label htmlFor="quiz-title">Quiz title *</label>
                                    <input
                                        id="quiz-title"
                                        name="title"
                                        value={quizForm.title}
                                        onChange={handleQuizFieldChange}
                                        placeholder="e.g. Grade 6 Science Quiz"
                                        maxLength={255}
                                        required
                                    />
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-grade">Grade *</label>
                                    <select
                                        id="quiz-grade"
                                        name="grade"
                                        value={quizForm.grade}
                                        onChange={handleQuizFieldChange}
                                        required
                                    >
                                        {Array.from({ length: 13 }, (_, index) => index + 1).map(
                                            (grade) => (
                                                <option key={grade} value={grade}>
                                                    Grade {grade}
                                                </option>
                                            )
                                        )}
                                    </select>
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-subject">Subject *</label>
                                    <select
                                        id="quiz-subject"
                                        name="subject"
                                        value={quizForm.subject}
                                        onChange={handleQuizFieldChange}
                                        required
                                        disabled={subjectsLoading || subjects.length === 0}
                                    >
                                        <option value="">
                                            {subjectsLoading
                                                ? "Loading registered subjects..."
                                                : subjects.length === 0
                                                    ? "No subjects registered by Admin"
                                                    : "Select a subject"}
                                        </option>
                                        {subjects.map((subject) => (
                                            <option key={subject.id} value={subject.subject_name}>
                                                {subject.subject_name}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="subject-input-hint">
                                        Only subjects registered in the Admin Dashboard are available here.
                                    </small>
                                    {subjectsError && (
                                        <small className="subject-field-message" role="alert">
                                            {subjectsError}
                                        </small>
                                    )}
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-duration">Duration (minutes) *</label>
                                    <input
                                        id="quiz-duration"
                                        name="duration_minutes"
                                        type="number"
                                        min="1"
                                        max="300"
                                        value={quizForm.duration_minutes}
                                        onChange={handleQuizFieldChange}
                                        required
                                    />
                                </div>

                                <div className="builder-field builder-field-full">
                                    <label htmlFor="quiz-available-at">Available Date &amp; Time *</label>
                                    <input
                                        id="quiz-available-at"
                                        name="available_at"
                                        type="datetime-local"
                                        value={quizForm.available_at}
                                        onChange={handleQuizFieldChange}
                                        required
                                    />
                                    <small>Students can access this quiz from the selected date and time.</small>
                                </div>

                                <div className="builder-field builder-field-full">
                                    <label htmlFor="quiz-description">Description</label>
                                    <textarea
                                        id="quiz-description"
                                        name="description"
                                        rows="3"
                                        value={quizForm.description}
                                        onChange={handleQuizFieldChange}
                                        placeholder="What will students learn from this quiz?"
                                    />
                                </div>
                            </div>

                            <div className="question-section-header">
                                <div>
                                    <h2>Quiz questions</h2>
                                    <p>Add multiple-choice questions and select the correct answer.</p>
                                </div>
                                <span className="question-count">
                  {questions.length} {questions.length === 1 ? "question" : "questions"}
                </span>
                            </div>

                            {questions.map((question, index) => (
                                <article className="question-card" key={index}>
                                    <div className="question-card-header">
                                        <h3>Question {index + 1}</h3>

                                        {questions.length > 1 && (
                                            <button
                                                type="button"
                                                className="remove-question-button"
                                                onClick={() => removeQuestion(index)}
                                            >
                                                Remove
                                            </button>
                                        )}
                                    </div>

                                    <div className="builder-field">
                                        <label htmlFor={`question-${index}`}>
                                            Question text *
                                        </label>
                                        <textarea
                                            id={`question-${index}`}
                                            rows="2"
                                            value={question.question_text}
                                            onChange={(event) =>
                                                handleQuestionChange(
                                                    index,
                                                    "question_text",
                                                    event.target.value
                                                )
                                            }
                                            placeholder="Enter your question"
                                            required
                                        />
                                    </div>

                                    <div className="builder-grid options-grid">
                                        {["a", "b", "c", "d"].map((option) => (
                                            <div className="builder-field" key={option}>
                                                <label htmlFor={`q${index}-option-${option}`}>
                                                    Option {option.toUpperCase()} *
                                                </label>
                                                <input
                                                    id={`q${index}-option-${option}`}
                                                    value={question[`option_${option}`]}
                                                    onChange={(event) =>
                                                        handleQuestionChange(
                                                            index,
                                                            `option_${option}`,
                                                            event.target.value
                                                        )
                                                    }
                                                    placeholder={`Enter option ${option.toUpperCase()}`}
                                                    required
                                                />
                                            </div>
                                        ))}
                                    </div>

                                    <div className="builder-grid answer-grid">
                                        <div className="builder-field">
                                            <label htmlFor={`correct-${index}`}>
                                                Correct answer *
                                            </label>
                                            <select
                                                id={`correct-${index}`}
                                                value={question.correct_option}
                                                onChange={(event) =>
                                                    handleQuestionChange(
                                                        index,
                                                        "correct_option",
                                                        event.target.value
                                                    )
                                                }
                                                required
                                            >
                                                {["A", "B", "C", "D"].map((option) => (
                                                    <option key={option} value={option}>
                                                        Option {option}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>

                                        <div className="builder-field">
                                            <label htmlFor={`marks-${index}`}>Marks *</label>
                                            <input
                                                id={`marks-${index}`}
                                                type="number"
                                                min="1"
                                                max="100"
                                                value={question.marks}
                                                onChange={(event) =>
                                                    handleQuestionChange(
                                                        index,
                                                        "marks",
                                                        event.target.value
                                                    )
                                                }
                                                required
                                            />
                                        </div>
                                    </div>
                                </article>
                            ))}

                            <button
                                type="button"
                                className="add-question-button"
                                onClick={addQuestion}
                            >
                                ＋ Add another question
                            </button>

                            <div className="form-actions">
                                <button
                                    type="button"
                                    className="cancel-button"
                                    onClick={() => {
                                        setView(editingQuizId !== null ? "quizzes" : "dashboard");
                                        setQuizForm(initialQuiz());
                                        setQuestions([emptyQuestion()]);
                                        setEditingQuizId(null);
                                        setError("");
                                        setNotice("");
                                    }}
                                >
                                    Cancel
                                </button>

                                <button
                                    type="submit"
                                    className="primary-action-button"
                                    disabled={savingQuiz}
                                >
                                    {savingQuiz
                                        ? (editingQuizId !== null ? "Updating schedule..." : "Scheduling quiz...")
                                        : (editingQuizId !== null ? "Update schedule" : "Schedule Quiz")}
                                </button>
                            </div>
                        </form>
                    </section>
                ) : view === "preview" ? (
                    <section className="content-card quiz-preview-card">
                        <div className="content-card-header">
                            <div>
                                <h2>{previewQuiz?.title || "Quiz preview"}</h2>
                                <p>Review the details and multiple-choice questions.</p>
                            </div>
                            <button
                                type="button"
                                className="text-button"
                                onClick={() => {
                                    setView("quizzes");
                                    setPreviewQuiz(null);
                                    setError("");
                                    setNotice("");
                                }}
                            >
                                Back to quizzes
                            </button>
                        </div>

                        {previewLoading ? (
                            <div className="empty-state">Loading quiz preview...</div>
                        ) : !previewQuiz ? (
                            <div className="empty-state">
                                <h3>Quiz details unavailable</h3>
                                <p>Choose a quiz from My Quizzes to preview it.</p>
                            </div>
                        ) : (
                            <div className="quiz-preview-content">
                                <div className="quiz-preview-summary">
                                    <div><span>Grade</span><strong>Grade {previewQuiz.grade}</strong></div>
                                    <div><span>Subject</span><strong>{previewQuiz.subject}</strong></div>
                                    <div><span>Duration</span><strong>{previewQuiz.duration_minutes} minutes</strong></div>
                                    <div><span>Status</span><strong className={`preview-status ${isFutureScheduledQuiz(previewQuiz) ? "is-scheduled" : previewQuiz.status === "published" ? "is-published" : "is-draft"}`}>{isFutureScheduledQuiz(previewQuiz) ? "scheduled" : previewQuiz.status}</strong></div>
                                </div>

                                {previewQuiz.description && (
                                    <p className="quiz-preview-description">{previewQuiz.description}</p>
                                )}

                                <div className="preview-questions-heading">
                                    <h3>Questions</h3>
                                    <span>{previewQuiz.questions?.length || 0} questions</span>
                                </div>

                                {previewQuiz.questions?.length ? (
                                    <div className="preview-question-list">
                                        {previewQuiz.questions.map((question, index) => (
                                            <article className="preview-question" key={question.id || index}>
                                                <div className="preview-question-title">
                                                    <strong>Question {index + 1}</strong>
                                                    <span>{question.marks} {Number(question.marks) === 1 ? "mark" : "marks"}</span>
                                                </div>
                                                <p>{question.question_text}</p>
                                                <div className="preview-options">
                                                    {["A", "B", "C", "D"].map((option) => {
                                                        const optionText = question[`option_${option.toLowerCase()}`];
                                                        const isCorrect = question.correct_option === option;
                                                        return (
                                                            <div
                                                                key={option}
                                                                className={`preview-option ${isCorrect ? "correct-option" : ""}`}
                                                            >
                                                                <span>{option}</span>
                                                                <p>{optionText}</p>
                                                                {isCorrect && <strong className="correct-answer-label">Correct answer</strong>}
                                                            </div>
                                                        );
                                                    })}
                                                </div>
                                            </article>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="empty-state">This quiz has no questions.</div>
                                )}

                                {(previewQuiz.status === "draft" || isFutureScheduledQuiz(previewQuiz)) && (
                                    <div className="preview-actions">
                                        <button
                                            type="button"
                                            className="cancel-button"
                                            onClick={() => handleEditQuiz(previewQuiz)}
                                        >
                                            {isFutureScheduledQuiz(previewQuiz) ? "Edit schedule" : "Edit draft"}
                                        </button>
                                        {previewQuiz.status === "draft" && (
                                            <button
                                                type="button"
                                                className="primary-action-button"
                                                onClick={() => handlePublish(previewQuiz)}
                                            >
                                                Publish quiz
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}
                    </section>
                ) : view === "results" ? (
                    <>
                        <section className="content-card">
                            <div className="content-card-header">
                                <div>
                                    <h2>Student submissions</h2>
                                    <p>View students' submitted answers and submission times. Marks are not calculated yet.</p>
                                </div>
                                <button
                                    type="button"
                                    className="text-button"
                                    onClick={handleLoadResults}
                                    disabled={resultsLoading}
                                >
                                    {resultsLoading ? "Refreshing..." : "Refresh results"}
                                </button>
                            </div>

                            {resultsLoading ? (
                                <div className="empty-state">Loading student results...</div>
                            ) : results.length === 0 ? (
                                <div className="empty-state">
                                    <div className="empty-icon">▥</div>
                                    <h3>No student submissions yet</h3>
                                    <p>When students submit quizzes, their submissions will appear here.</p>
                                </div>
                            ) : (
                                <div className="table-wrapper">
                                    <table className="quiz-table">
                                        <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Quiz</th>
                                            <th>Grade</th>
                                            <th>Subject</th>
                                            <th>Submitted at</th>
                                            <th>Answers</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {results.map((attempt) => {
                                            const answerCount = Object.keys(attempt.answers || {}).length;

                                            return (
                                                <tr key={attempt.id}>
                                                    <td>
                                                        <strong>{attempt.student?.name || `Student #${attempt.student_id}`}</strong>
                                                        <span className="table-subtitle">
                                                                {attempt.student?.email || attempt.student?.reg_no || "Student account"}
                                                            </span>
                                                    </td>
                                                    <td>
                                                        <strong>{attempt.quiz?.title || `Quiz #${attempt.quiz_id}`}</strong>
                                                        <span className="table-subtitle">
                                                                Submitted #{attempt.id}
                                                            </span>
                                                    </td>
                                                    <td>
                                                            <span className="grade-pill">
                                                                {attempt.quiz?.grade ? `Grade ${attempt.quiz.grade}` : "—"}
                                                            </span>
                                                    </td>
                                                    <td>{attempt.quiz?.subject || "—"}</td>
                                                    <td className="scheduled-date-cell">
                                                        {formatSubmissionDateTime(attempt.submitted_at)}
                                                    </td>
                                                    <td>{answerCount}</td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            className="publish-button preview-button"
                                                            onClick={() => setSelectedResult(attempt)}
                                                        >
                                                            View answers
                                                        </button>
                                                    </td>
                                                </tr>
                                            );
                                        })}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </section>

                        {selectedResult && (
                            <section className="content-card quiz-preview-card">
                                <div className="content-card-header">
                                    <div>
                                        <h2>{selectedResult.quiz?.title || "Submitted answers"}</h2>
                                        <p>
                                            {selectedResult.student?.name || `Student #${selectedResult.student_id}`} · Submitted {formatSubmissionDateTime(selectedResult.submitted_at)}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        className="text-button"
                                        onClick={() => setSelectedResult(null)}
                                    >
                                        Close details
                                    </button>
                                </div>

                                {selectedResult.quiz?.questions?.length ? (
                                    <div className="preview-question-list">
                                        {selectedResult.quiz.questions.map((question, index) => {
                                            const selectedOption = String(
                                                selectedResult.answers?.[String(question.id)] || ""
                                            ).toUpperCase();
                                            const optionText = selectedOption
                                                ? question[`option_${selectedOption.toLowerCase()}`]
                                                : "";

                                            return (
                                                <article className="preview-question" key={question.id || index}>
                                                    <div className="preview-question-title">
                                                        <strong>Question {index + 1}</strong>
                                                        <span>Student response</span>
                                                    </div>
                                                    <p>{question.question_text}</p>
                                                    <div className="preview-option">
                                                        <span>{selectedOption || "—"}</span>
                                                        <p>{optionText || (selectedOption ? "Selected option details unavailable" : "No answer recorded")}</p>
                                                    </div>
                                                </article>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="empty-state">
                                        <h3>Question details unavailable</h3>
                                        <p>The submitted answer data is saved, but question details could not be loaded.</p>
                                    </div>
                                )}
                            </section>
                        )}
                    </>
                ) : (
                    <section className="content-card">
                        <div className="content-card-header">
                            <div>
                                <h2>
                                    {view === "dashboard"
                                        ? "Recent quizzes"
                                        : view === "scheduled"
                                            ? "Upcoming scheduled quizzes"
                                            : "All quizzes"}
                                </h2>
                                <p>
                                    {view === "scheduled"
                                        ? "These quizzes become available at the date and time shown below."
                                        : view === "dashboard"
                                            ? "A quick look at your three most recently created quizzes."
                                            : "Search, filter, and manage all of your quizzes."}
                                </p>
                            </div>

                            {view === "dashboard" && quizzes.length > 3 && (
                                <button
                                    className="text-button"
                                    onClick={() => setView("quizzes")}
                                >
                                    View all →
                                </button>
                            )}
                        </div>

                        {view === "quizzes" && (
                            <div className="quiz-filters">
                                <div className="quiz-filter-field quiz-search-field">
                                    <label htmlFor="quiz-search">Search quizzes</label>
                                    <input
                                        id="quiz-search"
                                        type="search"
                                        value={quizSearch}
                                        onChange={(event) => setQuizSearch(event.target.value)}
                                        placeholder="Search by quiz title, subject, or grade..."
                                    />
                                </div>

                                <div className="quiz-filter-field quiz-status-field">
                                    <label htmlFor="quiz-status-filter">Filter by status</label>
                                    <select
                                        id="quiz-status-filter"
                                        value={quizStatusFilter}
                                        onChange={(event) => setQuizStatusFilter(event.target.value)}
                                    >
                                        <option value="all">All statuses</option>
                                        <option value="scheduled">Scheduled</option>
                                        <option value="available">Available</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>

                                <div className="quiz-filter-count" aria-live="polite">
                                    {displayedQuizzes.length} {displayedQuizzes.length === 1 ? "quiz" : "quizzes"}
                                </div>
                            </div>
                        )}

                        {dataLoading ? (
                            <div className="empty-state">Loading your quizzes...</div>
                        ) : displayedQuizzes.length === 0 ? (
                            <div className="empty-state">
                                <div className="empty-icon">
                                    {view === "scheduled" ? "◷" : view === "quizzes" && quizzes.length > 0 ? "⌕" : "☷"}
                                </div>
                                <h3>
                                    {view === "scheduled"
                                        ? "No scheduled quizzes"
                                        : view === "quizzes" && quizzes.length > 0
                                            ? "No matching quizzes"
                                            : "No quizzes yet"}
                                </h3>
                                <p>
                                    {view === "scheduled"
                                        ? "Quizzes with a future availability time will appear here."
                                        : view === "quizzes" && quizzes.length > 0
                                            ? "Try a different search term or status filter."
                                            : "Your created quizzes will appear here."}
                                </p>
                                {view === "quizzes" && quizzes.length > 0 && (
                                    <button
                                        type="button"
                                        className="text-button"
                                        onClick={() => {
                                            setQuizSearch("");
                                            setQuizStatusFilter("all");
                                        }}
                                    >
                                        Clear filters
                                    </button>
                                )}
                            </div>
                        ) : (
                            <div className="table-wrapper">
                                <table className="quiz-table">
                                    <thead>
                                    <tr>
                                        <th>Quiz title</th>
                                        <th>Grade</th>
                                        <th>Subject</th>
                                        {view === "scheduled" && <th>Available from</th>}
                                        <th>Questions</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {displayedQuizzes.map((quiz) => (
                                        <tr key={quiz.id}>
                                            <td>
                                                <strong>{quiz.title}</strong>
                                                <span className="table-subtitle">
                            {quiz.duration_minutes} minutes
                          </span>
                                            </td>
                                            <td>
                          <span className="grade-pill">
                            Grade {quiz.grade}
                          </span>
                                            </td>
                                            <td>{quiz.subject}</td>
                                            {view === "scheduled" && (
                                                <td className="scheduled-date-cell">{formatAvailabilityDateTime(quiz.available_at)}</td>
                                            )}
                                            <td>{quiz.questions_count ?? 0}</td>
                                            <td>
                          <span
                              className={`status-pill ${
                                  isFutureScheduledQuiz(quiz)
                                      ? "scheduled"
                                      : quiz.status === "published"
                                          ? "published"
                                          : "draft"
                              }`}
                          >
                            {isFutureScheduledQuiz(quiz) ? "scheduled" : quiz.status}
                          </span>
                                            </td>
                                            <td>
                                                <div className="quiz-row-actions">
                                                    <button
                                                        type="button"
                                                        className="publish-button preview-button"
                                                        onClick={() => handlePreviewQuiz(quiz)}
                                                        disabled={previewLoading}
                                                    >
                                                        {previewLoading ? "Loading..." : "Preview"}
                                                    </button>

                                                    {quiz.status === "draft" || isFutureScheduledQuiz(quiz) ? (
                                                        <>
                                                            <button
                                                                type="button"
                                                                className="publish-button edit-button"
                                                                onClick={() => handleEditQuiz(quiz)}
                                                            >
                                                                {isFutureScheduledQuiz(quiz) ? "Edit schedule" : "Edit"}
                                                            </button>
                                                            <button
                                                                type="button"
                                                                className="publish-button delete-button"
                                                                onClick={() => handleDeleteQuiz(quiz)}
                                                                disabled={deletingQuizId === quiz.id}
                                                            >
                                                                {deletingQuizId === quiz.id ? "Deleting..." : "Delete"}
                                                            </button>
                                                            {quiz.status === "draft" && (
                                                                <button
                                                                    type="button"
                                                                    className="publish-button"
                                                                    onClick={() => handlePublish(quiz)}
                                                                >
                                                                    Publish
                                                                </button>
                                                            )}
                                                        </>
                                                    ) : (
                                                        <span className="published-label">✓ Available</span>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </section>
                )}

                <footer className="dashboard-footer">
                    QuizSpace Teacher Portal · Powered by Laravel
                </footer>
            </main>
        </div>
    );
}

export default App;
