
import React, { useCallback, useEffect, useState } from 'react';
import {
    ActivityIndicator,
    Pressable,
    RefreshControl,
    ScrollView,
    StyleSheet,
    Text,
    View,
} from 'react-native';

const COLORS = {
    ink: '#18233F',
    muted: '#73809A',
    blue: '#5969F6',
    white: '#FFFFFF',
    background: '#F5F7FC',
    line: '#E5EAF4',
    green: '#16845B',
    greenBg: '#E5F8EF',
    amber: '#95600A',
    amberBg: '#FFF5D9',
    red: '#B42332',
};

function formatDate(value) {
    if (!value) return '';

    const parsed = new Date(String(value).replace(' ', 'T'));

    if (Number.isNaN(parsed.getTime())) {
        return String(value);
    }

    return parsed.toLocaleString();
}

export default function StudentNotificationsScreen({
                                                       api,
                                                       onUnreadCountChange,
                                                   }) {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [busyId, setBusyId] = useState(null);
    const [markingAll, setMarkingAll] = useState(false);
    const [error, setError] = useState('');

    useEffect(() => {
        onUnreadCountChange?.(unreadCount);
    }, [unreadCount, onUnreadCountChange]);

    const loadNotifications = useCallback(async (showLoading = true) => {
        if (!api) {
            setError('API connection is unavailable.');
            setLoading(false);
            return;
        }

        if (showLoading) setLoading(true);

        setError('');

        try {
            const response = await api.get('/student/notifications');

            const body = response.data || {};
            const notificationData = body.notifications;

            const records = Array.isArray(notificationData?.data)
                ? notificationData.data
                : Array.isArray(notificationData)
                    ? notificationData
                    : [];

            setNotifications(records);

            setUnreadCount(
                Number(
                    body.unread_count ??
                    records.filter((item) => !item.read_at).length
                )
            );
        } catch (fetchError) {
            setError(
                fetchError?.response?.data?.message ||
                'Unable to load notifications. Please try again.'
            );
        } finally {
            setLoading(false);
        }
    }, [api]);

    useEffect(() => {
        void loadNotifications();
    }, [loadNotifications]);

    const refreshNotifications = async () => {
        setRefreshing(true);
        await loadNotifications(false);
        setRefreshing(false);
    };

    const markAsRead = async (notification) => {
        if (!notification?.id || notification.read_at || busyId !== null) {
            return;
        }

        setBusyId(notification.id);
        setError('');

        try {
            await api.patch(
                `/student/notifications/${notification.id}/read`
            );

            setNotifications((current) =>
                current.map((item) =>
                    item.id === notification.id
                        ? { ...item, read_at: new Date().toISOString() }
                        : item
                )
            );

            setUnreadCount((current) => Math.max(0, current - 1));
        } catch (markError) {
            setError(
                markError?.response?.data?.message ||
                'Unable to mark notification as read.'
            );
        } finally {
            setBusyId(null);
        }
    };

    const markAllAsRead = async () => {
        if (unreadCount === 0 || markingAll) return;

        setMarkingAll(true);
        setError('');

        try {
            await api.patch('/student/notifications/read-all');

            const now = new Date().toISOString();

            setNotifications((current) =>
                current.map((item) => ({
                    ...item,
                    read_at: item.read_at || now,
                }))
            );

            setUnreadCount(0);
        } catch (markError) {
            setError(
                markError?.response?.data?.message ||
                'Unable to mark all notifications as read.'
            );
        } finally {
            setMarkingAll(false);
        }
    };

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <View style={styles.headerCopy}>
                    <Text style={styles.title}>Notifications</Text>
                    <Text style={styles.subtitle}>
                        Updates about your quizzes
                    </Text>
                </View>

                <View style={styles.countBadge}>
                    <Text style={styles.countText}>
                        {unreadCount} unread
                    </Text>
                </View>
            </View>

            {unreadCount > 0 && (
                <Pressable
                    onPress={markAllAsRead}
                    disabled={markingAll}
                    style={styles.markAllButton}
                >
                    {markingAll ? (
                        <ActivityIndicator size="small" color={COLORS.blue} />
                    ) : (
                        <Text style={styles.markAllText}>
                            Mark all as read
                        </Text>
                    )}
                </Pressable>
            )}

            {error !== '' && (
                <View style={styles.errorBox}>
                    <Text style={styles.errorText}>{error}</Text>
                    <Pressable onPress={() => loadNotifications()}>
                        <Text style={styles.retryText}>Try again</Text>
                    </Pressable>
                </View>
            )}

            {loading ? (
                <View style={styles.loading}>
                    <ActivityIndicator size="large" color={COLORS.blue} />
                    <Text style={styles.loadingText}>
                        Loading notifications...
                    </Text>
                </View>
            ) : (
                <ScrollView
                    contentContainerStyle={styles.listContent}
                    refreshControl={
                        <RefreshControl
                            refreshing={refreshing}
                            onRefresh={refreshNotifications}
                            tintColor={COLORS.blue}
                        />
                    }
                >
                    {notifications.length === 0 ? (
                        <View style={styles.emptyCard}>
                            <Text style={styles.emptyIcon}>🔔</Text>
                            <Text style={styles.emptyTitle}>
                                No notifications yet
                            </Text>
                            <Text style={styles.emptyText}>
                                New quiz announcements will appear here.
                            </Text>
                        </View>
                    ) : (
                        notifications.map((item) => (
                            <View
                                key={item.id}
                                style={[
                                    styles.notificationCard,
                                    !item.read_at && styles.unreadCard,
                                ]}
                            >
                                <View style={styles.cardRow}>
                                    <View style={styles.iconBox}>
                                        <Text style={styles.iconText}>Q</Text>
                                    </View>

                                    <View style={styles.cardContent}>
                                        <View style={styles.cardTitleRow}>
                                            <Text style={styles.cardTitle}>
                                                {item.title}
                                            </Text>

                                            {!item.read_at && (
                                                <View style={styles.unreadDot} />
                                            )}
                                        </View>

                                        <Text style={styles.message}>
                                            {item.message}
                                        </Text>

                                        <Text style={styles.date}>
                                            {formatDate(item.created_at)}
                                        </Text>

                                        {!item.read_at ? (
                                            <Pressable
                                                onPress={() => markAsRead(item)}
                                                disabled={busyId === item.id || busyId !== null}
                                                style={styles.readButton}
                                            >
                                                <Text style={styles.readButtonText}>
                                                    {busyId === item.id
                                                        ? 'Updating...'
                                                        : 'Mark as read'}
                                                </Text>
                                            </Pressable>
                                        ) : (
                                            <Text style={styles.readLabel}>
                                                ✓ Read
                                            </Text>
                                        )}
                                    </View>
                                </View>
                            </View>
                        ))
                    )}
                </ScrollView>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
    },
    header: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        marginBottom: 12,
        gap: 10,
    },
    headerCopy: {
        flex: 1,
    },
    title: {
        color: COLORS.ink,
        fontSize: 23,
        fontWeight: '900',
    },
    subtitle: {
        color: COLORS.muted,
        fontSize: 11,
        marginTop: 5,
    },
    countBadge: {
        backgroundColor: COLORS.amberBg,
        borderRadius: 20,
        paddingHorizontal: 11,
        paddingVertical: 7,
    },
    countText: {
        color: COLORS.amber,
        fontSize: 10,
        fontWeight: '900',
    },
    markAllButton: {
        alignSelf: 'flex-end',
        paddingHorizontal: 12,
        paddingVertical: 9,
        borderRadius: 9,
        backgroundColor: '#F0F1FF',
        marginBottom: 12,
    },
    markAllText: {
        color: COLORS.blue,
        fontSize: 11,
        fontWeight: '900',
    },
    errorBox: {
        backgroundColor: '#FFF1F2',
        borderColor: '#F1C8CE',
        borderWidth: 1,
        borderRadius: 12,
        padding: 12,
        marginBottom: 12,
    },
    errorText: {
        color: COLORS.red,
        fontSize: 12,
    },
    retryText: {
        color: COLORS.blue,
        fontWeight: '900',
        fontSize: 11,
        marginTop: 8,
    },
    loading: {
        padding: 35,
        alignItems: 'center',
    },
    loadingText: {
        color: COLORS.muted,
        fontSize: 11,
        marginTop: 10,
    },
    listContent: {
        paddingBottom: 20,
    },
    notificationCard: {
        backgroundColor: COLORS.white,
        borderWidth: 1,
        borderColor: COLORS.line,
        borderRadius: 16,
        padding: 14,
        marginBottom: 11,
    },
    unreadCard: {
        borderColor: '#B9BFFF',
        backgroundColor: '#FAFAFF',
    },
    cardRow: {
        flexDirection: 'row',
        gap: 11,
    },
    iconBox: {
        width: 37,
        height: 37,
        borderRadius: 12,
        backgroundColor: '#F0F1FF',
        alignItems: 'center',
        justifyContent: 'center',
    },
    iconText: {
        color: COLORS.blue,
        fontSize: 17,
        fontWeight: '900',
    },
    cardContent: {
        flex: 1,
    },
    cardTitleRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        gap: 8,
    },
    cardTitle: {
        color: COLORS.ink,
        fontSize: 13,
        fontWeight: '900',
        flex: 1,
    },
    unreadDot: {
        width: 8,
        height: 8,
        borderRadius: 5,
        backgroundColor: COLORS.blue,
    },
    message: {
        color: '#46536D',
        fontSize: 11,
        lineHeight: 18,
        marginTop: 8,
    },
    date: {
        color: COLORS.muted,
        fontSize: 9,
        marginTop: 9,
    },
    readButton: {
        alignSelf: 'flex-start',
        marginTop: 10,
        paddingHorizontal: 10,
        paddingVertical: 7,
        borderRadius: 8,
        backgroundColor: '#F0F1FF',
    },
    readButtonText: {
        color: COLORS.blue,
        fontSize: 10,
        fontWeight: '900',
    },
    readLabel: {
        color: COLORS.green,
        fontSize: 10,
        fontWeight: '800',
        marginTop: 10,
    },
    emptyCard: {
        backgroundColor: COLORS.white,
        borderWidth: 1,
        borderColor: COLORS.line,
        borderRadius: 16,
        padding: 25,
        alignItems: 'center',
        marginTop: 10,
    },
    emptyIcon: {
        fontSize: 30,
        marginBottom: 12,
    },
    emptyTitle: {
        color: COLORS.ink,
        fontWeight: '900',
        fontSize: 15,
    },
    emptyText: {
        color: COLORS.muted,
        fontSize: 11,
        textAlign: 'center',
        lineHeight: 17,
        marginTop: 7,
    },
});
