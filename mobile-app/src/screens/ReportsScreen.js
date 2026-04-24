import React from 'react';
import { View, Text, StyleSheet, ScrollView } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import Svg, { Circle, G } from 'react-native-svg';

export default function ReportsScreen({ navigation }) {
    // Örnek veri
    const data = {
        totalExpense: 124500,
        fuel: 65000,
        maintenance: 35000,
        other: 24500
    };

    const radius = 60;
    const strokeWidth = 16;
    const circumference = 2 * Math.PI * radius;
    
    const fuelPct = data.fuel / data.totalExpense;
    const maintPct = data.maintenance / data.totalExpense;
    const otherPct = data.other / data.totalExpense;

    const fuelStroke = circumference * fuelPct;
    const maintStroke = circumference * maintPct;
    const otherStroke = circumference * otherPct;

    const maintOffset = circumference - fuelStroke;
    const otherOffset = maintOffset - maintStroke;

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <Icon name="chart-pie" size={28} color="#fff" />
                        <Text style={s.hTitle}>Finansal Raporlar</Text>
                        <Icon name="calendar-month-outline" size={26} color="#fff" />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView contentContainerStyle={s.content} showsVerticalScrollIndicator={false}>
                
                <View style={s.chartCard}>
                    <Text style={s.cardTitle}>Gider Dağılımı</Text>
                    <Text style={s.cardSub}>Nisan 2026</Text>
                    
                    <View style={s.chartBox}>
                        <Svg width="160" height="160" viewBox="0 0 160 160">
                            <G rotation="-90" origin="80, 80">
                                {/* Diğer */}
                                <Circle cx="80" cy="80" r={radius} stroke="#F59E0B" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${otherStroke} ${circumference}`} strokeDashoffset={-otherOffset} strokeLinecap="round" />
                                {/* Bakım */}
                                <Circle cx="80" cy="80" r={radius} stroke="#10B981" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${maintStroke} ${circumference}`} strokeDashoffset={-maintOffset} strokeLinecap="round" />
                                {/* Yakıt */}
                                <Circle cx="80" cy="80" r={radius} stroke="#3B82F6" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${fuelStroke} ${circumference}`} strokeLinecap="round" />
                            </G>
                        </Svg>
                        <View style={s.chartCenter}>
                            <Text style={s.centerVal}>₺124k</Text>
                            <Text style={s.centerLbl}>Toplam</Text>
                        </View>
                    </View>

                    <View style={s.legend}>
                        <LegendItem color="#3B82F6" label="Yakıt" value="₺65.000" pct="52%" />
                        <LegendItem color="#10B981" label="Bakım" value="₺35.000" pct="28%" />
                        <LegendItem color="#F59E0B" label="Diğer" value="₺24.500" pct="20%" />
                    </View>
                </View>

            </ScrollView>
        </View>
    );
}

function LegendItem({ color, label, value, pct }) {
    return (
        <View style={s.lItem}>
            <View style={s.lLeft}>
                <View style={[s.lDot, { backgroundColor: color }]} />
                <Text style={s.lLbl}>{label}</Text>
            </View>
            <View style={s.lRight}>
                <Text style={s.lPct}>{pct}</Text>
                <Text style={s.lVal}>{value}</Text>
            </View>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 32, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    hRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    hTitle: { fontSize: 24, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    content: { padding: 20, paddingBottom: 120, marginTop: -16 },
    chartCard: { backgroundColor: '#fff', borderRadius: 28, padding: 24, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 5 },
    cardTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    cardSub: { fontSize: 14, color: '#94A3B8', fontWeight: '500', marginTop: 2, marginBottom: 24 },
    chartBox: { alignItems: 'center', justifyContent: 'center', height: 160, marginBottom: 32 },
    chartCenter: { position: 'absolute', alignItems: 'center', justifyContent: 'center' },
    centerVal: { fontSize: 24, fontWeight: '900', color: '#0F172A' },
    centerLbl: { fontSize: 13, color: '#64748B', fontWeight: '600' },
    legend: { gap: 16 },
    lItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    lLeft: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    lDot: { width: 12, height: 12, borderRadius: 6 },
    lLbl: { fontSize: 16, color: '#475569', fontWeight: '700' },
    lRight: { alignItems: 'flex-end' },
    lPct: { fontSize: 13, color: '#94A3B8', fontWeight: '800', marginBottom: 2 },
    lVal: { fontSize: 16, fontWeight: '900', color: '#0F172A' },
});
