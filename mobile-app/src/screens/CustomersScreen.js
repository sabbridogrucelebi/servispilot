import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function CustomersScreen({ navigation }) {
    const [customers, setCustomers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const fetchCustomers = async () => {
        try {
            setError(null);
            const response = await api.get('/v1/customers');
            if (response.data.success) {
                setCustomers(response.data.data);
            } else {
                setError(response.data.message || 'Müşteri verileri alınamadı.');
            }
        } catch (err) {
            console.error(err);
            setError('Bağlantı hatası oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => { fetchCustomers(); }, []);
    const onRefresh = () => { setRefreshing(true); fetchCustomers(); };

    const renderCustomer = ({ item }) => (
        <TouchableOpacity 
            style={styles.card} 
            activeOpacity={0.85}
            onPress={() => navigation.navigate('CustomerDetail', { customerId: item.id, customerName: item.company_name })}
        >
            <View style={styles.cardTop}>
                <View style={styles.iconWrapper}>
                    <Icon name="office-building-outline" size={40} color="#F59E0B" />
                </View>
                <View style={[styles.statusBadge, { backgroundColor: item.is_active ? '#ECFDF5' : '#FEF2F2' }]}>
                    <View style={[styles.statusDot, { backgroundColor: item.is_active ? '#10B981' : '#EF4444' }]} />
                    <Text style={[styles.statusText, { color: item.is_active ? '#10B981' : '#EF4444' }]}>
                        {item.is_active ? 'Aktif' : 'Pasif'}
                    </Text>
                </View>
            </View>
            
            <Text style={styles.nameText}>{item.company_name || 'İsimsiz Müşteri'}</Text>

            <View style={styles.cardFooter}>
                <View style={styles.footerItem}>
                    <Icon name="account-tie-outline" size={20} color="#94A3B8" />
                    <Text style={styles.footerText}>{item.authorized_person || 'Yetkili Yok'}</Text>
                </View>
                <View style={styles.footerItem}>
                    <Icon name="phone-outline" size={20} color="#94A3B8" />
                    <Text style={styles.footerText}>{item.phone || 'Telefon Yok'}</Text>
                </View>
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={styles.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={styles.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                            <Icon name="chevron-left" size={28} color="#ffffff" />
                        </TouchableOpacity>
                        <Text style={styles.headerTitle}>Müşteriler</Text>
                        <TouchableOpacity style={styles.backBtn}>
                            <Icon name="magnify" size={26} color="#ffffff" />
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {loading ? (
                <View style={styles.centerContent}><ActivityIndicator size="large" color="#3B82F6" /></View>
            ) : error ? (
                <View style={styles.centerContent}>
                    <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                    <Text style={[styles.emptyText, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                    <TouchableOpacity onPress={fetchCustomers} style={{ marginTop: 24, padding: 12, backgroundColor: '#3B82F6', borderRadius: 8 }}>
                        <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                    </TouchableOpacity>
                </View>
            ) : (
                <FlatList
                    data={customers}
                    keyExtractor={(item) => item.id.toString()}
                    renderItem={renderCustomer}
                    contentContainerStyle={styles.listContainer}
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Icon name="office-building-marker-outline" size={64} color="#CBD5E1" />
                            <Text style={styles.emptyText}>Henüz müşteri bulunmuyor.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    backBtn: { alignItems: 'center', justifyContent: 'center' },
    headerTitle: { fontSize: 24, fontWeight: '900', color: '#ffffff', letterSpacing: -0.5 },

    listContainer: { padding: 20, paddingBottom: 120 },
    card: { backgroundColor: '#ffffff', borderRadius: 24, padding: 24, marginBottom: 16, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.05, shadowRadius: 16, elevation: 4 },
    cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    iconWrapper: { alignItems: 'center', justifyContent: 'center' },
    statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 12, gap: 6 },
    statusDot: { width: 8, height: 8, borderRadius: 4 },
    statusText: { fontSize: 13, fontWeight: '800' },

    nameText: { fontSize: 22, fontWeight: '900', color: '#0F172A', marginBottom: 24 },

    cardFooter: { flexDirection: 'row', gap: 24 },
    footerItem: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    footerText: { fontSize: 14, color: '#475569', fontWeight: '700' },

    centerContent: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    emptyContainer: { alignItems: 'center', justifyContent: 'center', paddingVertical: 48 },
    emptyText: { color: '#94A3B8', fontSize: 16, marginTop: 16, fontWeight: '600' },
});
