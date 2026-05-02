import React, { useEffect, useRef } from 'react';
import { View, Text, StyleSheet, Animated, Easing, Dimensions, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import Svg, { Path, Circle, Defs, LinearGradient as SvgLinearGradient, Stop } from 'react-native-svg';
import SpaceWaves from './SpaceWaves';

const { width, height } = Dimensions.get('window');

export default function GlobalSplashScreen({ onFinish }) {
    const scaleAnim = useRef(new Animated.Value(0.4)).current;
    const opacityAnim = useRef(new Animated.Value(0)).current;
    const rotateAnim = useRef(new Animated.Value(0)).current;
    const rocketTranslateY = useRef(new Animated.Value(0)).current; // Uzaya uçuş için Y ekseni
    const textTranslateX = useRef(new Animated.Value(-150)).current;
    const textOpacity = useRef(new Animated.Value(0)).current;
    const bgOpacity = useRef(new Animated.Value(1)).current;

    useEffect(() => {
        // Tam Güvenli Animasyon Dizilimi
        Animated.sequence([
            Animated.parallel([
                Animated.timing(opacityAnim, { toValue: 1, duration: 600, useNativeDriver: true }),
                Animated.spring(scaleAnim, { toValue: 1, friction: 5, tension: 40, useNativeDriver: true }),
                Animated.timing(rotateAnim, { toValue: 1, duration: 1200, easing: Easing.out(Easing.cubic), useNativeDriver: true })
            ]),
            Animated.parallel([
                Animated.timing(textOpacity, { toValue: 1, duration: 600, useNativeDriver: true }),
                Animated.timing(textTranslateX, { toValue: 0, duration: 600, easing: Easing.out(Easing.back(1.5)), useNativeDriver: true })
            ]),
            // 3. Ekranda Kalma Süresi (Biraz kısaltıldı ki hemen uçsun)
            Animated.delay(1000),
            // 4. Uzaya Fırlama (Launch!) ve Ekranın Kararması
            Animated.parallel([
                // Roket hızlanarak (Easing.poly) yukarı fırlar (-height kadar)
                Animated.timing(rocketTranslateY, { 
                    toValue: -height, 
                    duration: 800, 
                    easing: Easing.in(Easing.poly(3)), 
                    useNativeDriver: true 
                }),
                // Roket uçarken yazılar yavaşça kaybolur
                Animated.timing(textOpacity, { 
                    toValue: 0, 
                    duration: 400, 
                    delay: 200, 
                    useNativeDriver: true 
                }),
                // Son olarak tüm ekran kararır ve App.js'e geçilir
                Animated.timing(bgOpacity, { 
                    toValue: 0, 
                    duration: 600, 
                    delay: 300, 
                    useNativeDriver: true 
                })
            ])
        ]).start(() => {
            if (onFinish) onFinish();
        });
    }, []);

    const spin = rotateAnim.interpolate({
        inputRange: [0, 1],
        outputRange: ['-180deg', '0deg']
    });

    return (
        <Animated.View style={[styles.container, { opacity: bgOpacity }]}>
            <LinearGradient colors={['#020617', '#0F172A', '#1E293B']} style={StyleSheet.absoluteFillObject} />
            <SpaceWaves />
            
            <View style={styles.content}>
                {/* 3D Logo Konteyneri (Arkaplansız, Büyük) */}
                <Animated.View style={[
                    styles.logoWrapper, 
                    { opacity: opacityAnim, transform: [{ translateY: rocketTranslateY }, { scale: scaleAnim }, { rotate: spin }] }
                ]}>
                    <View style={styles.logoShadow1} />

                    <View style={styles.logoBox}>
                        {/* %100 Uyumlu Saf SVG 3D Roket Çizimi (Büyütüldü) */}
                        <Svg width="140" height="168" viewBox="0 0 100 120">
                            <Defs>
                                <SvgLinearGradient id="flame" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <Stop offset="0%" stopColor="#FCD34D" />
                                    <Stop offset="50%" stopColor="#F97316" />
                                    <Stop offset="100%" stopColor="#B91C1C" />
                                </SvgLinearGradient>
                            </Defs>

                            {/* Alev (Flame) */}
                            <Path d="M40,90 Q50,130 60,90 Z" fill="url(#flame)" />
                            <Path d="M45,90 Q50,115 55,90 Z" fill="#FEF08A" />

                            {/* Sol Kanat (Mor) */}
                            <Path d="M30,60 C20,70 15,85 15,90 L30,85 Z" fill="#9333EA" />
                            {/* Sağ Kanat (Mor) */}
                            <Path d="M70,60 C80,70 85,85 85,90 L70,85 Z" fill="#7E22CE" />
                            {/* Orta Kuyruk Kanadı (Koyu Mor) */}
                            <Path d="M47,75 L53,75 L53,95 L47,95 Z" fill="#6B21A8" />

                            {/* Motor Egzozu (Gümüş) */}
                            <Path d="M40,85 L36,92 L64,92 L60,85 Z" fill="#94A3B8" />
                            <Path d="M36,90 L36,93 L64,93 L64,90 Z" fill="#64748B" />

                            {/* Ana Gövde (Beyaz/Gümüş 3D) */}
                            <Path d="M50,10 C80,30 80,70 70,85 L30,85 C20,70 20,30 50,10 Z" fill="#F8FAFC" />
                            {/* Gövde Gölgesi (Sağ Taraf - 3D Hissi İçin) */}
                            <Path d="M50,10 C80,30 80,70 70,85 L50,85 Z" fill="#E2E8F0" />

                            {/* Burun (Nose Cone - Mor) */}
                            <Path d="M50,10 C65,20 68,30 68,35 L32,35 C32,30 35,20 50,10 Z" fill="#A855F7" />
                            {/* Burun Gölgesi (Sağ Taraf - Koyu Mor) */}
                            <Path d="M50,10 C65,20 68,30 68,35 L50,35 Z" fill="#9333EA" />

                            {/* Panel Çizgileri */}
                            <Path d="M28,65 Q50,70 72,65" fill="none" stroke="#CBD5E1" strokeWidth="1.5" />
                            <Path d="M32,35 L68,35" fill="none" stroke="#6B21A8" strokeWidth="1.5" />

                            {/* Pencere Çerçevesi (Gümüş) */}
                            <Circle cx="50" cy="50" r="14" fill="#CBD5E1" />
                            <Circle cx="50" cy="50" r="14" fill="none" stroke="#94A3B8" strokeWidth="1" />
                            
                            {/* Pencere Camı (Koyu Mavi/Uzay) */}
                            <Circle cx="50" cy="50" r="10" fill="#1E3A8A" />
                            {/* Cam Yansıması (Parlaklık - Güvenli opacity kullanımı) */}
                            <Circle cx="47" cy="47" r="3" fill="#FFFFFF" opacity="0.5" />
                        </Svg>
                    </View>
                </Animated.View>

                {/* Yazı Bölümü (Soldan Sağa Animasyonlu) */}
                <Animated.View style={{ 
                    opacity: textOpacity, 
                    transform: [{ translateX: textTranslateX }],
                    alignItems: 'center'
                }}>
                    <View style={styles.brandRow}>
                        <Text style={styles.brandNameWhite}>Filo</Text>
                        <Text style={styles.brandNamePurple}>MERKEZ</Text>
                    </View>
                    <Text style={styles.brandSubtitle}>YENİ NESİL FİLO YÖNETİMİ</Text>
                </Animated.View>
            </View>
        </Animated.View>
    );
}

const styles = StyleSheet.create({
    container: {
        ...StyleSheet.absoluteFillObject,
        zIndex: 9999, // En üstte durması için
    },
    content: {
        flex: 1,
        alignItems: 'center',
        justifyContent: 'center',
    },
    logoWrapper: {
        width: 180,
        height: 180,
        marginBottom: 20,
        alignItems: 'center',
        justifyContent: 'center',
        position: 'relative'
    },
    logoBox: {
        width: 180,
        height: 180,
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 10,
        backgroundColor: 'transparent', // Gri arka plan tamamen kaldırıldı!
        // Gölgeler kutuya değil, roketin kendisine etki etsin diye kutudan kaldırıldı.
    },
    logoShadow1: {
        position: 'absolute',
        top: 20,
        width: 120,
        height: 120,
        borderRadius: 60,
        backgroundColor: '#9333EA', // Hafif Mor Parıltı
        opacity: 0.3,
        zIndex: 1,
        ...Platform.select({
            web: { filter: 'blur(30px)' }
        })
    },
    brandRow: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    brandNameWhite: {
        fontSize: 56,
        fontWeight: '900',
        color: '#FFFFFF',
        letterSpacing: -1.5,
        textShadowColor: '#070D18', // Koyu Gölge
        textShadowOffset: { width: 3, height: 4 }, // Stark block shadow (3D Extrude effect)
        textShadowRadius: 0, // Sıfır blur = Katı 3D Blok!
    },
    brandNamePurple: {
        fontSize: 56,
        fontWeight: '900',
        color: '#9333EA', // Roketin başlığı ile tam uyumlu parlak mor
        letterSpacing: -1.5,
        textShadowColor: '#070D18', // Koyu Gölge
        textShadowOffset: { width: 3, height: 4 }, // Stark block shadow (3D Extrude effect)
        textShadowRadius: 0, // Sıfır blur = Katı 3D Blok!
    },
    brandSubtitle: {
        fontSize: 12,
        color: '#94A3B8',
        fontWeight: '700',
        marginTop: 10,
        textTransform: 'uppercase',
        letterSpacing: 6,
        opacity: 0.9,
    }
});
