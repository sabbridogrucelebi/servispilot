import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';

export default function VehicleReportsScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { vehicleId, plate } = route.params || {};

    // For simplicity, let's use a native date picker logic or just basic prev/next month for mobile
    const [currentDate, setCurrentDate] = useState(new Date());
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const [totals, setTotals] = useState({ morning: 0, evening: 0, income: 0 });
    const [reports, setReports] = useState([]);

    useEffect(() => {
        fetchReports();
    }, [currentDate]);

    const getMonthString = (date) => {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        return `${yyyy}-${mm}`;
    };

    const getMonthLabel = (date) => {
        const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        return `${months[date.getMonth()]} ${date.getFullYear()}`;
    };

    const handlePrevMonth = () => {
        const newDate = new Date(currentDate);
        newDate.setMonth(newDate.getMonth() - 1);
        setCurrentDate(newDate);
    };

    const handleNextMonth = () => {
        const newDate = new Date(currentDate);
        newDate.setMonth(newDate.getMonth() + 1);
        setCurrentDate(newDate);
    };

    const fetchReports = async (isRefreshing = false) => {
        try {
            if (!isRefreshing) setLoading(true);
            const monthStr = getMonthString(currentDate);
            const response = await api.get(`/vehicles/${vehicleId}/reports?reports_month=${monthStr}`);
            
            setTotals(response.data.totals);
            setReports(response.data.details);
        } catch (error) {
            console.error('Reports Error:', error);
            Alert.alert('Hata', 'Rapor verileri alınırken bir sorun oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const renderCustomerReport = ({ item }) => (
        <View style={s.reportCard}>
            <View style={s.reportHeader}>
                <View style={s.avatarWrap}>
                    <Text style={s.avatarTxt}>{item.customer_name.substring(0, 1)}</Text>
                </View>
                <View style={s.reportTitleWrap}>
                    <Text style={s.customerName}>{item.customer_name}</Text>
                    <Text style={s.customerDesc}>Operasyon Özeti</Text>
                </View>
                <View style={s.incomeBadge}>
                    <Text style={s.incomeBadgeTxt}>{Number(item.total_price).toLocaleString('tr-TR')} ₺</Text>
                </View>
            </View>
            
            <View style={s.statsRow}>
                <View style={s.statBox}>
                    <View style={s.statBoxIconWrap}><Icon name="weather-sunny" size={16} color="#D97706" /></View>
                    <View>
                        <Text style={s.statVal}>{item.morning_count}</Text>
                        <Text style={s.statLabel}>Sabah Seferi</Text>
                    </View>
                </View>
                <View style={s.statBox}>
                    <View style={[s.statBoxIconWrap, {backgroundColor: '#EEF2FF'}]}><Icon name="weather-night" size={16} color="#4F46E5" /></View>
                    <View>
                        <Text style={s.statVal}>{item.evening_count}</Text>
                        <Text style={s.statLabel}>Akşam Seferi</Text>
                    </View>
                </View>
            </View>
        </View>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={s.header} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="arrow-left" size={20} color="#fff" />
                        </TouchableOpacity>
                        <View style={s.headerTitleWrap}>
                            <Text style={s.headerTitle}>{plate} · Raporlar</Text>
                            <View style={s.headerSubWrap}>
                                <View style={s.statusDotSmall} />
                                <Text style={s.headerSubTxt}>Aylık Performans Özeti</Text>
                            </View>
                        </View>
                        <View style={{flexDirection:'row', gap: 8}}>
                            <TouchableOpacity style={s.topBtn}><Icon name="share-variant" size={20} color="#fff" /></TouchableOpacity>
                            <TouchableOpacity style={s.topAddBtn}><Icon name="download" size={20} color="#fff" /></TouchableOpacity>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Date Selector */}
            <View style={s.monthSelector}>
                <TouchableOpacity onPress={handlePrevMonth} style={s.monthBtn}><Icon name="chevron-left" size={24} color="#64748B" /></TouchableOpacity>
                <View style={s.monthDisplay}>
                    <Icon name="calendar-month-outline" size={20} color="#3B82F6" />
                    <Text style={s.monthTxt}>{getMonthLabel(currentDate)}</Text>
                </View>
                <TouchableOpacity onPress={handleNextMonth} style={s.monthBtn}><Icon name="chevron-right" size={24} color="#64748B" /></TouchableOpacity>
            </View>

            {loading && !refreshing ? (
                <ActivityIndicator style={{marginTop: 40}} color="#3B82F6" size="large" />
            ) : (
                <FlatList
                    data={reports}
                    keyExtractor={(item, index) => index.toString()}
                    renderItem={renderCustomerReport}
                    contentContainerStyle={s.listContent}
                    refreshing={refreshing}
                    onRefresh={() => fetchReports(true)}
                    ListHeaderComponent={
                        <View style={s.summaryCards}>
                            <View style={s.rowCards}>
                                {/* Morning Card */}
                                <View style={[s.healthCard, { borderColor: 'rgba(245, 158, 11, 0.3)' }]}>
                                    <LinearGradient colors={['rgba(245, 158, 11, 0.1)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={24} />
                                    <View style={s.hcHeader}>
                                        <View style={[s.hcIconWrap, {backgroundColor: '#FFFBEB'}]}><Icon name="weather-sunny" size={20} color="#D97706" /></View>
                                        <Text style={s.hcLabel}>Sabah Seferi</Text>
                                    </View>
                                    <Text style={s.hcVal}>{totals.morning}</Text>
                                </View>

                                {/* Evening Card */}
                                <View style={[s.healthCard, { borderColor: 'rgba(59, 130, 246, 0.3)' }]}>
                                    <LinearGradient colors={['rgba(59, 130, 246, 0.1)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={24} />
                                    <View style={s.hcHeader}>
                                        <View style={[s.hcIconWrap, {backgroundColor: '#EFF6FF'}]}><Icon name="weather-night" size={20} color="#2563EB" /></View>
                                        <Text style={s.hcLabel}>Akşam Seferi</Text>
                                    </View>
                                    <Text style={s.hcVal}>{totals.evening}</Text>
                                </View>
                            </View>

                            {/* Income Card */}
                            <LinearGradient colors={['#022C22', '#064E3B', '#047857']} style={s.incomeCard} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                                <LinearGradient colors={['rgba(16,185,129,0.2)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={24} />
                                <View style={s.incomeCardHeader}>
                                    <View>
                                        <Text style={[s.scLabel, {color: '#6EE7B7'}]}>Aylık Toplam Hakediş</Text>
                                        <Text style={[s.scVal, {color: '#FFF', fontSize: 32}]}>{Number(totals.income).toLocaleString('tr-TR')} ₺</Text>
                                    </View>
                                    <View style={s.incomeIconBox}>
                                        <Icon name="finance" size={32} color="#10B981" />
                                    </View>
                                </View>
                            </LinearGradient>
                            <Text style={s.sectionTitle}>Müşteri Bazlı Hakedişler</Text>
                        </View>
                    }
                    ListEmptyComponent={
                        <View style={s.emptyBox}>
                            <Icon name="clipboard-text-off-outline" size={48} color="#CBD5E1" />
                            <Text style={s.emptyTitle}>Sefer Bulunamadı</Text>
                            <Text style={s.emptyDesc}>Bu ay için kaydedilmiş herhangi bir operasyon verisi yok.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden', paddingBottom: 30 },
    headerRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 24, paddingTop: 10, marginBottom: 20 },
    backBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    headerTitleWrap: { alignItems: 'center' },
    headerTitle: { color: '#fff', fontSize: 18, fontWeight: '800', letterSpacing: 0.5 },
    headerSubWrap: { flexDirection: 'row', alignItems: 'center', marginTop: 4, gap: 4 },
    statusDotSmall: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#10B981' },
    headerSubTxt: { fontSize: 11, color: '#94A3B8', fontWeight: '600' },
    topBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    topAddBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(59, 130, 246, 0.4)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#3B82F6' },
    
    monthSelector: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginHorizontal: 20, marginTop: -26, backgroundColor: '#fff', borderRadius: 24, padding: 10, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 12 }, shadowOpacity: 0.08, shadowRadius: 25, elevation: 6, borderWidth: 1, borderColor: '#F1F5F9', zIndex: 20 },
    monthBtn: { width: 44, height: 44, backgroundColor: '#F8FAFC', borderRadius: 16, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#F1F5F9' },
    monthDisplay: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    monthTxt: { fontSize: 16, fontWeight: '900', color: '#1E293B', letterSpacing: -0.5 },

    listContent: { padding: 20, paddingBottom: 100, paddingTop: 20 },
    summaryCards: { marginBottom: 24 },
    rowCards: { flexDirection: 'row', gap: 16, marginBottom: 16 },
    
    healthCard: { flex: 1, padding: 18, borderRadius: 24, backgroundColor: '#fff', shadowColor: '#000', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.04, shadowRadius: 20, elevation: 4, borderWidth: 1, borderColor: '#F1F5F9', position: 'relative', overflow: 'hidden' },
    hcHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 16 },
    hcIconWrap: { width: 36, height: 36, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    hcLabel: { fontSize: 12, fontWeight: '700', color: '#64748B' },
    hcVal: { fontSize: 28, fontWeight: '900', color: '#0F172A', letterSpacing: -1 },

    incomeCard: { width: '100%', padding: 24, borderRadius: 24, position: 'relative', overflow: 'hidden', shadowColor: '#047857', shadowOffset: { width: 0, height: 12 }, shadowOpacity: 0.25, shadowRadius: 20, elevation: 8, borderWidth: 1, borderColor: '#059669' },
    incomeCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    scLabel: { fontSize: 12, fontWeight: '800', marginBottom: 4, textTransform: 'uppercase', letterSpacing: 0.5 },
    scVal: { fontSize: 28, fontWeight: '900', letterSpacing: -1 },
    incomeIconBox: { width: 64, height: 64, backgroundColor: 'rgba(16, 185, 129, 0.15)', borderRadius: 20, alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(52, 211, 153, 0.3)' },

    sectionTitle: { fontSize: 16, fontWeight: '900', color: '#0F172A', marginTop: 28, marginBottom: 16, marginLeft: 4, letterSpacing: -0.5 },

    reportCard: { backgroundColor: '#fff', borderRadius: 24, padding: 20, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4, borderWidth: 1, borderColor: '#F1F5F9' },
    reportHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 20 },
    avatarWrap: { width: 48, height: 48, borderRadius: 16, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center', marginRight: 14, borderWidth: 1, borderColor: '#F1F5F9' },
    avatarTxt: { fontSize: 20, fontWeight: '900', color: '#3B82F6' },
    reportTitleWrap: { flex: 1 },
    customerName: { fontSize: 16, fontWeight: '900', color: '#0F172A', marginBottom: 4, letterSpacing: -0.5 },
    customerDesc: { fontSize: 11, fontWeight: '800', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: 0.5 },
    incomeBadge: { backgroundColor: '#ECFDF5', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, borderWidth: 1, borderColor: '#D1FAE5' },
    incomeBadgeTxt: { color: '#10B981', fontSize: 13, fontWeight: '900' },

    statsRow: { flexDirection: 'row', gap: 16, borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 16 },
    statBox: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: '#F8FAFC', padding: 14, borderRadius: 16, borderWidth: 1, borderColor: '#F1F5F9' },
    statBoxIconWrap: { width: 36, height: 36, borderRadius: 12, backgroundColor: '#FFFBEB', alignItems: 'center', justifyContent: 'center' },
    statVal: { fontSize: 18, fontWeight: '900', color: '#1E293B' },
    statLabel: { fontSize: 11, fontWeight: '700', color: '#64748B' },

    emptyBox: { alignItems: 'center', marginTop: 60 },
    emptyTitle: { fontSize: 18, fontWeight: '900', color: '#64748B', marginTop: 20, letterSpacing: -0.5 },
    emptyDesc: { fontSize: 14, color: '#94A3B8', textAlign: 'center', marginTop: 8, paddingHorizontal: 30, fontWeight: '500' }
});
