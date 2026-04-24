import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, TextInput, ScrollView, RefreshControl, Alert } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as FileSystem from 'expo-file-system';
import * as Sharing from 'expo-sharing';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import DatePickerInput from '../components/DatePickerInput';
import { toApiDate, todayUi } from '../utils/date';

export default function VehicleMaintenancesScreen({ route, navigation }) {
    const { vehicleId, plate } = route.params || {};
    const [maintenances, setMaintenances] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    
    // Filters
    const [search, setSearch] = useState('');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [showFilters, setShowFilters] = useState(false);

    useEffect(() => {
        fetchMaintenances();
    }, [search, startDate, endDate]);

    const fetchMaintenances = async (isRefreshing = false) => {
        if (!vehicleId) return;
        if (!isRefreshing) setLoading(true);
        try {
            const params = { 
                search, 
                start_date: toApiDate(startDate), 
                end_date: toApiDate(endDate) 
            };
            const r = await api.get(`/vehicles/${vehicleId}/maintenances`, { params });
            setMaintenances(r.data.maintenances);
        } catch (e) { console.error(e); }
        finally { setLoading(false); setRefreshing(false); }
    };

    const onRefresh = () => {
        setRefreshing(true);
        fetchMaintenances(true);
    };

    const handleExportPdf = async () => {
        try {
            setLoading(true);
            const token = await AsyncStorage.getItem('userToken');
            const baseUrl = api.defaults.baseURL;
            const queryParams = `start_date=${toApiDate(startDate) || ''}&end_date=${toApiDate(endDate) || ''}&search=${search || ''}&token=${token}`;
            const url = `${baseUrl}/vehicles/${vehicleId}/maintenances/export-pdf?${queryParams}`;

            if (Platform.OS === 'web') {
                window.location.href = url;
                setLoading(false);
                return;
            }

            const fileUri = `${FileSystem.documentDirectory}${plate}_bakimlari.pdf`;
            
            const downloadRes = await FileSystem.downloadAsync(url, fileUri, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });

            if (downloadRes.status === 200) {
                await Sharing.shareAsync(downloadRes.uri, { 
                    mimeType: 'application/pdf',
                    dialogTitle: 'PDF Raporunu Paylaş / Görüntüle'
                });
            } else {
                Alert.alert('Hata', 'PDF oluşturulurken bir sorun oluştu.');
            }
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Dosya indirilemedi.');
        } finally {
            setLoading(false);
        }
    };

    const renderItem = ({ item }) => {
        // Mock icons based on type
        const t = (item.type || '').toLowerCase();
        let iconName = 'wrench';
        let iconColor = '#8B5CF6';
        let iconBg = '#F3E8FF';
        
        if (t.includes('yağ')) { iconName = 'water-drop'; iconColor = '#A855F7'; iconBg = '#F3E8FF'; }
        else if (t.includes('lastik')) { iconName = 'tire'; iconColor = '#10B981'; iconBg = '#ECFDF5'; }
        else if (t.includes('fren')) { iconName = 'car-brake-alert'; iconColor = '#F59E0B'; iconBg = '#FEF3C7'; }

        const isCompleted = item.status !== 'PLANLANDI';

        return (
            <View style={s.card}>
                <View style={s.cardTop}>
                    <View style={[s.cardIconBox, { backgroundColor: iconBg }]}>
                        <Icon name={iconName} size={28} color={iconColor} />
                    </View>
                    
                    <View style={s.cardInfo}>
                        <View style={s.cardHeaderRow}>
                            <Text style={s.cardTitle}>{item.title}</Text>
                            <View style={{alignItems: 'flex-end'}}>
                                <View style={[s.statusBadge, isCompleted ? s.statusCompleted : s.statusPlanned]}>
                                    <Icon name={isCompleted ? "check" : "circle"} size={10} color={isCompleted ? "#10B981" : "#F59E0B"} style={{marginRight: 4}} />
                                    <Text style={[s.statusTxt, {color: isCompleted ? '#10B981' : '#F59E0B'}]}>{isCompleted ? 'TAMAMLANDI' : 'PLANLANDI'}</Text>
                                </View>
                                <Text style={s.amountTxt}>{Number(item.amount || 0).toLocaleString('tr-TR')} ₺</Text>
                            </View>
                        </View>
                        
                        <View style={[s.typePill, { backgroundColor: iconBg }]}>
                            <Text style={[s.typePillTxt, { color: iconColor }]}>{item.type || 'GENEL BAKIM'}</Text>
                        </View>

                        <View style={s.datePersonRow}>
                            <Icon name="calendar-blank" size={14} color="#94A3B8" />
                            <Text style={s.dpTxt}>{item.date ? new Date(item.date).toLocaleDateString('tr-TR') : '-'}</Text>
                            <Icon name="clock-outline" size={14} color="#94A3B8" style={{marginLeft: 8}} />
                            <Text style={s.dpTxt}>-</Text>
                            <Icon name="account-outline" size={14} color="#94A3B8" style={{marginLeft: 8}} />
                            <Text style={s.dpTxt}>{item.driver || 'Bilinmiyor'}</Text>
                        </View>
                    </View>
                </View>

                <View style={s.cardDivider} />

                <View style={s.cardBottom}>
                    <View style={s.cbItem}>
                        <Icon name="speedometer" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>KM</Text>
                            <Text style={s.cbVal}>{Number(item.km || 0).toLocaleString('tr-TR')} km</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={s.cbItem}>
                        <Icon name="shield-check-outline" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>Servis</Text>
                            <Text style={s.cbVal} numberOfLines={1}>{item.service_name || '-'}</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={[s.cbItem, {flex: 1.5}]}>
                        <Icon name="file-document-outline" size={16} color="#94A3B8" />
                        <View style={{flex: 1}}>
                            <Text style={s.cbLabel}>Not</Text>
                            <Text style={s.cbVal} numberOfLines={2}>{item.description || '-'}</Text>
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
                            <Text style={s.headerTitle}>{plate} · Bakımlar</Text>
                            <View style={s.headerSubWrap}>
                                <View style={s.statusDotSmall} />
                                <Text style={s.headerSubTxt}>Aktif • TEMSA PRESTİJ • MİDİBÜS</Text>
                            </View>
                        </View>
                        <View style={{flexDirection:'row', gap: 8}}>
                            <TouchableOpacity onPress={handleExportPdf} style={s.topBtn}><Icon name="file-pdf-box" size={22} color="#fff" /><Text style={{color:'#fff', fontSize: 10, fontWeight: '700'}}>PDF</Text></TouchableOpacity>
                            <TouchableOpacity style={s.topAddBtn}><Icon name="plus" size={22} color="#fff" /></TouchableOpacity>
                        </View>
                    </View>

                    {/* KPI Cards */}
                    <View style={s.kpiWrapper}>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.kpiScroll}>
                            
                            <View style={[s.kpiCard, { borderColor: 'rgba(56, 189, 248, 0.3)' }]}>
                                <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: 'rgba(56, 189, 248, 0.1)'}]}><Icon name="calendar-month" size={18} color="#38BDF8" /></View>
                                    <Text style={s.kpiLabel}>Bu Ay Bakım</Text>
                                </View>
                                <Text style={s.kpiValue}>2 <Text style={{fontSize: 14, color: '#94A3B8'}}>İşlem</Text></Text>
                                <Text style={[s.kpiSub, {color: '#10B981'}]}>+1 vs geçen ay</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(20, 184, 166, 0.3)' }]}>
                                <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: 'rgba(20, 184, 166, 0.1)'}]}><Icon name="currency-try" size={18} color="#14B8A6" /></View>
                                    <Text style={s.kpiLabel}>Tahmini Maliyet</Text>
                                </View>
                                <Text style={s.kpiValue}>1.250,00 ₺</Text>
                                <Text style={[s.kpiSub, {color: '#10B981'}]}>-250,00 ₺ vs geçen ay</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(245, 158, 11, 0.3)' }]}>
                                <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: 'rgba(245, 158, 11, 0.1)'}]}><Icon name="briefcase-outline" size={18} color="#F59E0B" /></View>
                                    <Text style={s.kpiLabel}>Açık İş Emri</Text>
                                </View>
                                <Text style={s.kpiValue}>1 <Text style={{fontSize: 14, color: '#94A3B8'}}>Aktif</Text></Text>
                                <Text style={[s.kpiSub, {color: '#F59E0B'}]}>Aksiyon bekliyor</Text>
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
                            placeholder="Bakım, servis veya açıklama ara..." 
                            value={search}
                            onChangeText={setSearch}
                        />
                        <TouchableOpacity style={s.filterBtn} onPress={() => setShowFilters(!showFilters)}>
                            <Icon name="filter-variant" size={18} color="#0F172A" />
                            <Text style={s.filterBtnTxt}>Filtrele</Text>
                        </TouchableOpacity>
                    </View>

                    <View style={s.pillRow}>
                        <TouchableOpacity style={s.pillBtn}><Icon name="layers-outline" size={16} color="#64748B" /><Text style={s.pillTxt}>Tümü</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}><Icon name="calendar-blank" size={16} color="#64748B" /><Text style={s.pillTxt}>Tarih</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}><Icon name="wrench" size={16} color="#64748B" /><Text style={s.pillTxt}>Tür</Text><Icon name="chevron-down" size={16} color="#94A3B8" /></TouchableOpacity>
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
                <Text style={s.listTitle}>Bakım Geçmişi</Text>
                <TouchableOpacity style={s.sortDropBtn}>
                    <Icon name="sort" size={16} color="#64748B" />
                    <Text style={s.sortDropTxt}>En Yeni</Text>
                    <Icon name="chevron-down" size={16} color="#94A3B8" />
                </TouchableOpacity>
            </View>

            {loading ? <ActivityIndicator style={{marginTop:40}} color="#8B5CF6" size="large" /> : (
                <FlatList
                    data={maintenances}
                    renderItem={renderItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={s.list}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <Icon name="wrench-outline" size={48} color="#CBD5E1" />
                            <Text style={s.emptyTxt}>Bakım kaydı bulunamadı.</Text>
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
    statusPlanned: { backgroundColor: '#FEF3C7' },
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
