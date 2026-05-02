import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { colors, spacing, typography } from '../theme';
import EmptyIcon3D from './EmptyIcon3D';

export default function EmptyState({ icon = 'folder-open-outline', title = 'Veri Bulunamadı', description, actionLabel, onAction, accent = 'info' }) {
    const accentColor = colors[accent] || { 500: colors.info };

    return (
        <View style={styles.container}>
            <EmptyIcon3D icon={icon} accent={accent} size={80} />
            <Text style={styles.title}>{title}</Text>
            {description && <Text style={styles.description}>{description}</Text>}
            {actionLabel && onAction && (
                <TouchableOpacity style={[styles.button, { backgroundColor: accentColor[500] }]} onPress={onAction}>
                    <Text style={styles.buttonText}>{actionLabel}</Text>
                </TouchableOpacity>
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
        padding: spacing.xxxl,
    },
    title: {
        ...typography.title,
        color: colors.textPrimary,
        marginTop: spacing.xl,
        marginBottom: spacing.sm,
        textAlign: 'center',
    },
    description: {
        ...typography.body,
        color: colors.textSecondary,
        textAlign: 'center',
        marginBottom: spacing.xl,
    },
    button: {
        paddingHorizontal: spacing.xl,
        paddingVertical: spacing.md,
        borderRadius: 999,
    },
    buttonText: {
        ...typography.body,
        fontWeight: '600',
        color: '#FFFFFF',
    }
});
