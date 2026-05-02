import React, { useState, useEffect, useContext } from 'react';
import { View, StyleSheet, FlatList, ActivityIndicator, Alert, Text, Platform, TouchableOpacity, RefreshControl, ScrollView, Linking, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';
import { EmptyState } from '../components';
import DatePickerInput from '../components/DatePickerInput';

const fmtMoney = (v) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 2 }).format(v || 0);

const toTitleCase = (str) => {
    if (!str) return '';
    return str.toString().split(' ').map(word => {
        if (!word) return '';
        const first = word.charAt(0).toLocaleUpperCase('tr-TR');
        const rest = word.slice(1).toLocaleLowerCase('tr-TR');
        return first + rest;
    }).join(' ');
};

export default function PenaltiesScreen({ navigation }) {
    const { hasPermission } = useContext(AuthContext);
    const [penalties, setPenalties] = useState([]);
    const [stats, setStats] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    
    // Filters
    const [filter, setFilter] = useState('all');
    const [search, setSearch] = useState('');
    const [dateFrom, setDateFrom] = useState('');
    const [dateTo, setDateTo] = useState('');
    const [showFilters, setShowFilters] = useState(false);

    const fetchData = async (isRefreshing = false) => {
        if (!isRefreshing) setLoading(true);
        try {
            // İstatistikleri çek
            const statsRes = await api.get('/v1/penalties/statistics');
            if (statsRes.data && statsRes.data.success) {
                setStats(statsRes.data.data);
            }

            // Cezaları çek
            const listRes = await api.get('/v1/penalties', {
                params: {
                    search,
                    date_from: dateFrom,
                    date_to: dateTo
                }
            });
            
            if (listRes.data && listRes.data.success && listRes.data.data) {
                setPenalties(listRes.data.data);
            } else {
                setPenalties([]);
            }
        } catch (e) {
            console.error('Fetch penalties error:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => { 
        const unsubscribe = navigation.addListener('focus', () => {
            fetchData();
        });
        return unsubscribe;
    }, [navigation]);

    // Handle search triggers explicitly
    useEffect(() => {
        // Debounce search slightly to avoid excessive calls
        const delayDebounceFn = setTimeout(() => {
            fetchData();
        }, 500);
        return () => clearTimeout(delayDebounceFn);
    }, [search, dateFrom, dateTo]);

    const openAdd = () => {
        if (!hasPermission('penalties.create')) {
            Alert.alert('Yetki Yok', 'Ceza kaydı ekleme yetkiniz bulunmuyor.');
            return;
        }
        navigation.navigate('PenaltyForm');
    };

    const confirmDelete = (id) => {
        if (!hasPermission('penalties.delete')) {
            Alert.alert('Yetki Yok', 'Ceza kaydı silme yetkiniz bulunmuyor.');
            return;
        }
        Alert.alert('Silinecek', 'Bu ceza kaydını silmek istediğinize emin misiniz?', [
            { text: 'Vazgeç', style: 'cancel' },
            { text: 'Sil', style: 'destructive', onPress: async () => {
                try { 
                    await api.delete(`/v1/penalties/${id}`); 
                    fetchData(true); 
                }
                catch (e) { Alert.alert('Hata', 'Silinemedi.'); }
            }}
        ]);
    };

    const handleShare = async (path, prefix) => {
        try {
            // Encode URI to handle spaces or special characters in file paths
            const encodedPath = encodeURI(path);
            const url = api.defaults.baseURL.replace('/api', '') + '/storage/' + encodedPath;
            
            const ext = path.split('.').pop() || 'pdf';
            const filename = `${prefix}_Belgesi.${ext}`;
            const fileUri = FileSystem.cacheDirectory + filename;
            
            // Download the file
            const downloadRes = await FileSystem.downloadAsync(url, fileUri);
            
            // Proceed to share
            if (await Sharing.isAvailableAsync()) {
                await Sharing.shareAsync(downloadRes.uri, {
                    dialogTitle: 'Belgeyi Paylaş',
                    mimeType: ext.toLowerCase() === 'pdf' ? 'application/pdf' : `image/${ext}`,
                    UTI: ext.toLowerCase() === 'pdf' ? 'com.adobe.pdf' : 'public.image'
                });
            } else {
                Alert.alert('Hata', 'Cihazınızda paylaşım özelliği desteklenmiyor.');
            }
        } catch (e) {
            console.error('Share error:', e);
            Alert.alert('Hata', `Paylaşım hatası: ${e.message || 'Bilinmeyen bir hata oluştu'}`);
        }
    };

    const filteredData = Array.isArray(penalties) ? penalties.filter(p => {
        if (filter === 'paid') return p.payment_status === 'paid';
        if (filter === 'unpaid') return p.payment_status === 'unpaid';
        return true;
    }) : [];

    // İstatistik Kartı Bileşeni (3D Premium)
    const StatCard = ({ title, value, subValue, colors, icon }) => (
        <View style={st.statCardContainer}>
            <LinearGradient 
                colors={colors} 
                start={{ x: 0, y: 0 }} 
                end={{ x: 1, y: 1 }} 
                style={st.statCard}
            >
                {/* 3D Background Icon for Depth */}
                <Icon name={icon} size={90} color="rgba(255,255,255,0.15)" style={st.statCardBgIcon} />
                
                <View style={st.statCardInner}>
                    <View style={st.statCardHeader}>
                        <Text style={st.statCardTitle}>{title}</Text>
                        <View style={st.statCardIconWrapper}>
                            <Icon name={icon} size={18} color={colors[0]} />
                        </View>
                    </View>
                    <Text style={st.statCardValue}>{value}</Text>
                    <Text style={st.statCardSub}>{subValue}</Text>
                </View>
            </LinearGradient>
        </View>
    );

    const renderItem = ({ item }) => {
        const isPaid = item.payment_status === 'paid';
        const discountDeadline = item.penalty_date ? new Date(new Date(item.penalty_date).getTime() + 30 * 24 * 60 * 60 * 1000) : new Date();
        
        const now = new Date();
        const isDiscountExpired = now > discountDeadline;
        
        let paymentStatusText = isPaid ? 'İndirimsiz Ödendi' : 'Ödenmedi';
        let paymentStatusColor = isPaid ? '#E11D48' : '#DC2626'; // İndirimsiz ödenenler KIRMIZI olacak
        let paymentIconColor = isPaid ? '#E11D48' : '#EF4444';

        if (isPaid && item.paid_amount && item.discounted_amount && parseFloat(item.paid_amount) == parseFloat(item.discounted_amount)) {
            paymentStatusText = '%25 İndirimli Ödendi';
            paymentStatusColor = '#059669'; // İndirimli ise YEŞİL
            paymentIconColor = '#10B981';
        }

        return (
            <View style={st.card}>
                <View style={st.cardHeader}>
                    <View style={[st.iconBox, { backgroundColor: isPaid ? '#ECFDF5' : '#FEF2F2' }]}>
                        <Icon name={isPaid ? "check-circle-outline" : "alert-circle-outline"} size={20} color={isPaid ? '#10B981' : '#EF4444'} />
                    </View>
                    <View style={{ flex: 1, paddingLeft: 10, paddingRight: 8 }}>
                        <Text style={st.cardTitle}>{item.penalty_no?.toUpperCase() || '-'}</Text>
                        <Text style={st.cardDesc}>
                            {item.vehicle?.plate || 'Plaka Yok'} • Şoför: {toTitleCase(item.driver_name) || '-'}
                        </Text>
                    </View>
                    <View style={{ alignItems: 'flex-end' }}>
                        <Text style={[st.amountText, isPaid && { color: '#059669' }]}>
                            {fmtMoney(isPaid && item.paid_amount ? item.paid_amount : item.penalty_amount)}
                        </Text>
                        {!isPaid && !isDiscountExpired && (
                            <Text style={st.discountText}>
                                İndirimli: {fmtMoney(item.discounted_amount)}
                            </Text>
                        )}
                    </View>
                </View>

                {/* Grid Structure */}
                <View style={st.cardGrid}>
                    <View style={st.gridRow}>
                        <View style={st.gridCol}>
                            <View style={st.gridLabelRow}>
                                <Icon name="calendar-blank-outline" size={12} color="#F59E0B" />
                                <Text style={[st.gridLabel, { color: '#F59E0B' }]}>TARİH</Text>
                            </View>
                            <Text style={[st.gridValue, { color: '#D97706' }]}>
                                {item.penalty_date ? new Date(item.penalty_date).toLocaleDateString('tr-TR') : '-'} 
                                {item.penalty_time ? ` ${item.penalty_time.substring(0,5)}` : ''}
                            </Text>
                        </View>
                        <View style={st.gridDivider} />
                        <View style={st.gridCol}>
                            <View style={st.gridLabelRow}>
                                <Icon name="file-document-outline" size={12} color="#3B82F6" />
                                <Text style={[st.gridLabel, { color: '#3B82F6' }]}>MADDE / YER</Text>
                            </View>
                            <Text style={[st.gridValue, { color: '#2563EB', flexWrap: 'wrap' }]}>
                                {item.penalty_article?.toUpperCase() || '-'}
                            </Text>
                            <Text style={[st.gridSubValue, { color: '#60A5FA', flexWrap: 'wrap', marginTop: 2 }]}>
                                {toTitleCase(item.penalty_location) || '-'}
                            </Text>
                        </View>
                    </View>
                    
                    <View style={st.gridHorizontalDivider} />
                    
                    <View style={st.gridRow}>
                        <View style={st.gridCol}>
                            <View style={st.gridLabelRow}>
                                <Icon name="credit-card-outline" size={12} color={paymentIconColor} />
                                <Text style={[st.gridLabel, { color: paymentIconColor }]}>ÖDEME DURUMU</Text>
                            </View>
                            <Text style={[st.gridValue, { color: paymentStatusColor }]}>
                                {paymentStatusText}
                            </Text>
                            {isPaid && item.payment_date && (
                                <Text style={[st.gridSubValue, { color: '#34D399' }]}>
                                    Tarih: {new Date(item.payment_date).toLocaleDateString('tr-TR')}
                                </Text>
                            )}
                        </View>
                        <View style={st.gridDivider} />
                        <View style={[st.gridCol, { justifyContent: 'center' }]}>
                            <View style={st.docsRow}>
                                {item.traffic_penalty_document ? (
                                    <View style={st.docGroup}>
                                        <TouchableOpacity style={st.docBtn} onPress={() => Linking.openURL(api.defaults.baseURL.replace('/api', '') + '/storage/' + item.traffic_penalty_document)}>
                                            <Icon name="file-document-outline" size={12} color="#6366F1" />
                                            <Text style={st.docText}>Ceza</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity style={st.shareBtn} onPress={() => handleShare(item.traffic_penalty_document, 'Trafik_Cezasi')}>
                                            <Icon name="share-variant" size={12} color="#64748B" />
                                        </TouchableOpacity>
                                    </View>
                                ) : null}
                                {item.payment_receipt ? (
                                    <View style={st.docGroup}>
                                        <TouchableOpacity style={st.docBtn} onPress={() => Linking.openURL(api.defaults.baseURL.replace('/api', '') + '/storage/' + item.payment_receipt)}>
                                            <Icon name="receipt" size={12} color="#10B981" />
                                            <Text style={st.docText}>Dekont</Text>
                                        </TouchableOpacity>
                                        <TouchableOpacity style={st.shareBtn} onPress={() => handleShare(item.payment_receipt, 'Odeme_Dekontu')}>
                                            <Icon name="share-variant" size={12} color="#64748B" />
                                        </TouchableOpacity>
                                    </View>
                                ) : null}
                                {!item.traffic_penalty_document && !item.payment_receipt && (
                                    <Text style={[st.gridSubValue, { color: '#94A3B8', fontStyle: 'italic' }]}>Belge yok</Text>
                                )}
                            </View>
                        </View>
                    </View>
                </View>

                {item.notes ? (
                    <View style={st.notesBox}>
                        <Icon name="information-outline" size={12} color="#64748B" />
                        <Text style={st.notesText}>Not: {toTitleCase(item.notes)}</Text>
                    </View>
                ) : null}

                {/* Actions */}
                {hasPermission('penalties.edit') && (
                    <View style={st.actionsRow}>
                        <TouchableOpacity style={st.actionBtnEdit} onPress={() => navigation.navigate('PenaltyForm', { penaltyId: item.id, penalty: item })}>
                            <Icon name="pencil" size={14} color="#3B82F6" />
                            <Text style={st.actionBtnTextEdit}>Düzenle</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={st.actionBtnDelete} onPress={() => confirmDelete(item.id)}>
                            <Icon name="trash-can-outline" size={14} color="#EF4444" />
                            <Text style={st.actionBtnTextDelete}>Sil</Text>
                        </TouchableOpacity>
                    </View>
                )}
            </View>
        );
    };

    return (
        <SafeAreaView style={st.container} edges={['top']}>
            <View style={{ backgroundColor: '#fff', zIndex: 10, paddingBottom: 12 }}>
                <View style={st.header}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={st.backBtn}>
                        <Icon name="chevron-left" size={26} color="#0F172A" />
                    </TouchableOpacity>
                    <View style={st.headerCenter}>
                        <Text style={st.headerTitle}>Trafik Cezaları</Text>
                        <Text style={st.headerSubtitle}>Araç ceza takip yönetimi</Text>
                    </View>
                    <TouchableOpacity style={st.addHeaderBtn} onPress={openAdd}>
                        <Icon name="plus" size={24} color="#fff" />
                    </TouchableOpacity>
                </View>
            </View>

            <ScrollView 
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => fetchData(true)} tintColor="#E11D48" />}
                showsVerticalScrollIndicator={false}
                contentContainerStyle={{ flexGrow: 1 }}
                keyboardShouldPersistTaps="handled"
            >
                {/* İstatistikler */}
                {stats && (
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={st.statsContainer}>
                        <StatCard 
                            title="Toplam Ceza Kaydı" 
                            value={stats.totalCount} 
                            subValue="Sistemdeki kayıt sayısı"
                            colors={['#EC4899', '#BE185D']} 
                            icon="file-document-multiple-outline" 
                        />
                        <StatCard 
                            title="Ödenmemiş Ceza" 
                            value={stats.unpaidCount} 
                            subValue="Kapatılmamış kayıt"
                            colors={['#F97316', '#C2410C']} 
                            icon="alert-octagon-outline" 
                        />
                        <StatCard 
                            title="Toplam Ceza Tutarı" 
                            value={fmtMoney(stats.totalAmount)} 
                            subValue="Tüm kayıtların maliyeti"
                            colors={['#8B5CF6', '#6D28D9']} 
                            icon="cash-multiple" 
                        />
                        <StatCard 
                            title="Tahsil Edilebilir" 
                            value={fmtMoney(stats.collectableAmount)} 
                            subValue="Bugün ödenecek tutar"
                            colors={['#10B981', '#047857']} 
                            icon="cash-check" 
                        />
                        <StatCard 
                            title="Bu Ay Kesilen" 
                            value={stats.thisMonthCount} 
                            subValue="Bu ay açılan ceza kaydı"
                            colors={['#0EA5E9', '#0369A1']} 
                            icon="calendar-alert" 
                        />
                    </ScrollView>
                )}

                {/* Filtre ve Arama Alanı */}
                <View style={st.searchContainer}>
                    <View style={st.searchInputWrapper}>
                        <Icon name="magnify" size={20} color="#94A3B8" />
                        <TextInput 
                            style={st.searchInput}
                            placeholder="Ceza no, şoför, madde, yer..."
                            placeholderTextColor="#94A3B8"
                            value={search}
                            onChangeText={setSearch}
                        />
                        {search ? (
                            <TouchableOpacity onPress={() => setSearch('')}>
                                <Icon name="close-circle" size={18} color="#94A3B8" />
                            </TouchableOpacity>
                        ) : null}
                    </View>
                    <TouchableOpacity 
                        style={[st.filterToggleBtn, showFilters && { backgroundColor: '#E11D48', borderColor: '#E11D48' }]}
                        onPress={() => setShowFilters(!showFilters)}
                    >
                        <Icon name="filter-variant" size={20} color={showFilters ? '#fff' : '#64748B'} />
                    </TouchableOpacity>
                </View>

                {showFilters && (
                    <View style={st.expandedFilters}>
                        <View style={{ flexDirection: 'row', gap: 10 }}>
                            <View style={{ flex: 1 }}>
                                <DatePickerInput 
                                    label="BAŞLANGIÇ" 
                                    value={dateFrom} 
                                    onChange={(d) => setDateFrom(d)} 
                                />
                            </View>
                            <View style={{ flex: 1 }}>
                                <DatePickerInput 
                                    label="BİTİŞ" 
                                    value={dateTo} 
                                    onChange={(d) => setDateTo(d)} 
                                />
                            </View>
                        </View>
                        <View style={{ flexDirection: 'row', justifyContent: 'flex-end', marginTop: 10 }}>
                            <TouchableOpacity 
                                style={st.clearDatesBtn} 
                                onPress={() => { setDateFrom(''); setDateTo(''); }}
                            >
                                <Text style={st.clearDatesText}>Tarihleri Temizle</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                )}

                <View style={st.filterBar}>
                    {[
                        { label: 'Tümü', value: 'all' },
                        { label: 'Ödenenler', value: 'paid' },
                        { label: 'Ödenmeyenler', value: 'unpaid' },
                    ].map(chip => (
                        <TouchableOpacity 
                            key={chip.value} 
                            style={[st.filterChip, filter === chip.value && st.filterChipActive]}
                            onPress={() => setFilter(chip.value)}
                        >
                            <Text style={[st.filterChipText, filter === chip.value && st.filterChipTextActive]}>{chip.label}</Text>
                        </TouchableOpacity>
                    ))}
                </View>

                {loading ? (
                    <View style={st.loader}><ActivityIndicator size="large" color="#E11D48" /></View>
                ) : (
                    <View style={st.listContent}>
                        {filteredData.length > 0 ? (
                            filteredData.map((item, index) => <React.Fragment key={index}>{renderItem({item})}</React.Fragment>)
                        ) : (
                            <EmptyState title="Kayıt Bulunamadı" message="Arama kriterlerinize uygun trafik cezası bulunmuyor." icon="alert-octagon-outline" />
                        )}
                        <View style={{height: 100}} />
                    </View>
                )}
            </ScrollView>
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    loader: { marginTop: 50, justifyContent: 'center', alignItems: 'center' },
    
    // Header
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingTop: Platform.OS === 'ios' ? 44 : 24 },
    backBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    headerCenter: { flex: 1, alignItems: 'center' },
    headerTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
    headerSubtitle: { fontSize: 13, color: '#64748B', marginTop: 2, fontWeight: '500' },
    addHeaderBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#E11D48', alignItems: 'center', justifyContent: 'center', shadowColor: '#E11D48', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 4 },

    // Stats (3D Premium)
    statsContainer: { paddingHorizontal: 12, paddingTop: 16, paddingBottom: 16 },
    statCardContainer: { 
        width: 175, 
        height: 120, 
        marginHorizontal: 6, 
        borderRadius: 24,
        shadowColor: '#000', 
        shadowOffset: { width: 0, height: 8 }, 
        shadowOpacity: 0.25, 
        shadowRadius: 12, 
        elevation: 10,
        backgroundColor: '#fff' 
    },
    statCard: { 
        flex: 1, 
        borderRadius: 24, 
        overflow: 'hidden',
        borderTopWidth: 1.5,
        borderTopColor: 'rgba(255,255,255,0.4)',
        borderBottomWidth: 4,
        borderBottomColor: 'rgba(0,0,0,0.2)',
        borderLeftWidth: 0.5,
        borderRightWidth: 0.5,
        borderColor: 'rgba(0,0,0,0.1)'
    },
    statCardBgIcon: { position: 'absolute', right: -15, bottom: -15, transform: [{ rotate: '-15deg' }] },
    statCardInner: { flex: 1, padding: 16, justifyContent: 'space-between', backgroundColor: 'rgba(255,255,255,0.05)' },
    statCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
    statCardTitle: { fontSize: 13, fontWeight: '800', color: '#fff', flex: 1, marginRight: 8, letterSpacing: 0.5, textShadowColor: 'rgba(0,0,0,0.2)', textShadowOffset: { width: 0, height: 1 }, textShadowRadius: 2 },
    statCardIconWrapper: { width: 32, height: 32, borderRadius: 12, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4, elevation: 3 },
    statCardValue: { fontSize: 24, fontWeight: '900', color: '#fff', marginTop: 8, textShadowColor: 'rgba(0,0,0,0.2)', textShadowOffset: { width: 0, height: 2 }, textShadowRadius: 4 },
    statCardSub: { fontSize: 10, fontWeight: '600', color: 'rgba(255,255,255,0.85)', marginTop: 4, letterSpacing: 0.5 },

    // Search and Filters
    searchContainer: { flexDirection: 'row', paddingHorizontal: 16, marginTop: 12, gap: 10 },
    searchInputWrapper: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 12, paddingHorizontal: 12, height: 48, borderWidth: 1, borderColor: '#E2E8F0' },
    searchInput: { flex: 1, height: '100%', paddingHorizontal: 8, fontSize: 14, color: '#0F172A' },
    filterToggleBtn: { width: 48, height: 48, borderRadius: 12, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#E2E8F0' },
    
    expandedFilters: { backgroundColor: '#fff', marginHorizontal: 16, marginTop: 10, padding: 16, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0' },
    clearDatesBtn: { backgroundColor: '#F1F5F9', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8 },
    clearDatesText: { fontSize: 12, fontWeight: '700', color: '#64748B' },

    filterBar: { flexDirection: 'row', paddingHorizontal: 16, marginVertical: 12, gap: 6 },
    filterChip: { paddingHorizontal: 4, paddingVertical: 10, borderRadius: 12, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0', flex: 1, alignItems: 'center', justifyContent: 'center' },
    filterChipActive: { backgroundColor: '#10B981', borderColor: '#10B981' },
    filterChipText: { fontSize: 11, fontWeight: '800', color: '#64748B', textAlign: 'center' },
    filterChipTextActive: { color: '#fff' },

    listContent: { padding: 16 },
    
    // Card Styles
    card: { backgroundColor: '#fff', borderRadius: 24, padding: 16, marginBottom: 16, borderWidth: 1, borderColor: '#F1F5F9', shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.08, shadowRadius: 16, elevation: 4 },
    cardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    iconBox: { width: 44, height: 44, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    cardTitle: { fontSize: 16, fontWeight: '800', color: '#0F172A', letterSpacing: 0.2 },
    cardDesc: { fontSize: 13, color: '#64748B', marginTop: 2, fontWeight: '500' },
    amountText: { fontSize: 17, fontWeight: '900', color: '#0F172A' },
    discountText: { fontSize: 10, color: '#10B981', fontWeight: '700', marginTop: 2 },

    cardGrid: { backgroundColor: '#F8FAFC', borderRadius: 16, padding: 12, borderWidth: 1, borderColor: '#F1F5F9' },
    gridRow: { flexDirection: 'row', alignItems: 'center' },
    gridCol: { flex: 1, paddingVertical: 4, paddingHorizontal: 6 },
    gridDivider: { width: 1, height: '100%', backgroundColor: '#E2E8F0', marginHorizontal: 8 },
    gridHorizontalDivider: { height: 1, backgroundColor: '#E2E8F0', marginVertical: 8 },
    
    gridLabelRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4, gap: 4 },
    gridLabel: { fontSize: 10, fontWeight: '800', letterSpacing: 0.5 },
    gridValue: { fontSize: 13, fontWeight: '700', color: '#1E293B', marginBottom: 2 },
    gridSubValue: { fontSize: 11, fontWeight: '600' },

    docsRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 6 },
    docGroup: { flexDirection: 'row', alignItems: 'center' },
    docBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', paddingHorizontal: 8, paddingVertical: 6, borderTopLeftRadius: 6, borderBottomLeftRadius: 6, gap: 4, borderWidth: 1, borderColor: '#E2E8F0', borderRightWidth: 0 },
    docText: { fontSize: 10, fontWeight: '700', color: '#475569' },
    shareBtn: { backgroundColor: '#F1F5F9', paddingHorizontal: 8, paddingVertical: 6, justifyContent: 'center', borderTopRightRadius: 6, borderBottomRightRadius: 6, borderWidth: 1, borderColor: '#E2E8F0' },
    
    notesBox: { flexDirection: 'row', alignItems: 'flex-start', gap: 6, marginTop: 12, paddingHorizontal: 4 },
    notesText: { fontSize: 12, color: '#64748B', fontStyle: 'italic', flex: 1, lineHeight: 18 },

    actionsRow: { flexDirection: 'row', justifyContent: 'flex-end', marginTop: 16, gap: 8, borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 12 },
    actionBtnEdit: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#EFF6FF', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, gap: 4 },
    actionBtnTextEdit: { color: '#3B82F6', fontSize: 12, fontWeight: '700' },
    actionBtnDelete: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FEF2F2', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, gap: 4 },
    actionBtnTextDelete: { color: '#E11D48', fontSize: 12, fontWeight: '700' },
});
