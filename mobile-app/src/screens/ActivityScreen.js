import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Modal, TextInput } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';
import { Header } from '../components';
import { LinearGradient } from 'expo-linear-gradient';

export default function ActivityScreen({ navigation }) {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);
    
    // Pagination states
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);

    // Filter states
    const [filterModal, setFilterModal] = useState(false);
    const [filters, setFilters] = useState({ search: '', module: '', action: '' });
    const [activeFilters, setActiveFilters] = useState({ search: '', module: '', action: '' });

    const fetchLogs = async (pageNumber = 1, isRefresh = false, currentFilters = activeFilters) => {
        try {
            if (isRefresh) setRefreshing(true);
            else if (pageNumber === 1) setLoading(true);
            else setLoadingMore(true);

            setError(null);
            
            // Query params build
            let query = `/v1/activity-logs?page=${pageNumber}&per_page=20`;
            if (currentFilters.search) query += `&search=${encodeURIComponent(currentFilters.search)}`;
            if (currentFilters.module) query += `&module=${encodeURIComponent(currentFilters.module)}`;
            if (currentFilters.action) query += `&action=${encodeURIComponent(currentFilters.action)}`;

            const response = await api.get(query);

            if (response.data.success) {
                const newLogs = response.data.data;
                const meta = response.data.meta;
                
                if (pageNumber === 1) setLogs(newLogs);
                else setLogs(prev => [...prev, ...newLogs]);

                setHasMore(meta.current_page < meta.last_page);
                setPage(meta.current_page);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            setError(err.response?.status === 403 ? 'Bu alanı görüntüleme yetkiniz yok.' : 'Bağlantı hatası oluştu.');
        } finally {
            setLoading(false); setRefreshing(false); setLoadingMore(false);
        }
    };

    useEffect(() => { fetchLogs(1); }, []);

    const onRefresh = () => { setHasMore(true); fetchLogs(1, true); };
    const loadMore = () => { if (!loadingMore && hasMore && !loading && !refreshing) fetchLogs(page + 1); };

    const applyFilters = () => {
        setActiveFilters(filters);
        setFilterModal(false);
        fetchLogs(1, false, filters);
    };

    const clearFilters = () => {
        const empty = { search: '', module: '', action: '' };
        setFilters(empty);
        setActiveFilters(empty);
        setFilterModal(false);
        fetchLogs(1, false, empty);
    };

    const getIconDetails = (module, action) => {
        let iconName = 'view-grid';
        
        switch (module) {
            case 'vehicles': iconName = 'car'; break;
            case 'drivers': iconName = 'steering'; break;
            case 'trips': iconName = 'map-marker-path'; break;
            case 'fuels': iconName = 'gas-station'; break;
            case 'maintenances': iconName = 'wrench'; break;
            case 'penalties': iconName = 'alert-octagon'; break;
            case 'documents': iconName = 'file-document'; break;
            case 'customers': iconName = 'domain'; break;
            case 'users': iconName = 'account-group'; break;
        }

        let bg = '#F1F5F9', color = '#64748B'; // Default Slate

        if (['created', 'image_uploaded', 'document_uploaded'].includes(action)) { bg = '#D1FAE5'; color = '#059669'; } // Emerald
        else if (action === 'updated') { bg = '#DBEAFE'; color = '#2563EB'; } // Blue
        else if (['deleted', 'image_deleted', 'document_deleted'].includes(action)) { bg = '#FEE2E2'; color = '#DC2626'; } // Red
        else if (action === 'exported') { bg = '#F3E8FF'; color = '#9333EA'; } // Purple

        const actionNames = {
            created: 'OLUŞTURULDU',
            updated: 'GÜNCELLENDİ',
            deleted: 'SİLİNDİ',
            exported: 'DIŞA AKTARILDI',
            image_uploaded: 'GÖRSEL EKLENDİ',
            image_deleted: 'GÖRSEL SİLİNDİ',
            document_uploaded: 'BELGE EKLENDİ',
            document_deleted: 'BELGE SİLİNDİ'
        };

        const actionText = actionNames[action] || action.toUpperCase();

        return { icon: iconName, bg, color, actionText };
    };

    const isFiltered = activeFilters.search || activeFilters.module || activeFilters.action;

    const renderItem = ({ item, index }) => {
        const { icon, bg, color, actionText } = getIconDetails(item.module, item.action);
        const isLast = index === logs.length - 1;

        return (
            <View style={st.timelineItem}>
                {/* Timeline Line */}
                {!isLast && <View style={st.timelineLine} />}
                
                {/* Timeline Node */}
                <View style={[st.timelineNode, { backgroundColor: bg }]}>
                    <Icon name={icon} size={20} color={color} />
                </View>

                {/* Timeline Content */}
                <View style={st.timelineContent}>
                    <View style={st.card}>
                        <View style={st.cardHeader}>
                            <Text style={st.cardTitle}>{item.title}</Text>
                            <View style={[st.badge, { backgroundColor: bg }]}>
                                <Text style={[st.badgeTxt, { color }]}>{actionText}</Text>
                            </View>
                        </View>
                        <Text style={st.cardDesc}>{item.description}</Text>
                        
                        <View style={st.cardFooter}>
                            <View style={st.footerLeft}>
                                <Icon name="account" size={14} color="#94A3B8" />
                                <Text style={st.footerTxt}>{item.user_name}</Text>
                            </View>
                            <View style={st.footerRight}>
                                <Icon name="clock-outline" size={14} color="#94A3B8" />
                                <Text style={st.footerTxt}>{item.created_at_human}</Text>
                            </View>
                        </View>
                    </View>
                </View>
            </View>
        );
    };

    return (
        <SafeAreaView style={st.container}>
            <Header 
                title="Sistem Logları" 
                subtitle="Hareket Dökümü" 
                onBack={() => navigation.goBack()} 
                rightIcon={isFiltered ? "filter" : "filter-outline"}
                rightIconColor={isFiltered ? "#3B82F6" : "#64748B"}
                onRightPress={() => setFilterModal(true)}
            />

            {error && !loading && logs.length === 0 ? (
                <View style={st.center}>
                    <Icon name="alert-circle-outline" size={48} color="#EF4444" />
                    <Text style={st.errorTxt}>{error}</Text>
                    <TouchableOpacity onPress={() => fetchLogs(1)} style={st.retryBtn}><Text style={st.retryTxt}>Tekrar Dene</Text></TouchableOpacity>
                </View>
            ) : (
                <FlatList
                    data={logs}
                    keyExtractor={(i) => i.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={st.listContent}
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
                    onEndReached={loadMore}
                    onEndReachedThreshold={0.5}
                    ListEmptyComponent={
                        !loading && (
                            <View style={st.center}>
                                <Icon name="magnify" size={56} color="#CBD5E1" />
                                <Text style={st.emptyTxt}>Log bulunamadı.</Text>
                            </View>
                        )
                    }
                    ListFooterComponent={
                        loadingMore ? <ActivityIndicator size="small" color="#3B82F6" style={{ marginVertical: 20 }} /> : null
                    }
                />
            )}

            {/* FİLTRE MODALI */}
            <Modal visible={filterModal} animationType="slide" transparent={true} onRequestClose={() => setFilterModal(false)}>
                <View style={st.modalOverlay}>
                    <View style={st.modalContent}>
                        <View style={st.modalHeader}>
                            <Text style={st.modalTitle}>Gelişmiş Filtreler</Text>
                            <TouchableOpacity onPress={() => setFilterModal(false)}><Icon name="close" size={24} color="#64748B" /></TouchableOpacity>
                        </View>

                        <Text style={st.label}>Arama</Text>
                        <TextInput
                            style={st.input}
                            placeholder="Başlık veya içerik..."
                            value={filters.search}
                            onChangeText={(t) => setFilters(p => ({...p, search: t}))}
                            placeholderTextColor="#94A3B8"
                        />

                        <Text style={st.label}>Modül</Text>
                        <View style={st.chipGroup}>
                            {['', 'vehicles', 'trips', 'fuels', 'maintenances', 'penalties'].map((mod, i) => {
                                const isActive = filters.module === mod;
                                return (
                                    <TouchableOpacity key={i} style={[st.chip, isActive && st.chipActive]} onPress={() => setFilters(p => ({...p, module: mod}))}>
                                        <Text style={[st.chipTxt, isActive && st.chipTxtActive]}>{mod || 'Tümü'}</Text>
                                    </TouchableOpacity>
                                );
                            })}
                        </View>

                        <Text style={st.label}>İşlem Tipi</Text>
                        <View style={st.chipGroup}>
                            {[
                                {val: '', label: 'Tümü'}, 
                                {val: 'created', label: 'Oluşturma'}, 
                                {val: 'updated', label: 'Güncelleme'}, 
                                {val: 'deleted', label: 'Silme'},
                                {val: 'exported', label: 'Dışa Aktarım'},
                                {val: 'image_uploaded', label: 'Görsel Ekleme'},
                                {val: 'document_uploaded', label: 'Belge Ekleme'}
                            ].map((act, i) => {
                                const isActive = filters.action === act.val;
                                return (
                                    <TouchableOpacity key={i} style={[st.chip, isActive && st.chipActive]} onPress={() => setFilters(p => ({...p, action: act.val}))}>
                                        <Text style={[st.chipTxt, isActive && st.chipTxtActive]}>{act.label}</Text>
                                    </TouchableOpacity>
                                );
                            })}
                        </View>

                        <View style={st.modalActions}>
                            <TouchableOpacity style={st.clearBtn} onPress={clearFilters}>
                                <Text style={st.clearBtnTxt}>Temizle</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={st.applyBtn} onPress={applyFilters}>
                                <Text style={st.applyBtnTxt}>Uygula</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
    errorTxt: { color: '#64748B', marginTop: 10, textAlign: 'center' },
    retryBtn: { marginTop: 15, paddingHorizontal: 20, paddingVertical: 10, backgroundColor: '#3B82F6', borderRadius: 8 },
    retryTxt: { color: '#fff', fontWeight: 'bold' },
    emptyTxt: { color: '#94A3B8', fontSize: 16, fontWeight: '500', marginTop: 10 },
    
    listContent: { padding: 16, paddingBottom: 100 },
    
    // Timeline Styles
    timelineItem: { flexDirection: 'row', position: 'relative' },
    timelineLine: { position: 'absolute', left: 19, top: 40, bottom: -10, width: 2, backgroundColor: '#E2E8F0', zIndex: 0 },
    timelineNode: { width: 40, height: 40, borderRadius: 20, justifyContent: 'center', alignItems: 'center', zIndex: 1 },
    timelineContent: { flex: 1, marginLeft: 16, marginBottom: 16 },
    
    card: { backgroundColor: '#fff', borderRadius: 16, padding: 16, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.05, shadowRadius: 6, elevation: 2, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 8 },
    cardTitle: { flex: 1, fontSize: 15, fontWeight: '700', color: '#0F172A', marginRight: 8 },
    badge: { paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 },
    badgeTxt: { fontSize: 9, fontWeight: '800' },
    cardDesc: { fontSize: 13, color: '#64748B', lineHeight: 18, marginBottom: 12 },
    cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 10 },
    footerLeft: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    footerRight: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    footerTxt: { fontSize: 11, fontWeight: '600', color: '#94A3B8' },

    // Modal Styles
    modalOverlay: { flex: 1, backgroundColor: 'rgba(15, 23, 42, 0.4)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 24, paddingBottom: 40 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
    label: { fontSize: 13, fontWeight: '700', color: '#475569', marginBottom: 8, marginTop: 12 },
    input: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, padding: 12, fontSize: 14, color: '#0F172A' },
    
    chipGroup: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    chip: { backgroundColor: '#F1F5F9', paddingHorizontal: 14, paddingVertical: 8, borderRadius: 20, borderWidth: 1, borderColor: 'transparent' },
    chipActive: { backgroundColor: '#DBEAFE', borderColor: '#3B82F6' },
    chipTxt: { fontSize: 13, fontWeight: '600', color: '#64748B' },
    chipTxtActive: { color: '#2563EB' },

    modalActions: { flexDirection: 'row', gap: 12, marginTop: 30 },
    clearBtn: { flex: 1, paddingVertical: 14, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center' },
    clearBtnTxt: { fontSize: 15, fontWeight: '700', color: '#475569' },
    applyBtn: { flex: 2, paddingVertical: 14, borderRadius: 12, backgroundColor: '#3B82F6', alignItems: 'center', shadowColor: '#3B82F6', shadowOffset: {width:0,height:4}, shadowopacity: 1, shadowRadius: 8, elevation: 4 },
    applyBtnTxt: { fontSize: 15, fontWeight: '700', color: '#fff' },
});
