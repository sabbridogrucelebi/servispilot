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
            <LinearGradient colors={['#040B16', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()}><Icon name="chevron-left" size={28} color="#fff" /></TouchableOpacity>
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Raporlar</Text></View>
                        <View style={{width: 28}} />
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
                                <LinearGradient colors={['#FFFBEB', '#FEF3C7']} style={s.summaryCard}>
                                    <Icon name="weather-sunny" size={24} color="#F59E0B" />
                                    <Text style={s.scLabel}>Sabah Seferi</Text>
                                    <Text style={s.scVal}>{totals.morning}</Text>
                                    <Icon name="arrow-top-right-thin-circle-outline" size={60} color="rgba(245, 158, 11, 0.05)" style={s.scBgIcon} />
                                </LinearGradient>

                                {/* Evening Card */}
                                <LinearGradient colors={['#EEF2FF', '#E0E7FF']} style={s.summaryCard}>
                                    <Icon name="weather-night" size={24} color="#6366F1" />
                                    <Text style={s.scLabel}>Akşam Seferi</Text>
                                    <Text style={s.scVal}>{totals.evening}</Text>
                                    <Icon name="arrow-bottom-right-thin-circle-outline" size={60} color="rgba(99, 102, 241, 0.05)" style={s.scBgIcon} />
                                </LinearGradient>
                            </View>

                            {/* Income Card */}
                            <LinearGradient colors={['#ECFDF5', '#D1FAE5']} style={[s.summaryCard, s.incomeCard]}>
                                <View style={s.incomeCardHeader}>
                                    <View>
                                        <Text style={[s.scLabel, {color: '#059669', marginBottom: 4}]}>Aylık Toplam Hakediş</Text>
                                        <Text style={[s.scVal, {color: '#065F46', fontSize: 28}]}>{Number(totals.income).toLocaleString('tr-TR')} ₺</Text>
                                    </View>
                                    <View style={s.incomeIconBox}>
                                        <Icon name="currency-try" size={28} color="#10B981" />
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
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 30, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 0 : 40 },
    headerTitle: { color: '#fff', fontSize: 16, fontWeight: '800' },
    
    monthSelector: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginHorizontal: 20, marginTop: -20, backgroundColor: '#fff', borderRadius: 20, padding: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 5, borderWidth: 1, borderColor: '#F1F5F9' },
    monthBtn: { padding: 8, backgroundColor: '#F8FAFC', borderRadius: 12 },
    monthDisplay: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    monthTxt: { fontSize: 15, fontWeight: '800', color: '#1E293B' },

    listContent: { padding: 16, paddingBottom: 40 },
    summaryCards: { marginBottom: 16 },
    rowCards: { flexDirection: 'row', gap: 12, marginBottom: 12 },
    summaryCard: { flex: 1, padding: 16, borderRadius: 24, position: 'relative', overflow: 'hidden' },
    scLabel: { fontSize: 10, fontWeight: '800', color: '#64748B', marginTop: 12, marginBottom: 4, textTransform: 'uppercase', letterSpacing: 0.5 },
    scVal: { fontSize: 24, fontWeight: '900', color: '#1E293B' },
    scBgIcon: { position: 'absolute', right: -10, bottom: -10 },
    
    incomeCard: { width: '100%', padding: 20 },
    incomeCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    incomeIconBox: { width: 56, height: 56, backgroundColor: '#fff', borderRadius: 20, alignItems: 'center', justifyContent: 'center', shadowColor: '#10B981', shadowOffset: {width:0,height:8}, shadowOpacity: 0.2, shadowRadius: 15, elevation: 5 },

    sectionTitle: { fontSize: 14, fontWeight: '800', color: '#475569', marginTop: 24, marginBottom: 8, marginLeft: 4 },

    reportCard: { backgroundColor: '#fff', borderRadius: 24, padding: 16, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 10, elevation: 2, borderWidth: 1, borderColor: '#F1F5F9' },
    reportHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    avatarWrap: { width: 44, height: 44, borderRadius: 16, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center', marginRight: 12 },
    avatarTxt: { fontSize: 18, fontWeight: '900', color: '#64748B' },
    reportTitleWrap: { flex: 1 },
    customerName: { fontSize: 14, fontWeight: '800', color: '#1E293B', marginBottom: 2 },
    customerDesc: { fontSize: 10, fontWeight: '700', color: '#94A3B8', textTransform: 'uppercase' },
    incomeBadge: { backgroundColor: '#ECFDF5', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 12 },
    incomeBadgeTxt: { color: '#059669', fontSize: 12, fontWeight: '800' },

    statsRow: { flexDirection: 'row', gap: 12, borderTopWidth: 1, borderTopColor: '#F8FAFC', paddingTop: 16 },
    statBox: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 12, backgroundColor: '#F8FAFC', padding: 12, borderRadius: 16 },
    statBoxIconWrap: { width: 32, height: 32, borderRadius: 10, backgroundColor: '#FEF3C7', alignItems: 'center', justifyContent: 'center' },
    statVal: { fontSize: 16, fontWeight: '900', color: '#1E293B' },
    statLabel: { fontSize: 10, fontWeight: '600', color: '#64748B' },

    emptyBox: { alignItems: 'center', marginTop: 40 },
    emptyTitle: { fontSize: 16, fontWeight: '800', color: '#64748B', marginTop: 16 },
    emptyDesc: { fontSize: 13, color: '#94A3B8', textAlign: 'center', marginTop: 8, paddingHorizontal: 30 }
});
