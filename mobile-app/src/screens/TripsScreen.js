import React, { useState, useEffect, useContext } from 'react';
import { View, StyleSheet, ActivityIndicator, Alert, Text, TouchableOpacity, ScrollView, Modal, Platform, TextInput, KeyboardAvoidingView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';
import { EmptyState } from '../components';

const CURRENT_YEAR = new Date().getFullYear();
const YEARS = Array.from({length: 6}, (_, i) => (CURRENT_YEAR - 1 + i).toString());
const MONTHS = [
    { value: '1', label: 'Ocak' }, { value: '2', label: 'Şubat' }, { value: '3', label: 'Mart' },
    { value: '4', label: 'Nisan' }, { value: '5', label: 'Mayıs' }, { value: '6', label: 'Haziran' },
    { value: '7', label: 'Temmuz' }, { value: '8', label: 'Ağustos' }, { value: '9', label: 'Eylül' },
    { value: '10', label: 'Ekim' }, { value: '11', label: 'Kasım' }, { value: '12', label: 'Aralık' }
];

export default function TripsScreen({ route, navigation }) {
    const { hasPermission } = useContext(AuthContext);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);

    const params = route?.params || {};

    // Filters
    const [customers, setCustomers] = useState([]);
    const [selectedCustomer, setSelectedCustomer] = useState(params.customer_id ? params.customer_id.toString() : '');
    const [selectedMonth, setSelectedMonth] = useState(params.month ? params.month.toString() : (new Date().getMonth() + 1).toString());
    const [selectedYear, setSelectedYear] = useState(params.year ? params.year.toString() : CURRENT_YEAR.toString());

    // Matrix Data
    const [monthDays, setMonthDays] = useState([]);
    const [serviceRoutes, setServiceRoutes] = useState([]);
    const [matrix, setMatrix] = useState({});
    const [summary, setSummary] = useState(null);
    const [vehicles, setVehicles] = useState([]);

    // Cell Modal
    const [cellModalVisible, setCellModalVisible] = useState(false);
    const [activeCell, setActiveCell] = useState(null);
    const [formData, setFormData] = useState({ price: '', morning_id: '', evening_id: '', status: 'Yapıldı' });

    // Selection Modal (3D Premium Alternative to Picker)
    const [selectionModalVisible, setSelectionModalVisible] = useState(false);
    const [selectionType, setSelectionType] = useState(null); // 'customer' | 'month' | 'year' | 'morning_vehicle' | 'evening_vehicle'

    const [exportingType, setExportingType] = useState(null);

    const exportReport = async (type) => {
        try {
            setExportingType(type);
            
            // Web ve Mobil uyumlu token alma
            const token = Platform.OS === 'web' 
                ? await AsyncStorage.getItem('userToken') 
                : await require('expo-secure-store').getItemAsync('userToken');
                
            if (!token) {
                Alert.alert("Hata", "Oturum bilgisi bulunamadı.");
                return;
            }

            const url = `${api.defaults.baseURL}/v1/trips/export-${type}?customer_id=${selectedCustomer}&month=${selectedMonth}&year=${selectedYear}`;
            const fileExt = type === 'excel' ? 'xlsx' : 'pdf';
            const customerName = selectedCustomerObj?.company_name.replace(/ /g, '_') || 'Firma';
            const monthName = selectedMonthObj?.label || 'Ay';
            const filename = `${customerName}_${monthName}_Puantaj.${fileExt}`;
            const fileUri = `${FileSystem.documentDirectory}${filename}`;

            const downloadRes = await FileSystem.downloadAsync(url, fileUri, {
                headers: {
                    Authorization: `Bearer ${token}`
                }
            });

            if (downloadRes.status === 200) {
                await Sharing.shareAsync(downloadRes.uri, {
                    dialogTitle: 'Puantaj Raporunu Paylaş'
                });
            } else {
                Alert.alert('Hata', 'Rapor indirilirken bir sorun oluştu.');
            }
        } catch (error) {
            console.error("Export Error:", error);
            Alert.alert('Hata', 'Rapor oluşturulamadı.');
        } finally {
            setExportingType(null);
        }
    };

    const fetchMatrix = async () => {
        try {
            setLoading(true);
            const res = await api.get('/v1/trips/matrix', {
                params: { customer_id: selectedCustomer, month: selectedMonth, year: selectedYear }
            });
            if (res.data.success) {
                const d = res.data.data;
                setCustomers(d.customers || []);
                if (!selectedCustomer && d.selectedCustomer) {
                    setSelectedCustomer(d.selectedCustomer.id.toString());
                }
                setMonthDays(d.monthDays || []);
                setServiceRoutes(d.serviceRoutes || []);
                setMatrix(d.matrix || {});
                setSummary(d.summary || null);
                setVehicles(d.vehicles || []);
            }
        } catch (err) {
            console.error(err);
            Alert.alert('Hata', 'Matris verisi alınamadı.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchMatrix(); }, [selectedCustomer, selectedMonth, selectedYear]);

    const openCellModal = (route, day) => {
        if (!hasPermission('trips.create') && !hasPermission('trips.edit')) {
            Alert.alert('Yetkisiz', 'Veri girişi yapmaya yetkiniz yok.');
            return;
        }
        const cellData = matrix[day.date_key]?.[route.id];
        let initialPrice = cellData?.value !== null && cellData?.value !== undefined ? cellData.value.toString() : '';
        let initialMorning = cellData?.morning_vehicle_id || cellData?.default_morning_vehicle_id || '';
        let initialEvening = cellData?.evening_vehicle_id || cellData?.default_evening_vehicle_id || '';

        setActiveCell({ route, day, cellData });
        setFormData({
            price: initialPrice,
            morning_id: initialMorning ? initialMorning.toString() : '',
            evening_id: initialEvening ? initialEvening.toString() : '',
            status: cellData?.trip_status || 'Yapıldı'
        });
        setCellModalVisible(true);
    };

    const handleSaveCell = async () => {
        if (!activeCell) return;
        setSaving(true);
        try {
            const payload = {
                service_route_id: activeCell.route.id,
                trip_date: activeCell.day.date_key,
                trip_price: formData.price,
                morning_vehicle_id: formData.morning_id,
                evening_vehicle_id: formData.evening_id,
                trip_status: formData.status
            };
            const res = await api.post('/v1/trips/upsert-cell', payload);
            if (res.data.success) {
                setCellModalVisible(false);
                fetchMatrix();
            } else {
                Alert.alert('Hata', res.data.message || 'Kayıt yapılamadı.');
            }
        } catch (e) {
            Alert.alert('Hata', 'Kayıt başarısız.');
        } finally {
            setSaving(false);
        }
    };

    const handleDeleteCell = async () => {
        if (!activeCell) return;
        setSaving(true);
        try {
            const payload = {
                service_route_id: activeCell.route.id,
                trip_date: activeCell.day.date_key,
                trip_price: '',
                trip_status: 'İptal'
            };
            const res = await api.post('/v1/trips/upsert-cell', payload);
            if (res.data.success) {
                setCellModalVisible(false);
                fetchMatrix();
            }
        } catch (e) {
            Alert.alert('Hata', 'Silme işlemi başarısız.');
        } finally {
            setSaving(false);
        }
    };

    const fmtMoney = (v) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 2 }).format(v || 0);

    const getSelectionData = () => {
        if (selectionType === 'customer') return customers.map(c => ({ label: c.company_name, value: c.id.toString() }));
        if (selectionType === 'month') return MONTHS;
        if (selectionType === 'year') return YEARS.map(y => ({ label: y, value: y }));
        if (selectionType === 'morning_vehicle' || selectionType === 'evening_vehicle') {
            const currentRoute = activeCell?.route;
            const defaultPlate = selectionType === 'morning_vehicle' ? currentRoute?.morning_plate : currentRoute?.evening_plate;
            return [
                { label: `Varsayılan: ${defaultPlate || 'Yok'}`, value: '' },
                ...vehicles.map(v => ({ label: v.plate, value: v.id.toString() }))
            ];
        }
        return [];
    };

    const handleSelectOption = (val) => {
        if (selectionType === 'customer') setSelectedCustomer(val);
        else if (selectionType === 'month') setSelectedMonth(val);
        else if (selectionType === 'year') setSelectedYear(val);
        else if (selectionType === 'morning_vehicle') setFormData({ ...formData, morning_id: val });
        else if (selectionType === 'evening_vehicle') setFormData({ ...formData, evening_id: val });
        setSelectionModalVisible(false);
    };

    const selectedCustomerObj = customers.find(c => c.id.toString() === selectedCustomer);
    const selectedMonthObj = MONTHS.find(m => m.value === selectedMonth);
    const morningVehicleObj = formData.morning_id ? vehicles.find(v => v.id.toString() === formData.morning_id) : null;
    const eveningVehicleObj = formData.evening_id ? vehicles.find(v => v.id.toString() === formData.evening_id) : null;

    const FilterChip = ({ icon, label, value, onPress, flex }) => (
        <TouchableOpacity onPress={onPress} activeOpacity={0.8} style={[styles.filterChip, flex ? { flex } : {}]}>
            <LinearGradient colors={['#ffffff', '#f8fafc']} style={styles.filterChipGradient}>
                <View style={styles.filterIconWrap}>
                    <Icon name={icon} size={16} color="#3B82F6" />
                </View>
                <View style={{ flex: 1, paddingLeft: 8 }}>
                    <Text style={styles.filterLabel}>{label}</Text>
                    <Text style={styles.filterValue} numberOfLines={1}>{value || 'Seçiniz'}</Text>
                </View>
                <Icon name="chevron-down" size={18} color="#94A3B8" />
            </LinearGradient>
        </TouchableOpacity>
    );

    return (
        <SafeAreaView style={styles.container} edges={['top', 'bottom']}>
            <View style={styles.headerContainer}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Icon name="chevron-left" size={26} color="#0F172A" />
                    </TouchableOpacity>
                    <View style={{ flex: 1, alignItems: 'center' }}>
                        <Text style={styles.headerTitle}>Puantaj / Seferler</Text>
                        <Text style={styles.headerSubtitle}>Canlı Matris Tablosu</Text>
                    </View>
                    <View style={{ width: 40 }} />
                </View>

                {/* 3D Premium Filter Chips */}
                <View style={{ paddingHorizontal: 16, marginTop: 16, gap: 12 }}>
                    <FilterChip 
                        icon="domain" 
                        label="MÜŞTERİ" 
                        value={selectedCustomerObj?.company_name} 
                        onPress={() => { setSelectionType('customer'); setSelectionModalVisible(true); }}
                    />
                    <View style={{ flexDirection: 'row', gap: 12 }}>
                        <FilterChip 
                            icon="calendar-month" 
                            label="AY" 
                            value={selectedMonthObj?.label} 
                            onPress={() => { setSelectionType('month'); setSelectionModalVisible(true); }}
                            flex={1}
                        />
                        <FilterChip 
                            icon="calendar-blank" 
                            label="YIL" 
                            value={selectedYear} 
                            onPress={() => { setSelectionType('year'); setSelectionModalVisible(true); }}
                            flex={1}
                        />
                    </View>
                </View>

                {/* Actions & Info */}
                {selectedCustomer && (
                    <View style={styles.actionsRow}>
                        <View style={styles.routeCountBadge}>
                            <Text style={styles.routeCountText}>Toplam Güzergah: {serviceRoutes.length}</Text>
                        </View>
                        <View style={{ flexDirection: 'row', gap: 8 }}>
                            <TouchableOpacity 
                                style={[styles.exportBtn, exportingType === 'pdf' && { opacity: 1 }]}
                                onPress={() => exportReport('pdf')}
                                disabled={exportingType !== null}
                            >
                                {exportingType === 'pdf' ? <ActivityIndicator size="small" color="#DC2626" /> : <Icon name="file-pdf-box" size={16} color="#DC2626" />}
                                <Text style={[styles.exportBtnText, { color: '#DC2626' }]}>PDF</Text>
                            </TouchableOpacity>
                            <TouchableOpacity 
                                style={[styles.exportBtn, exportingType === 'excel' && { opacity: 1 }]}
                                onPress={() => exportReport('excel')}
                                disabled={exportingType !== null}
                            >
                                {exportingType === 'excel' ? <ActivityIndicator size="small" color="#16A34A" /> : <Icon name="file-excel-box" size={16} color="#16A34A" />}
                                <Text style={[styles.exportBtnText, { color: '#16A34A' }]}>Excel</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                )}
            </View>

            {loading && !serviceRoutes.length ? (
                <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}><ActivityIndicator size="large" color="#3B82F6" /></View>
            ) : !selectedCustomer ? (
                <View style={{ flex: 1 }}>
                    <EmptyState title="Müşteri Bekleniyor" message="Puantaj tablosunu görüntülemek için yukarıdan bir müşteri seçiniz." icon="domain" />
                </View>
            ) : (
                <ScrollView style={{ flex: 1, backgroundColor: '#F8FAFC' }}>
                    <ScrollView horizontal showsHorizontalScrollIndicator={true} bounces={false}>
                        <View style={styles.matrixWrapper}>
                            <View style={styles.row}>
                                <View style={[styles.cellHeader, styles.routeCol]}>
                                    <Text style={styles.colHeaderText}>GÜZERGAH</Text>
                                </View>
                                {monthDays.map(day => (
                                    <View key={day.date_key} style={[styles.cellHeader, styles.dayCol, day.is_weekend && styles.weekendHeader, day.is_holiday && styles.holidayHeader]}>
                                        <Text style={[styles.dayText, (day.is_weekend||day.is_holiday) && { color: '#BE123C' }]}>{day.day}</Text>
                                        <Text style={[styles.dayNameText, (day.is_weekend||day.is_holiday) && { color: '#BE123C' }]}>{day.day_name.substring(0,3)}</Text>
                                        {day.is_holiday && <Text style={styles.holidayLabel} numberOfLines={1}>{day.holiday_name}</Text>}
                                    </View>
                                ))}
                            </View>

                            {serviceRoutes.map(route => (
                                <View key={route.id} style={styles.row}>
                                    <View style={[styles.cell, styles.routeCol, { backgroundColor: '#FFFFFF', borderRightWidth: 2, borderRightColor: '#E2E8F0' }]}>
                                        <Text style={styles.routeTitle} numberOfLines={2}>{route.route_name}</Text>
                                        <View style={{ marginTop: 4 }}>
                                            <Text style={styles.routeVehicleInfo} numberOfLines={1}>S: {route.morning_plate || '-'}</Text>
                                            <Text style={styles.routeVehicleInfo} numberOfLines={1}>A: {route.evening_plate || '-'}</Text>
                                        </View>
                                    </View>
                                    
                                    {monthDays.map(day => {
                                        const cell = matrix[day.date_key]?.[route.id] || {};
                                        const hasRecord = cell.has_record;
                                        const price = cell.value !== null && cell.value !== undefined ? cell.value : '';
                                        
                                        let cellStyle = [styles.cell, styles.dayCol];
                                        if (day.is_weekend) cellStyle.push({ backgroundColor: '#FFF1F2' });
                                        if (day.is_holiday) cellStyle.push({ backgroundColor: '#FAE8FF' });
                                        if (hasRecord) cellStyle.push({ backgroundColor: '#EFF6FF', borderColor: '#BFDBFE', borderWidth: 1 });

                                        return (
                                            <TouchableOpacity 
                                                key={day.date_key} 
                                                style={cellStyle}
                                                activeOpacity={0.7}
                                                onPress={() => openCellModal(route, day)}
                                            >
                                                <Text style={[styles.cellPrice, hasRecord && { color: '#1D4ED8' }]}>
                                                    {price !== '' ? price : '-'}
                                                </Text>
                                                {hasRecord && (cell.morning_vehicle_id !== cell.default_morning_vehicle_id || cell.evening_vehicle_id !== cell.default_evening_vehicle_id) && (
                                                    <View style={styles.changedVehicleDot} />
                                                )}
                                            </TouchableOpacity>
                                        );
                                    })}
                                </View>
                            ))}
                        </View>
                    </ScrollView>

                    {summary && (
                        <View style={styles.summaryContainer}>
                            <View style={[styles.summaryCard, { backgroundColor: '#FFFFFF' }]}>
                                <Text style={styles.summaryLabel}>Ara Toplam</Text>
                                <Text style={styles.summaryValue}>{fmtMoney(summary.subtotal)}</Text>
                            </View>
                            <View style={[styles.summaryCard, { backgroundColor: '#FFFFFF' }]}>
                                <Text style={styles.summaryLabel}>KDV (%{summary.vat_rate})</Text>
                                <Text style={styles.summaryValue}>{fmtMoney(summary.vat_amount)}</Text>
                            </View>
                            {summary.withholding_amount > 0 && (
                                <View style={[styles.summaryCard, { backgroundColor: '#FFFBEB', borderColor: '#FDE68A' }]}>
                                    <Text style={[styles.summaryLabel, { color: '#92400E' }]}>Tevkifat Tutarı</Text>
                                    <Text style={[styles.summaryValue, { color: '#B45309' }]}>{fmtMoney(summary.withholding_amount)}</Text>
                                </View>
                            )}
                            <LinearGradient colors={['#1E293B', '#0F172A']} style={[styles.summaryCard, { borderColor: 'transparent' }]}>
                                <Text style={[styles.summaryLabel, { color: '#94A3B8' }]}>Net Fatura Tutarı</Text>
                                <Text style={[styles.summaryValue, { color: '#FFFFFF', fontSize: 24 }]}>{fmtMoney(summary.net_total)}</Text>
                            </LinearGradient>
                        </View>
                    )}
                    <View style={{ height: 40 }} />
                </ScrollView>
            )}

            {/* HÜCRE GİRİŞ MODALI */}
            <Modal visible={cellModalVisible} animationType="slide" transparent>
                <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <View>
                                <Text style={styles.modalTitle}>{activeCell?.day?.display_date}</Text>
                                <Text style={styles.modalSubtitle} numberOfLines={1}>{activeCell?.route?.route_name}</Text>
                            </View>
                            <TouchableOpacity onPress={() => setCellModalVisible(false)} style={styles.modalCloseBtn}>
                                <Icon name="close" size={24} color="#64748B" />
                            </TouchableOpacity>
                        </View>

                        <ScrollView contentContainerStyle={{ padding: 20 }}>
                            <Text style={styles.inputLabel}>TUTAR (₺) *</Text>
                            <TextInput 
                                style={styles.priceInput}
                                value={formData.price}
                                onChangeText={t => setFormData({...formData, price: t})}
                                keyboardType="numeric"
                                placeholder="0.00"
                                placeholderTextColor="#94A3B8"
                                autoFocus
                            />
                            <Text style={styles.hintText}>Fiyatı silmek/sıfırlamak bu kaydı tamamen iptal eder.</Text>

                            <Text style={[styles.inputLabel, { marginTop: 20, color: '#0369A1' }]}>☀️ SABAH ARACI (İsteğe Bağlı)</Text>
                            <TouchableOpacity 
                                style={styles.vehicleSelectorBtn} 
                                onPress={() => { setSelectionType('morning_vehicle'); setSelectionModalVisible(true); }}
                            >
                                <Icon name="white-balance-sunny" size={20} color="#0284C7" />
                                <Text style={styles.vehicleSelectorText}>
                                    {morningVehicleObj ? morningVehicleObj.plate : `Varsayılan: ${activeCell?.route?.morning_plate || 'Yok'}`}
                                </Text>
                                <Icon name="chevron-down" size={20} color="#94A3B8" />
                            </TouchableOpacity>

                            <Text style={[styles.inputLabel, { marginTop: 16, color: '#4338CA' }]}>🌙 AKŞAM ARACI (İsteğe Bağlı)</Text>
                            <TouchableOpacity 
                                style={[styles.vehicleSelectorBtn, { backgroundColor: '#EEF2FF', borderColor: '#C7D2FE' }]} 
                                onPress={() => { setSelectionType('evening_vehicle'); setSelectionModalVisible(true); }}
                            >
                                <Icon name="moon-waning-crescent" size={20} color="#4F46E5" />
                                <Text style={[styles.vehicleSelectorText, { color: '#312E81' }]}>
                                    {eveningVehicleObj ? eveningVehicleObj.plate : `Varsayılan: ${activeCell?.route?.evening_plate || 'Yok'}`}
                                </Text>
                                <Icon name="chevron-down" size={20} color="#94A3B8" />
                            </TouchableOpacity>

                            <View style={styles.modalActions}>
                                <TouchableOpacity style={styles.deleteBtn} onPress={handleDeleteCell} disabled={saving || !activeCell?.cellData?.has_record}>
                                    <Icon name="trash-can-outline" size={20} color={activeCell?.cellData?.has_record ? "#EF4444" : "#CBD5E1"} />
                                </TouchableOpacity>
                                <TouchableOpacity style={[styles.saveBtn, saving && { opacity: 0.7 }]} onPress={handleSaveCell} disabled={saving}>
                                    {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>Kaydet</Text>}
                                </TouchableOpacity>
                            </View>
                        </ScrollView>

                        {/* INNER SELECTION OVERLAY FOR VEHICLES */}
                        {selectionModalVisible && (selectionType === 'morning_vehicle' || selectionType === 'evening_vehicle') && (
                            <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end', zIndex: 9999, borderRadius: 32 }]}>
                                <View style={[styles.modalContent, { maxHeight: '80%' }]}>
                                    <View style={styles.modalHeader}>
                                        <Text style={styles.modalTitle}>Araç Seçiniz</Text>
                                        <TouchableOpacity onPress={() => setSelectionModalVisible(false)} style={styles.modalCloseBtn}>
                                            <Icon name="close" size={24} color="#64748B" />
                                        </TouchableOpacity>
                                    </View>
                                    <ScrollView contentContainerStyle={{ padding: 16 }}>
                                        {getSelectionData().map((item, index) => (
                                            <TouchableOpacity 
                                                key={index} 
                                                style={styles.selectionListItem}
                                                onPress={() => handleSelectOption(item.value)}
                                            >
                                                <Text style={styles.selectionListText}>{item.label}</Text>
                                            </TouchableOpacity>
                                        ))}
                                    </ScrollView>
                                </View>
                            </View>
                        )}

                    </View>
                </KeyboardAvoidingView>
            </Modal>

            {/* MAIN SELECTION MODAL (For Customer, Month, Year) */}
            <Modal visible={selectionModalVisible && ['customer', 'month', 'year'].includes(selectionType)} animationType="slide" transparent>
                <View style={styles.modalOverlay}>
                    <View style={[styles.modalContent, { maxHeight: '70%' }]}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Lütfen Seçiniz</Text>
                            <TouchableOpacity onPress={() => setSelectionModalVisible(false)} style={styles.modalCloseBtn}>
                                <Icon name="close" size={24} color="#64748B" />
                            </TouchableOpacity>
                        </View>
                        <ScrollView contentContainerStyle={{ padding: 16 }}>
                            {getSelectionData().map((item, index) => (
                                <TouchableOpacity 
                                    key={index} 
                                    style={styles.selectionListItem}
                                    onPress={() => handleSelectOption(item.value)}
                                >
                                    <Text style={styles.selectionListText}>{item.label}</Text>
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </View>
            </Modal>



        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F1F5F9' },
    
    headerContainer: { backgroundColor: '#F1F5F9', paddingBottom: 16 },
    headerTop: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingTop: 10 },
    backBtn: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#FFFFFF', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: {width: 0, height: 2}, shadowOpacity: 0.05, shadowRadius: 4, elevation: 2 },
    headerTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    headerSubtitle: { fontSize: 12, fontWeight: '600', color: '#10B981', marginTop: 2, letterSpacing: 0.5 },
    
    // 3D Premium Filter Chips
    filterChip: { borderRadius: 16, overflow: 'hidden', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 3, borderWidth: 1, borderColor: '#E2E8F0', backgroundColor: '#FFFFFF' },
    filterChipGradient: { flexDirection: 'row', alignItems: 'center', padding: 12 },
    filterIconWrap: { width: 32, height: 32, borderRadius: 10, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center' },
    filterLabel: { fontSize: 10, fontWeight: '800', color: '#64748B', letterSpacing: 0.5, marginBottom: 2 },
    filterValue: { fontSize: 14, fontWeight: '800', color: '#0F172A' },

    actionsRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, marginTop: 16 },
    routeCountBadge: { backgroundColor: '#F1F5F9', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    routeCountText: { fontSize: 11, fontWeight: '800', color: '#64748B' },
    exportBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFFFFF', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 4, elevation: 1, gap: 4 },
    exportBtnText: { fontSize: 12, fontWeight: '800' },

    // Matrix
    matrixWrapper: { padding: 16, backgroundColor: '#F8FAFC', minHeight: 400 },
    row: { flexDirection: 'row', borderBottomWidth: 1, borderBottomColor: '#E2E8F0', shadowColor: '#000', shadowOffset: {width:0, height: 1}, shadowOpacity: 0.02, elevation: 1 },
    routeCol: { width: 140, paddingHorizontal: 8, paddingVertical: 10, justifyContent: 'center' },
    dayCol: { width: 55, paddingHorizontal: 2, paddingVertical: 10, justifyContent: 'center', alignItems: 'center', borderLeftWidth: 1, borderLeftColor: '#E2E8F0' },
    
    cellHeader: { backgroundColor: '#F1F5F9', borderBottomWidth: 2, borderBottomColor: '#CBD5E1' },
    colHeaderText: { fontSize: 11, fontWeight: '800', color: '#64748B', letterSpacing: 0.5 },
    weekendHeader: { backgroundColor: '#FFE4E6' },
    holidayHeader: { backgroundColor: '#FDF4FF' },
    
    dayText: { fontSize: 16, fontWeight: '900', color: '#334155' },
    dayNameText: { fontSize: 9, fontWeight: '700', color: '#64748B', textTransform: 'uppercase' },
    holidayLabel: { fontSize: 7, fontWeight: '800', color: '#D946EF', marginTop: 2 },

    cell: { backgroundColor: '#FFFFFF' },
    routeTitle: { fontSize: 12, fontWeight: '800', color: '#0F172A', lineHeight: 14 },
    routeVehicleInfo: { fontSize: 9, color: '#64748B', fontWeight: '600' },
    
    cellPrice: { fontSize: 12, fontWeight: '700', color: '#94A3B8' },
    changedVehicleDot: { position: 'absolute', top: 4, right: 4, width: 6, height: 6, borderRadius: 3, backgroundColor: '#F59E0B' },

    // Summary Cards
    summaryContainer: { padding: 16, gap: 12 },
    summaryCard: { padding: 20, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 12, elevation: 4 },
    summaryLabel: { fontSize: 12, fontWeight: '800', color: '#64748B', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 },
    summaryValue: { fontSize: 22, fontWeight: '900', color: '#0F172A' },

    // Modals
    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: '#FFFFFF', borderTopLeftRadius: 32, borderTopRightRadius: 32, maxHeight: '95%' },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 24, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    modalTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A' },
    modalSubtitle: { fontSize: 13, fontWeight: '700', color: '#64748B', marginTop: 4, maxWidth: 250 },
    modalCloseBtn: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    
    inputLabel: { fontSize: 11, fontWeight: '800', color: '#64748B', marginBottom: 8, marginLeft: 4, letterSpacing: 0.5 },
    priceInput: { fontSize: 28, fontWeight: '900', color: '#0F172A', backgroundColor: '#F8FAFC', borderWidth: 2, borderColor: '#E2E8F0', borderRadius: 16, padding: 20, textAlign: 'center' },
    hintText: { fontSize: 11, color: '#94A3B8', textAlign: 'center', marginTop: 8, fontWeight: '600' },

    vehicleSelectorBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F0F9FF', borderWidth: 1, borderColor: '#BAE6FD', borderRadius: 16, padding: 16 },
    vehicleSelectorText: { flex: 1, fontSize: 14, fontWeight: '700', color: '#0369A1', marginLeft: 12 },

    modalActions: { flexDirection: 'row', marginTop: 32, gap: 12 },
    deleteBtn: { width: 60, height: 60, borderRadius: 16, backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#FEE2E2' },
    saveBtn: { flex: 1, height: 60, borderRadius: 16, backgroundColor: '#3B82F6', alignItems: 'center', justifyContent: 'center', shadowColor: '#3B82F6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 4 },
    saveBtnText: { color: '#FFFFFF', fontSize: 16, fontWeight: '800', letterSpacing: 0.5 },

    selectionListItem: { paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    selectionListText: { fontSize: 16, fontWeight: '700', color: '#1E293B', textAlign: 'center' },

    // Dummy Tab
    dummyTabBar: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E2E8F0', paddingBottom: Platform.OS === 'ios' ? 20 : 0, flexDirection: 'row', height: Platform.OS === 'ios' ? 85 : 65, alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 10 },
    dummyTab: { flex: 1, alignItems: 'center', justifyContent: 'center', height: '100%' },
    dummyTabLabel: { fontSize: 10, fontWeight: '600', marginTop: 4, color: '#94A3B8' },
    dummyTabCenter: { flex: 1, alignItems: 'center' },
    dummyTabCenterInner: { width: 56, height: 56, borderRadius: 28, backgroundColor: '#2563EB', alignItems: 'center', justifyContent: 'center', marginTop: -35, shadowColor: '#2563EB', shadowOffset: {width:0, height:4}, shadowOpacity:0.3, shadowRadius:8, elevation: 5 }
});
