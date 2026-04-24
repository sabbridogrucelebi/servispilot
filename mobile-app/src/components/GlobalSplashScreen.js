import React, { useEffect, useRef, useState } from 'react';
import { View, Text, StyleSheet, Animated, Easing } from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import SpaceWaves from './SpaceWaves';

export default function GlobalSplashScreen({ onFinish }) {
    const servispilotOpacity = useRef(new Animated.Value(0)).current;
    const servispilotScale = useRef(new Animated.Value(0.8)).current;
    
    const firmOpacity = useRef(new Animated.Value(0)).current;
    const firmScale = useRef(new Animated.Value(0.9)).current;
    
    const bgOpacity = useRef(new Animated.Value(1)).current;

    useEffect(() => {
        // 1. ServisPilot Fade In ve Büyüme
        Animated.parallel([
            Animated.timing(servispilotOpacity, { toValue: 1, duration: 1500, useNativeDriver: true }),
            Animated.timing(servispilotScale, { toValue: 1.1, duration: 4000, easing: Easing.out(Easing.ease), useNativeDriver: true })
        ]).start();

        // 2. 4 saniye sonra ServisPilot Fade Out, Firma İsmi Fade In
        setTimeout(() => {
            Animated.sequence([
                Animated.timing(servispilotOpacity, { toValue: 0, duration: 1000, useNativeDriver: true }),
                Animated.parallel([
                    Animated.timing(firmOpacity, { toValue: 1, duration: 1500, useNativeDriver: true }),
                    Animated.timing(firmScale, { toValue: 1, duration: 4000, easing: Easing.out(Easing.ease), useNativeDriver: true })
                ])
            ]).start();
        }, 4000);

        // 3. 10. saniyede (toplam) tüm splash ekranı kaybolur ve uygulama açılır
        setTimeout(() => {
            Animated.timing(bgOpacity, { toValue: 0, duration: 1500, useNativeDriver: true }).start(() => {
                if (onFinish) onFinish();
            });
        }, 10000);

    }, []);

    return (
        <Animated.View style={[styles.container, { opacity: bgOpacity }]}>
            <SpaceWaves />
            
            <View style={styles.content}>
                {/* 1. Aşama: ServisPilot Logosu */}
                <Animated.View style={[styles.absoluteCenter, { opacity: servispilotOpacity, transform: [{ scale: servispilotScale }] }]}>
                    <Icon name="steering" size={90} color="#3B82F6" style={styles.iconGlow} />
                    <Text style={styles.title}>ServisPilot</Text>
                    <Text style={styles.subtitle}>Sistem Başlatılıyor...</Text>
                </Animated.View>

                {/* 2. Aşama: Firma Logosu */}
                <Animated.View style={[styles.absoluteCenter, { opacity: firmOpacity, transform: [{ scale: firmScale }] }]}>
                    <View style={styles.firmCircle}>
                        <Icon name="domain" size={50} color="#000000" />
                    </View>
                    <Text style={styles.firmTitle}>IRMAK TURİZM</Text>
                    <Text style={styles.firmSubtitle}>Kurumsal Filo Yönetimi</Text>
                </Animated.View>
            </View>
        </Animated.View>
    );
}

const styles = StyleSheet.create({
    container: {
        ...StyleSheet.absoluteFillObject,
        backgroundColor: '#040B16',
        zIndex: 9999, // En üstte durması için
    },
    content: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
    },
    absoluteCenter: {
        position: 'absolute',
        alignItems: 'center',
        justifyContent: 'center',
    },
    iconGlow: {
        shadowColor: '#3B82F6',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 1,
        shadowRadius: 30,
        marginBottom: 20,
    },
    title: {
        fontSize: 52,
        fontWeight: '900',
        color: '#ffffff',
        letterSpacing: -1,
        textShadowColor: 'rgba(59, 130, 246, 0.8)',
        textShadowOffset: { width: 0, height: 0 },
        textShadowRadius: 20,
    },
    subtitle: {
        fontSize: 16,
        color: '#3B82F6',
        fontWeight: '700',
        marginTop: 12,
        textTransform: 'uppercase',
        letterSpacing: 4,
    },
    firmCircle: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: '#ffffff',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#ffffff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.5,
        shadowRadius: 30,
        marginBottom: 24,
    },
    firmTitle: {
        fontSize: 40,
        fontWeight: '900',
        color: '#ffffff',
        letterSpacing: 2,
    },
    firmSubtitle: {
        fontSize: 15,
        color: 'rgba(255,255,255,0.7)',
        fontWeight: '600',
        marginTop: 8,
        textTransform: 'uppercase',
        letterSpacing: 4,
    }
});
