import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput, ActivityIndicator, Alert, Platform, Linking } from 'react-native';
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
            const response = await api.get(`/vehicles/${vehicleId}/penalties`, {
                params: {
                    start_date: toApiDate(startDate),
                    end_date: toApiDate(endDate),
                    search: search
                }
            });
            setPenalties(response.data.penalties);
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

    const renderPenalty = ({ item }) => (
        <View style={s.card}>
            <View style={s.cardHeader}>
                <View style={s.titleGroup}>
                    <View style={[s.iconBox, { backgroundColor: item.status === 'paid' ? '#DCFCE7' : '#FEE2E2' }]}>
                        <Icon name="alert-decagram" size={20} color={item.status === 'paid' ? '#16A34A' : '#DC2626'} />
                    </View>
                    <View>
                        <Text style={s.penaltyNo}>{item.penalty_no}</Text>
                        <Text style={s.driverText}>{item.driver_name || 'Şoför Belirtilmedi'}</Text>
                    </View>
                </View>
                <View style={[s.statusBadge, { backgroundColor: item.status === 'paid' ? '#DCFCE7' : '#FEE2E2' }]}>
                    <Text style={[s.statusText, { color: item.status === 'paid' ? '#16A34A' : '#DC2626' }]}>
                        {item.status === 'paid' ? 'ÖDENDİ' : 'ÖDENMEDİ'}
                    </Text>
                </View>
            </View>

            <View style={s.cardBody}>
                <View style={s.infoRow}>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>TARİH / SAAT</Text>
                        <Text style={s.infoValue}>{item.date ? new Date(item.date).toLocaleDateString('tr-TR') : '-'} {item.time || ''}</Text>
                        {item.status === 'unpaid' && item.discount_deadline && (
                            <Text style={s.deadlineText}>Son İndirim: {new Date(item.discount_deadline).toLocaleDateString('tr-TR')}</Text>
                        )}
                    </View>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>MADDE / YER</Text>
                        <Text style={s.infoValue}>{item.article || '-'}</Text>
                        <Text style={s.subInfoValue} numberOfLines={1}>{item.location || '-'}</Text>
                    </View>
                </View>

                <View style={s.infoRow}>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>CEZA TUTARI</Text>
                        <Text style={s.amountValue}>{Number(item.amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺</Text>
                    </View>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>İNDİRİMLİ TUTAR</Text>
                        <Text style={[s.amountValue, { color: '#16A34A' }]}>{Number(item.discounted_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 })} ₺</Text>
                    </View>
                </View>

                {item.payment_date && (
                    <View style={s.paymentInfo}>
                        <Icon name="check-circle" size={14} color="#16A34A" />
                        <Text style={s.paymentText}>
                            Ödeme Tarihi: {new Date(item.payment_date).toLocaleDateString('tr-TR')} 
                            {item.paid_amount > 0 && ` (${Number(item.paid_amount).toLocaleString('tr-TR')} ₺)`}
                        </Text>
                    </View>
                )}

                <View style={s.actionRow}>
                    {item.traffic_penalty_document && (
                        <TouchableOpacity style={s.docBtn} onPress={() => Linking.openURL(item.traffic_penalty_document)}>
                            <Icon name="file-document-outline" size={16} color="#4F46E5" />
                            <Text style={s.docBtnText}>Ceza Belgesi</Text>
                        </TouchableOpacity>
                    )}
                    {item.payment_receipt && (
                        <TouchableOpacity style={s.docBtn} onPress={() => Linking.openURL(item.payment_receipt)}>
                            <Icon name="receipt" size={16} color="#16A34A" />
                            <Text style={s.docBtnText}>Dekont</Text>
                        </TouchableOpacity>
                    )}
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
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Cezalar</Text></View>
                        <TouchableOpacity style={s.addBtn}><Icon name="plus" size={24} color="#fff" /></TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.filterContainer}>
                <View style={s.searchBar}>
                    <Icon name="magnify" size={20} color="#94A3B8" />
                    <TextInput 
                        style={s.searchInput} 
                        placeholder="Ceza no, madde, yer ara..." 
                        value={search}
                        onChangeText={setSearch}
                        onSubmitEditing={() => fetchPenalties()}
                    />
                    <TouchableOpacity onPress={() => setShowFilters(!showFilters)}>
                        <Icon name="tune" size={20} color={showFilters ? '#4F46E5' : '#94A3B8'} />
                    </TouchableOpacity>
                </View>
                {showFilters && (
                    <View style={s.dateFilters}>
                        <DatePickerInput label="Başlangıç" value={startDate} onChange={setStartDate} />
                        <DatePickerInput label="Bitiş" value={endDate} onChange={setEndDate} />
                    </View>
                )}
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
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 30, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 0 : 40 },
    headerTitle: { color: '#fff', fontSize: 16, fontWeight: '800' },
    addBtn: { width: 36, height: 36, borderRadius: 10, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center' },

    filterContainer: { 
        padding: 16, 
        backgroundColor: '#fff', 
        marginHorizontal: 16,
        marginTop: -20,
        borderRadius: 24,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.05,
        shadowRadius: 20,
        elevation: 5,
        borderWidth: 1,
        borderColor: '#F1F5F9'
    },
    searchBar: { 
        flexDirection: 'row', 
        alignItems: 'center', 
        backgroundColor: '#F8FAFC', 
        borderRadius: 12, 
        paddingHorizontal: 12, 
        paddingVertical: 10, 
        gap: 8, 
        borderWidth: 1, 
        borderColor: '#E2E8F0' 
    },
    searchInput: { flex: 1, fontSize: 14, color: '#0F172A', paddingVertical: 0 },
    dateFilters: { 
        flexDirection: 'row', 
        marginTop: 12,
        paddingTop: 12,
        borderTopWidth: 1,
        borderTopColor: '#F1F5F9',
        justifyContent: 'space-between'
    },

    list: { padding: 16, paddingBottom: 40, marginTop: 10 },
    card: { backgroundColor: '#fff', borderRadius: 20, padding: 18, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 15, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 15, borderBottomWidth: 1, borderBottomColor: '#F8FAFC', paddingBottom: 12 },
    titleGroup: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 12 },
    iconBox: { width: 40, height: 40, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    penaltyNo: { fontSize: 14, fontWeight: '800', color: '#1E293B' },
    driverText: { fontSize: 11, color: '#64748B', fontWeight: '500', marginTop: 2 },
    statusBadge: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8 },
    statusText: { fontSize: 10, fontWeight: '800' },

    cardBody: { gap: 15 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between' },
    infoItem: { flex: 1 },
    infoLabel: { fontSize: 9, fontWeight: '800', color: '#94A3B8', marginBottom: 4, letterSpacing: 0.5 },
    infoValue: { fontSize: 12, fontWeight: '700', color: '#334155' },
    subInfoValue: { fontSize: 11, color: '#64748B', marginTop: 2 },
    amountValue: { fontSize: 14, fontWeight: '900', color: '#0F172A' },
    deadlineText: { fontSize: 10, color: '#EF4444', fontWeight: '700', marginTop: 4 },
    
    paymentInfo: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#F0FDF4', padding: 10, borderRadius: 10 },
    paymentText: { fontSize: 11, color: '#16A34A', fontWeight: '600' },

    actionRow: { flexDirection: 'row', gap: 10, marginTop: 5 },
    docBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, backgroundColor: '#F8FAFC', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, borderWidth: 1, borderColor: '#E2E8F0' },
    docBtnText: { fontSize: 11, fontWeight: '700', color: '#475569' },

    empty: { alignItems: 'center', marginTop: 100 },
    emptyTxt: { color: '#94A3B8', marginTop: 12, fontWeight: '600' }
});
