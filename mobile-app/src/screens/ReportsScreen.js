import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import Svg, { Circle, G } from 'react-native-svg';
import api from '../api/axios';

export default function ReportsScreen({ navigation }) {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const fetchSummary = async (isRefresh = false) => {
        try {
            if (isRefresh) setRefreshing(true);
            else setLoading(true);
            setError(null);

            const response = await api.get('/v1/finance/summary');
            if (response.data.success) {
                setSummary(response.data.data);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            if (err.response?.status === 403) {
                setError('Bu alanı görüntüleme yetkiniz yok.');
            } else {
                setError('Bağlantı hatası.');
            }
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => {
        fetchSummary();
    }, []);

    const onRefresh = () => {
        fetchSummary(true);
    };

    const formatCurrency = (amount) => {
        if (amount === null || amount === undefined) return '- (Yetki Yok)';
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount);
    };

    const formatShortCurrency = (amount) => {
        if (amount === null || amount === undefined) return '-';
        if (amount === 0) return '₺0';
        if (amount >= 1000) return `₺${(amount / 1000).toFixed(1)}k`;
        return `₺${amount}`;
    };

    if (loading && !refreshing) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#3B82F6" />
                <Text style={{ marginTop: 16, color: '#64748b' }}>Finansal Raporlar Yükleniyor...</Text>
            </SafeAreaView>
        );
    }

    if (error && !refreshing && !summary) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                <Text style={{ color: '#ef4444', marginTop: 12 }}>{error}</Text>
                <TouchableOpacity onPress={() => fetchSummary()} style={s.retryBtn}>
                    <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                </TouchableOpacity>
            </SafeAreaView>
        );
    }

    // Harcama dağılımı hesaplamaları (null kontrolü ile, null ise 0 kabul ederek grafik hatasını önleriz)
    const fuel = summary?.expenses_detail?.fuels || 0;
    const payroll = summary?.expenses_detail?.payrolls || 0;
    const maint = summary?.additional_expenses?.maintenances || 0;
    const penalty = summary?.additional_expenses?.penalties || 0;
    
    // Grafikte genişletilmiş operasyonel giderleri gösteriyoruz
    const totalCalc = fuel + payroll + maint + penalty; 
    const safeTotal = totalCalc > 0 ? totalCalc : 1;

    const radius = 60;
    const strokeWidth = 16;
    const circumference = 2 * Math.PI * radius;
    
    // Yüzdeler
    const payrollPct = payroll / safeTotal;
    const fuelPct = fuel / safeTotal;
    const maintPct = maint / safeTotal;
    const penaltyPct = penalty / safeTotal;

    const payrollStroke = circumference * payrollPct;
    const fuelStroke = circumference * fuelPct;
    const maintStroke = circumference * maintPct;
    const penaltyStroke = circumference * penaltyPct;

    // Offset hesaplamaları
    const fuelOffset = circumference - payrollStroke;
    const maintOffset = fuelOffset - fuelStroke;
    const penaltyOffset = maintOffset - maintStroke;

    const hasAnyPermissionData = summary?.operational_total_expenses !== null && summary?.total_expenses !== null;

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="chevron-left" size={28} color="#ffffff" />
                        </TouchableOpacity>
                        <Text style={s.hTitle}>Finansal Raporlar</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView 
                contentContainerStyle={s.content} 
                showsVerticalScrollIndicator={false}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
            >
                <View style={s.chartCard}>
                    <Text style={s.cardTitle}>Gider Dağılımı (Yetkiniz Dahilinde)</Text>
                    <Text style={s.cardSub}>{summary?.period_human}</Text>
                    
                    {totalCalc > 0 && hasAnyPermissionData ? (
                        <>
                            <View style={s.chartBox}>
                                <Svg width="160" height="160" viewBox="0 0 160 160">
                                    <G rotation="-90" origin="80, 80">
                                        {/* Ceza */}
                                        <Circle cx="80" cy="80" r={radius} stroke="#EC4899" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${penaltyStroke} ${circumference}`} strokeDashoffset={-penaltyOffset} strokeLinecap="round" />
                                        {/* Bakım */}
                                        <Circle cx="80" cy="80" r={radius} stroke="#10B981" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${maintStroke} ${circumference}`} strokeDashoffset={-maintOffset} strokeLinecap="round" />
                                        {/* Yakıt */}
                                        <Circle cx="80" cy="80" r={radius} stroke="#3B82F6" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${fuelStroke} ${circumference}`} strokeDashoffset={-fuelOffset} strokeLinecap="round" />
                                        {/* Maaş */}
                                        <Circle cx="80" cy="80" r={radius} stroke="#8B5CF6" strokeWidth={strokeWidth} fill="transparent" strokeDasharray={`${payrollStroke} ${circumference}`} strokeLinecap="round" />
                                    </G>
                                </Svg>
                                <View style={s.chartCenter}>
                                    <Text style={s.centerVal}>{formatShortCurrency(totalCalc)}</Text>
                                    <Text style={s.centerLbl}>Toplam</Text>
                                </View>
                            </View>

                            <View style={s.legend}>
                                {summary?.expenses_detail?.payrolls !== null && <LegendItem color="#8B5CF6" label="Bordro" value={formatCurrency(payroll)} pct={`${Math.round(payrollPct * 100)}%`} />}
                                {summary?.expenses_detail?.fuels !== null && <LegendItem color="#3B82F6" label="Yakıt" value={formatCurrency(fuel)} pct={`${Math.round(fuelPct * 100)}%`} />}
                                {summary?.additional_expenses?.maintenances !== null && <LegendItem color="#10B981" label="Bakım" value={formatCurrency(maint)} pct={`${Math.round(maintPct * 100)}%`} />}
                                {summary?.additional_expenses?.penalties !== null && <LegendItem color="#EC4899" label="Cezalar" value={formatCurrency(penalty)} pct={`${Math.round(penaltyPct * 100)}%`} />}
                            </View>
                        </>
                    ) : (
                        <View style={s.emptyChart}>
                            <Icon name="chart-pie" size={48} color="#CBD5E1" />
                            <Text style={{color: '#94A3B8', marginTop: 12}}>Bu döneme ait geçerli veri yok veya yetkiniz kısıtlı.</Text>
                        </View>
                    )}
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
    backBtn: { alignItems: 'center', justifyContent: 'center' },
    hTitle: { flex: 1, textAlign: 'center', fontSize: 20, fontWeight: '900', color: '#ffffff', letterSpacing: -0.5 },
    content: { padding: 20, paddingBottom: 120, marginTop: -16 },
    chartCard: { backgroundColor: '#fff', borderRadius: 28, padding: 24, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 5 },
    cardTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    cardSub: { fontSize: 14, color: '#94A3B8', fontWeight: '500', marginTop: 2, marginBottom: 24 },
    chartBox: { alignItems: 'center', justifyContent: 'center', height: 160, marginBottom: 32 },
    chartCenter: { position: 'absolute', alignItems: 'center', justifyContent: 'center' },
    centerVal: { fontSize: 20, fontWeight: '900', color: '#0F172A' },
    centerLbl: { fontSize: 13, color: '#64748B', fontWeight: '600' },
    legend: { gap: 16 },
    lItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    lLeft: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    lDot: { width: 12, height: 12, borderRadius: 6 },
    lLbl: { fontSize: 16, color: '#475569', fontWeight: '700' },
    lRight: { alignItems: 'flex-end' },
    lPct: { fontSize: 13, color: '#94A3B8', fontWeight: '800', marginBottom: 2 },
    lVal: { fontSize: 16, fontWeight: '900', color: '#0F172A' },
    emptyChart: { padding: 40, alignItems: 'center', marginTop: 20 },
    retryBtn: { marginTop: 24, padding: 12, paddingHorizontal: 24, backgroundColor: '#3B82F6', borderRadius: 8 }
});
