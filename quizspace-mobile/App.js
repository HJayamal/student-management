import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
    ActivityIndicator,
    Alert,
    KeyboardAvoidingView,
    Platform,
    Pressable,
    ScrollView,
    StatusBar,
    StyleSheet,
    Text,
    TextInput,
    View,
} from 'react-native';
import * as SecureStore from 'expo-secure-store';
import { createApi, getApiError, normalizeApiBaseUrl } from './src/api';
import { formatDate, formatDuration, makeLocalDateTime } from './src/format';
import StudentNotificationsScreen from './src/StudentNotificationsScreen';

const COLORS = {
    ink: '#18233F',
    muted: '#73809A',
    line: '#E5EAF4',
    background: '#F5F7FC',
    white: '#FFFFFF',
    blue: '#5969F6',
    purple: '#7857E8',
    green: '#16845B',
    greenBg: '#E5F8EF',
    red: '#B42332',
    redBg: '#FFF1F2',
    amber: '#95600A',
    amberBg: '#FFF5D9',
    navy: '#191F3F',
};

const KEYS = {
    base: 'quizspace_api_base',
    token: 'quizspace_auth_token',
    role: 'quizspace_role',
    user: 'quizspace_user',
};

const EMPTY_QUESTION = () => ({
    question_text: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct_option: 'A',
    marks: '1',
});

function messageFrom(error) {
    const data = error?.response?.data;
    if (data?.errors) {
        const first = Object.values(data.errors).flat()[0];
        if (first) return String(first);
    }
    return getApiError(error);
}

function Field({ label, value, onChangeText, placeholder, secureTextEntry, keyboardType, multiline, editable = true, helperText, autoCapitalize = 'none' }) {
    return (
        <View style={styles.fieldWrap}>
            {!!label && <Text style={styles.fieldLabel}>{label}</Text>}
            <TextInput
                value={value}
                onChangeText={onChangeText}
                placeholder={placeholder}
                placeholderTextColor="#9AA5B8"
                secureTextEntry={secureTextEntry}
                keyboardType={keyboardType || 'default'}
                multiline={multiline}
                editable={editable}
                autoCapitalize={autoCapitalize}
                autoCorrect={false}
                style={[styles.input, multiline && styles.multilineInput, !editable && styles.inputDisabled]}
            />
            {!!helperText && <Text style={styles.helperText}>{helperText}</Text>}
        </View>
    );
}

function PrimaryButton({ title, onPress, disabled, variant = 'primary', small = false, loading = false }) {
    const buttonStyle = variant === 'secondary'
        ? styles.buttonSecondary
        : variant === 'danger'
            ? styles.buttonDanger
            : variant === 'subtle'
                ? styles.buttonSubtle
                : styles.buttonPrimary;
    const textStyle = variant === 'secondary'
        ? styles.buttonSecondaryText
        : variant === 'danger'
            ? styles.buttonDangerText
            : variant === 'subtle'
                ? styles.buttonSubtleText
                : styles.buttonPrimaryText;
    return (
        <Pressable
            accessibilityRole="button"
            onPress={onPress}
            disabled={disabled || loading}
            style={({ pressed }) => [buttonStyle, small && styles.buttonSmall, (disabled || loading) && styles.buttonDisabled, pressed && !(disabled || loading) && styles.pressed]}
        >
            {loading ? <ActivityIndicator color={variant === 'primary' ? '#FFFFFF' : COLORS.blue} size="small" /> : <Text style={textStyle}>{title}</Text>}
        </Pressable>
    );
}

function Notice({ text, type = 'error' }) {
    if (!text) return null;
    const noticeStyle = type === 'success' ? styles.noticeSuccess : type === 'warning' ? styles.noticeWarning : styles.noticeError;
    return <View style={[styles.notice, noticeStyle]}><Text style={styles.noticeText}>{text}</Text></View>;
}

function SectionTitle({ title, subtitle, action }) {
    return (
        <View style={styles.sectionTitleRow}>
            <View style={styles.sectionTitleCopy}>
                <Text style={styles.sectionTitle}>{title}</Text>
                {!!subtitle && <Text style={styles.sectionSubtitle}>{subtitle}</Text>}
            </View>
            {action}
        </View>
    );
}

function StatCard({ label, value, accent = COLORS.blue, symbol = '•' }) {
    return (
        <View style={styles.statCard}>
            <View style={[styles.statSymbol, { backgroundColor: `${accent}18` }]}><Text style={[styles.statSymbolText, { color: accent }]}>{symbol}</Text></View>
            <Text style={styles.statValue}>{value}</Text>
            <Text style={styles.statLabel}>{label}</Text>
        </View>
    );
}

function QuizCard({ quiz, onStart, starting }) {
    const available = Boolean(quiz.is_available ?? (quiz.status === 'published'));
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{quiz.title || 'Untitled quiz'}</Text>
                    <Text style={styles.cardSubtitle}>{quiz.subject || 'General subject'}</Text>
                </View>
                <View style={[styles.statusPill, available ? styles.statusAvailable : styles.statusUpcoming]}>
                    <Text style={[styles.statusText, available ? styles.statusAvailableText : styles.statusUpcomingText]}>{available ? (quiz.status === 'draft' ? 'Draft' : 'Available') : 'Upcoming'}</Text>
                </View>
            </View>
            {!!quiz.description && <Text style={styles.cardDescription}>{quiz.description}</Text>}
            <View style={styles.metaGrid}>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Duration</Text><Text style={styles.metaValue}>{quiz.duration_minutes ?? '—'} minutes</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Questions</Text><Text style={styles.metaValue}>{quiz.questions_count ?? quiz.questions?.length ?? '—'}</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Course</Text><Text style={styles.metaValue}>{quiz.course || '—'}</Text></View>
                <View style={styles.metaCell}><Text style={styles.metaLabel}>Batch</Text><Text style={styles.metaValue}>{quiz.batch || '—'}</Text></View>
            </View>
            {!!quiz.available_at && <Text style={styles.dateCaption}>Scheduled: {formatDate(quiz.available_at)}</Text>}
            {!!onStart && (
                <PrimaryButton title={starting ? 'Opening quiz…' : available ? 'Start / Resume Quiz' : 'Not available yet'} onPress={() => onStart(quiz)} disabled={!available || starting} loading={starting} />
            )}
        </View>
    );
}

function StudentResultCard({ item }) {
    const percent = Number(item.percentage || 0);
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{item.quiz_title || item.quiz?.title || 'Quiz result'}</Text>
                    <Text style={styles.cardSubtitle}>{item.subject || item.quiz?.subject || 'General subject'}</Text>
                </View>
                <View style={[styles.scorePill, percent >= 50 ? styles.scoreGood : styles.scoreLow]}><Text style={[styles.scorePillText, percent >= 50 ? styles.scoreGoodText : styles.scoreLowText]}>{percent.toFixed(2)}%</Text></View>
            </View>
            <View style={styles.resultMarksRow}>
                <View><Text style={styles.metaLabel}>Marks</Text><Text style={styles.resultMarks}>{item.marks_obtained ?? 0} / {item.total_marks ?? 0}</Text></View>
                <View><Text style={styles.metaLabel}>Attempt</Text><Text style={styles.metaValue}>#{item.attempt_id ?? item.id ?? '—'}</Text></View>
            </View>
            <Text style={styles.dateCaption}>Submitted: {formatDate(item.submitted_at)}</Text>
        </View>
    );
}

function TeacherSubmissionCard({ item }) {
    const student = item.student || {};
    const quiz = item.quiz || {};
    const answers = item.answers && typeof item.answers === 'object' ? Object.entries(item.answers) : [];
    return (
        <View style={styles.card}>
            <View style={styles.cardTopRow}>
                <View style={styles.flexOne}>
                    <Text style={styles.cardTitle}>{student.name || 'Student'} </Text>
                    <Text style={styles.cardSubtitle}>{quiz.title || 'Quiz submission'}</Text>
                </View>
                {item.percentage !== undefined && item.percentage !== null && (
                    <View style={[styles.scorePill, Number(item.percentage) >= 50 ? styles.scoreGood : styles.scoreLow]}>
                        <Text style={[styles.scorePillText, Number(item.percentage) >= 50 ? styles.scoreGoodText : styles.scoreLowText]}>{Number(item.percentage).toFixed(2)}%</Text>
                    </View>
                )}
            </View>
            <Text style={styles.metaValue}>{student.reg_no || student.email || 'Student account'}</Text>
            <View style={styles.resultMarksRow}>
                <View><Text style={styles.metaLabel}>Marks</Text><Text style={styles.resultMarks}>{item.marks_obtained ?? 'Not provided'}{item.total_marks !== undefined && item.total_marks !== null ? ` / ${item.total_marks}` : ''}</Text></View>
                <View><Text style={styles.metaLabel}>Answers saved</Text><Text style={styles.metaValue}>{answers.length}</Text></View>
            </View>
            <Text style={styles.dateCaption}>Submitted: {formatDate(item.submitted_at)}</Text>
        </View>
    );
}

export default function App() {
    const [booting, setBooting] = useState(true);
    const [baseInput, setBaseInput] = useState('');
    const [apiBase, setApiBase] = useState('');
    const [loginRole, setLoginRole] = useState('student');
    const [role, setRole] = useState(null);
    const [token, setToken] = useState(null);
    const [profile, setProfile] = useState(null);
    const [academic, setAcademic] = useState(null);
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loginBusy, setLoginBusy] = useState(false);
    const [loadingData, setLoadingData] = useState(false);
    const [error, setError] = useState('');
    const [notice, setNotice] = useState('');
    const [screen, setScreen] = useState('home');
    const [quizzes, setQuizzes] = useState([]);
    const [results, setResults] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [startingQuizId, setStartingQuizId] = useState(null);

    const [activeQuiz, setActiveQuiz] = useState(null);
    const [attemptSession, setAttemptSession] = useState(null);
    const [selectedAnswers, setSelectedAnswers] = useState({});
    const [deadlineMs, setDeadlineMs] = useState(null);
    const [serverOffsetMs, setServerOffsetMs] = useState(0);
    const [remainingSeconds, setRemainingSeconds] = useState(null);
    const [submittingAttempt, setSubmittingAttempt] = useState(false);
    const [attemptSubmitted, setAttemptSubmitted] = useState(false);
    const [attemptResult, setAttemptResult] = useState(null);
    const [attemptError, setAttemptError] = useState('');
    const [saveWarning, setSaveWarning] = useState('');
    const answerSaveChain = useRef(Promise.resolve());
    const submitQuizRef = useRef(null);
    const timeoutTriggeredRef = useRef(false);
    const submittingRef = useRef(false);

    const [quizForm, setQuizForm] = useState(() => ({
        title: '', grade: '13', course: '', degree: '', batch: '', subject: '',
        description: '', duration_minutes: '20', available_at: makeLocalDateTime(),
        questions: [EMPTY_QUESTION()],
    }));
    const [creatingQuiz, setCreatingQuiz] = useState(false);

    const api = useMemo(() => {
        if (!apiBase) return null;
        try { return createApi(apiBase, token); } catch { return null; }
    }, [apiBase, token]);

    useEffect(() => {
        let alive = true;
        async function restoreSession() {
            try {
                const [savedBase, savedToken, savedRole, savedUser] = await Promise.all([
                    SecureStore.getItemAsync(KEYS.base),
                    SecureStore.getItemAsync(KEYS.token),
                    SecureStore.getItemAsync(KEYS.role),
                    SecureStore.getItemAsync(KEYS.user),
                ]);
                if (!alive) return;
                if (savedBase) {
                    setApiBase(savedBase);
                    setBaseInput(savedBase);
                }
                if (savedToken && savedRole && savedUser) {
                    setToken(savedToken);
                    setRole(savedRole);
                    setLoginRole(savedRole);
                    setProfile(JSON.parse(savedUser));
                }
            } catch {
                // A corrupted secure-store entry should not prevent the app from opening.
            } finally {
                if (alive) setBooting(false);
            }
        }
        void restoreSession();
        return () => { alive = false; };
    }, []);

    const refreshData = useCallback(async (showSpinner = true) => {
        if (!api || !role || !token) return;
        if (showSpinner) setLoadingData(true);
        setError('');
        try {
            if (role === 'student') {
                const [dashboardResponse, quizzesResponse, resultsResponse] = await Promise.all([
                    api.get('/student/dashboard'),
                    api.get('/student/quizzes'),
                    api.get('/student/results'),
                ]);
                const dashboard = dashboardResponse.data || {};
                setProfile(dashboard.student || profile);
                setAcademic(dashboard.academic || null);
                setQuizzes(Array.isArray(quizzesResponse.data?.quizzes) ? quizzesResponse.data.quizzes : []);
                setResults(Array.isArray(resultsResponse.data?.results) ? resultsResponse.data.results : []);

                // Load the unread badge count without blocking the main dashboard.
                try {
                    const notificationCountResponse = await api.get('/student/notifications/unread-count');
                    setUnreadCount(Number(notificationCountResponse.data?.unread_count || 0));
                } catch {
                    // Notification count is optional; keep the rest of the student app usable.
                }

                if (dashboard.student) {
                    await SecureStore.setItemAsync(KEYS.user, JSON.stringify(dashboard.student));
                }
            } else {
                const [dashboardResponse, quizzesResponse, resultsResponse] = await Promise.all([
                    api.get('/teacher/dashboard'),
                    api.get('/teacher/quizzes'),
                    api.get('/teacher/results'),
                ]);
                const dashboard = dashboardResponse.data || {};
                setProfile(dashboard.teacher || profile);
                setQuizzes(Array.isArray(quizzesResponse.data?.quizzes) ? quizzesResponse.data.quizzes : []);
                setResults(Array.isArray(resultsResponse.data?.submissions) ? resultsResponse.data.submissions : []);
                if (dashboard.teacher) {
                    await SecureStore.setItemAsync(KEYS.user, JSON.stringify(dashboard.teacher));
                }
            }
        } catch (fetchError) {
            setError(messageFrom(fetchError));
        } finally {
            if (showSpinner) setLoadingData(false);
        }
    }, [api, role, token]);

    useEffect(() => {
        if (booting || !token || !role || !apiBase) return;
        void refreshData(true);
    }, [booting, token, role, apiBase, refreshData]);

    // In-app notifications are loaded from Laravel when the student opens Alerts.

    const handleLogin = async () => {
        setError('');
        setNotice('');
        let normalizedBase;
        try {
            normalizedBase = normalizeApiBaseUrl(baseInput);
        } catch (baseError) {
            setError(baseError.message);
            return;
        }
        if (!email.trim() || !password) {
            setError('Enter your email address and password.');
            return;
        }
        setLoginBusy(true);
        try {
            const response = await createApi(normalizedBase).post(`/${loginRole}/login`, {
                email: email.trim(),
                password,
            });
            const body = response.data || {};
            const newToken = body.token;
            const newProfile = body[loginRole] || body.student || body.teacher;
            if (!newToken || !newProfile) throw new Error('The server did not return a valid login session.');
            await Promise.all([
                SecureStore.setItemAsync(KEYS.base, normalizedBase),
                SecureStore.setItemAsync(KEYS.token, newToken),
                SecureStore.setItemAsync(KEYS.role, loginRole),
                SecureStore.setItemAsync(KEYS.user, JSON.stringify(newProfile)),
            ]);
            setApiBase(normalizedBase);
            setBaseInput(normalizedBase);
            setRole(loginRole);
            setToken(newToken);
            setProfile(newProfile);
            setPassword('');
            setScreen('home');
            setNotice('Signed in successfully.');
        } catch (loginError) {
            setError(messageFrom(loginError));
        } finally {
            setLoginBusy(false);
        }
    };

    const handleLogout = async () => {
        try {
            if (api && role) await api.post(`/${role}/logout`);
        } catch {
            // Remove local credentials even if the server is temporarily unavailable.
        }
        await Promise.all([KEYS.token, KEYS.role, KEYS.user].map((key) => SecureStore.deleteItemAsync(key).catch(() => {})));
        setToken(null);
        setRole(null);
        setProfile(null);
        setAcademic(null);
        setQuizzes([]);
        setResults([]);
        setUnreadCount(0);
        setActiveQuiz(null);
        setAttemptSession(null);
        setDeadlineMs(null);
        setAttemptResult(null);
        setScreen('home');
        setError('');
        setNotice('You have signed out.');
    };

    const openQuiz = async (quiz) => {
        if (!api || startingQuizId !== null) return;
        setError('');
        setNotice('');
        setAttemptError('');
        setSaveWarning('');
        setAttemptSubmitted(false);
        setAttemptResult(null);
        setStartingQuizId(quiz.id);
        try {
            // Starting first returns the server-owned deadline and any saved answers.
            const startResponse = await api.post(`/student/quizzes/${quiz.id}/start`);
            const session = startResponse.data?.attempt_session;
            if (!session?.id || !session?.deadline_at) throw new Error('The server did not return a timed quiz session.');
            const detailResponse = await api.get(`/student/quizzes/${quiz.id}`);
            const detail = detailResponse.data || {};
            const deadline = new Date(session.deadline_at).getTime();
            const serverNow = startResponse.data?.server_time ? new Date(startResponse.data.server_time).getTime() : Date.now();
            if (Number.isNaN(deadline)) throw new Error('The server returned an invalid quiz deadline.');
            setActiveQuiz({ ...(detail.quiz || quiz), questions: Array.isArray(detail.questions) ? detail.questions : [] });
            setAttemptSession(session);
            setSelectedAnswers(session.answers && typeof session.answers === 'object' ? session.answers : {});
            setDeadlineMs(deadline);
            setServerOffsetMs(serverNow - Date.now());
            setRemainingSeconds(Math.max(0, Math.ceil((deadline - serverNow) / 1000)));
            setAttemptError('');
            setSaveWarning('');
            answerSaveChain.current = Promise.resolve();
            timeoutTriggeredRef.current = false;
            submittingRef.current = false;
            setSubmittingAttempt(false);
            setScreen('takeQuiz');
        } catch (openError) {
            setError(messageFrom(openError));
        } finally {
            setStartingQuizId(null);
        }
    };

    const selectAnswer = (questionId, option) => {
        if (!api || !activeQuiz || !attemptSession || submittingAttempt || remainingSeconds === 0) return;
        setSelectedAnswers((current) => ({ ...current, [String(questionId)]: option }));
        setSaveWarning('');
        // Serialize saves so rapid answer changes cannot arrive out of order.
        answerSaveChain.current = answerSaveChain.current
            .then(() => api.put(`/student/quizzes/${activeQuiz.id}/attempt/answer`, {
                attempt_session_id: attemptSession.id,
                question_id: questionId,
                selected_option: option,
            }))
            .then(() => undefined)
            .catch((saveError) => {
                setSaveWarning(messageFrom(saveError));
            });
    };

    const submitQuiz = useCallback(async (automatic = false) => {
        if (!api || !activeQuiz || !attemptSession?.id || attemptSubmitted || submittingRef.current) return;
        const questions = Array.isArray(activeQuiz.questions) ? activeQuiz.questions : [];
        const unanswered = questions.filter((question) => !selectedAnswers[String(question.id)]);
        if (!automatic && unanswered.length > 0) {
            setAttemptError(`Please answer all questions before submitting. ${unanswered.length} question(s) remain unanswered.`);
            return;
        }
        submittingRef.current = true;
        setSubmittingAttempt(true);
        setAttemptError('');
        try {
            // Finish the queued server-answer writes before a manual submission.
            await answerSaveChain.current;
            const response = await api.post(`/student/quizzes/${activeQuiz.id}/submit`, automatic
                ? { attempt_session_id: attemptSession.id, timed_out: true }
                : { attempt_session_id: attemptSession.id, answers: selectedAnswers });
            const body = response.data || {};
            setAttemptResult({
                ...(body.result || {}),
                attempt_id: body.attempt?.id,
                submitted_at: body.attempt?.submitted_at,
                message: body.message || (automatic ? 'Time expired. Saved answers were submitted.' : 'Quiz submitted successfully.'),
                automatic,
            });
            setAttemptSubmitted(true);
            setScreen('quizResult');
            setNotice('Quiz result saved successfully.');
            try {
                const resultsResponse = await api.get('/student/results');
                setResults(Array.isArray(resultsResponse.data?.results) ? resultsResponse.data.results : []);
            } catch {
                // The submitted result screen can still be shown from the submit response.
            }
        } catch (submitError) {
            setAttemptError(automatic
                ? `Automatic submission could not reach the server. Check your connection and retry. ${messageFrom(submitError)}`
                : messageFrom(submitError));
        } finally {
            submittingRef.current = false;
            setSubmittingAttempt(false);
        }
    }, [api, activeQuiz, attemptSession, attemptSubmitted, selectedAnswers]);

    submitQuizRef.current = submitQuiz;

    useEffect(() => {
        if (screen !== 'takeQuiz' || deadlineMs === null) return undefined;
        timeoutTriggeredRef.current = false;
        const tick = () => {
            const seconds = Math.max(0, Math.ceil((deadlineMs - (Date.now() + serverOffsetMs)) / 1000));
            setRemainingSeconds(seconds);
            if (seconds <= 0 && !timeoutTriggeredRef.current) {
                timeoutTriggeredRef.current = true;
                void submitQuizRef.current?.(true);
            }
        };
        tick();
        const timerId = setInterval(tick, 500);
        return () => clearInterval(timerId);
    }, [screen, deadlineMs, serverOffsetMs]);

    const exitQuiz = () => {
        Alert.alert(
            'Leave quiz?',
            'Your server-saved answers and deadline remain active. You can resume this quiz from My Quizzes.',
            [
                { text: 'Stay here', style: 'cancel' },
                { text: 'Leave', style: 'destructive', onPress: () => {
                        setActiveQuiz(null);
                        setAttemptSession(null);
                        setDeadlineMs(null);
                        setRemainingSeconds(null);
                        setScreen('quizzes');
                    } },
            ],
        );
    };

    const updateQuizForm = (key, value) => setQuizForm((form) => ({ ...form, [key]: value }));
    const updateQuestion = (index, key, value) => setQuizForm((form) => ({
        ...form,
        questions: form.questions.map((question, currentIndex) => currentIndex === index ? { ...question, [key]: value } : question),
    }));

    const createQuiz = async () => {
        if (!api) return;
        setError('');
        setNotice('');
        const normalizedQuestions = quizForm.questions.map((question) => ({
            question_text: question.question_text.trim(),
            option_a: question.option_a.trim(),
            option_b: question.option_b.trim(),
            option_c: question.option_c.trim(),
            option_d: question.option_d.trim(),
            correct_option: question.correct_option,
            marks: Number(question.marks || 1),
        }));
        if (!quizForm.title.trim() || !quizForm.course.trim() || !quizForm.degree.trim() || !quizForm.batch.trim() || !quizForm.subject.trim()) {
            setError('Fill in the quiz title, course, degree, batch, and subject.');
            return;
        }
        if (normalizedQuestions.some((question) => !question.question_text || !question.option_a || !question.option_b || !question.option_c || !question.option_d || !Number.isInteger(question.marks) || question.marks < 1)) {
            setError('Each question needs text, all four options, and marks of at least 1.');
            return;
        }
        setCreatingQuiz(true);
        try {
            const response = await api.post('/teacher/quizzes', {
                title: quizForm.title.trim(),
                grade: Number(quizForm.grade || 13),
                course: quizForm.course.trim(),
                degree: quizForm.degree.trim(),
                batch: quizForm.batch.trim(),
                subject: quizForm.subject.trim(),
                description: quizForm.description.trim() || null,
                duration_minutes: Number(quizForm.duration_minutes),
                available_at: quizForm.available_at.trim(),
                questions: normalizedQuestions,
            });
            if (response.data?.quiz) setQuizzes((current) => [response.data.quiz, ...current]);
            setQuizForm({ title: '', grade: '13', course: '', degree: '', batch: '', subject: '', description: '', duration_minutes: '20', available_at: makeLocalDateTime(), questions: [EMPTY_QUESTION()] });
            setNotice(response.data?.message || 'Quiz created successfully.');
            setScreen('quizzes');
        } catch (createError) {
            setError(messageFrom(createError));
        } finally {
            setCreatingQuiz(false);
        }
    };

    const average = results.length
        ? results.reduce((sum, result) => sum + Number(result.percentage ?? 0), 0) / results.length
        : 0;
    const best = results.reduce((max, result) => Math.max(max, Number(result.percentage ?? 0)), 0);
    const displayName = profile?.name || (role === 'teacher' ? 'Teacher' : 'Student');

    if (booting) {
        return <View style={styles.loadingScreen}><StatusBar barStyle="dark-content" /><ActivityIndicator size="large" color={COLORS.blue} /><Text style={styles.loadingText}>Loading QuizSpace…</Text></View>;
    }

    if (!token || !role) {
        return (
            <KeyboardAvoidingView style={styles.loginScreen} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
                <StatusBar barStyle="dark-content" />
                <ScrollView contentContainerStyle={styles.loginScroll} keyboardShouldPersistTaps="handled">
                    <View style={styles.brandRow}>
                        <View style={styles.brandMark}><Text style={styles.brandMarkText}>Q</Text></View>
                        <View><Text style={styles.brandName}>QuizSpace</Text><Text style={styles.brandTagline}>Your campus learning app</Text></View>
                    </View>
                    <View style={styles.loginHero}>
                        <Text style={styles.eyebrow}>MOBILE PORTAL</Text>
                        <Text style={styles.loginTitle}>Learn. Practice. Progress.</Text>
                        <Text style={styles.loginDescription}>Attempt timed quizzes, keep track of your scores, and access your academic workspace from your phone.</Text>
                    </View>
                    <View style={styles.loginCard}>
                        <Text style={styles.loginCardTitle}>Sign in</Text>
                        <Text style={styles.loginCardSubtitle}>Choose your account type to continue.</Text>
                        <View style={styles.roleToggle}>
                            {['student', 'teacher'].map((item) => (
                                <Pressable key={item} onPress={() => setLoginRole(item)} style={[styles.roleOption, loginRole === item && styles.roleOptionActive]}>
                                    <Text style={[styles.roleOptionText, loginRole === item && styles.roleOptionTextActive]}>{item === 'student' ? '🎓 Student' : '🧑‍🏫 Teacher'}</Text>
                                </Pressable>
                            ))}
                        </View>
                        <Field label="Laravel API address" value={baseInput} onChangeText={setBaseInput} placeholder="http://192.168.1.20:8000/api" keyboardType="url" helperText="Phone: use your computer's Wi-Fi IPv4 address. Android emulator: http://10.0.2.2:8000/api" />
                        <Field label="Email address" value={email} onChangeText={setEmail} placeholder="you@example.com" keyboardType="email-address" autoCapitalize="none" />
                        <Field label="Password" value={password} onChangeText={setPassword} placeholder="Enter your password" secureTextEntry autoCapitalize="none" />
                        <Notice text={error} />
                        <Notice text={notice} type="success" />
                        <PrimaryButton title="Sign in to QuizSpace" onPress={handleLogin} loading={loginBusy} disabled={loginBusy} />
                    </View>
                    <View style={styles.connectionTip}>
                        <Text style={styles.connectionTipTitle}>Before signing in</Text>
                        <Text style={styles.connectionTipText}>Start Laravel using `php artisan serve --host=0.0.0.0 --port=8000`, and keep the phone and computer on the same Wi-Fi network.</Text>
                    </View>
                </ScrollView>
            </KeyboardAvoidingView>
        );
    }

    const goTo = (next) => {
        setError('');
        setNotice('');
        setScreen(next);
    };

    const renderHome = () => (
        <>
            <View style={styles.welcomeCard}>
                <Text style={styles.welcomeOverline}>{role === 'student' ? 'STUDENT PORTAL' : 'TEACHER WORKSPACE'}</Text>
                <Text style={styles.welcomeTitle}>Hello, {displayName.split(' ')[0] || displayName}!</Text>
                <Text style={styles.welcomeBody}>{role === 'student' ? 'Your learning journey, all in one place.' : 'Manage your quizzes and review student submissions.'}</Text>
            </View>
            {role === 'student' ? (
                <>
                    <SectionTitle title="Academic profile" subtitle="Your registered study details" />
                    <View style={styles.card}>
                        <ProfileRow label="Course" value={academic?.course || 'Not assigned'} />
                        <ProfileRow label="Degree" value={academic?.degree || 'Not assigned'} />
                        <ProfileRow label="Batch" value={academic?.batch || 'Not assigned'} />
                        <ProfileRow label="Admission" value={academic?.admission_status || 'Not linked'} last />
                    </View>
                    <SectionTitle title="Your progress" subtitle="Based on submitted attempts" />
                    <View style={styles.statsRow}>
                        <StatCard label="Attempts" value={results.length} symbol="✓" />
                        <StatCard label="Average" value={`${average.toFixed(1)}%`} accent={COLORS.green} symbol="↗" />
                        <StatCard label="Best score" value={`${best.toFixed(0)}%`} accent={COLORS.purple} symbol="★" />
                    </View>
                    <PrimaryButton title="View my quizzes  →" onPress={() => goTo('quizzes')} />
                </>
            ) : (
                <>
                    <View style={styles.statsRow}>
                        <StatCard label="My quizzes" value={quizzes.length} symbol="▤" />
                        <StatCard label="Submissions" value={results.length} accent={COLORS.green} symbol="✓" />
                    </View>
                    <View style={styles.card}>
                        <ProfileRow label="Email" value={profile?.email || '—'} />
                        <ProfileRow label="Teaching subject" value={profile?.subject || '—'} last />
                    </View>
                    <PrimaryButton title="Create a quiz  +" onPress={() => goTo('create')} />
                    <PrimaryButton title="Review student results  →" onPress={() => goTo('results')} variant="secondary" />
                </>
            )}
        </>
    );

    const renderQuizzes = () => (
        <>
            <SectionTitle
                title={role === 'student' ? 'My quizzes' : 'My quizzes'}
                subtitle={role === 'student' ? 'Assigned to your course, degree, and batch.' : 'Quizzes created by your teacher account.'}
                action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>}
            />
            {role === 'teacher' && <PrimaryButton title="Create new quiz  +" onPress={() => goTo('create')} />}
            {quizzes.length === 0 ? <EmptyState title="No quizzes found" body={role === 'student' ? 'Published quizzes assigned to your academic group will appear here.' : 'Create a quiz to start collecting student submissions.'} /> : quizzes.map((quiz) => (
                <QuizCard key={quiz.id} quiz={quiz} onStart={role === 'student' ? openQuiz : null} starting={startingQuizId === quiz.id} />
            ))}
        </>
    );

    const renderResults = () => {
        if (role === 'student') {
            return (
                <>
                    <SectionTitle title="My results" subtitle="Your submitted quizzes and performance." action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>} />
                    <View style={styles.statsRow}>
                        <StatCard label="Total attempts" value={results.length} symbol="✓" />
                        <StatCard label="Average score" value={`${average.toFixed(2)}%`} accent={COLORS.green} symbol="↗" />
                        <StatCard label="Best score" value={`${best.toFixed(0)}%`} accent={COLORS.purple} symbol="★" />
                    </View>
                    {results.length === 0 ? <EmptyState title="No results yet" body="Your marks and percentages will appear here after you submit a quiz." /> : results.map((item) => <StudentResultCard key={item.attempt_id || item.id} item={item} />)}
                </>
            );
        }
        return (
            <>
                <SectionTitle title="Student submissions" subtitle="Results from quizzes owned by your teacher account." action={<Pressable onPress={() => refreshData(false)} style={styles.refreshButton}><Text style={styles.refreshText}>↻ Refresh</Text></Pressable>} />
                {results.length === 0 ? <EmptyState title="No submissions yet" body="When students submit your quizzes, their submissions will appear here." /> : results.map((item) => <TeacherSubmissionCard key={item.id} item={item} />)}
            </>
        );
    };

    const renderCreateQuiz = () => (
        <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
            <SectionTitle title="Create a quiz" subtitle="This sends the quiz to your existing Laravel API." />
            <View style={styles.card}>
                <Field label="Quiz title" value={quizForm.title} onChangeText={(value) => updateQuizForm('title', value)} placeholder="e.g. Computer Networks Test" autoCapitalize="sentences" />
                <View style={styles.twoFields}>
                    <View style={styles.halfField}><Field label="Grade (1–13)" value={quizForm.grade} onChangeText={(value) => updateQuizForm('grade', value)} keyboardType="number-pad" /></View>
                    <View style={styles.halfField}><Field label="Duration (minutes)" value={quizForm.duration_minutes} onChangeText={(value) => updateQuizForm('duration_minutes', value)} keyboardType="number-pad" /></View>
                </View>
                <Field label="Course" value={quizForm.course} onChangeText={(value) => updateQuizForm('course', value)} placeholder="Course name" autoCapitalize="words" />
                <Field label="Degree" value={quizForm.degree} onChangeText={(value) => updateQuizForm('degree', value)} placeholder="Degree name" autoCapitalize="words" />
                <Field label="Batch" value={quizForm.batch} onChangeText={(value) => updateQuizForm('batch', value)} placeholder="e.g. 2026" autoCapitalize="words" />
                <Field label="Subject" value={quizForm.subject} onChangeText={(value) => updateQuizForm('subject', value)} placeholder="Subject name" autoCapitalize="words" />
                <Field label="Description (optional)" value={quizForm.description} onChangeText={(value) => updateQuizForm('description', value)} placeholder="About this quiz" multiline autoCapitalize="sentences" />
                <Field label="Available at" value={quizForm.available_at} onChangeText={(value) => updateQuizForm('available_at', value)} placeholder="YYYY-MM-DD HH:mm:ss" helperText="Use a local date/time after the current time. Example: 2026-10-29 09:00:00" autoCapitalize="none" />
            </View>
            {quizForm.questions.map((question, index) => (
                <View key={`question-${index}`} style={styles.card}>
                    <View style={styles.cardTopRow}>
                        <Text style={styles.cardTitle}>Question {index + 1}</Text>
                        {quizForm.questions.length > 1 && <Pressable onPress={() => setQuizForm((form) => ({ ...form, questions: form.questions.filter((_, i) => i !== index) }))}><Text style={styles.removeText}>Remove</Text></Pressable>}
                    </View>
                    <Field label="Question text" value={question.question_text} onChangeText={(value) => updateQuestion(index, 'question_text', value)} placeholder="Write the question" multiline autoCapitalize="sentences" />
                    {['a', 'b', 'c', 'd'].map((letter) => <Field key={letter} label={`Option ${letter.toUpperCase()}`} value={question[`option_${letter}`]} onChangeText={(value) => updateQuestion(index, `option_${letter}`, value)} placeholder={`Enter option ${letter.toUpperCase()}`} autoCapitalize="sentences" />)}
                    <Text style={styles.fieldLabel}>Correct answer</Text>
                    <View style={styles.optionRow}>
                        {['A', 'B', 'C', 'D'].map((option) => <Pressable key={option} onPress={() => updateQuestion(index, 'correct_option', option)} style={[styles.correctOption, question.correct_option === option && styles.correctOptionSelected]}><Text style={[styles.correctOptionText, question.correct_option === option && styles.correctOptionTextSelected]}>{option}</Text></Pressable>)}
                    </View>
                    <Field label="Marks" value={question.marks} onChangeText={(value) => updateQuestion(index, 'marks', value)} keyboardType="number-pad" />
                </View>
            ))}
            <PrimaryButton title="Add another question  +" onPress={() => setQuizForm((form) => ({ ...form, questions: [...form.questions, EMPTY_QUESTION()] }))} variant="secondary" />
            <PrimaryButton title="Create and publish quiz" onPress={createQuiz} loading={creatingQuiz} disabled={creatingQuiz} />
            <Text style={styles.formNote}>The current Laravel endpoint creates quizzes directly as published, and requires the course, degree, batch, and subject values to match your academic setup.</Text>
        </KeyboardAvoidingView>
    );

    const renderTakeQuiz = () => {
        const expired = remainingSeconds !== null && remainingSeconds <= 0;
        return (
            <>
                <Pressable onPress={exitQuiz} style={styles.backButton}><Text style={styles.backButtonText}>← Exit quiz</Text></Pressable>
                <View style={styles.quizHeaderCard}>
                    <Text style={styles.eyebrow}>TIMED QUIZ</Text>
                    <Text style={styles.quizHeaderTitle}>{activeQuiz?.title || 'Quiz'}</Text>
                    <Text style={styles.cardSubtitle}>{activeQuiz?.subject || 'General subject'} · {activeQuiz?.duration_minutes || '—'} minutes</Text>
                    <View style={[styles.timerPill, expired ? styles.timerExpired : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarning : styles.timerNormal]}>
                        <Text style={[styles.timerLabel, expired ? styles.timerExpiredText : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarningText : styles.timerNormalText]}>TIME REMAINING</Text>
                        <Text style={[styles.timerValue, expired ? styles.timerExpiredText : remainingSeconds !== null && remainingSeconds <= 60 ? styles.timerWarningText : styles.timerNormalText]}>{remainingSeconds === null ? '--:--' : formatDuration(remainingSeconds)}</Text>
                    </View>
                    <Text style={styles.timerHint}>The deadline is controlled by the server. Leaving and reopening the quiz will not reset the timer.</Text>
                </View>
                <Notice text={saveWarning} type="warning" />
                <Notice text={attemptError} />
                {Array.isArray(activeQuiz?.questions) && activeQuiz.questions.map((question, index) => (
                    <View key={question.id} style={styles.card}>
                        <View style={styles.questionTopRow}><Text style={styles.questionNumber}>QUESTION {index + 1}</Text><Text style={styles.questionMarks}>{question.marks ?? 1} mark(s)</Text></View>
                        <Text style={styles.questionText}>{question.question_text}</Text>
                        {Object.entries(question.options || {}).filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '').map(([option, value]) => {
                            const chosen = selectedAnswers[String(question.id)] === option;
                            return (
                                <Pressable key={option} onPress={() => selectAnswer(question.id, option)} disabled={submittingAttempt || expired} style={[styles.answerOption, chosen && styles.answerOptionChosen, (submittingAttempt || expired) && styles.answerOptionDisabled]}>
                                    <View style={[styles.answerLetter, chosen && styles.answerLetterChosen]}><Text style={[styles.answerLetterText, chosen && styles.answerLetterTextChosen]}>{option}</Text></View>
                                    <Text style={[styles.answerOptionText, chosen && styles.answerOptionTextChosen]}>{String(value)}</Text>
                                    {chosen && <Text style={styles.answerCheck}>✓</Text>}
                                </Pressable>
                            );
                        })}
                    </View>
                ))}
                <Text style={styles.answersCount}>{Object.keys(selectedAnswers).filter((key) => selectedAnswers[key]).length} of {activeQuiz?.questions?.length || 0} answered</Text>
                {expired ? (
                    <PrimaryButton title="Retry automatic submission" onPress={() => submitQuizRef.current?.(true)} loading={submittingAttempt} disabled={submittingAttempt} />
                ) : (
                    <PrimaryButton title="Submit answers" onPress={() => submitQuizRef.current?.(false)} loading={submittingAttempt} disabled={submittingAttempt || !activeQuiz?.questions?.length} />
                )}
            </>
        );
    };

    const renderQuizResult = () => (
        <View style={styles.card}>
            <View style={styles.resultSuccessIcon}><Text style={styles.resultSuccessIconText}>✓</Text></View>
            <Text style={styles.resultTitle}>Quiz submitted successfully</Text>
            <Text style={styles.resultMessage}>{attemptResult?.message || 'Your answers have been saved and graded.'}</Text>
            {attemptResult?.attempt_id && <Text style={styles.dateCaption}>Submission reference: #{attemptResult.attempt_id}</Text>}
            <View style={styles.resultBigScore}><Text style={styles.resultBigScoreLabel}>PERCENTAGE</Text><Text style={styles.resultBigScoreValue}>{Number(attemptResult?.percentage || 0).toFixed(2)}%</Text></View>
            <View style={styles.statsRow}>
                <StatCard label="Marks obtained" value={attemptResult?.marks_obtained ?? 0} accent={COLORS.green} symbol="✓" />
                <StatCard label="Total marks" value={attemptResult?.total_marks ?? 0} accent={COLORS.purple} symbol="Σ" />
            </View>
            <PrimaryButton title="Go to My Results" onPress={() => { setActiveQuiz(null); setAttemptSession(null); setDeadlineMs(null); setRemainingSeconds(null); setScreen('results'); void refreshData(false); }} />
            <PrimaryButton title="Back to My Quizzes" onPress={() => { setActiveQuiz(null); setAttemptSession(null); setDeadlineMs(null); setRemainingSeconds(null); setScreen('quizzes'); void refreshData(false); }} variant="secondary" />
        </View>
    );

    return (
        <View style={styles.appRoot}>
            <StatusBar barStyle="dark-content" backgroundColor={COLORS.background} />
            <View style={styles.topBar}>
                <View style={styles.topBrandMark}><Text style={styles.topBrandMarkText}>Q</Text></View>
                <View style={styles.topBarCopy}><Text style={styles.topBarTitle}>QuizSpace</Text><Text style={styles.topBarSubtitle}>{role === 'student' ? 'Student portal' : 'Teacher workspace'}</Text></View>
                <Pressable onPress={handleLogout} style={styles.signOutButton}><Text style={styles.signOutText}>Sign out</Text></Pressable>
            </View>
            {(error || notice) && screen !== 'takeQuiz' && screen !== 'quizResult' && <View style={styles.globalNotice}><Notice text={error} /><Notice text={notice} type="success" /></View>}
            {loadingData && <View style={styles.loadingBar}><ActivityIndicator size="small" color={COLORS.blue} /><Text style={styles.loadingBarText}>Loading your workspace…</Text></View>}
            <ScrollView
                key={screen}
                style={styles.mainScroll}
                contentContainerStyle={styles.mainContent}
                keyboardShouldPersistTaps="handled"
                showsVerticalScrollIndicator={false}
            >
                {screen === 'home' && renderHome()}
                {screen === 'quizzes' && renderQuizzes()}
                {screen === 'results' && renderResults()}
                {screen === 'notifications' && role === 'student' && (
                    <StudentNotificationsScreen
                        api={api}
                        onUnreadCountChange={setUnreadCount}
                    />
                )}
                {screen === 'create' && role === 'teacher' && renderCreateQuiz()}
                {screen === 'takeQuiz' && renderTakeQuiz()}
                {screen === 'quizResult' && renderQuizResult()}
                {screen === 'profile' && (
                    <>
                        <SectionTitle title="My profile" subtitle="Account details" />
                        <View style={styles.card}>
                            <ProfileRow label="Name" value={profile?.name || '—'} />
                            <ProfileRow label="Email" value={profile?.email || '—'} />
                            <ProfileRow label={role === 'student' ? 'Registration number' : 'Teaching subject'} value={role === 'student' ? (profile?.reg_no || '—') : (profile?.subject || '—')} />
                            <ProfileRow label="API endpoint" value={apiBase || '—'} last />
                        </View>
                        <PrimaryButton title="Refresh data" onPress={() => refreshData(true)} variant="secondary" />
                        <PrimaryButton title="Sign out" onPress={handleLogout} variant="danger" />
                    </>
                )}
            </ScrollView>
            {screen !== 'takeQuiz' && screen !== 'quizResult' && (
                <View style={styles.bottomNav}>
                    <NavItem label="Home" icon="⌂" active={screen === 'home'} onPress={() => goTo('home')} />
                    <NavItem label="Quizzes" icon="▤" active={screen === 'quizzes'} onPress={() => goTo('quizzes')} />
                    <NavItem label="Results" icon="▥" active={screen === 'results'} onPress={() => goTo('results')} />
                    {role === 'teacher' ? (
                        <NavItem label="Create" icon="+" active={screen === 'create'} onPress={() => goTo('create')} />
                    ) : (
                        <>
                            <NavItem label="Alerts" icon="🔔" badge={unreadCount} active={screen === 'notifications'} onPress={() => goTo('notifications')} />
                            <NavItem label="Profile" icon="◉" active={screen === 'profile'} onPress={() => goTo('profile')} />
                        </>
                    )}
                </View>
            )}
        </View>
    );
}

function ProfileRow({ label, value, last }) {
    return <View style={[styles.profileRow, last && styles.profileRowLast]}><Text style={styles.profileLabel}>{label}</Text><Text style={styles.profileValue}>{String(value ?? '—')}</Text></View>;
}

function EmptyState({ title, body }) {
    return <View style={styles.emptyState}><View style={styles.emptyIcon}><Text style={styles.emptyIconText}>✓</Text></View><Text style={styles.emptyTitle}>{title}</Text><Text style={styles.emptyBody}>{body}</Text></View>;
}

function NavItem({ label, icon, active, onPress, badge = 0 }) {
    return (
        <Pressable onPress={onPress} style={styles.navItem}>
            <View style={[styles.navIconWrap, active && styles.navIconWrapActive]}>
                <Text style={[styles.navIcon, active && styles.navIconActive]}>{icon}</Text>
                {badge > 0 && (
                    <View style={styles.navBadge}>
                        <Text style={styles.navBadgeText}>{badge > 99 ? '99+' : badge}</Text>
                    </View>
                )}
            </View>
            <Text style={[styles.navLabel, active && styles.navLabelActive]}>{label}</Text>
        </Pressable>
    );
}

const styles = StyleSheet.create({
    appRoot: { flex: 1, backgroundColor: COLORS.background },
    loadingScreen: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: COLORS.background, gap: 14 },
    loadingText: { color: COLORS.muted, fontSize: 14 },
    loginScreen: { flex: 1, backgroundColor: COLORS.background },
    loginScroll: { padding: 22, paddingTop: 34, paddingBottom: 42 },
    brandRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 34 },
    brandMark: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.blue },
    brandMarkText: { color: '#fff', fontSize: 25, fontWeight: '900' },
    brandName: { color: COLORS.ink, fontSize: 22, fontWeight: '900', letterSpacing: -0.5 },
    brandTagline: { color: COLORS.muted, fontSize: 12, marginTop: 3 },
    loginHero: { marginBottom: 22 },
    eyebrow: { color: COLORS.blue, fontSize: 10, fontWeight: '900', letterSpacing: 1.6, marginBottom: 9 },
    loginTitle: { color: COLORS.ink, fontSize: 35, fontWeight: '900', lineHeight: 40, letterSpacing: -1.3, marginBottom: 12 },
    loginDescription: { color: COLORS.muted, fontSize: 14, lineHeight: 22 },
    loginCard: { backgroundColor: COLORS.white, borderRadius: 23, borderWidth: 1, borderColor: COLORS.line, padding: 20, shadowColor: '#17213C', shadowOpacity: 0.05, shadowRadius: 15, shadowOffset: { width: 0, height: 7 }, elevation: 2 },
    loginCardTitle: { color: COLORS.ink, fontSize: 22, fontWeight: '900' },
    loginCardSubtitle: { color: COLORS.muted, fontSize: 12, marginTop: 5, marginBottom: 17 },
    roleToggle: { flexDirection: 'row', gap: 9, marginBottom: 14 },
    roleOption: { flex: 1, paddingVertical: 12, borderRadius: 12, borderWidth: 1, borderColor: COLORS.line, alignItems: 'center', backgroundColor: '#FAFBFE' },
    roleOptionActive: { backgroundColor: '#F0F1FF', borderColor: '#B9BFFF' },
    roleOptionText: { color: COLORS.muted, fontSize: 12, fontWeight: '800' },
    roleOptionTextActive: { color: COLORS.blue },
    fieldWrap: { marginBottom: 13 },
    fieldLabel: { color: '#34415E', fontSize: 12, fontWeight: '800', marginBottom: 7 },
    input: { width: '100%', minHeight: 47, borderRadius: 11, borderWidth: 1, borderColor: '#E0E6F0', backgroundColor: '#FBFCFF', paddingHorizontal: 12, paddingVertical: 11, color: COLORS.ink, fontSize: 13 },
    multilineInput: { minHeight: 85, textAlignVertical: 'top' },
    inputDisabled: { opacity: 0.65 },
    helperText: { color: COLORS.muted, fontSize: 10, lineHeight: 15, marginTop: 6 },
    buttonPrimary: { minHeight: 47, paddingHorizontal: 16, paddingVertical: 12, borderRadius: 12, backgroundColor: COLORS.blue, alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonPrimaryText: { color: '#fff', fontSize: 13, fontWeight: '900' },
    buttonSecondary: { minHeight: 45, paddingHorizontal: 15, paddingVertical: 11, borderRadius: 12, backgroundColor: '#F2F3FF', borderWidth: 1, borderColor: '#DDE0FF', alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonSecondaryText: { color: '#515FD8', fontSize: 13, fontWeight: '900' },
    buttonDanger: { minHeight: 45, paddingHorizontal: 15, paddingVertical: 11, borderRadius: 12, backgroundColor: COLORS.redBg, borderWidth: 1, borderColor: '#F1C8CE', alignItems: 'center', justifyContent: 'center', marginTop: 8, marginBottom: 8 },
    buttonDangerText: { color: COLORS.red, fontSize: 13, fontWeight: '900' },
    buttonSubtle: { minHeight: 38, paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, backgroundColor: '#F5F7FC', borderWidth: 1, borderColor: COLORS.line, alignItems: 'center', justifyContent: 'center' },
    buttonSubtleText: { color: COLORS.ink, fontSize: 11, fontWeight: '800' },
    buttonSmall: { minHeight: 34, paddingVertical: 7, marginTop: 0, marginBottom: 0 },
    buttonDisabled: { opacity: 0.5 },
    pressed: { opacity: 0.8 },
    notice: { paddingVertical: 10, paddingHorizontal: 12, borderWidth: 1, borderRadius: 10, marginBottom: 10 },
    noticeError: { backgroundColor: COLORS.redBg, borderColor: '#F1C8CE' },
    noticeSuccess: { backgroundColor: COLORS.greenBg, borderColor: '#C7EEDC' },
    noticeWarning: { backgroundColor: COLORS.amberBg, borderColor: '#F0DFB8' },
    noticeText: { color: COLORS.ink, fontSize: 11, lineHeight: 16 },
    connectionTip: { padding: 16, marginTop: 16, borderRadius: 15, backgroundColor: '#EEF1FB' },
    connectionTipTitle: { color: COLORS.ink, fontWeight: '900', fontSize: 12, marginBottom: 5 },
    connectionTipText: { color: COLORS.muted, fontSize: 11, lineHeight: 17 },
    topBar: { backgroundColor: COLORS.white, paddingHorizontal: 16, paddingTop: Platform.OS === 'android' ? 8 : 10, paddingBottom: 12, flexDirection: 'row', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: COLORS.line },
    topBrandMark: { width: 38, height: 38, borderRadius: 12, backgroundColor: COLORS.blue, alignItems: 'center', justifyContent: 'center', marginRight: 10 },
    topBrandMarkText: { color: '#FFFFFF', fontWeight: '900', fontSize: 21 },
    topBarCopy: { flex: 1 },
    topBarTitle: { color: COLORS.ink, fontSize: 16, fontWeight: '900' },
    topBarSubtitle: { color: COLORS.muted, fontSize: 10, marginTop: 2 },
    signOutButton: { borderRadius: 10, borderWidth: 1, borderColor: COLORS.line, paddingHorizontal: 11, paddingVertical: 8 },
    signOutText: { color: COLORS.ink, fontSize: 10, fontWeight: '800' },
    globalNotice: { paddingHorizontal: 15, paddingTop: 8 },
    loadingBar: { paddingHorizontal: 16, paddingVertical: 8, flexDirection: 'row', gap: 8, alignItems: 'center' },
    loadingBarText: { color: COLORS.muted, fontSize: 11 },
    mainScroll: { flex: 1 },
    mainContent: { paddingHorizontal: 16, paddingTop: 18, paddingBottom: 28 },
    welcomeCard: { borderRadius: 22, padding: 22, backgroundColor: COLORS.navy, marginBottom: 21 },
    welcomeOverline: { color: '#BFC8FF', fontSize: 10, letterSpacing: 1.4, fontWeight: '900', marginBottom: 10 },
    welcomeTitle: { color: '#FFFFFF', fontSize: 27, letterSpacing: -0.7, fontWeight: '900', marginBottom: 8 },
    welcomeBody: { color: '#D6DCF4', fontSize: 12, lineHeight: 19 },
    sectionTitleRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10, marginBottom: 13, gap: 12 },
    sectionTitleCopy: { flex: 1 },
    sectionTitle: { color: COLORS.ink, fontSize: 19, fontWeight: '900', letterSpacing: -0.3 },
    sectionSubtitle: { color: COLORS.muted, fontSize: 11, lineHeight: 16, marginTop: 4 },
    refreshButton: { paddingHorizontal: 10, paddingVertical: 8, borderRadius: 9, backgroundColor: '#F0F1FF' },
    refreshText: { color: COLORS.blue, fontSize: 10, fontWeight: '900' },
    card: { borderRadius: 18, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, padding: 16, marginBottom: 13, shadowColor: '#1D284D', shadowOpacity: 0.025, shadowRadius: 10, shadowOffset: { width: 0, height: 4 }, elevation: 1 },
    cardTopRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 10, marginBottom: 11 },
    flexOne: { flex: 1, minWidth: 0 },
    cardTitle: { color: COLORS.ink, fontSize: 15, lineHeight: 21, fontWeight: '900' },
    cardSubtitle: { color: COLORS.muted, fontSize: 11, marginTop: 4 },
    cardDescription: { color: COLORS.muted, fontSize: 11, lineHeight: 17, marginBottom: 12 },
    statusPill: { borderRadius: 999, paddingHorizontal: 9, paddingVertical: 6 },
    statusAvailable: { backgroundColor: COLORS.greenBg },
    statusUpcoming: { backgroundColor: COLORS.amberBg },
    statusText: { fontSize: 9, fontWeight: '900' },
    statusAvailableText: { color: COLORS.green },
    statusUpcomingText: { color: COLORS.amber },
    metaGrid: { flexDirection: 'row', flexWrap: 'wrap', borderTopWidth: 1, borderTopColor: '#EEF1F7', paddingTop: 12, marginTop: 4 },
    metaCell: { width: '50%', paddingVertical: 5, paddingRight: 8 },
    metaLabel: { color: '#8C98AD', fontSize: 10, marginBottom: 4 },
    metaValue: { color: '#33405E', fontSize: 11, fontWeight: '800' },
    dateCaption: { color: '#8B97AC', fontSize: 10, marginTop: 12, lineHeight: 15 },
    statsRow: { flexDirection: 'row', gap: 9, marginBottom: 17 },
    statCard: { flex: 1, minWidth: 0, borderWidth: 1, borderColor: COLORS.line, backgroundColor: COLORS.white, borderRadius: 15, padding: 12, marginBottom: 2 },
    statSymbol: { width: 29, height: 29, borderRadius: 9, alignItems: 'center', justifyContent: 'center', marginBottom: 10 },
    statSymbolText: { fontSize: 16, fontWeight: '900' },
    statValue: { color: COLORS.ink, fontSize: 18, fontWeight: '900' },
    statLabel: { color: COLORS.muted, fontSize: 9, lineHeight: 13, marginTop: 5 },
    profileRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12, paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#EEF1F7' },
    profileRowLast: { borderBottomWidth: 0 },
    profileLabel: { color: COLORS.muted, fontSize: 11, flex: 0.8 },
    profileValue: { color: COLORS.ink, fontSize: 11, fontWeight: '800', flex: 1.2, textAlign: 'right' },
    scorePill: { paddingVertical: 7, paddingHorizontal: 9, borderRadius: 10 },
    scoreGood: { backgroundColor: COLORS.greenBg },
    scoreLow: { backgroundColor: COLORS.redBg },
    scorePillText: { fontSize: 11, fontWeight: '900' },
    scoreGoodText: { color: COLORS.green },
    scoreLowText: { color: COLORS.red },
    resultMarksRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 9, borderTopWidth: 1, borderTopColor: '#EEF1F7', paddingTop: 12 },
    resultMarks: { color: COLORS.ink, fontSize: 18, fontWeight: '900', marginTop: 3 },
    emptyState: { borderRadius: 18, borderWidth: 1, borderColor: COLORS.line, padding: 24, alignItems: 'flex-start', backgroundColor: COLORS.white, marginBottom: 14 },
    emptyIcon: { width: 40, height: 40, borderRadius: 13, backgroundColor: '#F0F1FF', alignItems: 'center', justifyContent: 'center', marginBottom: 12 },
    emptyIconText: { color: COLORS.blue, fontSize: 19, fontWeight: '900' },
    emptyTitle: { color: COLORS.ink, fontSize: 15, fontWeight: '900', marginBottom: 6 },
    emptyBody: { color: COLORS.muted, fontSize: 11, lineHeight: 17 },
    bottomNav: { minHeight: 66, borderTopWidth: 1, borderTopColor: COLORS.line, backgroundColor: COLORS.white, flexDirection: 'row', justifyContent: 'space-around', paddingHorizontal: 4, paddingTop: 7, paddingBottom: Platform.OS === 'ios' ? 16 : 7 },
    navItem: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 3 },
    navIconWrap: { minWidth: 35, minHeight: 27, paddingHorizontal: 7, borderRadius: 9, alignItems: 'center', justifyContent: 'center' },
    navIconWrapActive: { backgroundColor: '#F0F1FF' },
    navIcon: { fontSize: 19, color: '#8B97AD', fontWeight: '700' },
    navIconActive: { color: COLORS.blue },
    navBadge: { position: 'absolute', top: -4, right: -2, minWidth: 16, height: 16, paddingHorizontal: 3, borderRadius: 8, backgroundColor: COLORS.red, alignItems: 'center', justifyContent: 'center' },
    navBadgeText: { color: COLORS.white, fontSize: 8, lineHeight: 10, fontWeight: '900' },
    navLabel: { color: '#8B97AD', fontSize: 9, fontWeight: '700' },
    navLabelActive: { color: COLORS.blue, fontWeight: '900' },
    twoFields: { flexDirection: 'row', gap: 10 },
    halfField: { flex: 1 },
    removeText: { color: COLORS.red, fontWeight: '800', fontSize: 11 },
    optionRow: { flexDirection: 'row', gap: 8, marginBottom: 13 },
    correctOption: { flex: 1, minHeight: 38, alignItems: 'center', justifyContent: 'center', borderRadius: 10, borderWidth: 1, borderColor: COLORS.line, backgroundColor: '#FAFBFE' },
    correctOptionSelected: { backgroundColor: COLORS.greenBg, borderColor: '#A9E4C9' },
    correctOptionText: { color: COLORS.muted, fontWeight: '900', fontSize: 12 },
    correctOptionTextSelected: { color: COLORS.green },
    formNote: { color: COLORS.muted, fontSize: 10, lineHeight: 16, marginTop: 10, marginBottom: 8 },
    backButton: { alignSelf: 'flex-start', paddingVertical: 8, paddingHorizontal: 11, borderWidth: 1, borderColor: COLORS.line, borderRadius: 10, backgroundColor: COLORS.white, marginBottom: 13 },
    backButtonText: { color: COLORS.ink, fontWeight: '800', fontSize: 11 },
    quizHeaderCard: { borderRadius: 20, borderWidth: 1, borderColor: COLORS.line, padding: 18, backgroundColor: COLORS.white, marginBottom: 13 },
    quizHeaderTitle: { color: COLORS.ink, fontWeight: '900', fontSize: 23, lineHeight: 29, marginBottom: 7 },
    timerPill: { marginTop: 15, borderRadius: 15, padding: 13, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
    timerNormal: { backgroundColor: '#EFF1FF', borderWidth: 1, borderColor: '#DDE0FF' },
    timerWarning: { backgroundColor: COLORS.amberBg, borderWidth: 1, borderColor: '#F0DFB8' },
    timerExpired: { backgroundColor: COLORS.redBg, borderWidth: 1, borderColor: '#F1C8CE' },
    timerLabel: { fontSize: 9, fontWeight: '900', letterSpacing: 0.8 },
    timerValue: { fontSize: 24, fontWeight: '900', fontVariant: ['tabular-nums'] },
    timerNormalText: { color: COLORS.blue },
    timerWarningText: { color: COLORS.amber },
    timerExpiredText: { color: COLORS.red },
    timerHint: { color: COLORS.muted, fontSize: 10, lineHeight: 15, marginTop: 10 },
    questionTopRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    questionNumber: { color: COLORS.blue, fontSize: 10, fontWeight: '900', letterSpacing: 0.7 },
    questionMarks: { color: '#929DB2', fontSize: 10 },
    questionText: { color: COLORS.ink, fontSize: 14, fontWeight: '800', lineHeight: 21, marginBottom: 14 },
    answerOption: { minHeight: 48, padding: 10, borderWidth: 1, borderColor: '#E3E8F2', borderRadius: 12, backgroundColor: COLORS.white, marginBottom: 8, flexDirection: 'row', alignItems: 'center', gap: 10 },
    answerOptionChosen: { borderColor: '#777FF0', backgroundColor: '#F2F3FF' },
    answerOptionDisabled: { opacity: 0.75 },
    answerLetter: { width: 27, height: 27, borderRadius: 8, backgroundColor: '#EFF0FF', alignItems: 'center', justifyContent: 'center' },
    answerLetterChosen: { backgroundColor: COLORS.blue },
    answerLetterText: { color: '#5D66DF', fontSize: 11, fontWeight: '900' },
    answerLetterTextChosen: { color: '#FFFFFF' },
    answerOptionText: { flex: 1, color: '#4D5A73', fontSize: 12, lineHeight: 17 },
    answerOptionTextChosen: { color: COLORS.ink, fontWeight: '700' },
    answerCheck: { color: COLORS.blue, fontWeight: '900', fontSize: 15 },
    answersCount: { color: COLORS.muted, fontSize: 11, textAlign: 'center', marginTop: 4, marginBottom: 8 },
    resultSuccessIcon: { width: 52, height: 52, borderRadius: 17, backgroundColor: COLORS.greenBg, alignItems: 'center', justifyContent: 'center', marginBottom: 15 },
    resultSuccessIconText: { color: COLORS.green, fontSize: 27, fontWeight: '900' },
    resultTitle: { color: COLORS.ink, fontSize: 21, fontWeight: '900', marginBottom: 8 },
    resultMessage: { color: COLORS.muted, fontSize: 12, lineHeight: 18 },
    resultBigScore: { backgroundColor: '#F4F6FF', borderRadius: 16, padding: 17, alignItems: 'center', marginTop: 20, marginBottom: 14 },
    resultBigScoreLabel: { color: COLORS.muted, fontSize: 9, fontWeight: '900', letterSpacing: 1.1 },
    resultBigScoreValue: { color: COLORS.blue, fontWeight: '900', fontSize: 31, marginTop: 5 },
});
