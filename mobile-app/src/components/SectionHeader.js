import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { colors, spacing, typography } from '../theme';

export default function SectionHeader({ title, count, actionLabel, onAction }) {
    return (
        <View style={styles.container}>
            <View style={styles.left}>
                <Text style={styles.title}>{title}</Text>
                {count !== undefined && (
                    <View style={styles.badge}>
                        <Text style={styles.badgeText}>{count}</Text>
                    </View>
                )}
            </View>
            {actionLabel && onAction && (
                <TouchableOpacity onPress={onAction}>
                    <Text style={styles.action}>{actionLabel}</Text>
                </TouchableOpacity>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.md,
        marginTop: spacing.sm,
    },
    left: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    title: {
        ...typography.title,
        fontSize: 18,
        color: colors.textPrimary,
    },
    badge: {
        backgroundColor: colors.surfaceAlt,
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: 12,
        marginLeft: spacing.sm,
    },
    badgeText: {
        ...typography.caption,
        fontWeight: '600',
        color: colors.textSecondary,
    },
    action: {
        ...typography.body,
        fontWeight: '600',
        color: colors.info,
    }
});
