import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ActivityIndicator, FlatList, Dimensions, Platform, Alert } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

const { width: W } = Dimensions.get('window');

const fmtMoney = (v) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 }).format(v || 0);

const MONTH_NAMES = [
    'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
    'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'
];

export default function VehicleReportsScreen({ route, navigation }) {
    const { vehicleId, vehicle } = route.params || {};
    
    const [dateObj, setDateObj] = useState(new Date());
    const [loading, setLoading] = useState(true);
    const [reports, setReports] = useState([]);
    const [totals, setTotals] = useState({ morning: 0, evening: 0, income: 0 });

    const fetchReports = async (date) => {
        setLoading(true);
        try {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const monthStr = `${y}-${m}`;
            
            const r = await api.get(`/v1/vehicles/${vehicleId}/reports`, {
                params: { reports_month: monthStr }
            });
            
            setReports(r.data.data.details || []);
            setTotals(r.data.data.totals || { morning: 0, evening: 0, income: 0 });
        } catch (error) {
            console.error('Reports Fetch Error:', error);
            Alert.alert('Hata', 'Rapor verileri alınamadı.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (vehicleId) {
            fetchReports(dateObj);
        } else {
            setLoading(false);
        }
    }, [dateObj]);

    const handlePrevMonth = () => {
        const d = new Date(dateObj);
        d.setMonth(d.getMonth() - 1);
        setDateObj(d);
    };

    const handleNextMonth = () => {
        const d = new Date(dateObj);
        d.setMonth(d.getMonth() + 1);
        setDateObj(d);
    };

    const renderHeader = () => (
        <View style={st.headerWrap}>
            <View style={st.header}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={st.backBtn}>
                    <Icon name="arrow-left" size={24} color="#1E293B" />
                </TouchableOpacity>
                <View style={st.headerTitleBox}>
                    <Text style={st.headerTitle}>Aylık Çalışma (Puantaj) Raporu</Text>
                    <Text style={st.headerSub}>{vehicle?.plate || 'Araç'}</Text>
                </View>
                <View style={{ width: 44 }} />
            </View>

            {/* Month Picker */}
            <View style={st.monthPicker}>
                <TouchableOpacity style={st.monthBtn} onPress={handlePrevMonth}>
                    <Icon name="chevron-left" size={24} color="#64748B" />
                </TouchableOpacity>
                <View style={st.monthDisplay}>
                    <Icon name="calendar-month-outline" size={20} color="#3B82F6" />
                    <Text style={st.monthText}>
                        {MONTH_NAMES[dateObj.getMonth()]} {dateObj.getFullYear()}
                    </Text>
                </View>
                <TouchableOpacity style={st.monthBtn} onPress={handleNextMonth}>
                    <Icon name="chevron-right" size={24} color="#64748B" />
                </TouchableOpacity>
            </View>

            {/* Summary Cards */}
            <View style={st.summaryGrid}>
                {/* Sabah */}
                <View style={[st.sumCard, { backgroundColor: '#FFFBEB', borderColor: '#FEF3C7' }]}>
                    <Text style={[st.sumLabel, { color: '#D97706' }]}>TOPLAM SABAH SEFERİ</Text>
                    <Text style={[st.sumValue, { color: '#B45309' }]}>{totals.morning}</Text>
                    <View style={st.sumFooter}>
                        <View style={[st.sumDot, { backgroundColor: '#F59E0B' }]} />
                        <Text style={[st.sumFooterText, { color: '#D97706' }]}>Gidiş / Sabah</Text>
                    </View>
                    <Icon name="weather-sunny" size={60} color="#FEF3C7" style={st.bgIcon} />
                </View>

                {/* Akşam */}
                <View style={[st.sumCard, { backgroundColor: '#EEF2FF', borderColor: '#E0E7FF' }]}>
                    <Text style={[st.sumLabel, { color: '#4F46E5' }]}>TOPLAM AKŞAM SEFERİ</Text>
                    <Text style={[st.sumValue, { color: '#3730A3' }]}>{totals.evening}</Text>
                    <View style={st.sumFooter}>
                        <View style={[st.sumDot, { backgroundColor: '#6366F1' }]} />
                        <Text style={[st.sumFooterText, { color: '#4F46E5' }]}>Dönüş / Akşam</Text>
                    </View>
                    <Icon name="weather-night" size={60} color="#E0E7FF" style={st.bgIcon} />
                </View>

                {/* Toplam */}
                <View style={[st.sumCard, { backgroundColor: '#ECFDF5', borderColor: '#D1FAE5' }]}>
                    <Text style={[st.sumLabel, { color: '#059669' }]}>AYLIK TOPLAM HAKEDİŞ</Text>
                    <Text style={[st.sumValue, { color: '#047857' }]}>{fmtMoney(totals.income)}</Text>
                    <View style={st.sumFooter}>
                        <View style={[st.sumDot, { backgroundColor: '#10B981' }]} />
                        <Text style={[st.sumFooterText, { color: '#059669' }]}>Araç Bazlı Ciro</Text>
                    </View>
                    <Icon name="currency-try" size={60} color="#D1FAE5" style={st.bgIcon} />
                </View>
            </View>

            <View style={st.listHeader}>
                <Text style={st.listTitle}>Müşteri / Kurum Analizi</Text>
            </View>
        </View>
    );

    const renderItem = ({ item }) => (
        <View style={st.rowCard}>
            <View style={st.rowTop}>
                <View style={st.rowCustomerIcon}>
                    <Text style={st.rowCustomerInitial}>{item.customer_name.substring(0, 1)}</Text>
                </View>
                <View style={st.rowCustomerInfo}>
                    <Text style={st.rowCustomerName}>{item.customer_name}</Text>
                    <Text style={st.rowCustomerSub}>OPERASYON</Text>
                </View>
            </View>
            
            <View style={st.rowStats}>
                <View style={st.statBox}>
                    <Text style={st.statBoxLabel}>SABAH</Text>
                    <View style={[st.statBoxBadge, { backgroundColor: '#FFFBEB' }]}>
                        <Text style={[st.statBoxValue, { color: '#D97706' }]}>{item.morning_count}</Text>
                    </View>
                </View>
                <View style={st.statBox}>
                    <Text style={st.statBoxLabel}>AKŞAM</Text>
                    <View style={[st.statBoxBadge, { backgroundColor: '#EEF2FF' }]}>
                        <Text style={[st.statBoxValue, { color: '#4F46E5' }]}>{item.evening_count}</Text>
                    </View>
                </View>
                <View style={[st.statBox, { alignItems: 'flex-end', flex: 1 }]}>
                    <Text style={st.statBoxLabel}>TOPLAM KAZANÇ</Text>
                    <Text style={st.statPrice}>{fmtMoney(item.total_price)}</Text>
                </View>
            </View>
        </View>
    );

    return (
        <SafeAreaView style={st.container} edges={['top']}>
            <FlatList
                data={reports}
                keyExtractor={(item, index) => index.toString()}
                ListHeaderComponent={renderHeader}
                renderItem={renderItem}
                contentContainerStyle={st.listContent}
                showsVerticalScrollIndicator={false}
                ListEmptyComponent={
                    !loading && (
                        <View style={st.emptyState}>
                            <Icon name="chart-box-outline" size={48} color="#CBD5E1" />
                            <Text style={st.emptyText}>Bu aya ait operasyon kaydı bulunamadı.</Text>
                        </View>
                    )
                }
            />
            {loading && (
                <View style={st.loadingOverlay}>
                    <ActivityIndicator size="large" color="#3B82F6" />
                </View>
            )}
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    listContent: { paddingBottom: 40 },
    headerWrap: { paddingHorizontal: 16, paddingTop: Platform.OS === 'android' ? 20 : 30, paddingBottom: 16 },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 20 },
    backBtn: { width: 44, height: 44, borderRadius: 12, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2 },
    headerTitleBox: { flex: 1, alignItems: 'center', paddingHorizontal: 10 },
    headerTitle: { fontSize: 18, fontWeight: '800', color: '#1E293B', textAlign: 'center' },
    headerSub: { fontSize: 13, color: '#64748B', textAlign: 'center', fontWeight: '500', marginTop: 2 },
    
    monthPicker: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#fff', borderRadius: 16, padding: 8, marginBottom: 20, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.04, shadowRadius: 8, elevation: 2 },
    monthBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    monthDisplay: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    monthText: { fontSize: 16, fontWeight: '700', color: '#1E293B' },

    summaryGrid: { gap: 12, marginBottom: 24 },
    sumCard: { padding: 16, borderRadius: 20, borderWidth: 1, overflow: 'hidden', position: 'relative' },
    sumLabel: { fontSize: 11, fontWeight: '800', letterSpacing: 0.5, marginBottom: 8 },
    sumValue: { fontSize: 28, fontWeight: '900', marginBottom: 12 },
    sumFooter: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    sumDot: { width: 6, height: 6, borderRadius: 3 },
    sumFooterText: { fontSize: 12, fontWeight: '600' },
    bgIcon: { position: 'absolute', right: -10, top: -5, opacity: 1, transform: [{ scale: 1.2 }] },

    listHeader: { marginBottom: 12 },
    listTitle: { fontSize: 16, fontWeight: '700', color: '#334155' },

    rowCard: { backgroundColor: '#fff', marginHorizontal: 16, marginBottom: 12, borderRadius: 16, padding: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.04, shadowRadius: 8, elevation: 2 },
    rowTop: { flexDirection: 'row', alignItems: 'center', marginBottom: 16, gap: 12 },
    rowCustomerIcon: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    rowCustomerInitial: { fontSize: 16, fontWeight: '800', color: '#64748B' },
    rowCustomerInfo: { flex: 1 },
    rowCustomerName: { fontSize: 15, fontWeight: '700', color: '#1E293B' },
    rowCustomerSub: { fontSize: 10, fontWeight: '700', color: '#94A3B8', marginTop: 2, letterSpacing: 0.5 },

    rowStats: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 12, padding: 12, gap: 16 },
    statBox: { gap: 6 },
    statBoxLabel: { fontSize: 10, fontWeight: '700', color: '#94A3B8', letterSpacing: 0.5 },
    statBoxBadge: { paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, alignSelf: 'flex-start' },
    statBoxValue: { fontSize: 14, fontWeight: '800' },
    statPrice: { fontSize: 16, fontWeight: '800', color: '#10B981', marginTop: 4 },

    loadingOverlay: { ...StyleSheet.absoluteFillObject, backgroundColor: 'rgba(255,255,255,0.7)', justifyContent: 'center', alignItems: 'center', zIndex: 10 },
    emptyState: { alignItems: 'center', paddingVertical: 40 },
    emptyText: { color: '#94A3B8', fontSize: 14, fontWeight: '500', marginTop: 12 }
});
