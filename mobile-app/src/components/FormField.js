import React, { useState } from 'react';
import { View, Text, TextInput, StyleSheet } from 'react-native';
import { colors, spacing, radius, typography } from '../theme';

export default function FormField({ label, value, onChangeText, error, placeholder, placeholderTextColor, secureTextEntry, keyboardType, multiline, accent = 'info', style }) {
    const [focused, setFocused] = useState(false);
    const accentColor = colors[accent] || { 500: colors.info };
    
    return (
        <View style={styles.container}>
            {label && <Text style={styles.label}>{label}</Text>}
            <TextInput
                style={[
                    styles.input,
                    focused && { borderColor: accentColor[500] },
                    error && { borderColor: colors.danger },
                    multiline && styles.multiline,
                    style
                ]}
                value={value}
                onChangeText={onChangeText}
                placeholder={placeholder}
                placeholderTextColor={placeholderTextColor || colors.textMuted}
                secureTextEntry={secureTextEntry}
                keyboardType={keyboardType}
                multiline={multiline}
                onFocus={() => setFocused(true)}
                onBlur={() => setFocused(false)}
            />
            {error && <Text style={styles.error}>{error}</Text>}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        marginBottom: spacing.lg,
    },
    label: {
        ...typography.label,
        color: colors.textSecondary,
        marginBottom: spacing.sm,
    },
    input: {
        backgroundColor: colors.surfaceAlt,
        borderWidth: 1,
        borderColor: colors.border,
        borderRadius: radius.md,
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.md,
        ...typography.body,
        color: colors.textPrimary,
    },
    multiline: {
        minHeight: 100,
        textAlignVertical: 'top',
    },
    error: {
        ...typography.caption,
        color: colors.danger,
        marginTop: spacing.xs,
        fontWeight: '600',
    }
});
