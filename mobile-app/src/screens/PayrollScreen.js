import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function PayrollScreen({ navigation }) {
    const [payrolls, setPayrolls] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);

    const fetchPayrolls = async (pageNumber = 1, isRefresh = false) => {
        try {
            if (isRefresh) setRefreshing(true);
            else if (pageNumber === 1) setLoading(true);
            else setLoadingMore(true);

            setError(null);
            const response = await api.get(`/v1/payrolls?page=${pageNumber}&per_page=20`);

            if (response.data.success) {
                const newPayrolls = response.data.data;
                const meta = response.data.meta;
                
                if (pageNumber === 1) {
                    setPayrolls(newPayrolls);
                } else {
                    setPayrolls(prev => [...prev, ...newPayrolls]);
                }

                setHasMore(meta.current_page < meta.last_page);
                setPage(meta.current_page);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            console.error(err);
            if (err.response?.status === 403) {
                setError('Bu alanı görüntüleme yetkiniz yok.');
            } else {
                setError('Bağlantı hatası oluştu.');
            }
        } finally {
            setLoading(false);
            setRefreshing(false);
            setLoadingMore(false);
        }
    };

    useEffect(() => {
        fetchPayrolls(1);
    }, []);

    const onRefresh = () => {
        setHasMore(true);
        fetchPayrolls(1, true);
    };

    const loadMore = () => {
        if (!loadingMore && hasMore && !loading) {
            fetchPayrolls(page + 1);
        }
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount);
    };

    const renderItem = ({ item }) => (
        <TouchableOpacity 
            style={s.card} 
            activeOpacity={0.85}
            onPress={() => navigation.navigate('PayrollDetail', { payrollId: item.id })}
        >
            <View style={s.cardTop}>
                <View style={s.badge}>
                    <Icon name="calendar-month-outline" size={20} color="#3B82F6" />
                    <Text style={s.badgeText}>{item.period_human}</Text>
                </View>
                {item.is_locked && (
                    <View style={s.lockBadge}>
                        <Icon name="lock" size={16} color="#EF4444" />
                        <Text style={s.lockText}>Kilitli</Text>
                    </View>
                )}
            </View>
            <Text style={s.driverText}>{item.driver_name}</Text>
            <View style={s.amountRow}>
                <View>
                    <Text style={s.amountLabel}>Ana Maaş</Text>
                    <Text style={s.amountValueBase}>{formatCurrency(item.base_salary)}</Text>
                </View>
                <View style={{ alignItems: 'flex-end' }}>
                    <Text style={s.amountLabel}>Net Ödenen</Text>
                    <Text style={s.amountValueNet}>{formatCurrency(item.net_salary)}</Text>
                </View>
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.hBtn}>
                            <Icon name="chevron-left" size={28} color="#fff" />
                        </TouchableOpacity>
                        <Text style={s.hTitle}>Bordrolar</Text>
                        <View style={{ width: 28 }} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {loading && page === 1 ? (
                <View style={s.centerContent}><ActivityIndicator size="large" color="#3B82F6" /></View>
            ) : error && page === 1 ? (
                <View style={s.centerContent}>
                    <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                    <Text style={[s.emptyT, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                    <TouchableOpacity onPress={() => fetchPayrolls(1)} style={s.retryBtn}>
                        <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                    </TouchableOpacity>
                </View>
            ) : (
                <FlatList
                    data={payrolls}
                    keyExtractor={(i) => i.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={s.content}
                    showsVerticalScrollIndicator={false}
                    onEndReached={loadMore}
                    onEndReachedThreshold={0.5}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <Icon name="cash-multiple" size={64} color="#CBD5E1" />
                            <Text style={s.emptyT}>Kayıtlı bordro bulunamadı.</Text>
                        </View>
                    }
                    ListFooterComponent={
                        loadingMore ? <ActivityIndicator size="small" color="#3B82F6" style={{ marginVertical: 16 }} /> : <View style={{ height: 20 }} />
                    }
                />
            )}
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    hRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    hBtn: { alignItems: 'center', justifyContent: 'center' },
    hTitle: { fontSize: 24, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    content: { padding: 20, paddingTop: 30, paddingBottom: 100 },
    card: { backgroundColor: '#fff', borderRadius: 20, padding: 20, marginBottom: 16, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 3 },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    badge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#EFF6FF', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, gap: 6 },
    badgeText: { color: '#3B82F6', fontWeight: '800', fontSize: 13 },
    lockBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FEF2F2', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6, gap: 4 },
    lockText: { color: '#EF4444', fontWeight: '700', fontSize: 11 },
    driverText: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginBottom: 16 },
    amountRow: { flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 16 },
    amountLabel: { fontSize: 12, color: '#64748B', fontWeight: '600', marginBottom: 4 },
    amountValueBase: { fontSize: 16, fontWeight: '700', color: '#475569' },
    amountValueNet: { fontSize: 18, fontWeight: '900', color: '#10B981' },
    centerContent: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    empty: { alignItems: 'center', paddingVertical: 64 },
    emptyT: { color: '#94A3B8', fontSize: 16, marginTop: 16, fontWeight: '600' },
    retryBtn: { marginTop: 24, padding: 12, paddingHorizontal: 24, backgroundColor: '#3B82F6', borderRadius: 8 }
});
