import React, { useEffect, useRef } from 'react';
import { View, StyleSheet, Animated } from 'react-native';
import { colors, radius, spacing } from '../theme';

export default function Skeleton({ rows = 3, type = 'list' }) {
    const opacity = useRef(new Animated.Value(0.3)).current;

    useEffect(() => {
        Animated.loop(
            Animated.sequence([
                Animated.timing(opacity, { toValue: 0.7, duration: 800, useNativeDriver: true }),
                Animated.timing(opacity, { toValue: 0.3, duration: 800, useNativeDriver: true })
            ])
        ).start();
    }, []);

    const Row = () => (
        <View style={styles.row}>
            <Animated.View style={[styles.avatar, { opacity }]} />
            <View style={styles.content}>
                <Animated.View style={[styles.line, { opacity, width: '70%' }]} />
                <Animated.View style={[styles.line, { opacity, width: '40%', marginTop: 8 }]} />
            </View>
        </View>
    );

    return (
        <View style={styles.container}>
            {Array.from({ length: rows }).map((_, i) => <Row key={i} />)}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        padding: spacing.lg,
    },
    row: {
        flexDirection: 'row',
        padding: spacing.lg,
        backgroundColor: colors.surface,
        borderRadius: radius.lg,
        marginBottom: spacing.md,
    },
    avatar: {
        width: 48,
        height: 48,
        borderRadius: 24,
        backgroundColor: colors.surfaceAlt,
        marginRight: spacing.lg,
    },
    content: {
        flex: 1,
        justifyContent: 'center',
    },
    line: {
        height: 12,
        backgroundColor: colors.surfaceAlt,
        borderRadius: 6,
    }
});
