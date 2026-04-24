import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

export default function ActivityScreen({ navigation }) {
    const activities = [
        { id: 1, type: 'Yakıt', title: 'Yakıt Fişi Eklendi', sub: '34ABC123 - ₺1250', time: '10 dk önce', icon: 'gas-station-outline', color: '#3B82F6' },
        { id: 2, type: 'Sefer', title: 'Sefer Tamamlandı', sub: 'Fabrika Servisi (Sabah)', time: '1 saat önce', icon: 'map-marker-check-outline', color: '#10B981' },
        { id: 3, type: 'Araç', title: 'Araç Bakıma Girdi', sub: '34XYZ789 - Rutin Bakım', time: '2 saat önce', icon: 'wrench-outline', color: '#F59E0B' },
        { id: 4, type: 'Personel', title: 'Yeni Sürücü Eklendi', sub: 'Ahmet Yılmaz', time: 'Dün', icon: 'account-plus-outline', color: '#6366F1' },
    ];

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <Icon name="bell-outline" size={28} color="#fff" />
                        <Text style={s.hTitle}>Aktivite</Text>
                        <TouchableOpacity><Icon name="filter-variant" size={28} color="#fff" /></TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView contentContainerStyle={s.content} showsVerticalScrollIndicator={false}>
                <View style={s.summaryCard}>
                    <View style={s.sumCol}>
                        <Text style={s.sumVal}>12</Text>
                        <Text style={s.sumLbl}>Bugün</Text>
                    </View>
                    <View style={s.divider} />
                    <View style={s.sumCol}>
                        <Text style={s.sumVal}>84</Text>
                        <Text style={s.sumLbl}>Bu Hafta</Text>
                    </View>
                    <View style={s.divider} />
                    <View style={s.sumCol}>
                        <Text style={[s.sumVal, {color: '#3B82F6'}]}>342</Text>
                        <Text style={s.sumLbl}>Bu Ay</Text>
                    </View>
                </View>

                <Text style={s.sectionTitle}>Tüm Hareketler</Text>

                {activities.map((item, index) => (
                    <View key={item.id} style={s.itemRow}>
                        <View style={s.timeline}>
                            <View style={[s.dot, { backgroundColor: item.color }]} />
                            {index !== activities.length - 1 && <View style={s.line} />}
                        </View>
                        <View style={s.card}>
                            <View style={s.cardHeader}>
                                <View style={s.badge}>
                                    <Icon name={item.icon} size={16} color={item.color} />
                                    <Text style={[s.badgeText, { color: item.color }]}>{item.type}</Text>
                                </View>
                                <Text style={s.time}>{item.time}</Text>
                            </View>
                            <Text style={s.title}>{item.title}</Text>
                            <Text style={s.sub}>{item.sub}</Text>
                        </View>
                    </View>
                ))}
            </ScrollView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 32, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    hRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    hTitle: { fontSize: 24, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    content: { padding: 20, paddingBottom: 120, marginTop: -20 },
    summaryCard: { flexDirection: 'row', backgroundColor: '#fff', borderRadius: 24, padding: 24, marginBottom: 32, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 4 },
    sumCol: { flex: 1, alignItems: 'center' },
    sumVal: { fontSize: 24, fontWeight: '900', color: '#0F172A' },
    sumLbl: { fontSize: 13, color: '#64748B', fontWeight: '600', marginTop: 4 },
    divider: { width: 1, backgroundColor: '#E2E8F0', marginHorizontal: 10 },
    sectionTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A', marginBottom: 20, marginLeft: 4 },
    itemRow: { flexDirection: 'row', marginBottom: 16 },
    timeline: { width: 24, alignItems: 'center', marginRight: 12 },
    dot: { width: 12, height: 12, borderRadius: 6, marginTop: 24, zIndex: 10, borderWidth: 2, borderColor: '#fff' },
    line: { position: 'absolute', top: 32, bottom: -20, width: 2, backgroundColor: '#E2E8F0', zIndex: 1 },
    card: { flex: 1, backgroundColor: '#fff', borderRadius: 20, padding: 16, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 10, elevation: 2 },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 },
    badge: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#F8FAFC', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8, borderWidth: 1, borderColor: '#F1F5F9' },
    badgeText: { fontSize: 12, fontWeight: '800' },
    time: { fontSize: 12, color: '#94A3B8', fontWeight: '600' },
    title: { fontSize: 16, fontWeight: '800', color: '#0F172A', marginBottom: 4 },
    sub: { fontSize: 14, color: '#64748B', fontWeight: '500' },
});
