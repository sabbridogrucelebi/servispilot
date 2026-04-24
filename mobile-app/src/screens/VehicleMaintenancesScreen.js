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

    const renderItem = ({ item }) => (
        <View style={s.card}>
            <View style={s.cardHeader}>
                <View style={s.titleGroup}>
                    <Icon name="wrench-cog" size={20} color="#8B5CF6" />
                    <Text style={s.maintenanceTitle}>{item.title}</Text>
                </View>
                <Text style={s.amountText}>{Number(item.amount || 0).toLocaleString('tr-TR')} ₺</Text>
            </View>

            <View style={s.cardBody}>
                <View style={s.infoRow}>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>TÜR</Text>
                        <Text style={s.infoValue}>{item.type || 'Bakım'}</Text>
                    </View>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>TARİH</Text>
                        <Text style={s.infoValue}>{item.date ? new Date(item.date).toLocaleDateString('tr-TR') : '-'}</Text>
                    </View>
                </View>

                <View style={s.infoRow}>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>KM</Text>
                        <Text style={s.infoValue}>{Number(item.km || 0).toLocaleString('tr-TR')} KM</Text>
                    </View>
                    <View style={s.infoItem}>
                        <Text style={s.infoLabel}>SERVİS</Text>
                        <Text style={s.infoValue}>{item.service_name || '-'}</Text>
                    </View>
                </View>

                {item.description ? (
                    <View style={s.noteBox}>
                        <Text style={s.noteText} numberOfLines={2}>{item.description}</Text>
                    </View>
                ) : null}
            </View>
        </View>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#040B16', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()}><Icon name="chevron-left" size={28} color="#fff" /></TouchableOpacity>
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Bakımlar</Text></View>
                        <View style={{flexDirection:'row', gap: 10}}>
                            <TouchableOpacity onPress={handleExportPdf} style={s.addBtn}><Icon name="file-pdf-box" size={24} color="#fff" /></TouchableOpacity>
                            <TouchableOpacity style={s.addBtn}><Icon name="plus" size={24} color="#fff" /></TouchableOpacity>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.filterContainer}>
                <View style={s.searchBar}>
                    <Icon name="magnify" size={20} color="#94A3B8" />
                    <TextInput 
                        style={s.searchInput} 
                        placeholder="Bakım, tür, servis ara..." 
                        value={search}
                        onChangeText={setSearch}
                    />
                    <TouchableOpacity onPress={() => setShowFilters(!showFilters)}>
                        <Icon name="tune" size={20} color={showFilters ? '#4F46E5' : '#94A3B8'} />
                    </TouchableOpacity>
                </View>
                {showFilters && (
                    <View style={s.dateFilters}>
                        <View style={{flex:1}}><DatePickerInput label="Başlangıç" value={startDate} onChange={setStartDate} /></View>
                        <View style={{flex:1}}><DatePickerInput label="Bitiş" value={endDate} onChange={setEndDate} /></View>
                    </View>
                )}
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
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 16, paddingHorizontal: 16 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 10, marginTop: 10 },
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
    dateGroup: { flex: 1 },
    dateLabel: { fontSize: 10, fontWeight: '700', color: '#64748B', marginBottom: 4, marginLeft: 4 },
    dateInp: { backgroundColor: '#F8FAFC', borderRadius: 10, padding: 8, fontSize: 12, color: '#0F172A', borderWidth: 1, borderColor: '#E2E8F0' },

    list: { padding: 16, paddingBottom: 40, marginTop: 10 },
    card: { backgroundColor: '#fff', borderRadius: 20, padding: 18, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 15, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15, borderBottomWidth: 1, borderBottomColor: '#F8FAFC', paddingBottom: 12 },
    titleGroup: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 10 },
    maintenanceTitle: { fontSize: 15, fontWeight: '800', color: '#1E293B' },
    amountText: { fontSize: 16, fontWeight: '900', color: '#0F172A' },

    cardBody: { gap: 12 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between' },
    infoItem: { flex: 1 },
    infoLabel: { fontSize: 9, fontWeight: '800', color: '#94A3B8', marginBottom: 4, letterSpacing: 0.5 },
    infoValue: { fontSize: 12, fontWeight: '700', color: '#475569' },
    
    noteBox: { backgroundColor: '#F8FAFC', padding: 10, borderRadius: 10, marginTop: 4 },
    noteText: { fontSize: 11, color: '#64748B', fontStyle: 'italic', lineHeight: 16 },

    empty: { alignItems: 'center', marginTop: 100 },
    emptyTxt: { color: '#94A3B8', marginTop: 12, fontWeight: '600' }
});
