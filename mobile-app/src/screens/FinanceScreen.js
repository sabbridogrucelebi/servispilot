import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function FinanceScreen({ navigation }) {
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

    if (loading && !refreshing) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#3B82F6" />
                <Text style={{ marginTop: 16, color: '#64748b' }}>Finansal Özet Yükleniyor...</Text>
            </SafeAreaView>
        );
    }

    if (error && !refreshing && !summary) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                <Text style={[s.emptyT, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                <TouchableOpacity onPress={() => fetchSummary()} style={s.retryBtn}>
                    <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                </TouchableOpacity>
            </SafeAreaView>
        );
    }

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <Icon name="wallet-outline" size={28} color="#fff" />
                        <Text style={s.hTitle}>Finans</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView 
                contentContainerStyle={s.content} 
                showsVerticalScrollIndicator={false}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
            >
                {/* Ana Net Durum Kartı (Web Panel ile Aynı) */}
                <View style={s.netCard}>
                    <Text style={s.netPeriod}>{summary?.period_human}</Text>
                    {summary?.is_locked && (
                        <View style={s.lockBadge}>
                            <Icon name="lock" size={14} color="#EF4444" />
                            <Text style={s.lockText}>Dönem Kilitli</Text>
                        </View>
                    )}
                    <Text style={s.netLabel}>Resmi Net Kâr / Zarar</Text>
                    <Text style={[s.netValue, { color: summary?.net_profit === null ? '#94A3B8' : (summary?.net_profit >= 0) ? '#10B981' : '#EF4444' }]}>
                        {formatCurrency(summary?.net_profit)}
                    </Text>
                    
                    <View style={s.summaryRow}>
                        <View style={s.summaryCol}>
                            <View style={s.summaryIconRow}>
                                <Icon name="arrow-up-circle" size={16} color="#10B981" />
                                <Text style={s.summaryLabel}>Toplam Gelir</Text>
                            </View>
                            <Text style={s.summaryIncome}>{formatCurrency(summary?.total_income)}</Text>
                        </View>
                        <View style={s.divider} />
                        <View style={s.summaryCol}>
                            <View style={s.summaryIconRow}>
                                <Icon name="arrow-down-circle" size={16} color="#EF4444" />
                                <Text style={s.summaryLabel}>Resmi Giderler</Text>
                            </View>
                            <Text style={s.summaryExpense}>{formatCurrency(summary?.total_expenses)}</Text>
                        </View>
                    </View>
                </View>

                {/* Operasyonel Net Kartı (Bakım ve Ceza Dahil) */}
                <View style={[s.netCard, { backgroundColor: '#F8FAFC', borderColor: '#E2E8F0', borderWidth: 1, elevation: 0, padding: 16 }]}>
                    <Text style={[s.netLabel, { fontSize: 13 }]}>Bakım ve Ceza Dahil Operasyonel Net</Text>
                    <Text style={[s.netValue, { fontSize: 24, marginTop: 4, marginBottom: 0, color: summary?.operational_net_profit === null ? '#94A3B8' : (summary?.operational_net_profit >= 0) ? '#059669' : '#DC2626' }]}>
                        {formatCurrency(summary?.operational_net_profit)}
                    </Text>
                </View>

                {/* Gider Dağılımı Detayı */}
                <Text style={s.sectionTitle}>Gider Detayları</Text>
                <View style={s.listCard}>
                    {/* Resmi Giderler */}
                    <View style={s.listItem}>
                        <View style={s.listLeft}>
                            <View style={[s.iconBox, { backgroundColor: '#EFF6FF' }]}><Icon name="cash-multiple" size={20} color="#3B82F6" /></View>
                            <Text style={s.listText}>Maaş & Bordrolar</Text>
                        </View>
                        <Text style={s.listValue}>{formatCurrency(summary?.expenses_detail?.payrolls)}</Text>
                    </View>
                    
                    <View style={summary?.additional_expenses ? s.listItem : [s.listItem, { borderBottomWidth: 0 }]}>
                        <View style={s.listLeft}>
                            <View style={[s.iconBox, { backgroundColor: '#FEF2F2' }]}><Icon name="gas-station" size={20} color="#EF4444" /></View>
                            <Text style={s.listText}>Yakıt Maliyeti</Text>
                        </View>
                        <Text style={s.listValue}>{formatCurrency(summary?.expenses_detail?.fuels)}</Text>
                    </View>

                    {/* Ekstra Giderler */}
                    {summary?.additional_expenses && (
                        <>
                            <View style={s.listItem}>
                                <View style={s.listLeft}>
                                    <View style={[s.iconBox, { backgroundColor: '#FFFBEB' }]}><Icon name="wrench" size={20} color="#F59E0B" /></View>
                                    <Text style={s.listText}>Bakım & Onarım</Text>
                                </View>
                                <Text style={s.listValue}>{formatCurrency(summary?.additional_expenses?.maintenances)}</Text>
                            </View>
                            <View style={[s.listItem, { borderBottomWidth: 0 }]}>
                                <View style={s.listLeft}>
                                    <View style={[s.iconBox, { backgroundColor: '#FDF2F8' }]}><Icon name="file-document-outline" size={20} color="#EC4899" /></View>
                                    <Text style={s.listText}>Trafik Cezaları</Text>
                                </View>
                                <Text style={s.listValue}>{formatCurrency(summary?.additional_expenses?.penalties)}</Text>
                            </View>
                        </>
                    )}
                </View>

                {/* Hızlı Erişim Menüsü */}
                <Text style={s.sectionTitle}>Hızlı Erişim</Text>
                <View style={s.menuRow}>
                    <TouchableOpacity style={s.menuBtn} onPress={() => navigation.navigate('Payrolls')}>
                        <Icon name="cash-multiple" size={28} color="#3B82F6" />
                        <Text style={s.menuBtnText}>Bordrolar</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={s.menuBtn} onPress={() => navigation.navigate('Reports')}>
                        <Icon name="chart-pie" size={28} color="#8B5CF6" />
                        <Text style={s.menuBtnText}>Raporlar</Text>
                    </TouchableOpacity>
                </View>

            </ScrollView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 32, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    hRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    hTitle: { fontSize: 24, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    content: { padding: 20, paddingTop: 30, paddingBottom: 100 },
    netCard: { backgroundColor: '#fff', borderRadius: 24, padding: 24, alignItems: 'center', marginBottom: 16, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 4 },
    netPeriod: { fontSize: 13, color: '#3B82F6', fontWeight: '800', backgroundColor: '#EFF6FF', paddingHorizontal: 12, paddingVertical: 4, borderRadius: 12, overflow: 'hidden', marginBottom: 8 },
    lockBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FEF2F2', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8, gap: 4, marginBottom: 12 },
    lockText: { color: '#EF4444', fontSize: 12, fontWeight: '700' },
    netLabel: { fontSize: 15, color: '#64748B', fontWeight: '600', marginBottom: 4, textAlign: 'center' },
    netValue: { fontSize: 32, fontWeight: '900', marginBottom: 24, textAlign: 'center' },
    summaryRow: { flexDirection: 'row', width: '100%', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 16 },
    summaryCol: { flex: 1, alignItems: 'center' },
    summaryIconRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 4 },
    summaryLabel: { fontSize: 12, color: '#94A3B8', fontWeight: '600' },
    summaryIncome: { fontSize: 16, fontWeight: '800', color: '#0F172A' },
    summaryExpense: { fontSize: 16, fontWeight: '800', color: '#0F172A' },
    divider: { width: 1, backgroundColor: '#F1F5F9' },
    sectionTitle: { fontSize: 16, fontWeight: '900', color: '#0F172A', marginBottom: 12, marginLeft: 4, marginTop: 8 },
    listCard: { backgroundColor: '#fff', borderRadius: 20, padding: 20, marginBottom: 24, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 10, elevation: 2 },
    listItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    listLeft: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    iconBox: { width: 36, height: 36, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
    listText: { fontSize: 15, fontWeight: '700', color: '#475569' },
    listValue: { fontSize: 15, fontWeight: '800', color: '#0F172A' },
    menuRow: { flexDirection: 'row', gap: 16 },
    menuBtn: { flex: 1, backgroundColor: '#fff', borderRadius: 20, padding: 20, alignItems: 'center', shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 10, elevation: 2 },
    menuBtnText: { fontSize: 14, fontWeight: '700', color: '#475569', marginTop: 12 },
    emptyT: { color: '#94A3B8', fontSize: 16, marginTop: 16, fontWeight: '600' },
    retryBtn: { marginTop: 24, padding: 12, paddingHorizontal: 24, backgroundColor: '#3B82F6', borderRadius: 8 }
});
