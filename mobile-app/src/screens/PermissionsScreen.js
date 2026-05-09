import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ActivityIndicator, Image, ScrollView, Platform } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as Location from 'expo-location';
import * as Notifications from 'expo-notifications';
import { Camera } from 'expo-camera';
import * as MediaLibrary from 'expo-media-library';

export default function PermissionsScreen({ onComplete }) {
    const [step, setStep] = useState(0);
    const [loading, setLoading] = useState(false);
    const insets = useSafeAreaInsets();

    const permissions = [
        {
            title: "Konum Erişimi",
            desc: "Sefer takibi, rota oluşturma ve canlı araç izleme için konumunuza erişmemiz gerekiyor.",
            icon: "map-marker-radius",
            request: async () => {
                let { status } = await Location.requestForegroundPermissionsAsync();
                if (status === 'granted') {
                    // Try asking for background immediately if foreground is granted (may not prompt on all OS versions)
                    await Location.requestBackgroundPermissionsAsync();
                }
                return true; // We continue even if denied, usually.
            }
        },
        {
            title: "Bildirimler",
            desc: "Yeni sefer atamaları, ceza ve bakım uyarıları gibi önemli gelişmeleri size anında bildirebilmemiz için gereklidir.",
            icon: "bell-ring-outline",
            request: async () => {
                await Notifications.requestPermissionsAsync();
                return true;
            }
        },
        {
            title: "Kamera Erişimi",
            desc: "Araç hasar fotoğrafları, belge ve fiş yüklemeleri için kameranızı kullanabilmemiz gerekiyor.",
            icon: "camera-outline",
            request: async () => {
                await Camera.requestCameraPermissionsAsync();
                return true;
            }
        },
        {
            title: "Galeri ve Dosyalar",
            desc: "Daha önce çektiğiniz fotoğrafları ve belgeleri (ruhsat, poliçe vb.) sisteme yükleyebilmeniz için gereklidir.",
            icon: "folder-image",
            request: async () => {
                await MediaLibrary.requestPermissionsAsync();
                return true;
            }
        }
    ];

    const handleNext = async () => {
        setLoading(true);
        const currentPerm = permissions[step];
        try {
            await currentPerm.request();
        } catch (e) {
            console.warn("Permission error:", e);
        }
        setLoading(false);

        if (step < permissions.length - 1) {
            setStep(step + 1);
        } else {
            if (onComplete) onComplete();
        }
    };

    const currentPerm = permissions[step];

    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0F172A', '#1E3A8A']} style={StyleSheet.absoluteFillObject} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} />
            
            <SafeAreaView style={{ flex: 1 }} edges={['top']}>
                <ScrollView 
                    contentContainerStyle={[s.scrollContent, { paddingBottom: Math.max(insets.bottom, 24) + 20 }]}
                    showsVerticalScrollIndicator={false}
                    bounces={false}
                >
                    <View style={s.header}>
                        <View style={s.logoWrap}>
                            <Image source={require('../../assets/icon.png')} style={{width: 60, height: 60, borderRadius: 16}} />
                        </View>
                        <Text style={s.title}>ServisPilot Deneyimi</Text>
                        <Text style={s.subtitle}>Uygulamayı tam kapasiteyle kullanabilmeniz için birkaç izne ihtiyacımız var. Her adımda tercihinize göre izin verebilir veya atlayabilirsiniz.</Text>
                    </View>

                    <View style={s.card}>
                        <View style={s.iconWrap}>
                            <Icon name={currentPerm.icon} size={64} color="#3B82F6" />
                        </View>
                        <Text style={s.permTitle}>{currentPerm.title}</Text>
                        <Text style={s.permDesc}>{currentPerm.desc}</Text>

                        <View style={s.progressWrap}>
                            {permissions.map((_, i) => (
                                <View key={i} style={[s.dot, step === i && s.dotActive, step > i && s.dotDone]} />
                            ))}
                        </View>
                    </View>

                    <View style={s.footer}>
                        <TouchableOpacity style={[s.btn, loading && s.btnDisabled]} onPress={handleNext} disabled={loading}>
                            {loading ? <ActivityIndicator color="#fff" /> : <Text style={s.btnTxt}>Devam Et</Text>}
                        </TouchableOpacity>
                        <TouchableOpacity 
                            style={{ alignItems: 'center', paddingVertical: 12 }} 
                            onPress={() => { if (onComplete) onComplete(); }}
                        >
                            <Text style={{ color: '#94A3B8', fontSize: 14, fontWeight: '600' }}>Şimdilik Atla</Text>
                        </TouchableOpacity>
                    </View>
                </ScrollView>
            </SafeAreaView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#020617' },
    scrollContent: { flexGrow: 1, justifyContent: 'space-between', padding: 24 },
    header: { alignItems: 'center', marginTop: 20 },
    logoWrap: { width: 80, height: 80, backgroundColor: '#1E293B', borderRadius: 24, alignItems: 'center', justifyContent: 'center', marginBottom: 20, borderWidth: 1, borderColor: '#3B82F6', overflow: 'hidden' },
    title: { fontSize: 24, fontWeight: '900', color: '#fff', marginBottom: 10 },
    subtitle: { fontSize: 14, color: '#94A3B8', textAlign: 'center', lineHeight: 22, paddingHorizontal: 20 },
    
    card: { backgroundColor: '#fff', borderRadius: 32, padding: 30, alignItems: 'center', shadowColor: '#3B82F6', shadowOffset: { width: 0, height: 20 }, shadowOpacity: 0.15, shadowRadius: 40, elevation: 20 },
    iconWrap: { width: 120, height: 120, borderRadius: 60, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center', marginBottom: 24 },
    permTitle: { fontSize: 22, fontWeight: '900', color: '#0F172A', marginBottom: 12 },
    permDesc: { fontSize: 14, color: '#64748B', textAlign: 'center', lineHeight: 22, marginBottom: 30 },
    
    progressWrap: { flexDirection: 'row', gap: 8 },
    dot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#E2E8F0' },
    dotActive: { width: 24, backgroundColor: '#3B82F6' },
    dotDone: { backgroundColor: '#3B82F6', opacity: 1 },

    footer: { marginBottom: 20 },
    btn: { backgroundColor: '#3B82F6', paddingVertical: 18, borderRadius: 20, alignItems: 'center', marginBottom: 16 },
    btnDisabled: { opacity: 0.7 },
    btnTxt: { color: '#fff', fontSize: 16, fontWeight: '800' }
});
