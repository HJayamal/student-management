import { useCallback, useEffect, useState } from "react";
import studentApi from "./studentApi";
import "./StudentApp.css";

function StudentApp() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [student, setStudent] = useState(null);
    const [academic, setAcademic] = useState(null);
    const [loading, setLoading] = useState(false);
    const [checkingSession, setCheckingSession] = useState(true);
    const [error, setError] = useState("");
    const [quizzes, setQuizzes] = useState([]);
    const [loadingQuizzes, setLoadingQuizzes] = useState(false);
    const [quizError, setQuizError] = useState("");
    const [quizNotice, setQuizNotice] = useState("");
    const [activeQuiz, setActiveQuiz] = useState(null);
    const [activeQuestions, setActiveQuestions] = useState([]);
    const [selectedAnswers, setSelectedAnswers] = useState({});
    const [loadingQuizId, setLoadingQuizId] = useState(null);
    const [submittingAttempt, setSubmittingAttempt] = useState(false);
    const [attemptError, setAttemptError] = useState("");
    const [attemptSubmitted, setAttemptSubmitted] = useState(false);
    const [attemptReceipt, setAttemptReceipt] = useState(null);
    const [attemptResult, setAttemptResult] = useState(null);
    const [attemptMessage, setAttemptMessage] = useState("");

    const loadStudentQuizzes = useCallback(async () => {
        if (!localStorage.getItem("student_token")) {
            setQuizzes([]);
            setQuizError("");
            setQuizNotice("");
            return;
        }

        setLoadingQuizzes(true);
        setQuizError("");
        setQuizNotice("");

        try {
            const response = await studentApi.get("/student/quizzes");
            const data = response.data || {};
            const returnedQuizzes = Array.isArray(data.quizzes) ? data.quizzes : [];

            setQuizzes(returnedQuizzes);

            if (returnedQuizzes.length === 0 && data.message && data.message !== "Student quizzes retrieved successfully.") {
                setQuizNotice(data.message);
            }
        } catch (err) {
            setQuizzes([]);
            setQuizError(
                err.response?.data?.message ||
                "Unable to load your quizzes. Please refresh and try again."
            );
        } finally {
            setLoadingQuizzes(false);
        }
    }, []);

    const handleStartQuiz = async (quiz) => {
        if (!quiz?.is_available || loadingQuizId !== null) return;

        setLoadingQuizId(quiz.id);
        setAttemptError("");
        setAttemptMessage("");
        setAttemptReceipt(null);
        setAttemptResult(null);
        setAttemptSubmitted(false);

        try {
            const response = await studentApi.get(`/student/quizzes/${quiz.id}`);
            const data = response.data || {};
            const questions = Array.isArray(data.questions) ? data.questions : [];

            setActiveQuiz(data.quiz || quiz);
            setActiveQuestions(questions);
            setSelectedAnswers({});

            if (questions.length === 0) {
                setAttemptError("This quiz does not have any questions yet. Please ask your teacher to add questions.");
            }
        } catch (err) {
            setQuizError(
                err.response?.data?.message ||
                "Unable to open this quiz. Please refresh and try again."
            );
            setActiveQuiz(null);
            setActiveQuestions([]);
        } finally {
            setLoadingQuizId(null);
        }
    };

    const handleSubmitQuiz = async (event) => {
        event.preventDefault();
        if (!activeQuiz || submittingAttempt || attemptSubmitted) return;

        const unanswered = activeQuestions.filter(
            (question) => !selectedAnswers[question.id]
        );

        if (unanswered.length > 0) {
            setAttemptError(`Please answer all questions before submitting. ${unanswered.length} question(s) remain unanswered.`);
            return;
        }

        setSubmittingAttempt(true);
        setAttemptError("");

        try {
            const response = await studentApi.post(
                `/student/quizzes/${activeQuiz.id}/submit`,
                { answers: selectedAnswers }
            );

            setAttemptReceipt(response.data?.attempt || null);
            setAttemptResult(response.data?.result || null);
            setAttemptMessage(
                response.data?.message || "Your answers were submitted successfully."
            );
            setAttemptSubmitted(true);
        } catch (err) {
            setAttemptError(
                err.response?.data?.message ||
                "Unable to submit your answers. Please try again."
            );
        } finally {
            setSubmittingAttempt(false);
        }
    };

    const handleReturnToQuizList = () => {
        setActiveQuiz(null);
        setActiveQuestions([]);
        setSelectedAnswers({});
        setAttemptError("");
        setAttemptMessage("");
        setAttemptReceipt(null);
        setAttemptResult(null);
        setAttemptSubmitted(false);
        loadStudentQuizzes();
    };

    const formatQuizDate = (value) => {
        if (!value) return "Available now";

        const parsedDate = new Date(String(value).replace(" ", "T"));
        if (Number.isNaN(parsedDate.getTime())) return String(value);

        return parsedDate.toLocaleString([], {
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "numeric",
            minute: "2-digit",
        });
    };

    useEffect(() => {
        let cancelled = false;

        async function loadExistingSession() {
            const token = localStorage.getItem("student_token");
            if (!token) {
                setCheckingSession(false);
                return;
            }

            try {
                const response = await studentApi.get("/student/dashboard");
                if (cancelled) return;

                setStudent(response.data.student);
                setAcademic(response.data.academic || null);
                localStorage.setItem("student_info", JSON.stringify(response.data.student));
                await loadStudentQuizzes();
            } catch (err) {
                if (cancelled) return;
                localStorage.removeItem("student_token");
                localStorage.removeItem("student_info");
                setError(err.response?.data?.message || "Your session has expired. Please sign in again.");
            } finally {
                if (!cancelled) setCheckingSession(false);
            }
        }

        loadExistingSession();
        return () => {
            cancelled = true;
        };
    }, [loadStudentQuizzes]);

    const handleLogin = async (event) => {
        event.preventDefault();
        setError("");
        setLoading(true);

        try {
            const loginResponse = await studentApi.post("/student/login", { email, password });
            localStorage.setItem("student_token", loginResponse.data.token);

            const dashboardResponse = await studentApi.get("/student/dashboard");
            setStudent(dashboardResponse.data.student);
            setAcademic(dashboardResponse.data.academic || null);
            localStorage.setItem("student_info", JSON.stringify(dashboardResponse.data.student));
            await loadStudentQuizzes();
            setPassword("");
        } catch (err) {
            localStorage.removeItem("student_token");
            localStorage.removeItem("student_info");
            setStudent(null);
            setAcademic(null);
            setQuizzes([]);
            setQuizError("");
            setQuizNotice("");
            setActiveQuiz(null);
            setActiveQuestions([]);
            setSelectedAnswers({});
            setAttemptSubmitted(false);
            setAttemptReceipt(null);
            setAttemptError("");
            setError(
                err.response?.data?.message ||
                "Unable to sign in. Check your student account details and try again."
            );
        } finally {
            setLoading(false);
        }
    };

    const handleLogout = async () => {
        try {
            await studentApi.post("/student/logout");
        } catch (err) {
            // Clear the local session even if the network request fails.
            console.error("Student logout request failed:", err);
        } finally {
            localStorage.removeItem("student_token");
            localStorage.removeItem("student_info");
            setStudent(null);
            setAcademic(null);
            setQuizzes([]);
            setQuizError("");
            setQuizNotice("");
            setActiveQuiz(null);
            setActiveQuestions([]);
            setSelectedAnswers({});
            setAttemptSubmitted(false);
            setAttemptReceipt(null);
            setAttemptError("");
            setEmail("");
            setPassword("");
            setError("");
        }
    };

    if (checkingSession) {
        return (
            <main className="student-loading-screen">
                <div className="student-loading-mark">Q</div>
                <p>Loading your student portal...</p>
            </main>
        );
    }

    if (!student) {
        return (
            <main className="student-login-page">
                <section className="student-login-layout">
                    <div className="student-welcome-panel">
                        <div className="student-brand">
                            <span className="student-brand-mark">Q</span>
                            <span>QuizSpace</span>
                        </div>
                        <div className="student-welcome-copy">
                            <span className="student-eyebrow">STUDENT PORTAL</span>
                            <h1>Your learning journey starts here.</h1>
                            <p>Sign in to view your academic profile and access quizzes assigned to your course and batch.</p>
                            <div className="student-feature-list">
                                <span><i>✓</i> Course-based learning</span>
                                <span><i>✓</i> Scheduled quizzes</span>
                                <span><i>✓</i> Simple quiz submissions</span>
                            </div>
                        </div>
                        <p className="student-welcome-footer">Learn. Practice. Progress.</p>
                    </div>

                    <div className="student-login-panel">
                        <div className="student-login-heading">
                            <div className="student-login-icon">↗</div>
                            <span className="student-eyebrow">WELCOME BACK</span>
                            <h2>Student Sign In</h2>
                            <p>Use the email and password registered for your student account.</p>
                        </div>

                        <form onSubmit={handleLogin} className="student-login-form">
                            <label htmlFor="student-email">Email address</label>
                            <input
                                id="student-email"
                                type="email"
                                value={email}
                                onChange={(event) => setEmail(event.target.value)}
                                placeholder="student@example.com"
                                autoComplete="username"
                                required
                            />

                            <label htmlFor="student-password">Password</label>
                            <input
                                id="student-password"
                                type="password"
                                value={password}
                                onChange={(event) => setPassword(event.target.value)}
                                placeholder="Enter your password"
                                autoComplete="current-password"
                                required
                            />

                            {error && <div className="student-login-error" role="alert">{error}</div>}

                            <button type="submit" disabled={loading}>
                                {loading ? "Signing in..." : "Sign in to Student Portal →"}
                            </button>
                        </form>
                        <p className="student-login-note">Access is available to registered student accounts.</p>
                    </div>
                </section>
            </main>
        );
    }

    const initials = (student.name || "Student")
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");

    return (
        <div className="student-dashboard-layout">
            <aside className="student-sidebar">
                <div className="student-brand student-sidebar-brand">
                    <span className="student-brand-mark">Q</span>
                    <span>QuizSpace</span>
                </div>
                <div className="student-sidebar-label">STUDENT WORKSPACE</div>
                <div className="student-nav-item active"><span>▦</span> Dashboard</div>
                <div className="student-sidebar-bottom">
                    <div className="student-sidebar-profile">
                        <div className="student-avatar small">{initials || "S"}</div>
                        <div><strong>{student.name}</strong><span>Student</span></div>
                    </div>
                    <button type="button" className="student-logout-button" onClick={handleLogout}>↪ &nbsp; Sign out</button>
                </div>
            </aside>

            <main className="student-dashboard-main">
                <header className="student-dashboard-header">
                    <div>
                        <span className="student-eyebrow">STUDENT PORTAL</span>
                        <h1>Welcome back, {student.name?.split(" ")[0] || "Student"}!</h1>
                        <p>Your academic details and quiz workspace at a glance.</p>
                    </div>
                    <div className="student-header-profile">
                        <div className="student-avatar">{initials || "S"}</div>
                        <div><strong>{student.name}</strong><span>{student.reg_no || "Student account"}</span></div>
                    </div>
                </header>

                <section className="student-welcome-banner">
                    <div>
                        <span>YOUR CAMPUS LEARNING SPACE</span>
                        <h2>Keep moving forward.</h2>
                        <p>Check your course details and stay ready for your upcoming quizzes.</p>
                    </div>
                    <div className="student-banner-symbol">Q</div>
                </section>

                <section className="student-section-heading">
                    <div><h2>Academic profile</h2><p>Your registered course and admission information.</p></div>
                    <span className={`student-account-status ${(academic?.admission_status || "").toLowerCase() === "active" ? "active" : "pending"}`}>
                        <i /> {academic?.admission_status || "Admission not linked"}
                    </span>
                </section>

                {academic ? (
                    <section className="student-academic-grid">
                        <article className="student-academic-card course-card">
                            <div className="student-card-icon">⌂</div>
                            <span>COURSE</span>
                            <h3>{academic.course || "Not assigned"}</h3>
                            <p>Registered course</p>
                        </article>
                        <article className="student-academic-card degree-card">
                            <div className="student-card-icon">✧</div>
                            <span>DEGREE</span>
                            <h3>{academic.degree || "Not assigned"}</h3>
                            <p>Academic programme</p>
                        </article>
                        <article className="student-academic-card batch-card">
                            <div className="student-card-icon">▦</div>
                            <span>BATCH</span>
                            <h3>{academic.batch || "Not assigned"}</h3>
                            <p>Student intake</p>
                        </article>
                    </section>
                ) : (
                    <section className="student-empty-academic">
                        <h3>Academic details not available</h3>
                        <p>Your registration number is not linked to an admission record yet. Please contact the campus administrator.</p>
                    </section>
                )}

                {activeQuiz ? (
                    <section className="student-quiz-attempt-panel" aria-labelledby="active-quiz-title">
                        <div className="student-quiz-attempt-header">
                            <button
                                type="button"
                                className="student-quiz-back-button"
                                onClick={handleReturnToQuizList}
                            >
                                ← Back to My Quizzes
                            </button>
                            <span className="student-eyebrow">QUIZ ATTEMPT</span>
                            <h2 id="active-quiz-title">{activeQuiz.title || "Quiz"}</h2>
                            <p>{activeQuiz.subject || "General subject"} · {activeQuiz.duration_minutes ?? "—"} minutes</p>
                        </div>

                        {attemptError && (
                            <div className="student-quiz-error" role="alert">
                                {attemptError}
                            </div>
                        )}

                        {attemptSubmitted ? (
                            <div className="student-quiz-submission-success" role="status">
                                <div className="student-quiz-success-icon">✓</div>
                                <h3>Answers submitted successfully</h3>
                                <p>{attemptMessage}</p>
                                {attemptReceipt?.id && (
                                    <p className="student-quiz-receipt">Submission reference: #{attemptReceipt.id}</p>
                                )}
                                {attemptResult ? (
                                    <>
                                        <div className="student-quiz-result-grid" aria-label="Quiz result">
                                            <div className="student-quiz-result-item">
                                                <span>Marks obtained</span>
                                                <strong>{attemptResult.marks_obtained ?? "—"}</strong>
                                            </div>
                                            <div className="student-quiz-result-item">
                                                <span>Total marks</span>
                                                <strong>{attemptResult.total_marks ?? "—"}</strong>
                                            </div>
                                            <div className="student-quiz-result-item">
                                                <span>Percentage</span>
                                                <strong>{attemptResult.percentage != null ? `${Number(attemptResult.percentage).toFixed(2)}%` : "—"}</strong>
                                            </div>
                                        </div>
                                        <p className="student-quiz-marking-note">Your answers have been marked automatically.</p>
                                    </>
                                ) : (
                                    <p className="student-quiz-marking-note">
                                        Your answers were saved, but the server did not return a score. Please check the quiz submission API response.
                                    </p>
                                )}
                                <button
                                    type="button"
                                    className="student-quiz-submit-button"
                                    onClick={handleReturnToQuizList}
                                >
                                    Back to My Quizzes
                                </button>
                            </div>
                        ) : activeQuestions.length === 0 ? (
                            <div className="student-quiz-empty">
                                <h3>No questions available</h3>
                                <p>This quiz has no questions to attempt yet.</p>
                                <button
                                    type="button"
                                    className="student-quiz-back-button"
                                    onClick={handleReturnToQuizList}
                                >
                                    Back to My Quizzes
                                </button>
                            </div>
                        ) : (
                            <form className="student-quiz-attempt-form" onSubmit={handleSubmitQuiz}>
                                <div className="student-quiz-instructions">
                                    <div>
                                        <strong>{activeQuestions.length} question{activeQuestions.length === 1 ? "" : "s"}</strong>
                                        <span>Select one answer for every question, then submit.</span>
                                    </div>
                                    <div className="student-quiz-duration-pill">
                                        <span>Duration</span>
                                        <strong>{activeQuiz.duration_minutes ?? "—"} min</strong>
                                    </div>
                                </div>

                                {activeQuestions.map((question, index) => (
                                    <fieldset className="student-question-card" key={question.id}>
                                        <legend>
                                            <span className="student-question-number">Question {index + 1}</span>
                                            <span className="student-question-marks">{question.marks ?? 1} mark{Number(question.marks ?? 1) === 1 ? "" : "s"}</span>
                                            <span className="student-question-text">{question.question_text}</span>
                                        </legend>
                                        <div className="student-question-options">
                                            {Object.entries(question.options || {})
                                                .filter(([, optionText]) => optionText !== null && optionText !== undefined && String(optionText).trim() !== "")
                                                .map(([optionKey, optionText]) => (
                                                    <label
                                                        className={`student-question-option ${selectedAnswers[question.id] === optionKey ? "selected" : ""}`}
                                                        key={optionKey}
                                                    >
                                                        <input
                                                            type="radio"
                                                            name={`question-${question.id}`}
                                                            value={optionKey}
                                                            checked={selectedAnswers[question.id] === optionKey}
                                                            onChange={() => setSelectedAnswers((current) => ({
                                                                ...current,
                                                                [question.id]: optionKey,
                                                            }))}
                                                            disabled={submittingAttempt}
                                                        />
                                                        <span className="student-question-option-key">{optionKey}</span>
                                                        <span className="student-question-option-text">{optionText}</span>
                                                    </label>
                                                ))}
                                        </div>
                                    </fieldset>
                                ))}

                                <div className="student-quiz-submit-row">
                                    <span>{Object.keys(selectedAnswers).length} of {activeQuestions.length} answered</span>
                                    <button
                                        type="submit"
                                        className="student-quiz-submit-button"
                                        disabled={submittingAttempt}
                                    >
                                        {submittingAttempt ? "Submitting..." : "Submit Answers"}
                                    </button>
                                </div>
                            </form>
                        )}
                    </section>
                ) : (
                    <section className="student-quizzes-section" aria-labelledby="student-quizzes-heading">
                        <div className="student-quizzes-header">
                            <div className="student-quiz-placeholder-icon" aria-hidden="true">▤</div>
                            <div className="student-quizzes-heading-copy">
                                <h2 id="student-quizzes-heading">My quizzes</h2>
                                <p>Quizzes assigned to your registered course, degree and batch.</p>
                            </div>
                            <button
                                type="button"
                                className="student-quizzes-refresh"
                                onClick={loadStudentQuizzes}
                                disabled={loadingQuizzes}
                            >
                                {loadingQuizzes ? "Loading..." : "Refresh"}
                            </button>
                        </div>

                        {loadingQuizzes && (
                            <div className="student-quiz-message" role="status">
                                Loading your quizzes...
                            </div>
                        )}

                        {!loadingQuizzes && quizError && (
                            <div className="student-quiz-error" role="alert">
                                {quizError}
                            </div>
                        )}

                        {!loadingQuizzes && !quizError && quizNotice && (
                            <div className="student-quiz-notice" role="status">
                                {quizNotice}
                            </div>
                        )}

                        {!loadingQuizzes && !quizError && quizzes.length === 0 && (
                            <div className="student-quiz-empty">
                                <div className="student-quiz-empty-icon" aria-hidden="true">✓</div>
                                <h3>No matching quizzes yet</h3>
                                <p>
                                    Published quizzes assigned to your academic group will appear here.
                                    If your teacher has already scheduled one, ask them to check that
                                    its course, degree and batch match your academic profile.
                                </p>
                            </div>
                        )}

                        {quizzes.length > 0 && (
                            <div className="student-quiz-grid">
                                {quizzes.map((quiz) => {
                                    const isAvailable = Boolean(quiz.is_available);

                                    return (
                                        <article className="student-quiz-card" key={quiz.id}>
                                            <div className="student-quiz-card-top">
                                                <div className="student-quiz-title-group">
                                                    <h3>{quiz.title || "Untitled quiz"}</h3>
                                                    <p>{quiz.subject || "General subject"}</p>
                                                </div>
                                                <span className={`student-quiz-status ${isAvailable ? "available" : "upcoming"}`}>
                                                {isAvailable ? "Available" : "Upcoming"}
                                            </span>
                                            </div>

                                            {quiz.description && (
                                                <p className="student-quiz-description">{quiz.description}</p>
                                            )}

                                            <div className="student-quiz-meta">
                                                <div>
                                                    <span>Duration</span>
                                                    <strong>{quiz.duration_minutes ?? "—"} minutes</strong>
                                                </div>
                                                <div>
                                                    <span>Questions</span>
                                                    <strong>{quiz.questions_count ?? 0}</strong>
                                                </div>
                                                <div>
                                                    <span>Batch</span>
                                                    <strong>{quiz.batch || "—"}</strong>
                                                </div>
                                                <div>
                                                    <span>Available date</span>
                                                    <strong>{formatQuizDate(quiz.available_at)}</strong>
                                                </div>
                                            </div>

                                            <p className="student-quiz-academic">
                                                {[quiz.course, quiz.degree].filter(Boolean).join(" · ")}
                                            </p>

                                            {!isAvailable && (
                                                <p className="student-quiz-upcoming-note">
                                                    This quiz will be available at its scheduled time.
                                                </p>
                                            )}

                                            <div className="student-quiz-card-footer">
                                            <span>
                                                {isAvailable
                                                    ? "You can start this quiz when quiz-taking is enabled."
                                                    : "Not available yet"}
                                            </span>
                                                <button
                                                    type="button"
                                                    className="student-quiz-start-button"
                                                    onClick={() => handleStartQuiz(quiz)}
                                                    disabled={!isAvailable || loadingQuizId !== null}
                                                    title={isAvailable ? "Open this quiz" : "This quiz is not available yet"}
                                                >
                                                    {loadingQuizId === quiz.id
                                                        ? "Opening..."
                                                        : isAvailable
                                                            ? "Start Quiz"
                                                            : "Upcoming"}
                                                </button>
                                            </div>
                                        </article>
                                    );
                                })}
                            </div>
                        )}
                    </section>

                )}

                <footer className="student-dashboard-footer">QuizSpace Student Portal · Powered by Laravel</footer>
            </main>
        </div>
    );
}

export default StudentApp;
