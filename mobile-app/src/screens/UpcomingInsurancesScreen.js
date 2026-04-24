import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Alert } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import * as Clipboard from 'expo-clipboard';
import api from '../api/axios';

export default function UpcomingInsurancesScreen({ navigation }) {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchVehicles = async () => {
        try {
            const r = await api.get('/vehicles?filter=upcoming_insurance');
            setVehicles(r.data.vehicles || []);
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Sigorta bilgileri alınamadı.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useFocusEffect(useCallback(() => {
        fetchVehicles();
    }, []));

    const copyToClipboard = async (text, label) => {
        if (!text || text === '-') return;
        await Clipboard.setStringAsync(text);
        Alert.alert('Kopyalandı', `${label} panoya kopyalandı.`);
    };

    const renderVehicle = ({ item }) => {
        let daysLeftText = '';
        let isOverdue = false;
        let formattedDate = '-';

        if (item.insurance_end_date) {
            const insDate = new Date(item.insurance_end_date);
            const today = new Date();
            insDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            
            const diffTime = insDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            formattedDate = insDate.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            if (diffDays < 0) {
                isOverdue = true;
                daysLeftText = `${Math.abs(diffDays)} GÜN GECİKTİ!`;
            } else {
                daysLeftText = `${diffDays} GÜN KALDI`;
            }
        }

        const iconName = item.vehicle_type === 'Otobüs' ? 'bus' : (item.vehicle_type === 'Minibüs' ? 'van-passenger' : 'car');

        return (
            <View style={s.card}>
                {/* Üst Kısım: Araç Bilgileri */}
                <View style={s.cardHeader}>
                    <View style={s.headerLeft}>
                        <View style={s.iconBox}>
                            <Icon name={iconName} size={26} color="#3B82F6" />
                        </View>
                        <View style={s.headerTextCol}>
                            <Text style={s.plateText}>{item.plate}</Text>
                            <Text style={s.brandText} numberOfLines={1}>{item.brand_model || 'Marka Belirtilmemiş'}</Text>
                        </View>
                    </View>
                    <View style={s.headerRight}>
                        <View style={s.badge}>
                            <Text style={s.badgeText}>{item.vehicle_type || 'Belirsiz Tip'}</Text>
                        </View>
                        <Text style={s.yearText}>{item.model_year ? `${item.model_year} Model` : ''}</Text>
                    </View>
                </View>

                <View style={s.driverRow}>
                    <Icon name="account-tie" size={16} color="#64748B" />
                    <Text style={s.driverText} numberOfLines={1}>{item.driver || 'Personel Atanmamış'}</Text>
                </View>

                {/* Alt Kısım: Sigorta Bilgisi ve Aksiyonlar */}
                <LinearGradient 
                    colors={isOverdue ? ['#FEF2F2', '#FEE2E2'] : ['#FDF4FF', '#FCE7F3']} 
                    style={[s.statusBox, isOverdue && { borderColor: '#FECACA' }]}
                >
                    <View style={s.statusTop}>
                        <View style={s.dateWrap}>
                            <Icon name={isOverdue ? "alert-circle" : "shield-half-full"} size={16} color={isOverdue ? "#EF4444" : "#D946EF"} />
                            <Text style={[s.dateLabel, isOverdue && { color: '#EF4444' }]}>POLİÇE BİTİŞ: {formattedDate}</Text>
                        </View>
                        <Text style={[s.daysLeft, isOverdue && { color: '#DC2626' }]}>{daysLeftText}</Text>
                    </View>

                    <TouchableOpacity style={s.actionBtn} onPress={() => navigation.navigate('VehicleDetail', { vehicle: item })} activeOpacity={0.8}>
                        <LinearGradient colors={['#D946EF', '#C026D3']} style={s.actionBtnInner} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                            <Text style={s.actionBtnText}>SİGORTA BİLGİLERİNE GİT</Text>
                            <Icon name="arrow-right" size={18} color="#fff" style={{ marginLeft: 6 }} />
                        </LinearGradient>
                    </TouchableOpacity>
                </LinearGradient>
            </View>
        );
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#040B16', '#0A1526', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }}>
                            <View style={s.backBtn}>
                                <Icon name="chevron-left" size={26} color="#fff" />
                            </View>
                        </TouchableOpacity>
                        <View style={s.headerTitleContainer}>
                            <Text style={s.headerTitle}>Yaklaşan Sigortalar</Text>
                            <Text style={s.headerSubtitle}>Canlı Veri Senkronizasyonu</Text>
                        </View>
                        <View style={s.headerRightPlaceholder} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.content}>
                {loading ? (
                    <View style={s.center}>
                        <ActivityIndicator size="large" color="#D946EF" />
                        <Text style={s.loadingText}>Sistemden canlı veriler çekiliyor...</Text>
                    </View>
                ) : (
                    <FlatList
                        data={vehicles}
                        keyExtractor={i => i.id.toString()}
                        renderItem={renderVehicle}
                        contentContainerStyle={s.listContent}
                        showsVerticalScrollIndicator={false}
                        refreshControl={
                            <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchVehicles(); }} tintColor="#D946EF" />
                        }
                        ListEmptyComponent={
                            <View style={s.empty}>
                                <View style={s.emptyIconBox}>
                                    <Icon name="shield-check" size={48} color="#10B981" />
                                </View>
                                <Text style={s.emptyTitle}>Harika Haber!</Text>
                                <Text style={s.emptyTxt}>Yakın zamanda sigorta poliçesi bitecek veya gecikmiş aracınız bulunmuyor.</Text>
                            </View>
                        }
                    />
                )}
            </View>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F1F5F9' },
    header: { paddingBottom: 24, paddingHorizontal: 16, paddingTop: 14, borderBottomLeftRadius: 36, borderBottomRightRadius: 36, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.15, shadowRadius: 20, elevation: 15 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 8 },
    backBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: 'rgba(255,255,255,0.1)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    headerTitleContainer: { alignItems: 'center' },
    headerTitle: { fontSize: 18, fontWeight: '900', color: '#fff', letterSpacing: 0.5 },
    headerSubtitle: { fontSize: 10, color: '#F9A8D4', fontWeight: '800', marginTop: 4, textTransform: 'uppercase', letterSpacing: 1.5 },
    headerRightPlaceholder: { width: 44 },
    content: { flex: 1, marginTop: -10 },
    listContent: { paddingHorizontal: 16, paddingTop: 24, paddingBottom: 120 },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    loadingText: { marginTop: 12, fontSize: 13, color: '#64748B', fontWeight: '700' },
    
    empty: { alignItems: 'center', paddingVertical: 60, paddingHorizontal: 30 },
    emptyIconBox: { width: 100, height: 100, borderRadius: 50, backgroundColor: '#ECFDF5', alignItems: 'center', justifyContent: 'center', marginBottom: 20, borderWidth: 4, borderColor: '#D1FAE5' },
    emptyTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A', marginBottom: 8 },
    emptyTxt: { color: '#64748B', fontSize: 14, fontWeight: '600', textAlign: 'center', lineHeight: 22 },
    
    /* Yeni Dikey Kart Tasarımı */
    card: { backgroundColor: '#fff', borderRadius: 28, marginBottom: 20, padding: 18, shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 12 }, shadowOpacity: 0.15, shadowRadius: 24, elevation: 10, borderWidth: 1, borderColor: '#E2E8F0' },
    
    // Header
    cardHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16 },
    headerLeft: { flexDirection: 'row', alignItems: 'center', flex: 1, paddingRight: 12 },
    iconBox: { width: 52, height: 52, borderRadius: 18, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center', marginRight: 14, borderWidth: 1, borderColor: '#DBEAFE' },
    headerTextCol: { flex: 1 },
    plateText: { fontSize: 18, fontWeight: '900', color: '#0F172A', letterSpacing: 0.5 },
    brandText: { fontSize: 12, color: '#64748B', fontWeight: '600', marginTop: 4 },
    
    headerRight: { alignItems: 'flex-end' },
    badge: { backgroundColor: '#F1F5F9', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 10, marginBottom: 6 },
    badgeText: { fontSize: 10, fontWeight: '900', color: '#475569', textTransform: 'uppercase', letterSpacing: 0.5 },
    yearText: { fontSize: 12, color: '#94A3B8', fontWeight: '700' },

    // Driver
    driverRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 20, backgroundColor: '#F8FAFC', paddingHorizontal: 14, paddingVertical: 12, borderRadius: 14, borderWidth: 1, borderColor: '#F1F5F9' },
    driverText: { fontSize: 13, color: '#475569', fontWeight: '700', marginLeft: 8, flex: 1 },

    // Status Box (Alt Kısım)
    statusBox: { borderRadius: 20, padding: 16, borderWidth: 1, borderColor: '#F9A8D4' },
    statusTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 16 },
    dateWrap: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.7)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 10 },
    dateLabel: { fontSize: 11, fontWeight: '900', color: '#A21CAF', marginLeft: 6, letterSpacing: 0.5 },
    daysLeft: { fontSize: 18, fontWeight: '900', color: '#C026D3', marginTop: 2 },
    
    // Actions
    actionBtn: { overflow: 'hidden', borderRadius: 16, shadowColor: '#C026D3', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.3, shadowRadius: 12, elevation: 8, marginTop: 12 },
    actionBtnInner: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16 },
    actionBtnText: { color: '#fff', fontSize: 13, fontWeight: '900', letterSpacing: 1 },
});
