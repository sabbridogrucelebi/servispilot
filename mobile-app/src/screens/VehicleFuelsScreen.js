import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, ScrollView, Modal, TextInput, Alert, KeyboardAvoidingView, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import DatePickerInput from '../components/DatePickerInput';
import { toApiDate, toUiDate, todayUi } from '../utils/date';

export default function VehicleFuelsScreen({ route, navigation }) {
    const { vehicleId, plate } = route.params || {};
    const [fuels, setFuels] = useState([]);
    const [summary, setSummary] = useState(null);
    const [options, setOptions] = useState({ stations: [], fuel_types: [] });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    
    // Filters
    const [showFilters, setShowFilters] = useState(false);
    const [search, setSearch] = useState('');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [selectedStation, setSelectedStation] = useState('');
    const [selectedFuelType, setSelectedFuelType] = useState('');

    // Add Modal
    const [modalVisible, setModalVisible] = useState(false);
    const [form, setForm] = useState({ date: todayUi(), km: '', liters: '', price_per_liter: '', station_id: '', fuel_type: 'Dizel', notes: '' });
    const [saving, setSaving] = useState(false);

    const fetchFuels = async (isRefreshing = false) => {
        if (!vehicleId) return;
        if (!isRefreshing) setLoading(true);
        try {
            const params = { 
                search, 
                start_date: toApiDate(startDate), 
                end_date: toApiDate(endDate), 
                station: selectedStation, 
                fuel_type: selectedFuelType 
            };
            const r = await api.get(`/vehicles/${vehicleId}/fuels`, { params });
            setFuels(r.data.fuels);
            setSummary(r.data.summary);
            setOptions(r.data.options);
        } catch (e) { console.error(e); }
        finally { setLoading(false); setRefreshing(false); }
    };

    useEffect(() => { fetchFuels(); }, [search, startDate, endDate, selectedStation, selectedFuelType]);

    const handleSave = async () => {
        if (!form.km || !form.liters || !form.price_per_liter) {
            Alert.alert('Hata', 'Lütfen zorunlu alanları doldurun.');
            return;
        }
        setSaving(true);
        try {
            await api.post(`/vehicles/${vehicleId}/fuels`, {
                ...form,
                date: toApiDate(form.date),
                total_cost: parseFloat(form.liters) * parseFloat(form.price_per_liter)
            });
            setModalVisible(false);
            setForm({ date: todayUi(), km: '', liters: '', price_per_liter: '', station_id: '', fuel_type: 'Dizel', notes: '' });
            fetchFuels();
        } catch (e) {
            Alert.alert('Hata', 'Kayıt eklenemedi.');
        } finally {
            setSaving(false);
        }
    };

    const renderSummary = () => (
        <View style={s.summaryContainer}>
            <View style={s.summaryHeader}>
                <Text style={s.summaryTitle}>Yakıt Özeti</Text>
                <Text style={s.summarySub}>Ay bazlı maliyet görünümü</Text>
            </View>
            
            <View style={s.summaryGrid}>
                {/* Big Orange Card: Month Total */}
                <LinearGradient colors={['#F59E0B', '#D97706']} style={s.sumCardFull}>
                    <Text style={s.sumLabel}>BU AY YAKIT GİDERİ</Text>
                    <Text style={s.sumValLarge}>{Number(summary?.month_total || 0).toLocaleString('tr-TR')} ₺</Text>
                </LinearGradient>

                {/* Thin Gray Card: All Time Total */}
                <View style={s.sumCardThin}>
                    <Text style={s.sumLabelSmall}>BUGÜNE KADARKİ TOPLAM</Text>
                    <Text style={s.sumValMedium}>{Number(summary?.all_time_total || 0).toLocaleString('tr-TR')} ₺</Text>
                </View>

                {/* Big Blue Card: Month KM */}
                <LinearGradient colors={['#4F46E5', '#3730A3']} style={s.sumCardFull}>
                    <Text style={s.sumLabel}>BU AY YAPILAN KM</Text>
                    <Text style={s.sumValLarge}>{Number(summary?.month_km || 0).toLocaleString('tr-TR')} KM</Text>
                    <Text style={s.sumDetail}>İlk KM: {Number(summary?.month_first_km || 0).toLocaleString('tr-TR')} · Son KM: {Number(summary?.month_last_km || 0).toLocaleString('tr-TR')}</Text>
                </LinearGradient>

                {/* Grid: Liters and Count */}
                <View style={s.sumRow}>
                    <View style={s.sumCardHalf}>
                        <Text style={s.sumLabelSmall}>SEÇİLİ AY LİTRE</Text>
                        <Text style={s.sumValSmall}>{Number(summary?.month_liters || 0).toLocaleString('tr-TR')}</Text>
                    </View>
                    <View style={s.sumCardHalf}>
                        <Text style={s.sumLabelSmall}>FİŞ ADEDİ</Text>
                        <Text style={s.sumValSmall}>{summary?.month_count || 0}</Text>
                    </View>
                </View>

                {/* Thin Gray Card: Last KM */}
                <View style={s.sumCardThin}>
                    <Text style={s.sumLabelSmall}>SON KM</Text>
                    <Text style={s.sumValMedium}>{Number(summary?.last_km || 0).toLocaleString('tr-TR')} KM</Text>
                </View>
            </View>
        </View>
    );

    const renderFilters = () => (
        <View style={s.filterContainer}>
            <View style={s.searchBar}>
                <Icon name="magnify" size={20} color="#94A3B8" />
                <TextInput 
                    style={s.searchInput} 
                    placeholder="Ara (İstasyon, KM, Not...)" 
                    value={search}
                    onChangeText={setSearch}
                />
                <TouchableOpacity onPress={() => setShowFilters(!showFilters)} style={s.filterToggle}>
                    <Icon name={showFilters ? "chevron-up" : "filter-variant"} size={20} color={showFilters ? "#4F46E5" : "#64748B"} />
                </TouchableOpacity>
            </View>
            {showFilters && (
                <View style={s.filterOptions}>
                    <View style={s.filterRow}>
                        <View style={{flex:1}}><DatePickerInput placeholder="Başlangıç" value={startDate} onChange={setStartDate} /></View>
                        <View style={{flex:1}}><DatePickerInput placeholder="Bitiş" value={endDate} onChange={setEndDate} /></View>
                    </View>
                    <View style={s.filterRow}>
                        <View style={s.selectWrapper}>
                            <TextInput style={s.miniInput} placeholder="İstasyon" value={selectedStation} onChangeText={setSelectedStation} />
                        </View>
                        <View style={s.selectWrapper}>
                            <TextInput style={s.miniInput} placeholder="Yakıt Türü" value={selectedFuelType} onChangeText={setSelectedFuelType} />
                        </View>
                    </View>
                </View>
            )}
        </View>
    );

    const renderItem = ({ item }) => (
        <View style={s.card}>
            <View style={s.cardHeader}>
                <View style={s.headerMain}>
                    <View style={s.stationBox}>
                        <Icon name="gas-station" size={18} color="#4F46E5" />
                        <Text style={s.stationName}>{item.station_name}</Text>
                    </View>
                    <Text style={s.dateTxt}>{new Date(item.date).toLocaleDateString('tr-TR')}</Text>
                </View>
                <View style={s.amountBox}>
                    <Text style={[s.totalAmount, { color: item.is_paid ? '#10B981' : '#EF4444' }]}>
                        {Number(item.total_cost).toLocaleString('tr-TR')} ₺
                    </Text>
                    <View style={[s.statusBadge, { backgroundColor: item.is_paid ? '#ECFDF5' : '#FEF2F2' }]}>
                        <Text style={[s.statusText, { color: item.is_paid ? '#10B981' : '#EF4444' }]}>
                            {item.is_paid ? 'ÖDENDİ' : 'BEKLİYOR'}
                        </Text>
                    </View>
                </View>
            </View>

            <View style={s.infoGrid}>
                <View style={s.infoItem}>
                    <Text style={s.infoLabel}>KM</Text>
                    <Text style={s.infoVal}>{Number(item.km).toLocaleString('tr-TR')}</Text>
                </View>
                <View style={s.infoItem}>
                    <Text style={s.infoLabel}>KM FARKI</Text>
                    <View style={s.diffPill}>
                        <Text style={s.diffText}>+{item.km_diff} KM</Text>
                    </View>
                </View>
                <View style={s.infoItem}>
                    <Text style={s.infoLabel}>LİTRE</Text>
                    <Text style={s.infoVal}>{item.liters} LT</Text>
                </View>
            </View>

            <View style={s.cardFooter}>
                <View style={s.footerDetail}>
                    <Text style={s.footerLabel}>BİRİM:</Text>
                    <Text style={s.footerVal}>{Number(item.price_per_liter).toLocaleString('tr-TR')} ₺</Text>
                </View>
                <View style={s.footerDetail}>
                    <Text style={s.footerLabel}>TÜR:</Text>
                    <Text style={s.footerVal}>{item.fuel_type} {item.km_per_liter > 0 && `(${item.km_per_liter} km/l)`}</Text>
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
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Yakıtlar</Text></View>
                        <TouchableOpacity onPress={() => setModalVisible(true)} style={s.addHeaderBtn}><Icon name="plus" size={20} color="#fff" /></TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <FlatList
                data={fuels}
                renderItem={renderItem}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={s.list}
                ListHeaderComponent={() => (
                    <>
                        {renderSummary()}
                        {renderFilters()}
                    </>
                )}
                onRefresh={() => { setRefreshing(true); fetchFuels(true); }}
                refreshing={refreshing}
                ListEmptyComponent={loading ? null : (
                    <View style={s.empty}>
                        <Icon name="gas-station-off" size={48} color="#CBD5E1" />
                        <Text style={s.emptyTxt}>Yakıt kaydı bulunamadı.</Text>
                    </View>
                )}
            />

            <Modal visible={modalVisible} animationType="slide" transparent>
                <View style={s.mOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{flex:1, justifyContent:'flex-end'}}>
                        <View style={s.mContent}>
                            <View style={s.mHeader}>
                                <Text style={s.mTitle}>Yeni Yakıt Kaydı</Text>
                                <TouchableOpacity onPress={() => setModalVisible(false)} style={s.mClose}>
                                    <Icon name="close" size={24} color="#64748B" />
                                </TouchableOpacity>
                            </View>
                            <ScrollView showsVerticalScrollIndicator={false}>
                                <View style={s.mForm}>
                                    <DatePickerInput label="Tarih" value={form.date} onChange={d => setForm({...form, date: d})} />
                                    <View style={s.mInpGroup}><Text style={s.mLabel}>KM</Text><TextInput style={s.mInp} value={form.km} keyboardType="numeric" onChangeText={t => setForm({...form, km:t})} placeholder="Örn: 150000" /></View>
                                    <View style={s.mInpGroup}><Text style={s.mLabel}>Litre</Text><TextInput style={s.mInp} value={form.liters} keyboardType="numeric" onChangeText={t => setForm({...form, liters:t})} placeholder="Örn: 50" /></View>
                                    <View style={s.mInpGroup}><Text style={s.mLabel}>Birim Fiyat</Text><TextInput style={s.mInp} value={form.price_per_liter} keyboardType="numeric" onChangeText={t => setForm({...form, price_per_liter:t})} placeholder="Örn: 45.50" /></View>
                                    <View style={s.mInpGroup}><Text style={s.mLabel}>İstasyon Adı</Text><TextInput style={s.mInp} value={form.station_name} onChangeText={t => setForm({...form, station_name:t})} placeholder="Örn: Shell" /></View>
                                    <TouchableOpacity style={s.mSaveBtn} onPress={handleSave} disabled={saving}>
                                        {saving ? <ActivityIndicator color="#fff" /> : <Text style={s.mSaveText}>Yakıt Kaydını Kaydet</Text>}
                                    </TouchableOpacity>
                                </View>
                            </ScrollView>
                        </View>
                    </KeyboardAvoidingView>
                </View>
            </Modal>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 16, paddingHorizontal: 16 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 10, marginTop: 10 },
    headerTitle: { color: '#fff', fontSize: 16, fontWeight: '800' },
    addHeaderBtn: { width: 32, height: 32, borderRadius: 8, backgroundColor: 'rgba(255,255,255,0.2)', alignItems: 'center', justifyContent: 'center' },

    summaryContainer: { padding: 20, backgroundColor: '#fff' },
    summaryHeader: { marginBottom: 15 },
    summaryTitle: { fontSize: 22, fontWeight: '900', color: '#0F172A' },
    summarySub: { fontSize: 13, color: '#64748B', marginTop: 2 },
    summaryGrid: { gap: 12 },
    sumCardFull: { padding: 20, borderRadius: 24, gap: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10, elevation: 4 },
    sumCardThin: { paddingHorizontal: 20, paddingVertical: 14, borderRadius: 16, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', gap: 2 },
    sumRow: { flexDirection: 'row', gap: 12 },
    sumCardHalf: { flex: 1, padding: 16, borderRadius: 20, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0', gap: 4 },
    sumLabel: { fontSize: 11, fontWeight: '800', color: 'rgba(255,255,255,0.8)', letterSpacing: 0.5 },
    sumLabelSmall: { fontSize: 10, fontWeight: '800', color: '#94A3B8', letterSpacing: 0.5 },
    sumValLarge: { fontSize: 26, fontWeight: '900', color: '#fff' },
    sumValMedium: { fontSize: 18, fontWeight: '800', color: '#334155' },
    sumValSmall: { fontSize: 16, fontWeight: '800', color: '#0F172A' },
    sumDetail: { fontSize: 10, color: 'rgba(255,255,255,0.7)', marginTop: 4, fontWeight: '600' },

    filterContainer: { paddingHorizontal: 20, paddingBottom: 10, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10, gap: 8, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, fontSize: 14, color: '#0F172A', fontWeight: '600' },
    filterToggle: { padding: 4 },
    filterOptions: { marginTop: 12, gap: 10 },
    filterRow: { flexDirection: 'row', gap: 10 },
    miniInput: { flex: 1, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 10, padding: 8, fontSize: 12, fontWeight: '600' },
    selectWrapper: { flex: 1 },

    list: { paddingBottom: 40 },
    card: { backgroundColor: '#fff', borderRadius: 24, padding: 20, marginHorizontal: 20, marginTop: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.06, shadowRadius: 15, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16, paddingBottom: 16, borderBottomWidth: 1, borderBottomColor: '#F8FAFC' },
    headerMain: { flex: 1 },
    stationBox: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 4 },
    stationName: { fontSize: 15, fontWeight: '900', color: '#1E293B' },
    dateTxt: { fontSize: 12, color: '#94A3B8', fontWeight: '600' },
    amountBox: { alignItems: 'flex-end' },
    totalAmount: { fontSize: 20, fontWeight: '900' },
    statusBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, marginTop: 4 },
    statusText: { fontSize: 9, fontWeight: '900' },

    infoGrid: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 16 },
    infoItem: { flex: 1 },
    infoLabel: { fontSize: 10, fontWeight: '800', color: '#94A3B8', marginBottom: 6 },
    infoVal: { fontSize: 14, fontWeight: '800', color: '#334155' },
    diffPill: { alignSelf: 'flex-start', backgroundColor: '#ECFDF5', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8 },
    diffText: { fontSize: 11, fontWeight: '800', color: '#10B981' },

    cardFooter: { flexDirection: 'row', gap: 20, paddingTop: 14, borderTopWidth: 1, borderTopColor: '#F8FAFC' },
    footerDetail: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    footerLabel: { fontSize: 10, fontWeight: '800', color: '#94A3B8' },
    footerVal: { fontSize: 11, fontWeight: '800', color: '#64748B' },

    empty: { alignItems: 'center', marginTop: 60 },
    emptyTxt: { color: '#94A3B8', marginTop: 12, fontWeight: '600' },

    mOverlay: { flex: 1, backgroundColor: 'rgba(15, 23, 42, 0.6)' },
    mContent: { backgroundColor: '#fff', borderTopLeftRadius: 30, borderTopRightRadius: 30, padding: 25, maxHeight: '90%' },
    mHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
    mTitle: { fontSize: 22, fontWeight: '900', color: '#0F172A' },
    mClose: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    mForm: { gap: 15, paddingBottom: 40 },
    mInpGroup: { gap: 8 },
    mLabel: { fontSize: 13, fontWeight: '800', color: '#64748B', marginLeft: 4 },
    mInp: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 15, fontSize: 15, fontWeight: '600', color: '#0F172A' },
    mSaveBtn: { backgroundColor: '#10B981', padding: 18, borderRadius: 20, alignItems: 'center', marginTop: 10, shadowColor: '#10B981', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.3, shadowRadius: 15, elevation: 5 },
    mSaveText: { color: '#fff', fontSize: 16, fontWeight: '900' }
});

