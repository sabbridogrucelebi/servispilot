import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Animated, Alert, Image, Dimensions, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import api from '../api/axios';

const { width: W } = Dimensions.get('window');

const getVehicleImage = (type) => {
    const t = (type || '').toLowerCase();
    if (t.includes('minib') || t.includes('minibüs')) return require('../../assets/arac_profilleri/servis_pilot_minibus_profil.png');
    if (t.includes('midib') || t.includes('midibüs')) return require('../../assets/arac_profilleri/servis_pilot_midibus_profil.png');
    if (t.includes('otob') || t.includes('otobüs')) return require('../../assets/arac_profilleri/servis_pilot_otobus_profil.png');
    if (t.includes('panelvan')) return require('../../assets/arac_profilleri/servis_pilot_panelvan_profil.png');
    if (t.includes('kamyonet')) return require('../../assets/arac_profilleri/servis_pilot_kamyonet_profil.png');
    if (t.includes('binek') || t.includes('sedan') || t.includes('taksi')) return require('../../assets/arac_profilleri/servis_pilot_taksi_profil.png');
    return require('../../assets/arac_profilleri/servis_pilot_panelvan_profil.png');
};

const fmtKm = (v) => new Intl.NumberFormat('tr-TR').format(v || 0);
const fmtMoney = (v) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 }).format(v || 0);
import { AuthContext } from '../context/AuthContext';
export default function VehicleDetailScreen({ route, navigation }) {
    const { hasPermission } = React.useContext(AuthContext);
    const { vehicle: init } = route.params || {};
    const [v, setV] = useState(init);
    const [stats, setStats] = useState({ revenue: 0, fuel: 0, salary: 0, net: 0 });
    const [mh, setMh] = useState({});
    const [loading, setLoading] = useState(true);
    const fadeAnim = useRef(new Animated.Value(0)).current;
    const slideAnim = useRef(new Animated.Value(30)).current;
    const blinkAnim = useRef(new Animated.Value(1)).current;

    useEffect(() => {
        Animated.loop(
            Animated.sequence([
                Animated.timing(blinkAnim, { toValue: 0.3, duration: 600, useNativeDriver: true }),
                Animated.timing(blinkAnim, { toValue: 1, duration: 600, useNativeDriver: true }),
            ])
        ).start();

        (async () => {
            if (!init?.id) { setLoading(false); return; }
            try {
                const r = await api.get(`/v1/vehicles/${init.id}`);
                setV(r.data.data.vehicle);
                setStats(r.data.data.stats || {});
                setMh(r.data.data.maintenance_health || {});
            } catch (e) {
                if (e.response?.status === 403) Alert.alert('Erişim Engellendi', 'Bu alanı görüntüleme yetkiniz yok.');
                else if (e.response?.status === 404) Alert.alert('Bulunamadı', 'Araç kaydı bulunamadı.');
                else if (!e.response) Alert.alert('Bağlantı Hatası', 'Sunucuya ulaşılamıyor.');
            } finally {
                setLoading(false);
                Animated.parallel([
                    Animated.timing(fadeAnim, { toValue: 1, duration: 500, useNativeDriver: true }),
                    Animated.spring(slideAnim, { toValue: 0, friction: 8, tension: 40, useNativeDriver: true }),
                ]).start();
            }
        })();
    }, []);

    const daysUntil = (d) => {
        if (!d) return { text: 'Tanımsız', days: null, color: '#94A3B8' };
        const diff = Math.ceil((new Date(d) - new Date()) / 86400000);
        if (diff < 0) return { text: `${Math.abs(diff)} gün geçti`, days: diff, color: '#EF4444' };
        if (diff <= 30) return { text: `${diff} gün kaldı`, days: diff, color: '#F59E0B' };
        return { text: `${diff} gün kaldı`, days: diff, color: '#10B981' };
    };

    const formatDate = (d) => d ? new Date(d).toLocaleDateString('tr-TR') : '-';

    if (loading) return <View style={st.loaderWrap}><ActivityIndicator size="large" color="#3B82F6" /></View>;
    if (!v) return <View style={st.loaderWrap}><Text style={{ color: '#64748B', fontSize: 16 }}>Araç bulunamadı</Text></View>;

    const inspection = daysUntil(v.inspection_date);
    const insurance = daysUntil(v.insurance_end_date);
    const imm = daysUntil(v.imm_end_date);
    const kasko = daysUntil(v.kasko_end_date);
    const exhaust = daysUntil(v.exhaust_date);
    const profit = (stats.revenue || 0) - (stats.fuel || 0) - (stats.salary || 0);

    const quickStats = [
        { icon: 'speedometer', label: 'Kilometre', value: `${fmtKm(v.current_km)} km`, color: '#3B82F6' },
        { icon: 'gas-station', label: 'Yakıt', value: v.fuel_type || '-', color: '#F59E0B' },
        { icon: 'calendar', label: 'Model Yılı', value: v.model_year || '-', color: '#8B5CF6' },
        { icon: 'palette', label: 'Renk', value: v.color || '-', color: '#EC4899' },
        { icon: 'seat', label: 'Koltuk', value: v.seat_count || '-', color: '#10B981' },
    ];

    const menuItems = [
        { icon: 'file-document-outline', label: 'Belgeler', color: '#3B82F6', gradient: ['#3B82F6', '#2563EB'], screen: 'VehicleDocuments', perm: 'documents.view' },
        { icon: 'gas-station-outline', label: 'Yakıt', color: '#F59E0B', gradient: ['#F59E0B', '#D97706'], screen: 'VehicleFuels', perm: 'fuels.view' },
        { icon: 'wrench-outline', label: 'Bakım', color: '#10B981', gradient: ['#10B981', '#059669'], screen: 'VehicleMaintenances', perm: 'maintenances.view' },
        { icon: 'alert-octagon-outline', label: 'Cezalar', color: '#EF4444', gradient: ['#EF4444', '#DC2626'], screen: 'VehiclePenalties', perm: 'penalties.view' },
        { icon: 'image-multiple-outline', label: 'Galeri', color: '#8B5CF6', gradient: ['#8B5CF6', '#7C3AED'], screen: 'VehicleGallery', perm: 'vehicles.view' },
        { icon: 'chart-bar', label: 'Raporlar', color: '#06B6D4', gradient: ['#06B6D4', '#0891B2'], screen: 'VehicleReports', perm: 'reports.view' },
    ].filter(m => hasPermission(m.perm));

    return (
        <View style={st.container}>
            <View style={StyleSheet.absoluteFillObject}>
                <SpaceWaves />
            </View>
            <ScrollView bounces={false} showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 80 }}>
                {/* ── HERO HEADER ── */}
                <View style={st.hero}>
                    <SafeAreaView edges={['top']} style={st.heroContent}>
                        <View style={st.heroTop}>
                            <TouchableOpacity onPress={() => navigation.goBack()} style={st.backBtn}>
                                <Icon name="chevron-left" size={26} color="#fff" />
                            </TouchableOpacity>
                            <View style={st.statusBadge}>
                                <View style={[st.statusDot, { backgroundColor: v.is_active ? '#10B981' : '#EF4444' }]} />
                                <Text style={st.statusText}>{v.is_active ? 'Aktif' : 'Pasif'}</Text>
                            </View>
                        </View>
                        <View style={st.heroCenter}>
                            <View style={st.imageContainer}>
                                <Image 
                                    source={getVehicleImage(v.vehicle_type)} 
                                    style={st.heroImage} 
                                    resizeMode="contain" 
                                />
                            </View>
                        </View>
                        <View style={st.heroBottom}>
                            <Text style={st.plateText}>{v.plate}</Text>
                            <Text style={st.brandText}>{[v.brand, v.model].filter(Boolean).join(' ') || v.vehicle_type || 'Araç'}</Text>
                            {v.vehicle_type && <View style={st.typeBadge}><Text style={st.typeText}>{v.vehicle_type}</Text></View>}
                        </View>
                    </SafeAreaView>
                </View>

                <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>
                    {/* ── QUICK STATS ── */}
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={st.quickRow} contentContainerStyle={{ paddingHorizontal: 16, gap: 10 }}>
                        {quickStats.map((s, i) => (
                            <View key={i} style={st.quickCard}>
                                <View style={[st.quickIcon, { backgroundColor: s.color + '15' }]}>
                                    <Icon name={s.icon} size={18} color={s.color} />
                                </View>
                                <Text style={st.quickLabel}>{s.label}</Text>
                                <Text style={st.quickValue}>{s.value}</Text>
                            </View>
                        ))}
                    </ScrollView>

                    {/* ── DATE STATUS CARDS ── */}
                    <View style={st.section}>
                        <Text style={st.sectionTitle}>Tarih Durumları</Text>
                        <View style={st.grid3Row}>
                            {[
                                { label: 'Egzoz Emisyon', date: v.exhaust_date, info: exhaust, icon: 'weather-windy' },
                                { label: 'İMM Poliçesi', date: v.imm_end_date, info: imm, icon: 'file-document-outline' },
                                { label: 'Kasko Poliçesi', date: v.kasko_end_date, info: kasko, icon: 'car-wrench' },
                            ].map((item, i) => (
                                <View key={i} style={st.dateCard3}>
                                    <View style={st.dateCardTop3}>
                                        <Icon name={item.icon} size={16} color="#64748B" />
                                        <Text style={st.dateCardLabel3} numberOfLines={1} adjustsFontSizeToFit>{item.label}</Text>
                                    </View>
                                    <Text style={st.dateCardDate3}>{formatDate(item.date)}</Text>
                                    <View style={[st.dateBadge, { backgroundColor: item.info.color + '18' }]}>
                                        <View style={[st.dateBadgeDot, { backgroundColor: item.info.color }]} />
                                        <Text style={[st.dateBadgeText, { color: item.info.color }]} numberOfLines={1} adjustsFontSizeToFit>{item.info.text}</Text>
                                    </View>
                                </View>
                            ))}
                        </View>
                        <View style={st.grid2RowCentered}>
                            {[
                                { label: 'TÜVTÜRK Muayene', date: v.inspection_date, info: inspection, icon: 'clipboard-check-outline' },
                                { label: 'Trafik Sigortası', date: v.insurance_end_date, info: insurance, icon: 'shield-check-outline' },
                            ].map((item, i) => (
                                <View key={i} style={st.dateCard2}>
                                    <View style={st.dateCardTop2}>
                                        <Icon name={item.icon} size={20} color="#64748B" />
                                        <Text style={st.dateCardLabel2} numberOfLines={1} adjustsFontSizeToFit>{item.label}</Text>
                                    </View>
                                    <Text style={st.dateCardDate2}>{formatDate(item.date)}</Text>
                                    <View style={[st.dateBadge, { backgroundColor: item.info.color + '18' }]}>
                                        <View style={[st.dateBadgeDot, { backgroundColor: item.info.color }]} />
                                        <Text style={[st.dateBadgeText, { color: item.info.color }]} numberOfLines={1} adjustsFontSizeToFit>{item.info.text}</Text>
                                    </View>
                                </View>
                            ))}
                        </View>
                    </View>

                    {/* ── FINANCIAL SUMMARY ── */}
                    {hasPermission('financials.view') && (
                        <View style={st.section}>
                            <Text style={st.sectionTitle}>Finansal Özet</Text>
                        <View style={st.finRow}>
                            {[
                                { label: 'Gelir', value: stats.revenue, icon: 'trending-up', color: '#10B981', bg: ['#ECFDF5', '#D1FAE5'] },
                                { label: 'Yakıt', value: stats.fuel, icon: 'gas-station', color: '#F59E0B', bg: ['#FFFBEB', '#FEF3C7'] },
                                { label: 'Maaş', value: stats.salary, icon: 'account-cash', color: '#3B82F6', bg: ['#EFF6FF', '#DBEAFE'] },
                            ].map((f, i) => (
                                <LinearGradient key={i} colors={f.bg} style={st.finCard}>
                                    <Icon name={f.icon} size={20} color={f.color} />
                                    <Text style={[st.finValue, { color: f.color }]}>{fmtMoney(f.value)}</Text>
                                    <Text style={st.finLabel}>{f.label}</Text>
                                </LinearGradient>
                            ))}
                        </View>
                        <LinearGradient colors={profit >= 0 ? ['#10B981', '#059669'] : ['#EF4444', '#DC2626']} style={st.profitCard}>
                            <View style={st.profitLeft}>
                                <Text style={st.profitLabel}>Net Kâr / Zarar</Text>
                                <Text style={st.profitValue}>{fmtMoney(profit)}</Text>
                            </View>
                            <Icon name={profit >= 0 ? 'trending-up' : 'trending-down'} size={32} color="rgba(255,255,255,0.4)" />
                        </LinearGradient>
                    </View>
                    )}

                    {/* ── MAINTENANCE HEALTH ── */}
                    {mh.has_setting && (
                        <View style={st.section}>
                            <Text style={st.sectionTitle}>Bakım Sağlığı</Text>
                            {[
                                { label: 'Yağ Değişimi', remaining: mh.oil_change_remaining_km, percent: mh.oil_change_percent },
                                { label: 'Alt Yağlama', remaining: mh.bottom_lube_remaining_km, percent: mh.bottom_lube_percent },
                            ].map((b, i) => {
                                if (b.remaining === undefined) return null;
                                
                                const hasRecord = b.remaining !== null;
                                const pct = Math.min(100, Math.max(0, b.percent || 0));
                                const barColor = !hasRecord ? '#64748B' : (pct > 60 ? '#10B981' : pct > 30 ? '#F59E0B' : '#EF4444');
                                
                                return (
                                    <View key={i} style={st.healthRow}>
                                        <View style={st.healthTop}>
                                            <Text style={st.healthLabel}>{b.label}</Text>
                                            <Animated.Text style={[st.healthKm, { color: barColor, opacity: b.remaining < 0 ? blinkAnim : 1 }]}>
                                                {hasRecord ? (b.remaining < 0 ? `${fmtKm(Math.abs(b.remaining))} km geçti` : `${fmtKm(b.remaining)} km kaldı`) : 'KAYIT BEKLENİYOR'}
                                            </Animated.Text>
                                        </View>
                                        <View style={st.healthBarBg}>
                                            <Animated.View style={[st.healthBarFill, { width: `${hasRecord ? pct : 0}%`, backgroundColor: barColor }]} />
                                        </View>
                                    </View>
                                );
                            })}
                        </View>
                    )}

                    {/* ── ACTION MENU ── */}
                    <View style={st.section}>
                        <Text style={st.sectionTitle}>İşlemler</Text>
                        <View style={st.menuGrid}>
                            {menuItems.map((m, i) => (
                                <TouchableOpacity key={i} style={st.menuCard} activeOpacity={0.8}
                                    onPress={() => navigation.navigate(m.screen, { vehicleId: v.id, vehicle: v })}>
                                    <LinearGradient colors={m.gradient} style={st.menuIcon}>
                                        <Icon name={m.icon} size={22} color="#fff" />
                                    </LinearGradient>
                                    <Text style={st.menuLabel}>{m.label}</Text>
                                    <Icon name="chevron-right" size={16} color="#CBD5E1" />
                                </TouchableOpacity>
                            ))}
                        </View>
                    </View>

                    <View style={{ height: 40 }} />
                </Animated.View>
            </ScrollView>
        </View>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#0F172A' },
    loaderWrap: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#0F172A' },

    // Hero
    hero: { paddingBottom: 30, backgroundColor: 'transparent', overflow: 'hidden' },
    heroContent: { zIndex: 1 },
    heroTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 16, paddingTop: Platform.OS === 'android' ? 40 : 8 },
    backBtn: { width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.12)', alignItems: 'center', justifyContent: 'center' },
    statusBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.1)', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 20, gap: 6 },
    statusDot: { width: 8, height: 8, borderRadius: 4 },
    statusText: { color: '#fff', fontSize: 13, fontWeight: '600' },
    heroCenter: { alignItems: 'center', marginVertical: 12 },
    imageContainer: { width: W * 0.9, height: 220, alignItems: 'center', justifyContent: 'center' },
    heroImage: { width: '100%', height: '100%' },
    heroBottom: { alignItems: 'center', paddingHorizontal: 20, marginTop: 4 },
    plateText: { fontSize: 28, fontWeight: '900', color: '#fff', letterSpacing: 2, textTransform: 'uppercase' },
    brandText: { fontSize: 16, color: 'rgba(255,255,255,0.7)', fontWeight: '600', marginTop: 4 },
    typeBadge: { marginTop: 8, backgroundColor: 'rgba(255,255,255,0.1)', paddingHorizontal: 14, paddingVertical: 4, borderRadius: 12 },
    typeText: { color: 'rgba(255,255,255,0.8)', fontSize: 12, fontWeight: '700' },

    // Quick stats
    quickRow: { marginTop: -16 },
    quickCard: { backgroundColor: '#fff', borderRadius: 16, padding: 14, width: 110, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.06, shadowRadius: 8, elevation: 3, alignItems: 'center' },
    quickIcon: { width: 36, height: 36, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginBottom: 8 },
    quickLabel: { fontSize: 11, color: '#94A3B8', fontWeight: '600', marginBottom: 2 },
    quickValue: { fontSize: 13, color: '#1E293B', fontWeight: '800' },

    // Sections
    section: { paddingHorizontal: 16, marginTop: 24 },
    sectionTitle: { fontSize: 18, fontWeight: '900', color: '#FFFFFF', marginBottom: 14, letterSpacing: -0.3 },

    // Date cards
    grid3Row: { flexDirection: 'row', gap: 8, marginBottom: 10 },
    grid2RowCentered: { flexDirection: 'row', gap: 10, justifyContent: 'center' },
    dateCard3: { flex: 1, backgroundColor: '#fff', borderRadius: 14, padding: 8, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2, alignItems: 'center' },
    dateCardTop3: { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: 4 },
    dateCardLabel3: { fontSize: 10, fontWeight: '800', color: '#334155' },
    dateCardDate3: { fontSize: 11, color: '#64748B', fontWeight: '700', marginBottom: 6 },
    
    dateCard2: { flex: 1, maxWidth: '48%', backgroundColor: '#fff', borderRadius: 16, padding: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2, alignItems: 'center' },
    dateCardTop2: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 6 },
    dateCardLabel2: { fontSize: 13, fontWeight: '800', color: '#334155' },
    dateCardDate2: { fontSize: 12, color: '#64748B', fontWeight: '700', marginBottom: 8 },
    
    dateBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 6, paddingVertical: 5, borderRadius: 8, gap: 4, alignSelf: 'center', width: '100%', justifyContent: 'center' },
    dateBadgeDot: { width: 6, height: 6, borderRadius: 3 },
    dateBadgeText: { fontSize: 9, fontWeight: '800' },

    // Financial
    finRow: { flexDirection: 'row', gap: 8, marginBottom: 10 },
    finCard: { flex: 1, borderRadius: 16, padding: 14, alignItems: 'center' },
    finValue: { fontSize: 14, fontWeight: '900', marginTop: 6 },
    finLabel: { fontSize: 11, color: '#64748B', fontWeight: '600', marginTop: 2 },
    profitCard: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', borderRadius: 18, padding: 20 },
    profitLeft: {},
    profitLabel: { color: 'rgba(255,255,255,0.75)', fontSize: 13, fontWeight: '600' },
    profitValue: { color: '#fff', fontSize: 24, fontWeight: '900', marginTop: 2 },

    // Health bars
    healthRow: { marginBottom: 16 },
    healthTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    healthLabel: { fontSize: 14, fontWeight: '700', color: '#FFFFFF' },
    healthKm: { fontSize: 13, fontWeight: '700' },
    healthBarBg: { height: 8, backgroundColor: 'rgba(255,255,255,0.15)', borderRadius: 4, overflow: 'hidden' },
    healthBarFill: { height: 8, borderRadius: 4 },

    // Menu
    menuGrid: { gap: 8 },
    menuCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 16, padding: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 1 }, shadowOpacity: 0.04, shadowRadius: 6, elevation: 2, gap: 14 },
    menuIcon: { width: 44, height: 44, borderRadius: 14, alignItems: 'center', justifyContent: 'center' },
    menuLabel: { flex: 1, fontSize: 15, fontWeight: '700', color: '#1E293B' },
});
