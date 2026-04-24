import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

const { width } = Dimensions.get('window');

export default function PersonnelDetailScreen({ route, navigation }) {
    const { driverId, driverName } = route.params;
    const [driver, setDriver] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('info');

    const fetchDriverDetail = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await api.get(`/v1/personnel/${driverId}`);
            if (response.data.success) {
                setDriver(response.data.data);
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
        fetchDriverDetail();
    }, [driverId]);

    const renderTabs = () => (
        <View style={styles.tabContainer}>
            <TouchableOpacity style={[styles.tab, activeTab === 'info' && styles.activeTab]} onPress={() => setActiveTab('info')}>
                <Text style={[styles.tabText, activeTab === 'info' && styles.activeTabText]}>Bilgiler</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.tab, activeTab === 'docs' && styles.activeTab]} onPress={() => setActiveTab('docs')}>
                <Text style={[styles.tabText, activeTab === 'docs' && styles.activeTabText]}>Belgeler</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.tab, activeTab === 'payroll' && styles.activeTab]} onPress={() => setActiveTab('payroll')}>
                <Text style={[styles.tabText, activeTab === 'payroll' && styles.activeTabText]}>Bordrolar</Text>
            </TouchableOpacity>
        </View>
    );

    const renderInfo = () => (
        <View style={styles.section}>
            <Text style={styles.sectionTitle}>Kişisel Bilgiler</Text>
            <View style={styles.infoCard}>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>T.C. No:</Text><Text style={styles.infoValue}>{driver?.tc_no || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Telefon:</Text><Text style={styles.infoValue}>{driver?.phone || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Doğum Tarihi:</Text><Text style={styles.infoValue}>{driver?.birth_date || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Ehliyet:</Text><Text style={styles.infoValue}>{driver?.license_class || '-'} / SRC: {driver?.src_type || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Adres:</Text><Text style={styles.infoValue}>{driver?.address || '-'}</Text></View>
            </View>

            <Text style={styles.sectionTitle}>İş Bilgileri</Text>
            <View style={styles.infoCard}>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>İşe Giriş:</Text><Text style={styles.infoValue}>{driver?.start_date || '-'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Vardiya:</Text><Text style={styles.infoValue}>{driver?.start_shift === 'morning' ? 'Sabah' : (driver?.start_shift === 'evening' ? 'Akşam' : 'Tam Gün')}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Baz Maaş:</Text><Text style={styles.infoValue}>₺{driver?.base_salary || '0'}</Text></View>
                <View style={styles.infoRow}><Text style={styles.infoLabel}>Atanan Araç:</Text><Text style={styles.infoValue}>{driver?.vehicle?.plate || 'Yok'}</Text></View>
            </View>
        </View>
    );

    const renderDocs = () => {
        if (!driver?.documents || driver.documents.length === 0) {
            return (
                <View style={styles.emptyBox}>
                    <Icon name="file-document-outline" size={48} color="#CBD5E1" />
                    <Text style={styles.emptyText}>Kayıtlı belge yok.</Text>
                </View>
            );
        }
        return (
            <View style={styles.section}>
                {driver.documents.map(doc => (
                    <View key={doc.id} style={styles.docCard}>
                        <Icon name="file-document" size={32} color="#6366F1" />
                        <View style={{flex: 1, marginLeft: 16}}>
                            <Text style={styles.docName}>{doc.document_name}</Text>
                            <Text style={styles.docDate}>Bitiş: {doc.end_date || 'Süresiz'}</Text>
                        </View>
                    </View>
                ))}
            </View>
        );
    };

    const renderPayroll = () => {
        if (!driver?.payrolls || driver.payrolls.length === 0) {
            return (
                <View style={styles.emptyBox}>
                    <Icon name="cash-multiple" size={48} color="#CBD5E1" />
                    <Text style={styles.emptyText}>Geçmiş bordro bulunmuyor.</Text>
                </View>
            );
        }
        return (
            <View style={styles.section}>
                {driver.payrolls.map(pr => (
                    <View key={pr.id} style={styles.docCard}>
                        <Icon name="cash-check" size={32} color="#10B981" />
                        <View style={{flex: 1, marginLeft: 16}}>
                            <Text style={styles.docName}>{pr.period_month}</Text>
                            <Text style={styles.docDate}>Net: ₺{pr.net_salary}</Text>
                        </View>
                        <View style={[styles.statusBadge, { backgroundColor: pr.is_paid ? '#ECFDF5' : '#FEF2F2' }]}>
                            <Text style={[styles.statusText, { color: pr.is_paid ? '#10B981' : '#EF4444' }]}>
                                {pr.is_paid ? 'Ödendi' : 'Ödenmedi'}
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
                <TouchableOpacity onPress={fetchDriverDetail} style={{ marginTop: 24, padding: 12, backgroundColor: '#3B82F6', borderRadius: 8 }}>
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
                        <Text style={styles.headerTitle} numberOfLines={1}>{driver?.full_name || driverName}</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {renderTabs()}

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                {activeTab === 'info' && renderInfo()}
                {activeTab === 'docs' && renderDocs()}
                {activeTab === 'payroll' && renderPayroll()}
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
