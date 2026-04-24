import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function CustomerDetailScreen({ route, navigation }) {
    const { customerId, customerName } = route.params;
    const [customer, setCustomer] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('info');

    const fetchCustomerDetail = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await api.get(`/v1/customers/${customerId}`);
            if (response.data.success) {
                setCustomer(response.data.data);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            setError('Bağlantı hatası.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCustomerDetail();
    }, [customerId]);

    const renderTabs = () => (
        <View style={styles.tabContainer}>
            <TouchableOpacity style={[styles.tab, activeTab === 'info' && styles.activeTab]} onPress={() => setActiveTab('info')}>
                <Text style={[styles.tabText, activeTab === 'info' && styles.activeTabText]}>Bilgiler</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.tab, activeTab === 'contracts' && styles.activeTab]} onPress={() => setActiveTab('contracts')}>
                <Text style={[styles.tabText, activeTab === 'contracts' && styles.activeTabText]}>Sözleşmeler</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.tab, activeTab === 'routes' && styles.activeTab]} onPress={() => setActiveTab('routes')}>
                <Text style={[styles.tabText, activeTab === 'routes' && styles.activeTabText]}>Rotalar</Text>
            </TouchableOpacity>
        </View>
    );

    const renderInfo = () => (
        <View style={styles.section}>
            <Text style={styles.sectionTitle}>Firma Bilgileri</Text>
            <View style={styles.infoCard}>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Unvan:</Text><Text style={styles.infoValue}>{customer?.company_title || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Müşteri Tipi:</Text><Text style={styles.infoValue}>{customer?.customer_type === 'school' ? 'Okul' : (customer?.customer_type === 'factory' ? 'Fabrika' : 'Şirket/Kurum')}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>E-Posta:</Text><Text style={styles.infoValue}>{customer?.email || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Adres:</Text><Text style={styles.infoValue}>{customer?.address || '-'}</Text></View>
            </View>

            <Text style={styles.sectionTitle}>İletişim & Finans</Text>
            <View style={styles.infoCard}>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Yetkili:</Text><Text style={styles.infoValue}>{customer?.authorized_person || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Yetkili Tel:</Text><Text style={styles.infoValue}>{customer?.authorized_phone || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Firma Tel:</Text><Text style={styles.infoValue}>{customer?.phone || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Aylık Tutar:</Text><Text style={styles.infoValue}>₺{customer?.monthly_price || '0'}</Text></View>
            </View>
        </View>
    );

    const renderContracts = () => {
        if (!customer?.contracts || customer.contracts.length === 0) {
            return (
                <View style={styles.emptyBox}>
                    <Icon name="file-sign" size={48} color="#CBD5E1" />
                    <Text style={styles.emptyText}>Kayıtlı sözleşme yok.</Text>
                </View>
            );
        }
        return (
            <View style={styles.section}>
                {customer.contracts.map(contract => (
                    <View key={contract.id} style={styles.docCard}>
                        <Icon name="file-document-edit" size={32} color="#F59E0B" />
                        <View style={{flex: 1, marginLeft: 16}}>
                            <Text style={styles.docName}>{contract.year} Sözleşmesi</Text>
                            <Text style={styles.docDate}>{contract.start_date} / {contract.end_date}</Text>
                        </View>
                        <View style={[styles.statusBadge, { backgroundColor: '#EEF2FF' }]}>
                            <Text style={[styles.statusText, { color: '#4F46E5' }]}>₺{contract.monthly_price}</Text>
                        </View>
                    </View>
                ))}
            </View>
        );
    };

    const renderRoutes = () => {
        if (!customer?.service_routes || customer.service_routes.length === 0) {
            return (
                <View style={styles.emptyBox}>
                    <Icon name="map-marker-path" size={48} color="#CBD5E1" />
                    <Text style={styles.emptyText}>Tanımlı güzergâh yok.</Text>
                </View>
            );
        }
        return (
            <View style={styles.section}>
                {customer.service_routes.map(route => (
                    <View key={route.id} style={styles.docCard}>
                        <Icon name="bus-marker" size={32} color="#3B82F6" />
                        <View style={{flex: 1, marginLeft: 16}}>
                            <Text style={styles.docName}>{route.route_name}</Text>
                            <Text style={styles.docDate}>Tutar: ₺{route.fee_type === 'paid' ? route.morning_fee : route.fallback_morning_fee}</Text>
                        </View>
                        <View style={[styles.statusBadge, { backgroundColor: route.is_active ? '#ECFDF5' : '#FEF2F2' }]}>
                            <Text style={[styles.statusText, { color: route.is_active ? '#10B981' : '#EF4444' }]}>
                                {route.is_active ? 'Aktif' : 'Pasif'}
                            </Text>
                        </View>
                    </View>
                ))}
            </View>
        );
    };

    if (loading) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#3B82F6" />
                <Text style={{ marginTop: 16, color: '#64748b' }}>Detaylar Yükleniyor...</Text>
            </SafeAreaView>
        );
    }

    if (error) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                <Text style={[styles.emptyText, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                <TouchableOpacity onPress={fetchCustomerDetail} style={{ marginTop: 24, padding: 12, backgroundColor: '#3B82F6', borderRadius: 8 }}>
                    <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                </TouchableOpacity>
            </SafeAreaView>
        );
    }

    return (
        <View style={styles.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={styles.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={styles.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                            <Icon name="chevron-left" size={28} color="#ffffff" />
                        </TouchableOpacity>
                        <Text style={styles.headerTitle} numberOfLines={1}>{customer?.company_name || customerName}</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {renderTabs()}

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                {activeTab === 'info' && renderInfo()}
                {activeTab === 'contracts' && renderContracts()}
                {activeTab === 'routes' && renderRoutes()}
            </ScrollView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    backBtn: { alignItems: 'center', justifyContent: 'center' },
    headerTitle: { flex: 1, textAlign: 'center', fontSize: 20, fontWeight: '900', color: '#ffffff', letterSpacing: -0.5 },
    
    tabContainer: { flexDirection: 'row', backgroundColor: '#ffffff', marginHorizontal: 20, marginTop: -20, borderRadius: 16, padding: 4, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 4 },
    tab: { flex: 1, paddingVertical: 12, alignItems: 'center', borderRadius: 12 },
    activeTab: { backgroundColor: '#EEF2FF' },
    tabText: { fontSize: 14, fontWeight: '600', color: '#94A3B8' },
    activeTabText: { color: '#4F46E5', fontWeight: '700' },

    scrollContent: { padding: 24, paddingBottom: 40 },
    section: { marginBottom: 24 },
    sectionTitle: { fontSize: 14, fontWeight: '800', color: '#94A3B8', marginBottom: 12, letterSpacing: 1 },
    infoCard: { backgroundColor: '#ffffff', borderRadius: 20, padding: 20, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    infoLabel: { fontSize: 14, color: '#64748B', fontWeight: '500' },
    infoValue: { fontSize: 14, color: '#0F172A', fontWeight: '700', maxWidth: '65%', textAlign: 'right' },

    docCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#ffffff', borderRadius: 16, padding: 16, marginBottom: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.02, shadowRadius: 4, elevation: 1 },
    docName: { fontSize: 15, fontWeight: '700', color: '#0F172A' },
    docDate: { fontSize: 13, color: '#64748B', marginTop: 4 },

    emptyBox: { alignItems: 'center', justifyContent: 'center', paddingVertical: 48 },
    emptyText: { color: '#94A3B8', fontSize: 15, marginTop: 12, fontWeight: '500' },
    
    statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    statusText: { fontSize: 12, fontWeight: '700' }
});
