import React, { useEffect, useRef, useMemo } from 'react';
import { Animated, StyleSheet, View, Dimensions, Easing } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';

const { width, height } = Dimensions.get('window');

// Gerçekte 10000 view kasmaya neden olur, bu yüzden sayıyı artırıp boyutları küçülterek illüzyonu artırıyoruz.
const STAR_COUNT = 500;

const ShootingStar = ({ id }) => {
    const anim = useRef(new Animated.Value(0)).current;
    // Generate random values once per star instance
    const randomStartX = useMemo(() => Math.random() * (width * 1.5) - width * 0.25, []);
    const randomStartY = useMemo(() => Math.random() * -200 - 100, []);
    const randomDelay = useMemo(() => Math.random() * 5000 + id * 300, []);
    const randomDuration = useMemo(() => Math.random() * 800 + 1000, []);
    const randomScale = useMemo(() => Math.random() * 0.5 + 0.5, []);

    useEffect(() => {
        const shoot = () => {
            anim.setValue(0);
            Animated.timing(anim, {
                toValue: 1,
                duration: randomDuration,
                easing: Easing.linear,
                useNativeDriver: true
            }).start(() => {
                setTimeout(shoot, 1000 + Math.random() * 5000);
            });
        };
        setTimeout(shoot, randomDelay);
    }, []);

    const translateX = anim.interpolate({ inputRange: [0, 1], outputRange: [randomStartX, randomStartX - width * 1.5] });
    const translateY = anim.interpolate({ inputRange: [0, 1], outputRange: [randomStartY, randomStartY + height * 1.5] });
    const opacity = anim.interpolate({ inputRange: [0, 0.1, 0.8, 1], outputRange: [0, 1, 1, 0] });

    return (
        <Animated.View 
            style={[
                styles.shootingStar, 
                { transform: [{ translateX }, { translateY }, { rotate: '-45deg' }, { scale: randomScale }], opacity }
            ]} 
        />
    );
};

export default function SpaceWaves() {
    const twinkle = useRef(new Animated.Value(0)).current;
    
    useEffect(() => {
        Animated.loop(
            Animated.sequence([
                Animated.timing(twinkle, { toValue: 1, duration: 2500, easing: Easing.inOut(Easing.ease), useNativeDriver: true }),
                Animated.timing(twinkle, { toValue: 0, duration: 2500, easing: Easing.inOut(Easing.ease), useNativeDriver: true }),
            ])
        ).start();
    }, []);

    const stars = useMemo(() => {
        const arr = [];
        for (let i = 0; i < STAR_COUNT; i++) {
            arr.push({
                x: Math.random() * width,
                y: Math.random() * height,
                size: Math.random() * 2.5 + 0.5, // 0.5 to 3.0 (büyüklü küçüklü)
                opacity: Math.random() * 0.9 + 0.1,
                isTwinkling: Math.random() > 0.4,
                color: Math.random() > 0.8 ? '#E0F2FE' : (Math.random() > 0.6 ? '#FEF08A' : '#ffffff')
            });
        }
        return arr;
    }, []);

    const twinkleOpacity = twinkle.interpolate({
        inputRange: [0, 1],
        outputRange: [0.1, 1]
    });

    return (
        <View style={styles.container} pointerEvents="none">
            {/* Gece mavisi gradyan arka plan */}
            <LinearGradient colors={['#020617', '#0F172A', '#1E3A8A']} style={StyleSheet.absoluteFillObject} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} />
            
            <View style={StyleSheet.absoluteFillObject}>
                {stars.map((star, i) => (
                    <Animated.View 
                        key={i} 
                        style={[
                            styles.star, 
                            { 
                                left: star.x, 
                                top: star.y, 
                                width: star.size, 
                                height: star.size, 
                                borderRadius: star.size / 2,
                                backgroundColor: star.color,
                                opacity: star.isTwinkling ? twinkleOpacity : star.opacity
                            }
                        ]} 
                    />
                ))}
            </View>
            
            {/* Kayan Yıldızlar (Toplam 15 adet, farklı boyut, konum ve hızlarda) */}
            {[...Array(15)].map((_, i) => (
                <ShootingStar key={`shooting-star-${i}`} id={i} />
            ))}

            {/* Merkez parlaklık illüzyonu */}
            <View style={styles.glow} />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        ...StyleSheet.absoluteFillObject,
        overflow: 'hidden',
        backgroundColor: '#020617',
    },
    star: {
        position: 'absolute',
        shadowColor: '#ffffff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.8,
        shadowRadius: 2,
        elevation: 2,
    },
    shootingStar: {
        position: 'absolute',
        top: 0,
        left: 0,
        width: 100,
        height: 2,
        backgroundColor: 'rgba(255,255,255,0.8)',
        shadowColor: '#ffffff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 1,
        shadowRadius: 4,
        elevation: 5,
    },
    glow: {
        position: 'absolute',
        top: '50%',
        left: '50%',
        width: width,
        height: width,
        marginLeft: -width / 2,
        marginTop: -width / 2,
        borderRadius: width / 2,
        backgroundColor: 'rgba(59, 130, 246, 0.08)',
        transform: [{ scale: 1.5 }],
    }
});
