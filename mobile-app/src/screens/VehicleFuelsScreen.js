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
        <View style={s.kpiWrapper}>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.kpiScroll}>
                
                <View style={[s.kpiCard, { borderColor: 'rgba(56, 189, 248, 0.3)' }]}>
                    <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                    <Text style={s.kpiLabel}>Toplam Yakıt</Text>
                    <Text style={[s.kpiValue, {color: '#38BDF8'}]}>{Number(summary?.month_liters || 0).toLocaleString('tr-TR')} L</Text>
                    <Text style={s.kpiSub}>Bu Ay</Text>
                </View>

                <View style={[s.kpiCard, { borderColor: 'rgba(16, 185, 129, 0.3)' }]}>
                    <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                    <Text style={s.kpiLabel}>Toplam Tutar</Text>
                    <Text style={[s.kpiValue, {color: '#10B981'}]}>₺{Number(summary?.month_total || 0).toLocaleString('tr-TR')}</Text>
                    <Text style={s.kpiSub}>Bu Ay</Text>
                </View>

                <View style={[s.kpiCard, { borderColor: 'rgba(167, 139, 250, 0.3)' }]}>
                    <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                    <Text style={s.kpiLabel}>Ort. Tüketim</Text>
                    <Text style={[s.kpiValue, {color: '#A78BFA', fontSize: 20}]}>33,6 <Text style={{fontSize: 12}}>L/100 km</Text></Text>
                    <Text style={s.kpiSub}>Bu Ay</Text>
                </View>

                <View style={[s.kpiCard, { borderColor: 'rgba(251, 146, 60, 0.3)' }]}>
                    <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                    <Text style={s.kpiLabel}>Toplam İşlem</Text>
                    <Text style={[s.kpiValue, {color: '#FB923C'}]}>{summary?.month_count || 0}</Text>
                    <Text style={s.kpiSub}>Bu Ay</Text>
                </View>

            </ScrollView>
        </View>
    );

    const renderFilters = () => (
        <View style={s.filterContainer}>
            <View style={s.searchBar}>
                <Icon name="magnify" size={20} color="#94A3B8" />
                <TextInput 
                    style={s.searchInput} 
                    placeholder="Tarih, istasyon veya açıklama ara..." 
                    value={search}
                    onChangeText={setSearch}
                />
                <TouchableOpacity onPress={() => setShowFilters(!showFilters)} style={s.filterToggle}>
                    <Icon name={showFilters ? "filter-variant-plus" : "filter-variant"} size={22} color={showFilters ? "#0F172A" : "#64748B"} />
                </TouchableOpacity>
            </View>
            
            <View style={s.pillRow}>
                <TouchableOpacity style={s.filterPill}>
                    <Icon name="calendar-range" size={16} color="#64748B" />
                    <View>
                        <Text style={s.pillLabel}>Tarih Aralığı</Text>
                        <Text style={s.pillValue}>Son 30 Gün</Text>
                    </View>
                </TouchableOpacity>
                <TouchableOpacity style={s.filterPill}>
                    <Icon name="gas-station" size={16} color="#64748B" />
                    <View>
                        <Text style={s.pillLabel}>İstasyon</Text>
                        <Text style={s.pillValue}>Tümü</Text>
                    </View>
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
            <View style={s.cardImageWrap}>
                <LinearGradient colors={['#EFF6FF', '#DBEAFE']} style={StyleSheet.absoluteFillObject} />
                <Icon name="gas-station" size={28} color="#3B82F6" />
            </View>

            <View style={s.cardContent}>
                <View style={s.cardHeaderRow}>
                    <Text style={s.dateTxt}>{new Date(item.date).toLocaleDateString('tr-TR')} · {new Date(item.date).toLocaleTimeString('tr-TR', {hour:'2-digit', minute:'2-digit'})}</Text>
                    <Text style={s.priceTxt}>₺{Number(item.total_cost).toLocaleString('tr-TR')}</Text>
                </View>
                
                <View style={s.cardMiddleRow}>
                    <Text style={s.stationName}>{item.station_name}</Text>
                    <Text style={s.literTxt}>{item.liters} L</Text>
                </View>

                <View style={s.cardBottomRow}>
                    <Text style={s.unitPriceTxt}>Birim Fiyat: ₺{Number(item.price_per_liter).toLocaleString('tr-TR')}/L</Text>
                    <View style={s.kmWrap}>
                        <Icon name="map-marker-outline" size={12} color="#94A3B8" />
                        <Text style={s.kmTxt}>{Number(item.km).toLocaleString('tr-TR')} km</Text>
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
                            <Icon name="arrow-left" size={22} color="#fff" />
                        </TouchableOpacity>
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Yakıtlar</Text></View>
                        <TouchableOpacity onPress={() => setModalVisible(true)} style={s.addHeaderBtn}><Icon name="plus" size={24} color="#fff" /></TouchableOpacity>
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
                        <View style={{marginTop: -40, zIndex: 20}}>
                            {renderSummary()}
                        </View>
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

            {/* Bottom Total Bar */}
            <View style={s.bottomBar}>
                <View style={s.bottomItem}>
                    <View style={s.bottomIconBox}><Icon name="gas-station" size={20} color="#64748B" /></View>
                    <View>
                        <Text style={s.bottomLabel}>Toplam Tutar</Text>
                        <Text style={s.bottomValue}>₺{Number(summary?.month_total || 0).toLocaleString('tr-TR')}</Text>
                    </View>
                </View>
                <View style={s.bottomDivider} />
                <View style={s.bottomItem}>
                    <View style={s.bottomIconBox}><Icon name="water-outline" size={20} color="#64748B" /></View>
                    <View>
                        <Text style={s.bottomLabel}>Toplam Litre</Text>
                        <Text style={s.bottomValue}>{Number(summary?.month_liters || 0).toLocaleString('tr-TR')} L</Text>
                    </View>
                </View>
            </View>

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
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    
    // Header
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden' },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, paddingTop: 10, paddingBottom: 50 },
    backBtn: { width: 46, height: 46, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', shadowColor: '#fff', shadowOffset: {width:0, height:4}, shadowOpacity: 0.1, shadowRadius: 10 },
    headerTitle: { fontSize: 18, fontWeight: '800', color: '#fff', letterSpacing: 0.5 },
    addHeaderBtn: { width: 46, height: 46, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    
    // KPIs (Inside Header Area)
    kpiWrapper: { height: 110, marginBottom: 20 },
    kpiScroll: { paddingHorizontal: 20, gap: 14 },
    kpiCard: { backgroundColor: 'rgba(30,41,59,0.8)', borderRadius: 20, padding: 16, width: 115, borderWidth: 1, overflow: 'hidden' },
    kpiLabel: { fontSize: 11, fontWeight: '700', color: '#94A3B8', marginBottom: 6 },
    kpiValue: { fontSize: 24, fontWeight: '900', letterSpacing: -0.5, marginBottom: 4 },
    kpiSub: { fontSize: 10, fontWeight: '600', color: '#64748B' },

    // Filters & Search
    filterContainer: { paddingHorizontal: 20, marginBottom: 16, zIndex: 10 },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 30, paddingHorizontal: 20, height: 56, gap: 12, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.05, shadowRadius: 16, elevation: 4, marginBottom: 16 },
    searchInput: { flex: 1, fontSize: 15, color: '#0F172A', fontWeight: '600' },
    filterToggle: { padding: 6 },
    
    pillRow: { flexDirection: 'row', gap: 12 },
    filterPill: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', padding: 14, borderRadius: 20, gap: 10, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:4}, shadowOpacity: 0.04, shadowRadius: 10, elevation: 2 },
    pillLabel: { fontSize: 10, fontWeight: '700', color: '#94A3B8', marginBottom: 2 },
    pillValue: { fontSize: 13, fontWeight: '800', color: '#334155' },

    filterOptions: { marginTop: 16, gap: 12, backgroundColor: '#fff', padding: 16, borderRadius: 20 },
    filterRow: { flexDirection: 'row', gap: 12 },
    miniInput: { flex: 1, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, padding: 12, fontSize: 13, fontWeight: '600' },
    selectWrapper: { flex: 1 },

    // List
    list: { paddingBottom: 120 },
    card: { backgroundColor: '#fff', borderRadius: 24, padding: 16, marginHorizontal: 20, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4, flexDirection: 'row', alignItems: 'center' },
    cardImageWrap: { width: 60, height: 60, borderRadius: 16, alignItems: 'center', justifyContent: 'center', marginRight: 16, overflow: 'hidden' },
    cardContent: { flex: 1 },
    
    cardHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 },
    dateTxt: { fontSize: 11, color: '#94A3B8', fontWeight: '600' },
    priceTxt: { fontSize: 16, fontWeight: '900', color: '#10B981' },
    
    cardMiddleRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    stationName: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    literTxt: { fontSize: 15, fontWeight: '800', color: '#0F172A' },
    
    cardBottomRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    unitPriceTxt: { fontSize: 11, fontWeight: '600', color: '#64748B' },
    kmWrap: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    kmTxt: { fontSize: 11, fontWeight: '600', color: '#94A3B8' },

    // Bottom Bar
    bottomBar: { position: 'absolute', bottom: 24, left: 20, right: 20, backgroundColor: '#fff', borderRadius: 30, padding: 16, flexDirection: 'row', alignItems: 'center', shadowColor: '#0A1A3A', shadowOffset: {width:0, height:16}, shadowOpacity: 0.15, shadowRadius: 30, elevation: 15 },
    bottomItem: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 12, paddingHorizontal: 10 },
    bottomIconBox: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center' },
    bottomLabel: { fontSize: 11, fontWeight: '700', color: '#64748B', marginBottom: 2 },
    bottomValue: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    bottomDivider: { width: 1, height: 40, backgroundColor: '#E2E8F0' },

    empty: { alignItems: 'center', marginTop: 80 },
    emptyTxt: { color: '#94A3B8', marginTop: 16, fontWeight: '600', fontSize: 16 },

    // Modals
    mOverlay: { flex: 1, backgroundColor: 'rgba(2, 6, 23, 0.7)' },
    mContent: { backgroundColor: '#fff', borderTopLeftRadius: 36, borderTopRightRadius: 36, padding: 30, maxHeight: '90%' },
    mHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 },
    mTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    mClose: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    mForm: { gap: 20, paddingBottom: 50 },
    mInpGroup: { gap: 10 },
    mLabel: { fontSize: 13, fontWeight: '800', color: '#475569', marginLeft: 4, textTransform: 'uppercase', letterSpacing: 0.5 },
    mInp: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 18, fontSize: 16, fontWeight: '600', color: '#0F172A' },
    mSaveBtn: { backgroundColor: '#0F172A', padding: 20, borderRadius: 20, alignItems: 'center', marginTop: 10, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.3, shadowRadius: 15, elevation: 6 },
    mSaveText: { color: '#fff', fontSize: 17, fontWeight: '900' }
});

