import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput, ActivityIndicator, Alert, Platform, Linking, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import DatePickerInput from '../components/DatePickerInput';
import { toApiDate } from '../utils/date';

export default function VehiclePenaltiesScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { vehicleId, plate } = route.params || {};

    const [penalties, setPenalties] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [search, setSearch] = useState('');
    const [showFilters, setShowFilters] = useState(true);
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');

    useEffect(() => {
        fetchPenalties();
    }, [startDate, endDate]);

    const fetchPenalties = async (isRefreshing = false) => {
        try {
            if (!isRefreshing) setLoading(true);
            const response = await api.get(`/v1/vehicles/${vehicleId}/penalties`, {
                params: {
                    start_date: toApiDate(startDate),
                    end_date: toApiDate(endDate),
                    search: search
                }
            });
            setPenalties(response.data.data.penalties);
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Cezalar yüklenirken bir sorun oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const onRefresh = () => {
        setRefreshing(true);
        fetchPenalties(true);
    };

    const renderPenalty = ({ item }) => {
        const isPaid = item.status === 'paid';
        const iconBg = isPaid ? '#ECFDF5' : '#FEF2F2';
        const iconColor = isPaid ? '#10B981' : '#EF4444';
        const iconName = isPaid ? 'check-decagram' : 'alert-decagram';
        const statusTxt = isPaid ? 'ÖDENDİ' : 'ÖDENMEDİ';
        
        return (
            <View style={s.card}>
                <View style={s.cardTop}>
                    <View style={[s.cardIconBox, { backgroundColor: iconBg }]}>
                        <Icon name={iconName} size={28} color={iconColor} />
                    </View>
                    
                    <View style={s.cardInfo}>
                        <View style={s.cardHeaderRow}>
                            <Text style={s.cardTitle} numberOfLines={1}>{item.penalty_no || 'Ceza No Yok'}</Text>
                            <View style={{alignItems: 'flex-end'}}>
                                <View style={[s.statusBadge, isPaid ? s.statusCompleted : s.statusPlanned]}>
                                    <Icon name={isPaid ? "check" : "circle"} size={10} color={iconColor} style={{marginRight: 4}} />
                                    <Text style={[s.statusTxt, {color: iconColor}]}>{statusTxt}</Text>
                                </View>
                                <Text style={s.amountTxt}>{Number(item.amount || 0).toLocaleString('tr-TR')} ₺</Text>
                            </View>
                        </View>
                        
                        <View style={[s.typePill, { backgroundColor: '#F8FAFC' }]}>
                            <Text style={[s.typePillTxt, { color: '#64748B' }]}>{item.article || 'Madde Yok'}</Text>
                        </View>

                        <View style={s.datePersonRow}>
                            <Icon name="account-outline" size={14} color="#94A3B8" />
                            <Text style={s.dpTxt}>{item.driver_name || 'Bilinmiyor'}</Text>
                        </View>
                    </View>
                </View>

                <View style={s.cardDivider} />

                <View style={s.cardBottom}>
                    <View style={s.cbItem}>
                        <Icon name="calendar-blank" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>Tarih</Text>
                            <Text style={s.cbVal}>{item.date ? new Date(item.date).toLocaleDateString('tr-TR') : '-'}</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={s.cbItem}>
                        <Icon name="map-marker-outline" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>Yer</Text>
                            <Text style={s.cbVal} numberOfLines={1}>{item.location || '-'}</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={[s.cbItem, {flex: 1.2}]}>
                        <Icon name="ticket-percent-outline" size={16} color="#94A3B8" />
                        <View style={{flex: 1}}>
                            <Text style={s.cbLabel}>İndirimli Tutar</Text>
                            <Text style={[s.cbVal, {color: '#10B981'}]} numberOfLines={1}>{Number(item.discounted_amount || 0).toLocaleString('tr-TR')} ₺</Text>
                        </View>
                        <Icon name="chevron-right" size={20} color="#CBD5E1" />
                    </View>
                </View>
            </View>
        );
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={s.header} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="arrow-left" size={20} color="#fff" />
                        </TouchableOpacity>
                        <View style={s.headerTitleWrap}>
                            <Text style={s.headerTitle}>{plate} · Cezalar</Text>
                            <View style={s.headerSubWrap}>
                                <View style={s.statusDotSmall} />
                                <Text style={s.headerSubTxt}>Aktif • TEMSA PRESTİJ • MİDİBÜS</Text>
                            </View>
                        </View>
                        <View style={{flexDirection:'row', gap: 8}}>
                            <TouchableOpacity style={s.topBtn}><Icon name="file-pdf-box" size={22} color="#fff" /><Text style={{color:'#fff', fontSize: 10, fontWeight: '700'}}>PDF</Text></TouchableOpacity>
                            <TouchableOpacity style={s.topAddBtn}><Icon name="plus" size={22} color="#fff" /></TouchableOpacity>
                        </View>
                    </View>

                    {/* KPI Cards */}
                    <View style={s.kpiWrapper}>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.kpiScroll}>
                            
                            <View style={[s.kpiCard, { borderColor: 'rgba(239, 68, 68, 0.4)' }]}>
                                <LinearGradient colors={['rgba(220,38,38,0.15)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: '#7F1D1D'}]}><Icon name="alert" size={18} color="#F87171" /></View>
                                    <Text style={s.kpiLabel}>Ödenmemiş</Text>
                                </View>
                                <Text style={s.kpiValue}>2</Text>
                                <Text style={[s.kpiSub, {color: '#EF4444'}]}>● Aksiyon gerekli</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(20, 184, 166, 0.3)' }]}>
                                <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: 'rgba(20, 184, 166, 0.1)'}]}><Icon name="check-all" size={18} color="#14B8A6" /></View>
                                    <Text style={s.kpiLabel}>Ödenen</Text>
                                </View>
                                <Text style={s.kpiValue}>14</Text>
                                <Text style={[s.kpiSub, {color: '#10B981'}]}>Bu yıl toplam</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(245, 158, 11, 0.3)' }]}>
                                <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: 'rgba(245, 158, 11, 0.1)'}]}><Icon name="ticket-percent" size={18} color="#F59E0B" /></View>
                                    <Text style={s.kpiLabel}>İndirim Fırsatı</Text>
                                </View>
                                <Text style={s.kpiValue}>1 <Text style={{fontSize: 14, color: '#94A3B8'}}>Kayıt</Text></Text>
                                <Text style={[s.kpiSub, {color: '#F59E0B'}]}>Son gün yaklaşıyor</Text>
                            </View>

                        </ScrollView>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.filterWrapper}>
                <View style={s.filterInner}>
                    <View style={s.searchBar}>
                        <Icon name="magnify" size={20} color="#94A3B8" />
                        <TextInput 
                            style={s.searchInput} 
                            placeholder="Ceza no, madde, yer ara..." 
                            value={search}
                            onChangeText={setSearch}
                            onSubmitEditing={() => fetchPenalties()}
                        />
                        <TouchableOpacity style={s.filterBtn} onPress={() => setShowFilters(!showFilters)}>
                            <Icon name="filter-variant" size={18} color="#0F172A" />
                            <Text style={s.filterBtnTxt}>Filtrele</Text>
                        </TouchableOpacity>
                    </View>

                    <View style={s.pillRow}>
                        <TouchableOpacity style={s.pillBtn}><Icon name="layers-outline" size={16} color="#64748B" /><Text style={s.pillTxt}>Tümü</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}><Icon name="earth" size={16} color="#64748B" /><Text style={s.pillTxt}>Durum</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}><Icon name="calendar-blank" size={16} color="#64748B" /><Text style={s.pillTxt}>Tarih</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
                        <TouchableOpacity style={s.sortBtn}><Icon name="swap-vertical" size={20} color="#64748B" /></TouchableOpacity>
                    </View>

                    {showFilters && (
                        <View style={s.dateFilters}>
                            <View style={{flex:1}}><DatePickerInput label="Başlangıç" value={startDate} onChange={setStartDate} /></View>
                            <View style={{flex:1}}><DatePickerInput label="Bitiş" value={endDate} onChange={setEndDate} /></View>
                        </View>
                    )}
                </View>
            </View>

            <View style={s.listHeader}>
                <Text style={s.listTitle}>Ceza Geçmişi</Text>
                <TouchableOpacity style={s.sortDropBtn}>
                    <Icon name="sort" size={16} color="#64748B" />
                    <Text style={s.sortDropTxt}>En Yeni</Text>
                    <Icon name="chevron-down" size={16} color="#94A3B8" />
                </TouchableOpacity>
            </View>

            {loading ? <ActivityIndicator style={{marginTop:40}} color="#4F46E5" size="large" /> : (
                <FlatList
                    data={penalties}
                    renderItem={renderPenalty}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={s.list}
                    refreshing={refreshing}
                    onRefresh={onRefresh}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <Icon name="alert-circle-outline" size={60} color="#E2E8F0" />
                            <Text style={s.emptyTxt}>Kayıtlı ceza bulunamadı.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    
    // Header
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden' },
    headerRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 24, paddingTop: 10, marginBottom: 20 },
    backBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    headerTitleWrap: { alignItems: 'center' },
    headerTitle: { color: '#fff', fontSize: 18, fontWeight: '800', letterSpacing: 0.5 },
    headerSubWrap: { flexDirection: 'row', alignItems: 'center', marginTop: 4, gap: 4 },
    statusDotSmall: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#10B981' },
    headerSubTxt: { fontSize: 11, color: '#94A3B8', fontWeight: '600' },
    topBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    topAddBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(59, 130, 246, 0.4)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#3B82F6' },

    // KPIs
    kpiWrapper: { height: 120, marginBottom: 24 },
    kpiScroll: { paddingHorizontal: 20, gap: 14 },
    kpiCard: { backgroundColor: 'rgba(30,41,59,0.4)', borderRadius: 24, padding: 16, width: 160, borderWidth: 1 },
    kpiHeader: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 },
    kpiIconWrap: { width: 32, height: 32, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
    kpiLabel: { fontSize: 12, fontWeight: '700', color: '#CBD5E1' },
    kpiValue: { fontSize: 22, fontWeight: '900', color: '#fff', letterSpacing: -0.5, marginBottom: 6 },
    kpiSub: { fontSize: 11, fontWeight: '700' },

    // Filters
    filterWrapper: { paddingHorizontal: 20, marginTop: -40, zIndex: 20 },
    filterInner: { backgroundColor: '#fff', borderRadius: 24, padding: 16, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.08, shadowRadius: 24, elevation: 8, borderWidth: 1, borderColor: '#F1F5F9' },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 16, paddingHorizontal: 16, height: 50, borderWidth: 1, borderColor: '#E2E8F0', marginBottom: 12 },
    searchInput: { flex: 1, fontSize: 14, color: '#0F172A', fontWeight: '600', marginLeft: 8 },
    filterBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingLeft: 12, borderLeftWidth: 1, borderLeftColor: '#E2E8F0' },
    filterBtnTxt: { fontSize: 13, fontWeight: '700', color: '#0F172A' },
    
    pillRow: { flexDirection: 'row', gap: 8 },
    pillBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 4, paddingVertical: 10, backgroundColor: '#F8FAFC', borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    pillTxt: { fontSize: 12, fontWeight: '700', color: '#334155' },
    sortBtn: { width: 42, height: 42, borderRadius: 12, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#E2E8F0' },

    dateFilters: { flexDirection: 'row', marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: '#F1F5F9', gap: 12 },

    listHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, marginTop: 24, marginBottom: 12 },
    listTitle: { fontSize: 16, fontWeight: '900', color: '#0F172A' },
    sortDropBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    sortDropTxt: { fontSize: 13, fontWeight: '700', color: '#64748B' },

    // List Cards
    list: { paddingHorizontal: 20, paddingBottom: 100 },
    card: { backgroundColor: '#fff', borderRadius: 24, padding: 16, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4, borderWidth: 1, borderColor: '#F1F5F9' },
    cardTop: { flexDirection: 'row', alignItems: 'flex-start' },
    cardIconBox: { width: 50, height: 50, borderRadius: 16, alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    cardInfo: { flex: 1 },
    cardHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 },
    cardTitle: { fontSize: 15, fontWeight: '900', color: '#0F172A', flex: 1, paddingRight: 8 },
    statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, marginBottom: 4 },
    statusCompleted: { backgroundColor: '#ECFDF5' },
    statusPlanned: { backgroundColor: '#FEF2F2' },
    statusTxt: { fontSize: 9, fontWeight: '900', letterSpacing: 0.5 },
    amountTxt: { fontSize: 16, fontWeight: '900', color: '#0F172A', alignSelf: 'flex-end' },
    
    typePill: { alignSelf: 'flex-start', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, marginBottom: 12 },
    typePillTxt: { fontSize: 9, fontWeight: '900', letterSpacing: 0.5, textTransform: 'uppercase' },
    
    datePersonRow: { flexDirection: 'row', alignItems: 'center' },
    dpTxt: { fontSize: 11, fontWeight: '600', color: '#64748B', marginLeft: 4 },

    cardDivider: { height: 1, backgroundColor: '#F1F5F9', marginVertical: 16 },
    
    cardBottom: { flexDirection: 'row', alignItems: 'flex-start' },
    cbItem: { flex: 1, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    cbLabel: { fontSize: 10, fontWeight: '600', color: '#94A3B8', marginBottom: 2 },
    cbVal: { fontSize: 11, fontWeight: '800', color: '#334155' },
    cbDivider: { width: 1, height: 30, backgroundColor: '#F1F5F9', marginHorizontal: 8 },

    empty: { alignItems: 'center', marginTop: 80 },
    emptyTxt: { color: '#94A3B8', marginTop: 16, fontWeight: '600', fontSize: 16 }
});
