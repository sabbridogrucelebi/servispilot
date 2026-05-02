import React from 'react';
import { ScrollView, TouchableOpacity, Text, StyleSheet, View } from 'react-native';
import { colors, spacing, radius, typography } from '../theme';

export default function FilterChipRow({ chips, selected, onSelect, accent = 'info' }) {
    const accentColor = colors[accent] || { 500: colors.info, 50: colors.surfaceAlt };

    return (
        <View style={styles.container}>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.scroll}>
                {chips.map((chip, index) => {
                    const isSelected = selected === chip.value;
                    return (
                        <TouchableOpacity
                            key={index}
                            style={[
                                styles.chip,
                                isSelected ? { backgroundColor: accentColor[500], borderColor: accentColor[500] } : styles.chipUnselected
                            ]}
                            onPress={() => onSelect(chip.value)}
                        >
                            <Text style={[styles.label, isSelected ? styles.labelSelected : styles.labelUnselected]}>
                                {chip.label}
                            </Text>
                            {chip.count !== undefined && (
                                <View style={[styles.badge, isSelected ? { backgroundColor: 'rgba(255,255,255,0.2)' } : { backgroundColor: colors.border }]}>
                                    <Text style={[styles.badgeText, isSelected ? { color: '#FFF' } : { color: colors.textSecondary }]}>
                                        {chip.count}
                                    </Text>
                                </View>
                            )}
                        </TouchableOpacity>
                    );
                })}
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        marginBottom: spacing.md,
    },
    scroll: {
        paddingHorizontal: spacing.lg,
        gap: spacing.sm,
    },
    chip: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: spacing.lg,
        paddingVertical: spacing.sm,
        borderRadius: radius.full,
        borderWidth: 1,
    },
    chipUnselected: {
        backgroundColor: colors.surface,
        borderColor: colors.border,
    },
    label: {
        ...typography.body,
        fontWeight: '600',
    },
    labelSelected: {
        color: '#FFFFFF',
    },
    labelUnselected: {
        color: colors.textSecondary,
    },
    badge: {
        marginLeft: spacing.xs,
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: radius.full,
    },
    badgeText: {
        ...typography.caption,
        fontSize: 11,
        fontWeight: '700',
    }
});
