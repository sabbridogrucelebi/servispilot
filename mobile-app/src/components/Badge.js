import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, radius, typography } from '../theme';

export default function Badge({ label, tone = 'neutral' }) {
    let bgColor = colors.surfaceAlt;
    let textColor = colors.textSecondary;

    if (tone === 'success') { bgColor = '#D1FAE5'; textColor = '#065F46'; }
    else if (tone === 'warning') { bgColor = '#FEF3C7'; textColor = '#92400E'; }
    else if (tone === 'danger') { bgColor = '#FEE2E2'; textColor = '#991B1B'; }
    else if (tone === 'info') { bgColor = '#DBEAFE'; textColor = '#1E40AF'; }

    return (
        <View style={[styles.badge, { backgroundColor: bgColor }]}>
            <Text style={[styles.text, { color: textColor }]}>{label}</Text>
        </View>
    );
}

const styles = StyleSheet.create({
    badge: {
        paddingHorizontal: 8,
        paddingVertical: 2,
        borderRadius: radius.full,
        alignSelf: 'flex-start',
    },
    text: {
        ...typography.caption,
        fontSize: 10,
        fontWeight: '700',
        textTransform: 'uppercase',
        letterSpacing: 0.5,
    }
});
