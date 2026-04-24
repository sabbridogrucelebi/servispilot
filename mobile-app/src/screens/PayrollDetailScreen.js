import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function PayrollDetailScreen({ route, navigation }) {
    const { payrollId } = route.params;
    const [payroll, setPayroll] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchDetail = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await api.get(`/v1/payrolls/${payrollId}`);
            if (response.data.success) {
                setPayroll(response.data.data);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            if (err.response?.status === 403) {
                setError('Bu alanı görüntüleme yetkiniz yok.');
            } else {
                setError('Bağlantı hatası.');
            }
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDetail();
    }, [payrollId]);

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount || 0);
    };

    if (loading) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#3B82F6" />
                <Text style={{ marginTop: 16, color: '#64748b' }}>Bordro Detayı Yükleniyor...</Text>
            </SafeAreaView>
        );
    }

    if (error) {
        return (
            <SafeAreaView style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                <Text style={[s.emptyText, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                <TouchableOpacity onPress={fetchDetail} style={s.retryBtn}>
                    <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                </TouchableOpacity>
            </SafeAreaView>
        );
    }

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="chevron-left" size={28} color="#ffffff" />
                        </TouchableOpacity>
                        <Text style={s.hTitle} numberOfLines={1}>Bordro Detayı</Text>
                        <View style={{width: 28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView contentContainerStyle={s.scrollContent} showsVerticalScrollIndicator={false}>
                
                <View style={s.profileCard}>
                    <Icon name="account-tie-outline" size={40} color="#3B82F6" style={{ marginBottom: 12 }} />
                    <Text style={s.driverName}>{payroll?.driver?.full_name || 'Bilinmeyen Sürücü'}</Text>
                    <Text style={s.periodText}>{payroll?.period_human}</Text>
                    
                    {payroll?.is_locked && (
                        <View style={s.lockWarning}>
                            <Icon name="lock" size={16} color="#EF4444" />
                            <Text style={s.lockWarningText}>Bu dönem kilitlenmiştir, salt okunurdur.</Text>
                        </View>
                    )}
                </View>

                {/* Hakedişler */}
                <View style={s.section}>
                    <Text style={s.sectionTitle}>HAKEDİŞLER (GELİRLER)</Text>
                    <View style={s.infoCard}>
                        <View style={s.infoRow}><Text style={s.infoLabel}>Ana Maaş:</Text><Text style={s.infoValuePos}>{formatCurrency(payroll?.base_salary)}</Text></View>
                        <View style={s.infoRow}><Text style={s.infoLabel}>Sefer Hakedişi:</Text><Text style={s.infoValuePos}>{formatCurrency(payroll?.extra_payment)}</Text></View>
                        <View style={[s.infoRow, { borderBottomWidth: 0 }]}><Text style={s.infoLabel}>Ekstra Prim/Bonus:</Text><Text style={s.infoValuePos}>{formatCurrency(payroll?.extra_bonus)}</Text></View>
                        {payroll?.extra_notes && <Text style={s.notesText}>Not: {payroll.extra_notes}</Text>}
                    </View>
                </View>

                {/* Kesintiler */}
                <View style={s.section}>
                    <Text style={s.sectionTitle}>KESİNTİLER VE ÖDEMELER</Text>
                    <View style={s.infoCard}>
                        <View style={s.infoRow}><Text style={s.infoLabel}>Banka Ödemesi:</Text><Text style={s.infoValueNeg}>- {formatCurrency(payroll?.bank_payment)}</Text></View>
                        <View style={s.infoRow}><Text style={s.infoLabel}>Trafik Cezası Kesintisi:</Text><Text style={s.infoValueNeg}>- {formatCurrency(payroll?.traffic_penalty)}</Text></View>
                        <View style={s.infoRow}><Text style={s.infoLabel}>Verilen Avans:</Text><Text style={s.infoValueNeg}>- {formatCurrency(payroll?.advance_payment)}</Text></View>
                        <View style={[s.infoRow, { borderBottomWidth: 0 }]}><Text style={s.infoLabel}>Özel Kesinti:</Text><Text style={s.infoValueNeg}>- {formatCurrency(payroll?.deduction)}</Text></View>
                        {payroll?.deduction_notes && <Text style={s.notesText}>Not: {payroll.deduction_notes}</Text>}
                    </View>
                </View>

                {/* Net */}
                <View style={[s.infoCard, { backgroundColor: '#F0FDF4', borderColor: '#BBF7D0', borderWidth: 1 }]}>
                    <View style={[s.infoRow, { borderBottomWidth: 0, paddingVertical: 4 }]}><Text style={[s.infoLabel, { color: '#166534', fontWeight: '800', fontSize: 16 }]}>NET ÖDENECEK:</Text><Text style={[s.infoValuePos, { fontSize: 24 }]}>{formatCurrency(payroll?.net_salary)}</Text></View>
                </View>

            </ScrollView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    hRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    backBtn: { alignItems: 'center', justifyContent: 'center' },
    hTitle: { flex: 1, textAlign: 'center', fontSize: 20, fontWeight: '900', color: '#ffffff', letterSpacing: -0.5 },
    scrollContent: { padding: 24, paddingBottom: 40 },
    profileCard: { backgroundColor: '#fff', borderRadius: 24, padding: 24, alignItems: 'center', marginBottom: 24, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    driverName: { fontSize: 20, fontWeight: '900', color: '#0F172A', textAlign: 'center' },
    periodText: { fontSize: 14, color: '#64748B', fontWeight: '600', marginTop: 4 },
    lockWarning: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FEF2F2', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, marginTop: 16, gap: 6 },
    lockWarningText: { color: '#EF4444', fontSize: 12, fontWeight: '700' },
    section: { marginBottom: 24 },
    sectionTitle: { fontSize: 13, fontWeight: '800', color: '#94A3B8', marginBottom: 12, letterSpacing: 1.5 },
    infoCard: { backgroundColor: '#ffffff', borderRadius: 20, padding: 20, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    infoLabel: { fontSize: 14, color: '#475569', fontWeight: '600' },
    infoValuePos: { fontSize: 16, color: '#10B981', fontWeight: '800' },
    infoValueNeg: { fontSize: 16, color: '#EF4444', fontWeight: '800' },
    notesText: { fontSize: 12, color: '#94A3B8', fontStyle: 'italic', marginTop: 8 },
    emptyText: { color: '#94A3B8', fontSize: 15, marginTop: 12, fontWeight: '500' },
    retryBtn: { marginTop: 24, padding: 12, paddingHorizontal: 24, backgroundColor: '#3B82F6', borderRadius: 8 }
});
