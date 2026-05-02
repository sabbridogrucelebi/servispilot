import React, { useState, useEffect, useContext } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert, Dimensions, TextInput, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';
import dayjs from 'dayjs';
import 'dayjs/locale/tr';
import { BottomSheetModal, Header } from '../components';

dayjs.locale('tr');
const { width: W } = Dimensions.get('window');
const fmtMoney = (v) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 }).format(v || 0);

export default function PayrollScreen({ navigation }) {
    const { hasPermission, user } = useContext(AuthContext);
    
    const [period, setPeriod] = useState(dayjs().format('YYYY-MM'));
    const [data, setData] = useState([]);
    const [isLocked, setIsLocked] = useState(false);
    const [loading, setLoading] = useState(true);
    
    // Modal & Form State
    const [modalVisible, setModalVisible] = useState(false);
    const [saving, setSaving] = useState(false);
    const [editingDriver, setEditingDriver] = useState(null);
    const [formData, setFormData] = useState({
        base_salary: '0', bank_payment: '0', advance_payment: '0', deduction: '0', extra_bonus: '0'
    });

    const fetchPeriodData = async (currentPeriod) => {
        setLoading(true);
        try {
            const r = await api.get(`/v1/payrolls/period/${currentPeriod}`);
            if (r.data.success) {
                setData(r.data.data.payrolls || []);
                setIsLocked(r.data.data.is_locked || false);
            }
        } catch (e) {
            Alert.alert('Hata', 'Maaş verileri alınamadı.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchPeriodData(period);
    }, [period]);

    const changeMonth = (diff) => {
        const newPeriod = dayjs(period, 'YYYY-MM').add(diff, 'month').format('YYYY-MM');
        setPeriod(newPeriod);
    };

    const toggleLock = async () => {
        if (!user?.role?.includes('admin')) {
            Alert.alert('Yetkisiz', 'Sadece yöneticiler kilidi değiştirebilir.');
            return;
        }
        try {
            const r = await api.post(`/v1/payrolls/period/${period}/lock`);
            if (r.data.success) setIsLocked(r.data.data.is_locked);
        } catch (e) {
            Alert.alert('Hata', 'Kilit durumu değiştirilemedi.');
        }
    };

    const openEdit = (item) => {
        if (isLocked) {
            Alert.alert('Kilitli Dönem', 'Bu dönem kilitli olduğu için düzenleme yapılamaz.');
            return;
        }
        setEditingDriver(item);
        const existing = item.existing || {};
        const calc = item.calculation || {};
        
        setFormData({
            base_salary: (existing.base_salary ?? calc.base_salary ?? 0).toString(),
            bank_payment: (existing.bank_payment ?? 0).toString(),
            traffic_penalty: (existing.traffic_penalty ?? 0).toString(),
            advance_payment: (existing.advance_payment ?? 0).toString(),
            deduction: (existing.deduction ?? 0).toString(),
            deduction_notes: existing.deduction_notes ?? '',
            extra_bonus: (existing.extra_bonus ?? 0).toString(),
            extra_notes: existing.extra_notes ?? ''
        });
        setModalVisible(true);
    };

    const handleSave = async () => {
        setSaving(true);
        try {
            const payload = {
                driver_id: editingDriver.driver.id,
                period: period,
                data: {
                    base_salary: parseFloat(formData.base_salary) || 0,
                    bank_payment: parseFloat(formData.bank_payment) || 0,
                    traffic_penalty: parseFloat(formData.traffic_penalty) || 0,
                    advance_payment: parseFloat(formData.advance_payment) || 0,
                    deduction: parseFloat(formData.deduction) || 0,
                    deduction_notes: formData.deduction_notes || '',
                    extra_bonus: parseFloat(formData.extra_bonus) || 0,
                    extra_notes: formData.extra_notes || '',
                    extra_earnings: editingDriver.calculation?.extra_earnings || 0,
                }
            };
            
            const r = await api.post('/v1/payrolls/single-update', payload);
            if (r.data.success) {
                setModalVisible(false);
                fetchPeriodData(period);
            }
        } catch (e) {
            Alert.alert('Hata', e.response?.data?.message || 'Bordro güncellenemedi.');
        } finally {
            setSaving(false);
        }
    };

    // Calculate Grand Totals
    const tNet = data.reduce((sum, d) => {
        let net = 0;
        if (d.existing && d.existing.net_salary != null) {
            net = parseFloat(d.existing.net_salary);
        } else {
            const base = parseFloat(d.calculation?.base_salary || 0);
            const extra = parseFloat(d.calculation?.extra_earnings || 0);
            const penalty = parseFloat(d.existing?.traffic_penalty || 0);
            net = base + extra - penalty;
        }
        return sum + (isNaN(net) ? 0 : net);
    }, 0);

    const tBank = data.reduce((sum, d) => sum + parseFloat(d.existing?.bank_payment || 0), 0);
    const tExtra = data.reduce((sum, d) => sum + parseFloat(d.calculation?.extra_earnings || 0) + parseFloat(d.existing?.extra_bonus || 0), 0);

    const renderCard = ({ item }) => {
        const c = item.calculation || {};
        const e = item.existing || {};
        
        const base = parseFloat(e.base_salary ?? c.base_salary ?? 0);
        const bank = parseFloat(e.bank_payment ?? 0);
        const extraEarn = parseFloat(c.extra_earnings ?? 0);
        const penalty = parseFloat(e.traffic_penalty ?? 0);
        const advance = parseFloat(e.advance_payment ?? 0);
        const deduc = parseFloat(e.deduction ?? 0);
        const extraB = parseFloat(e.extra_bonus ?? 0);
        const net = e.net_salary != null ? parseFloat(e.net_salary) : (base + extraEarn + extraB - bank - penalty - advance - deduc);

        return (
            <View style={st.card}>
                <View style={st.cardHeader}>
                    <View style={st.cardTitleArea}>
                        <View style={st.driverIcon}><Icon name="account-tie" size={20} color="#3B82F6" /></View>
                        <View>
                            <Text style={st.driverName}>{item.driver.full_name}</Text>
                            {item.driver.vehicle?.plate ? (
                                <View style={st.plateBadge}><Text style={st.plateText}>{item.driver.vehicle.plate}</Text></View>
                            ) : null}
                        </View>
                    </View>
                    <View style={st.cardActions}>
                        <TouchableOpacity style={st.actionBtn} onPress={() => navigation.navigate('PayrollDetail', { driverData: item, periodMonth: period })}>
                            <View style={st.detailBtn}>
                                <Icon name="file-document-outline" size={16} color="#64748B" />
                            </View>
                        </TouchableOpacity>
                        <TouchableOpacity style={st.editBtn} onPress={() => openEdit(item)}>
                            <LinearGradient colors={['#10B981', '#059669']} style={st.editBtnGrad}>
                                <Icon name="pencil" size={16} color="#fff" />
                                <Text style={st.editBtnText}>İşlem</Text>
                            </LinearGradient>
                        </TouchableOpacity>
                    </View>
                </View>

                <View style={st.statsGrid}>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Ana Maaş</Text><Text style={st.statVal} numberOfLines={1} adjustsFontSizeToFit>{fmtMoney(base)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Banka</Text><Text style={[st.statVal, {color:'#3B82F6'}]} numberOfLines={1} adjustsFontSizeToFit>{fmtMoney(bank)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Ek Hakediş</Text><Text style={[st.statVal, {color:'#10B981'}]} numberOfLines={1} adjustsFontSizeToFit>+{fmtMoney(extraEarn)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>T. Cezası</Text><Text style={[st.statVal, {color:'#EF4444'}]} numberOfLines={1} adjustsFontSizeToFit>-{fmtMoney(penalty)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Avans</Text><Text style={[st.statVal, {color:'#F59E0B'}]} numberOfLines={1} adjustsFontSizeToFit>-{fmtMoney(advance)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Kesinti</Text><Text style={[st.statVal, {color:'#EF4444'}]} numberOfLines={1} adjustsFontSizeToFit>-{fmtMoney(deduc)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Ekstra(+)</Text><Text style={[st.statVal, {color:'#8B5CF6'}]} numberOfLines={1} adjustsFontSizeToFit>+{fmtMoney(extraB)}</Text></View>
                    <View style={st.statBox}><Text style={st.statLabel} numberOfLines={1} adjustsFontSizeToFit>Net Ödenecek</Text><Text style={[st.statVal, {color:'#10B981', fontWeight:'900'}]} numberOfLines={1} adjustsFontSizeToFit>{fmtMoney(net)}</Text></View>
                </View>
            </View>
        );
    };

    return (
        <SafeAreaView style={st.container}>
            <Header title="Maaşlar" subtitle="Maaş ve Finansal Yönetim" onBack={() => navigation.goBack()} />
            
            <View style={st.periodSection}>
                <View style={st.monthPicker}>
                    <TouchableOpacity onPress={() => changeMonth(-1)} style={st.monthArrow}><Icon name="chevron-left" size={24} color="#1E293B" /></TouchableOpacity>
                    <Text style={st.monthText}>{dayjs(period, 'YYYY-MM').format('MMMM YYYY')}</Text>
                    <TouchableOpacity onPress={() => changeMonth(1)} style={st.monthArrow}><Icon name="chevron-right" size={24} color="#1E293B" /></TouchableOpacity>
                </View>
                <TouchableOpacity onPress={toggleLock} style={[st.lockBadge, { backgroundColor: isLocked ? '#FEF2F2' : '#F0FDF4' }]}>
                    <Icon name={isLocked ? 'lock' : 'lock-open-variant'} size={18} color={isLocked ? '#EF4444' : '#10B981'} />
                    <Text style={[st.lockText, { color: isLocked ? '#EF4444' : '#10B981' }]}>{isLocked ? 'KİLİTLİ' : 'AÇIK'}</Text>
                </TouchableOpacity>
            </View>

            <View style={st.totalsWrap}>
                <LinearGradient colors={['#0F172A', '#1E293B']} style={st.totalsCard}>
                    <Text style={st.tTitle}>GENEL TOPLAMLAR</Text>
                    <View style={st.tGrid}>
                        <View style={st.tBox}><Text style={st.tLabel}>Toplam Net</Text><Text style={[st.tVal, {color:'#10B981'}]}>{fmtMoney(tNet)}</Text></View>
                        <View style={st.tBox}><Text style={st.tLabel}>Toplam Banka</Text><Text style={[st.tVal, {color:'#3B82F6'}]}>{fmtMoney(tBank)}</Text></View>
                        <View style={st.tBox}><Text style={st.tLabel}>Ek Hakediş</Text><Text style={[st.tVal, {color:'#8B5CF6'}]}>{fmtMoney(tExtra)}</Text></View>
                    </View>
                </LinearGradient>
            </View>

            {loading ? (
                <ActivityIndicator size="large" color="#3B82F6" style={{ marginTop: 40 }} />
            ) : (
                <FlatList
                    data={data}
                    keyExtractor={(i) => i.driver.id.toString()}
                    renderItem={renderCard}
                    contentContainerStyle={st.listContent}
                    showsVerticalScrollIndicator={false}
                />
            )}

            <BottomSheetModal visible={modalVisible} onClose={() => setModalVisible(false)} title="Bordro Düzenle">
                {editingDriver && (
                    <ScrollView style={st.formWrap} showsVerticalScrollIndicator={false}>
                        <Text style={st.formSubtitle}>{editingDriver.driver.full_name} - {dayjs(period, 'YYYY-MM').format('MMMM YYYY')}</Text>
                        
                        <View style={st.inputRow}>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Ana Maaş</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.base_salary} onChangeText={(t) => setFormData({...formData, base_salary: t})} />
                            </View>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Bankaya Yatan</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.bank_payment} onChangeText={(t) => setFormData({...formData, bank_payment: t})} />
                            </View>
                        </View>
                        
                        <View style={st.inputRow}>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Trafik Cezası</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.traffic_penalty} onChangeText={(t) => setFormData({...formData, traffic_penalty: t})} />
                            </View>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Avans</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.advance_payment} onChangeText={(t) => setFormData({...formData, advance_payment: t})} />
                            </View>
                        </View>

                        <View style={st.inputRow}>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Kesinti / İcra</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.deduction} onChangeText={(t) => setFormData({...formData, deduction: t})} />
                                <TextInput style={[st.input, { marginTop: 8, fontSize: 12, fontStyle: 'italic', color: '#EF4444', backgroundColor: '#FEF2F2', borderColor: '#FECACA' }]} placeholder="İcra / Kesinti Sebebi" placeholderTextColor="#FCA5A5" value={formData.deduction_notes} onChangeText={(t) => setFormData({...formData, deduction_notes: t})} />
                            </View>
                            <View style={st.inputWrap}>
                                <Text style={st.inputLabel}>Ekstra (+)</Text>
                                <TextInput style={st.input} keyboardType="numeric" value={formData.extra_bonus} onChangeText={(t) => setFormData({...formData, extra_bonus: t})} />
                                <TextInput style={[st.input, { marginTop: 8, fontSize: 12, fontStyle: 'italic', color: '#10B981', backgroundColor: '#F0FDF4', borderColor: '#BBF7D0' }]} placeholder="Ekstra Sebebi" placeholderTextColor="#86EFAC" value={formData.extra_notes} onChangeText={(t) => setFormData({...formData, extra_notes: t})} />
                            </View>
                        </View>

                        <TouchableOpacity style={st.saveBtn} onPress={handleSave} disabled={saving}>
                            <LinearGradient colors={['#3B82F6', '#2563EB']} style={st.saveBtnGrad}>
                                {saving ? <ActivityIndicator color="#fff" /> : <Text style={st.saveBtnText}>Kaydet</Text>}
                            </LinearGradient>
                        </TouchableOpacity>
                        
                        <View style={{height: 40}} />
                    </ScrollView>
                )}
            </BottomSheetModal>
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    periodSection: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 12, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#E2E8F0' },
    monthPicker: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F1F5F9', borderRadius: 12, paddingHorizontal: 6, paddingVertical: 4 },
    monthArrow: { padding: 4 },
    monthText: { fontSize: 16, fontWeight: '800', color: '#1E293B', marginHorizontal: 12, minWidth: 100, textAlign: 'center' },
    lockBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, gap: 6 },
    lockText: { fontSize: 12, fontWeight: '800' },
    totalsWrap: { padding: 16, paddingBottom: 8 },
    totalsCard: { borderRadius: 16, padding: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 8, elevation: 4 },
    tTitle: { fontSize: 11, fontWeight: '800', color: '#94A3B8', marginBottom: 12, letterSpacing: 1 },
    tGrid: { flexDirection: 'row', justifyContent: 'space-between' },
    tBox: { flex: 1, alignItems: 'center' },
    tLabel: { fontSize: 11, color: '#CBD5E1', fontWeight: '600', marginBottom: 4 },
    tVal: { fontSize: 16, fontWeight: '900' },
    listContent: { padding: 16, gap: 16, paddingBottom: 100 },
    card: { backgroundColor: '#fff', borderRadius: 20, padding: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 3, borderWidth: 1, borderColor: 'rgba(0,0,0,0.02)' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 },
    cardTitleArea: { flexDirection: 'row', alignItems: 'center', gap: 10, flex: 1 },
    driverIcon: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center' },
    driverName: { fontSize: 15, fontWeight: '800', color: '#1E293B' },
    plateBadge: { backgroundColor: '#F1F5F9', alignSelf: 'flex-start', paddingHorizontal: 8, paddingVertical: 2, borderRadius: 6, marginTop: 4 },
    plateText: { fontSize: 10, fontWeight: '700', color: '#475569' },
    cardActions: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    actionBtn: { borderRadius: 10, overflow: 'hidden' },
    detailBtn: { paddingHorizontal: 10, paddingVertical: 8, backgroundColor: '#F1F5F9', borderRadius: 10 },
    editBtn: { borderRadius: 10, overflow: 'hidden' },
    editBtnGrad: { flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 12, paddingVertical: 8 },
    editBtnText: { color: '#fff', fontSize: 12, fontWeight: '700' },
    statsGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 6, justifyContent: 'space-between' },
    statBox: { width: '23.5%', backgroundColor: '#F8FAFC', borderRadius: 10, paddingVertical: 8, paddingHorizontal: 2, alignItems: 'center', justifyContent: 'center' },
    statLabel: { fontSize: 9, color: '#64748B', fontWeight: '700', marginBottom: 4, textAlign: 'center' },
    statVal: { fontSize: 11, color: '#334155', fontWeight: '800', textAlign: 'center' },
    formWrap: { padding: 20, paddingBottom: Platform.OS === 'ios' ? 40 : 20 },
    formSubtitle: { fontSize: 15, fontWeight: '700', color: '#64748B', marginBottom: 20 },
    inputRow: { flexDirection: 'row', gap: 12, marginBottom: 16 },
    inputWrap: { flex: 1 },
    inputLabel: { fontSize: 12, fontWeight: '700', color: '#334155', marginBottom: 6 },
    input: { backgroundColor: '#F1F5F9', borderRadius: 12, padding: 14, fontSize: 15, fontWeight: '600', color: '#1E293B', borderWidth: 1, borderColor: '#E2E8F0' },
    saveBtn: { marginTop: 10, borderRadius: 14, overflow: 'hidden' },
    saveBtnGrad: { paddingVertical: 16, alignItems: 'center', justifyContent: 'center' },
    saveBtnText: { color: '#fff', fontSize: 16, fontWeight: '700' }
});
