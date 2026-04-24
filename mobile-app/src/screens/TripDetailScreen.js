import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function TripDetailScreen({ route, navigation }) {
    const { tripId } = route.params;
    const [trip, setTrip] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchTripDetail = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await api.get(`/v1/trips/${tripId}`);
            if (response.data.success) {
                setTrip(response.data.data);
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
        fetchTripDetail();
    }, [tripId]);

    if (loading) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#3B82F6" />
                <Text style={{ marginTop: 16, color: '#64748b' }}>Sefer Detayları Yükleniyor...</Text>
            </SafeAreaView>
        );
    }

    if (error) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                <Text style={[styles.emptyText, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                <TouchableOpacity onPress={fetchTripDetail} style={{ marginTop: 24, padding: 12, backgroundColor: '#3B82F6', borderRadius: 8 }}>
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
                        <Text style={styles.headerTitle} numberOfLines={1}>Sefer Detayı</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                
                {/* Genel Bilgiler */}
                <View style={styles.section}>
                    <Text style={styles.sectionTitle}>GENEL BİLGİLER</Text>
                    <View style={styles.infoCard}>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Tarih:</Text><Text style={styles.infoValue}>{trip?.trip_date || '-'} ({trip?.day_name})</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Durum:</Text>
                            <View style={[styles.statusBadge, { backgroundColor: trip?.trip_status === 'completed' ? '#ECFDF5' : '#FEF2F2' }]}>
                                <Text style={[styles.statusText, { color: trip?.trip_status === 'completed' ? '#10B981' : '#EF4444' }]}>
                                    {trip?.trip_status === 'completed' ? 'Tamamlandı' : 'İptal / Pasif'}
                                </Text>
                            </View>
                        </View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Sefer Ücreti:</Text><Text style={styles.infoValue}>₺{trip?.formatted_price || '0'}</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Notlar:</Text><Text style={styles.infoValue}>{trip?.notes || 'Not yok'}</Text></View>
                    </View>
                </View>

                {/* Rota ve Müşteri */}
                <View style={styles.section}>
                    <Text style={styles.sectionTitle}>MÜŞTERİ & GÜZERGÂH</Text>
                    <View style={styles.infoCard}>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Firma Adı:</Text><Text style={styles.infoValue}>{trip?.service_route?.customer?.company_name || 'Bilinmiyor'}</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Güzergâh:</Text><Text style={styles.infoValue}>{trip?.service_route?.route_name || 'Bilinmiyor'}</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Sabah Fiyatı:</Text><Text style={styles.infoValue}>₺{trip?.service_route?.morning_fee || '0'}</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Akşam Fiyatı:</Text><Text style={styles.infoValue}>₺{trip?.service_route?.evening_fee || '0'}</Text></View>
                    </View>
                </View>

                {/* Operasyon (Araç ve Sürücü) */}
                <View style={styles.section}>
                    <Text style={styles.sectionTitle}>OPERASYON DETAYI</Text>
                    <View style={styles.infoCard}>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Sürücü:</Text><Text style={styles.infoValue}>{trip?.driver?.full_name || 'Atanmamış'}</Text></View>
                        <View style={styles.infoRow}><Text style={styles.infoLabel}>Ana Araç:</Text><Text style={styles.infoValue}>{trip?.vehicle?.plate || 'Atanmamış'}</Text></View>
                        
                        {trip?.morning_vehicle_id && (
                            <View style={styles.infoRow}><Text style={styles.infoLabel}>Sabah Aracı (Yedek):</Text><Text style={styles.infoValue}>{trip?.morning_vehicle?.plate || '-'}</Text></View>
                        )}
                        {trip?.evening_vehicle_id && (
                            <View style={styles.infoRow}><Text style={styles.infoLabel}>Akşam Aracı (Yedek):</Text><Text style={styles.infoValue}>{trip?.evening_vehicle?.plate || '-'}</Text></View>
                        )}
                    </View>
                </View>

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
    
    scrollContent: { padding: 24, paddingBottom: 40, paddingTop: 16 },
    section: { marginBottom: 24 },
    sectionTitle: { fontSize: 13, fontWeight: '800', color: '#94A3B8', marginBottom: 12, letterSpacing: 1.5 },
    infoCard: { backgroundColor: '#ffffff', borderRadius: 20, padding: 20, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    infoLabel: { fontSize: 14, color: '#64748B', fontWeight: '500' },
    infoValue: { fontSize: 14, color: '#0F172A', fontWeight: '700', maxWidth: '65%', textAlign: 'right' },

    emptyText: { color: '#94A3B8', fontSize: 15, marginTop: 12, fontWeight: '500' },
    statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    statusText: { fontSize: 12, fontWeight: '700' }
});
