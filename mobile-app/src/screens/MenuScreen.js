import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

const menuItems = [
    { id: 1, icon: 'home-variant-outline', label: 'Ana Sayfa', sub: 'Genel Bakış', color: '#3B82F6', route: 'HomeTab' },
    { id: 2, icon: 'car-multiple', label: 'Araçlar', sub: 'Filo Yönetimi', color: '#10B981', route: 'Vehicles' },
    { id: 3, icon: 'crosshairs-gps', label: 'Araç Takip', sub: 'Canlı İzleme', color: '#F59E0B' },
    { id: 4, icon: 'account-group-outline', label: 'Personeller', sub: 'Personel Yönetimi', color: '#6366F1', route: 'Personnel' },
    { id: 5, icon: 'wrench-outline', label: 'Bakım / Tamir', sub: 'Servis ve Bakım', color: '#EF4444' },
    { id: 6, icon: 'gas-station-outline', label: 'Yakıt', sub: 'Yakıt Takibi', color: '#F97316' },
    { id: 7, icon: 'alert-decagram-outline', label: 'Trafik Cezaları', sub: 'Yasal Uyumluluk', color: '#E11D48' },
    { id: 8, icon: 'calendar-clock-outline', label: 'Puantaj / Sefer', sub: 'Operasyon Kayıtları', color: '#06B6D4', route: 'Trips' },
    { id: 9, icon: 'cash-multiple', label: 'Maaşlar', sub: 'Finansal Kayıtlar', color: '#10B981' },
    { id: 10, icon: 'office-building-outline', label: 'Müşteriler', sub: 'Müşteri Yönetimi', color: '#8B5CF6', route: 'Customers' },
    { id: 11, icon: 'chart-pie', label: 'Raporlar', sub: 'Analiz Merkezi', color: '#EC4899', route: 'Reports' },
    { id: 12, icon: 'shield-account-outline', label: 'Kullanıcılar', sub: 'Erişim Kontrolü', color: '#64748B' },
    { id: 13, icon: 'cog-outline', label: 'Ayarlar', sub: 'Sistem Yapılandırması', color: '#334155' },
];

export default function MenuScreen({ navigation }) {
    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <Text style={s.headerTitle}>Menü</Text>
                    <Text style={s.headerSub}>Tüm modüller</Text>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={s.list}>
                {menuItems.map(item => (
                    <TouchableOpacity
                        key={item.id}
                        style={s.card}
                        activeOpacity={0.85}
                        onPress={() => item.route && navigation.navigate(item.route)}
                    >
                        {/* Kutu kaldırıldı, sadece ikon bırakıldı */}
                        <View style={s.iconWrapper}>
                            <Icon name={item.icon} size={30} color={item.color} />
                        </View>
                        <View style={s.info}>
                            <Text style={s.label}>{item.label}</Text>
                            <Text style={s.sub}>{item.sub}</Text>
                        </View>
                        <Icon name="chevron-right" size={24} color="#CBD5E1" />
                    </TouchableOpacity>
                ))}
            </ScrollView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerTitle: { fontSize: 32, fontWeight: '900', color: '#fff', paddingTop: 16, letterSpacing: -0.5 },
    headerSub: { fontSize: 14, color: 'rgba(255,255,255,0.7)', fontWeight: '500', marginTop: 4 },
    list: { padding: 20, paddingBottom: 120 },
    card: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 20, padding: 18, marginBottom: 12, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.04, shadowRadius: 12, elevation: 2 },
    iconWrapper: { marginRight: 18, alignItems: 'center', justifyContent: 'center' },
    info: { flex: 1 },
    label: { fontSize: 16, fontWeight: '800', color: '#0F172A', marginBottom: 2 },
    sub: { fontSize: 12, color: '#64748B', fontWeight: '500' },
});
