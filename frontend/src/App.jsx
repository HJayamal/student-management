import { useCallback, useEffect, useState } from "react";
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
    course: "",
    degree: "",
    batch: "",
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

const formatMaterialFileSize = (bytes) => {
    const size = Number(bytes || 0);
    if (!size) return "";
    if (size < 1024 * 1024) return `${(size / 1024).toFixed(0)} KB`;
    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
};

const formatMaterialDateTime = (value) => {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return date.toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
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

    // Lecture materials shared by this teacher.
    const [materials, setMaterials] = useState([]);
    const [materialsLoading, setMaterialsLoading] = useState(false);
    const [materialsError, setMaterialsError] = useState("");
    const [materialTitle, setMaterialTitle] = useState("");
    const [materialDescription, setMaterialDescription] = useState("");
    const [materialLink, setMaterialLink] = useState("");
    const [selectedMaterialFile, setSelectedMaterialFile] = useState(null);
    const [materialFileInputKey, setMaterialFileInputKey] = useState(0);
    const [savingMaterial, setSavingMaterial] = useState(false);
    const [deletingMaterialId, setDeletingMaterialId] = useState(null);

    const [subjects, setSubjects] = useState([]);
    const [subjectsLoading, setSubjectsLoading] = useState(false);
    const [subjectsError, setSubjectsError] = useState("");
    const [academicOptions, setAcademicOptions] = useState([]);
    const [academicOptionsLoading, setAcademicOptionsLoading] = useState(false);
    const [academicOptionsError, setAcademicOptionsError] = useState("");
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

        async function loadAcademicOptions() {
            setAcademicOptionsLoading(true);
            setAcademicOptionsError("");

            try {
                const response = await api.get("/teacher/academic-options");

                if (cancelled) return;

                setAcademicOptions(
                    Array.isArray(response.data?.academic_options)
                        ? response.data.academic_options
                        : []
                );
            } catch (err) {
                if (cancelled) return;

                setAcademicOptions([]);
                setAcademicOptionsError(
                    err.response?.data?.message ||
                    "Unable to load Course, Degree and Batch options."
                );
            } finally {
                if (!cancelled) setAcademicOptionsLoading(false);
            }
        }

        loadDashboard();
        loadSubjects();
        loadAcademicOptions();

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
                `Publish "${quiz.title}" for ${quiz.course || "the selected course"} · ${quiz.degree || "degree"} · Batch ${quiz.batch || "batch"}?`
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
                title: data.title || "",
                course: data.course || "",
                degree: data.degree || "",
                batch: data.batch || "",
                subject: data.subject || "",
                description: data.description || "",
                duration_minutes: String(data.duration_minutes ?? 15),
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

        setQuizForm((current) => {
            const next = { ...current, [name]: value };

            // Keep academic selections linked to a valid academic combination.
            // A course or degree change also clears the selected subject.
            if (name === "course") {
                next.degree = "";
                next.batch = "";
                next.subject = "";
            } else if (name === "degree") {
                next.batch = "";
                next.subject = "";
            }

            return next;
        });

        if (name === "subject") setSubjectsError("");
        if (["course", "degree", "batch"].includes(name)) {
            setAcademicOptionsError("");
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
                (subject) =>
                    subject.subject_name === quizForm.subject &&
                    String(subject.course_name || "").trim() === quizForm.course.trim() &&
                    String(subject.degree_name || "").trim() === quizForm.degree.trim()
            );

            if (!selectedSubject) {
                throw new Error(
                    "Please select a subject assigned to the selected Course and Degree in the Admin Dashboard."
                );
            }

            const hasValidAcademicGroup = academicOptions.some(
                (option) =>
                    option.course === quizForm.course &&
                    option.degree === quizForm.degree &&
                    option.batch === quizForm.batch
            );

            if (!hasValidAcademicGroup) {
                throw new Error(
                    "Please select a Course, Degree and Batch registered in Admissions."
                );
            }

            if (!quizForm.available_at || Number.isNaN(new Date(quizForm.available_at).getTime())) {
                throw new Error("Please select a valid availability date and time.");
            }

            const payload = {
                ...quizForm,
                course: quizForm.course,
                degree: quizForm.degree,
                batch: quizForm.batch,
                subject: selectedSubject.subject_name,
                // Convert the selected local time to UTC before sending it to Laravel.
                available_at: new Date(quizForm.available_at).toISOString(),
                // Temporary compatibility with the current backend's required legacy field.
                // This will be removed when the grade database column is migrated.
                grade: 1,
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

    const loadMaterials = useCallback(async (showSpinner = true) => {
        if (showSpinner) setMaterialsLoading(true);
        setMaterialsError("");

        try {
            const response = await api.get("/teacher/materials");
            setMaterials(Array.isArray(response.data?.materials) ? response.data.materials : []);
        } catch (err) {
            setMaterials([]);
            setMaterialsError(
                err.response?.data?.message ||
                "Unable to load lecture materials. Please try again."
            );
        } finally {
            if (showSpinner) setMaterialsLoading(false);
        }
    }, []);

    useEffect(() => {
        if (view !== "materials" || !teacher) return;
        loadMaterials(true);
    }, [view, teacher?.id, loadMaterials]);

    const handleShareMaterial = async (event) => {
        event.preventDefault();
        setMaterialsError("");
        setError("");

        if (!materialTitle.trim()) {
            setMaterialsError("Enter a title for this lecture material.");
            return;
        }

        if (materialTitle.trim().length > 180) {
            setMaterialsError("The title cannot exceed 180 characters.");
            return;
        }

        if (materialDescription.length > 3000) {
            setMaterialsError("The description cannot exceed 3000 characters.");
            return;
        }

        const hasExternalLink = materialLink.trim() !== "";

        if (!selectedMaterialFile && !hasExternalLink) {
            setMaterialsError("Choose a document or add an online link. You can also provide both.");
            return;
        }

        if (selectedMaterialFile) {
            const allowedExtensions = ["pdf", "doc", "docx", "ppt", "pptx"];
            const extension = selectedMaterialFile.name.split(".").pop()?.toLowerCase();

            if (!allowedExtensions.includes(extension)) {
                setMaterialsError("Only PDF, DOC, DOCX, PPT, and PPTX files are allowed.");
                return;
            }

            if (selectedMaterialFile.size > 25 * 1024 * 1024) {
                setMaterialsError("The selected file is larger than 25 MB.");
                return;
            }
        }

        if (hasExternalLink && !/^https?:\/\//i.test(materialLink.trim())) {
            setMaterialsError("Enter a valid link starting with https:// or http://.");
            return;
        }

        setSavingMaterial(true);

        try {
            let response;

            if (selectedMaterialFile) {
                const formData = new FormData();
                formData.append("title", materialTitle.trim());
                formData.append("description", materialDescription.trim());
                formData.append("file", selectedMaterialFile);
                if (hasExternalLink) formData.append("external_url", materialLink.trim());

                // Match the portal's existing Axios multipart upload configuration.
                // The browser/Axios supplies the multipart boundary.
                response = await api.post("/teacher/materials", formData, {
                    headers: { Accept: "application/json", "Content-Type": "multipart/form-data" },
                });
            } else {
                response = await api.post("/teacher/materials", {
                    title: materialTitle.trim(),
                    description: materialDescription.trim(),
                    external_url: materialLink.trim(),
                }, {
                    headers: { Accept: "application/json" },
                });
            }

            setNotice(response.data?.message || "Lecture material shared with students successfully.");
            setMaterialTitle("");
            setMaterialDescription("");
            setMaterialLink("");
            setSelectedMaterialFile(null);
            setMaterialFileInputKey((current) => current + 1);
            await loadMaterials(false);
        } catch (err) {
            const validationMessage = err.response?.data?.errors
                ? Object.values(err.response.data.errors).flat()[0]
                : null;
            setMaterialsError(
                validationMessage ||
                err.response?.data?.message ||
                err.message ||
                "Unable to share this lecture material. Please try again."
            );
        } finally {
            setSavingMaterial(false);
        }
    };

    const handleDeleteMaterial = async (material) => {
        if (!window.confirm(`Delete "${material.title}"? This cannot be undone.`)) return;

        setMaterialsError("");
        setDeletingMaterialId(material.id);

        try {
            const response = await api.delete(`/teacher/materials/${material.id}`);
            setMaterials((current) => current.filter((item) => item.id !== material.id));
            setNotice(response.data?.message || "Lecture material deleted successfully.");
        } catch (err) {
            setMaterialsError(
                err.response?.data?.message ||
                "Unable to delete this material. Please try again."
            );
        } finally {
            setDeletingMaterialId(null);
        }
    };

    const handleOpenMaterial = (material, resourceType = "file") => {
        const target = resourceType === "link"
            ? material.external_url
            : material.download_url;

        if (!target) {
            setMaterialsError(resourceType === "link"
                ? "This material does not have an external link."
                : "This material does not have a downloadable document.");
            return;
        }

        window.open(target, "_blank", "noopener,noreferrer");
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
            setMaterials([]);
            setMaterialsError("");
            setMaterialTitle("");
            setMaterialDescription("");
            setMaterialLink("");
            setSelectedMaterialFile(null);
            setSubjects([]);
            setSubjectsError("");
            setSubjectsLoading(false);
            setAcademicOptions([]);
            setAcademicOptionsError("");
            setAcademicOptionsLoading(false);
            setView("dashboard");
            setEmail("");
            setPassword("");
            setError("");
            setNotice("");
        }
    };

    const courseOptions = [...new Set(
        academicOptions.map((option) => option.course).filter(Boolean)
    )].sort((a, b) => a.localeCompare(b));

    const degreeOptions = [...new Set(
        academicOptions
            .filter((option) => option.course === quizForm.course)
            .map((option) => option.degree)
            .filter(Boolean)
    )].sort((a, b) => a.localeCompare(b));

    const batchOptions = [...new Set(
        academicOptions
            .filter(
                (option) =>
                    option.course === quizForm.course &&
                    option.degree === quizForm.degree
            )
            .map((option) => option.batch)
            .filter(Boolean)
    )].sort((a, b) => a.localeCompare(b));

    // Only show subjects assigned to the selected Course and Degree.
    const subjectOptions = subjects
        .filter(
            (subject) =>
                String(subject.course_name || "").trim() === quizForm.course.trim() &&
                String(subject.degree_name || "").trim() === quizForm.degree.trim()
        )
        .sort((a, b) => a.subject_name.localeCompare(b.subject_name));

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
                            <div className="brand-icon">S</div>
                            <span>StudentManage</span>
                        </div>

                        <div className="welcome-content">
                            <span className="welcome-label">TEACHER PORTAL</span>
                            <h1>Inspire learning.<br />Track progress.</h1>
                            <p>
                                Create quizzes, manage questions, and help students
                                reach their full potential.
                            </p>

                            <div className="feature-list">
                                <div>✓ &nbsp; Create course-based quizzes</div>
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
        const searchableText = [
            quiz.title,
            quiz.subject,
            quiz.course,
            quiz.degree,
            quiz.batch,
        ]
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
                    <div className="brand-icon">S</div>
                    <span>StudentManage</span>
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
                    className={`nav-item ${view === "materials" ? "active" : ""}`}
                    onClick={() => {
                        setView("materials");
                        setError("");
                        setNotice("");
                        setMaterialsError("");
                    }}
                >
                    <span>▤</span> Lecture Materials
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
                                                : view === "materials"
                                                    ? "Lecture Materials"
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
                                                : view === "materials"
                                                    ? "Upload lecture notes or share learning links with your students."
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

                {view === "materials" ? (
                    <div className="materials-page">
                        <section className="materials-panel">
                            <div className="materials-panel-heading">
                                <div>
                                    <h2>Share learning materials</h2>
                                    <p>Upload lecture slides or share a Google Drive / online resource link.</p>
                                </div>
                                <span className="materials-heading-icon">▤</span>
                            </div>

                            <form className="materials-form" onSubmit={handleShareMaterial}>
                                <div className="materials-field">
                                    <label htmlFor="material-title">Material title *</label>
                                    <input
                                        id="material-title"
                                        type="text"
                                        value={materialTitle}
                                        onChange={(event) => setMaterialTitle(event.target.value)}
                                        placeholder="e.g. Week 01 - Introduction to Computing"
                                        maxLength={180}
                                        required
                                    />
                                </div>

                                <div className="materials-field">
                                    <label htmlFor="material-description">Description <span>(optional)</span></label>
                                    <textarea
                                        id="material-description"
                                        value={materialDescription}
                                        onChange={(event) => setMaterialDescription(event.target.value)}
                                        placeholder="Add a short note for your students"
                                        rows={3}
                                        maxLength={3000}
                                    />
                                </div>

                                <div className="materials-field">
                                    <label htmlFor="material-file">Lecture document <span>(optional)</span></label>
                                    <input
                                        key={materialFileInputKey}
                                        id="material-file"
                                        className="materials-file-input"
                                        type="file"
                                        accept=".pdf,.doc,.docx,.ppt,.pptx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation"
                                        onChange={(event) => {
                                            setSelectedMaterialFile(event.target.files?.[0] || null);
                                            setMaterialsError("");
                                        }}
                                    />
                                    <p className="materials-help-text">PDF, Word (DOC/DOCX), or PowerPoint (PPT/PPTX). Maximum file size: 25 MB.</p>
                                    {selectedMaterialFile && (
                                        <div className="materials-selected-file">
                                            <span className="materials-file-symbol">FILE</span>
                                            <div>
                                                <strong>{selectedMaterialFile.name}</strong>
                                                <span>{formatMaterialFileSize(selectedMaterialFile.size)}</span>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="materials-field">
                                    <label htmlFor="material-link">Google Drive / online link <span>(optional)</span></label>
                                    <input
                                        id="material-link"
                                        type="url"
                                        value={materialLink}
                                        onChange={(event) => setMaterialLink(event.target.value)}
                                        placeholder="https://drive.google.com/..."
                                        autoComplete="url"
                                    />
                                    <p className="materials-help-text">Add a link to the same lesson document or a related resource. Set Google Drive access so your students can view it.</p>
                                </div>

                                {materialsError && (
                                    <div className="materials-feedback materials-feedback-error" role="alert">
                                        {materialsError}
                                    </div>
                                )}

                                <div className="materials-form-actions">
                                    <button type="submit" className="materials-submit-button" disabled={savingMaterial}>
                                        {savingMaterial ? "Sharing..." : "Share with students →"}
                                    </button>
                                </div>
                            </form>
                        </section>

                        <section className="materials-panel">
                            <div className="materials-panel-heading materials-list-heading">
                                <div>
                                    <h2>Shared by me</h2>
                                    <p>Documents and links you have shared with students.</p>
                                </div>
                                <button
                                    type="button"
                                    className="materials-refresh-button"
                                    onClick={() => loadMaterials(true)}
                                    disabled={materialsLoading}
                                >
                                    {materialsLoading ? "Refreshing..." : "↻ Refresh"}
                                </button>
                            </div>

                            {materialsLoading ? (
                                <div className="materials-empty-state">Loading lecture materials...</div>
                            ) : materials.length === 0 ? (
                                <div className="materials-empty-state">
                                    <span className="materials-empty-icon">▤</span>
                                    <h3>No materials shared yet</h3>
                                    <p>Upload your first lecture document or share an online resource link.</p>
                                </div>
                            ) : (
                                <div className="materials-list">
                                    {materials.map((material) => (
                                        <article className="materials-item" key={material.id}>
                                            <div className="materials-item-main">
                                                <div className="materials-item-icon">
                                                    {material.material_type === "link" ? "↗" : "▤"}
                                                </div>
                                                <div className="materials-item-copy">
                                                    <h3>{material.title}</h3>
                                                    <p>{material.description || "No description provided."}</p>
                                                    <div className="materials-item-meta">
                                                        {material.original_filename ? <span>{material.original_filename}</span> : null}
                                                        {material.file_size ? <span>{formatMaterialFileSize(material.file_size)}</span> : null}
                                                        {material.external_url ? <span>Online link attached</span> : null}
                                                        {material.created_at ? <span>{formatMaterialDateTime(material.created_at)}</span> : null}
                                                    </div>
                                                </div>
                                                <div className="materials-type-pills">
                                                    {material.download_url ? <span className="materials-type-pill file">FILE</span> : null}
                                                    {material.external_url ? <span className="materials-type-pill link">LINK</span> : null}
                                                </div>
                                            </div>
                                            <div className="materials-item-actions">
                                                {material.download_url ? (
                                                    <button
                                                        type="button"
                                                        className="materials-open-button"
                                                        onClick={() => handleOpenMaterial(material, "file")}
                                                    >
                                                        Download document ↓
                                                    </button>
                                                ) : null}
                                                {material.external_url ? (
                                                    <button
                                                        type="button"
                                                        className="materials-open-button materials-link-button"
                                                        onClick={() => handleOpenMaterial(material, "link")}
                                                    >
                                                        Open shared link ↗
                                                    </button>
                                                ) : null}
                                                <button
                                                    type="button"
                                                    className="materials-delete-button"
                                                    onClick={() => handleDeleteMaterial(material)}
                                                    disabled={deletingMaterialId === material.id}
                                                >
                                                    {deletingMaterialId === material.id ? "Deleting..." : "Delete"}
                                                </button>
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            )}
                            <p className="materials-footnote">Uploaded files are stored securely by StudentManage. Shared download links expire after 12 hours. External link permissions are managed by the link owner.</p>
                        </section>
                    </div>
                ) : view === "create" ? (
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
                                        placeholder="e.g. Software Engineering Fundamentals Quiz"
                                        maxLength={255}
                                        required
                                    />
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-course">Course *</label>
                                    <select
                                        id="quiz-course"
                                        name="course"
                                        value={quizForm.course}
                                        onChange={handleQuizFieldChange}
                                        required
                                        disabled={academicOptionsLoading || courseOptions.length === 0}
                                    >
                                        <option value="">
                                            {academicOptionsLoading
                                                ? "Loading registered courses..."
                                                : courseOptions.length === 0
                                                    ? "No active admission records found"
                                                    : "Select a course"}
                                        </option>
                                        {courseOptions.map((course) => (
                                            <option key={course} value={course}>{course}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-degree">Degree *</label>
                                    <select
                                        id="quiz-degree"
                                        name="degree"
                                        value={quizForm.degree}
                                        onChange={handleQuizFieldChange}
                                        required
                                        disabled={!quizForm.course || degreeOptions.length === 0}
                                    >
                                        <option value="">Select a degree</option>
                                        {degreeOptions.map((degree) => (
                                            <option key={degree} value={degree}>{degree}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="builder-field">
                                    <label htmlFor="quiz-batch">Batch *</label>
                                    <select
                                        id="quiz-batch"
                                        name="batch"
                                        value={quizForm.batch}
                                        onChange={handleQuizFieldChange}
                                        required
                                        disabled={!quizForm.course || !quizForm.degree || batchOptions.length === 0}
                                    >
                                        <option value="">Select a batch</option>
                                        {batchOptions.map((batch) => (
                                            <option key={batch} value={batch}>{batch}</option>
                                        ))}
                                    </select>
                                </div>

                                {academicOptionsError && (
                                    <div className="builder-field builder-field-full">
                                        <small className="subject-field-message" role="alert">
                                            {academicOptionsError}
                                        </small>
                                    </div>
                                )}

                                <div className="builder-field">
                                    <label htmlFor="quiz-subject">Subject *</label>
                                    <select
                                        id="quiz-subject"
                                        name="subject"
                                        value={quizForm.subject}
                                        onChange={handleQuizFieldChange}
                                        required
                                        disabled={
                                            subjectsLoading ||
                                            !quizForm.course ||
                                            !quizForm.degree ||
                                            subjectOptions.length === 0
                                        }
                                    >
                                        <option value="">
                                            {subjectsLoading
                                                ? "Loading registered subjects..."
                                                : subjectsError
                                                    ? "Unable to load subjects"
                                                    : subjects.length === 0
                                                        ? "No subjects registered by Admin"
                                                        : !quizForm.course
                                                            ? "Select a course first"
                                                            : !quizForm.degree
                                                                ? "Select a degree first"
                                                                : subjectOptions.length === 0
                                                                    ? "No subjects assigned to this Course and Degree"
                                                                    : "Select a subject"}
                                        </option>
                                        {subjectOptions.map((subject) => (
                                            <option key={subject.id} value={subject.subject_name}>
                                                {subject.subject_name}
                                            </option>
                                        ))}
                                    </select>
                                    <small className="subject-input-hint">
                                        Subjects are filtered by the selected Course and Degree. Register or assign subjects in the Admin Dashboard if the list is empty.
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
                                    <div><span>Course</span><strong>{previewQuiz.course || "Not set"}</strong></div>
                                    <div><span>Degree / Batch</span><strong>{previewQuiz.degree || "Not set"} · {previewQuiz.batch || "Not set"}</strong></div>
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
                                            <th>Academic group</th>
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
                                                        <strong>{attempt.quiz?.course || "—"}</strong>
                                                        <span className="table-subtitle">
                                                            {[attempt.quiz?.degree, attempt.quiz?.batch]
                                                                .filter(Boolean)
                                                                .join(" · ") || "Academic group not set"}
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
                                        placeholder="Search by title, subject, course, degree, or batch..."
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
                                        <th>Academic group</th>
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
                                                <strong>{quiz.course || "Course not set"}</strong>
                                                <span className="table-subtitle">
                                                    {[quiz.degree, quiz.batch].filter(Boolean).join(" · ") || "Academic group not set"}
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
                    StudentManage Teacher Portal · Powered by Laravel
                </footer>
            </main>
        </div>
    );
}

export default App;
