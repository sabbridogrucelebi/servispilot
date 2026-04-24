import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, View, Dimensions, Easing } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

const { width, height } = Dimensions.get('window');
const PARTICLE_COUNT = 40;

export default function SpaceWaves() {
    const rotation = useRef(new Animated.Value(0)).current;
    const pulse = useRef(new Animated.Value(0)).current;

    useEffect(() => {
        // Sürekli yavaş dönüş animasyonu
        Animated.loop(
            Animated.timing(rotation, {
                toValue: 1,
                duration: 40000, // 40 saniyede bir tam tur
                easing: Easing.linear,
                useNativeDriver: true,
            })
        ).start();

        // Nefes alma / dalga animasyonu
        Animated.loop(
            Animated.sequence([
                Animated.timing(pulse, { toValue: 1, duration: 4000, easing: Easing.inOut(Easing.ease), useNativeDriver: true }),
                Animated.timing(pulse, { toValue: 0, duration: 4000, easing: Easing.inOut(Easing.ease), useNativeDriver: true }),
            ])
        ).start();
    }, []);

    const spin = rotation.interpolate({
        inputRange: [0, 1],
        outputRange: ['0deg', '360deg']
    });
    
    const spinReverse = rotation.interpolate({
        inputRange: [0, 1],
        outputRange: ['360deg', '0deg']
    });

    const scale = pulse.interpolate({
        inputRange: [0, 1],
        outputRange: [0.95, 1.05]
    });

    const renderParticles = (count, color, size, offsetRadius) => {
        const particles = [];
        for (let i = 0; i < count; i++) {
            const angle = (i * (360 / count)) * (Math.PI / 180);
            const x = Math.cos(angle) * offsetRadius;
            const y = Math.sin(angle) * offsetRadius;
            particles.push(
                <View 
                    key={i} 
                    style={[
                        styles.particle, 
                        { backgroundColor: color, width: size, height: size, borderRadius: size / 2, transform: [{ translateX: x }, { translateY: y }] }
                    ]} 
                />
            );
        }
        return particles;
    };

    return (
        <View style={styles.container} pointerEvents="none">
            {/* Arka plan derin uzay gece mavisi */}
            <LinearGradient colors={['#040B16', '#0A1526', '#02050A']} style={StyleSheet.absoluteFillObject} />
            
            <Animated.View style={[styles.center, { transform: [{ rotate: spin }, { scale }] }]}>
                {/* Farklı yörüngelerde dönen noktalı dalgalar */}
                {renderParticles(16, '#3B82F6', 3, width * 0.3)} {/* Mavi */}
                {renderParticles(24, '#10B981', 2, width * 0.45)} {/* Yeşil */}
                {renderParticles(32, '#8B5CF6', 2.5, width * 0.6)} {/* Mor */}
                {renderParticles(12, '#F59E0B', 4, width * 0.2)} {/* Turuncu */}
                {renderParticles(40, '#ffffff', 1.5, width * 0.8)} {/* Beyaz uzak yıldızlar */}
            </Animated.View>

            <Animated.View style={[styles.center, { transform: [{ rotate: spinReverse }, { scale }] }]}>
                {/* Ters yöne dönen ekstra dalgalar */}
                {renderParticles(20, '#06B6D4', 2, width * 0.5)} {/* Turkuaz */}
                {renderParticles(15, '#EC4899', 3, width * 0.35)} {/* Pembe */}
            </Animated.View>
            
            {/* Ortayı hafif aydınlatan çok hafif bir gradient hissi için bir view */}
            <View style={styles.glow} />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        ...StyleSheet.absoluteFillObject,
        overflow: 'hidden',
        backgroundColor: '#040B16',
    },
    center: {
        position: 'absolute',
        top: '50%',
        left: '50%',
        width: 0,
        height: 0,
    },
    particle: {
        position: 'absolute',
        shadowColor: '#ffffff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.8,
        shadowRadius: 4,
        elevation: 5,
    },
    glow: {
        position: 'absolute',
        top: '50%',
        left: '50%',
        width: width * 1.5,
        height: width * 1.5,
        marginLeft: -width * 0.75,
        marginTop: -width * 0.75,
        borderRadius: width * 0.75,
        backgroundColor: 'rgba(59, 130, 246, 0.05)',
    }
});
