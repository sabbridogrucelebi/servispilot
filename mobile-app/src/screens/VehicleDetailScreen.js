import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Image, Animated } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function VehicleDetailScreen({ route, navigation }) {
    const { vehicle: initialVehicle } = route.params || {};
    const [vehicle, setVehicle] = useState(initialVehicle);
    const [stats, setStats] = useState({ revenue: 0, fuel: 0, salary: 0, net: 0 });
    const [loading, setLoading] = useState(true);

    const fadeAnim = useRef(new Animated.Value(0)).current;
    const translateY = useRef(new Animated.Value(20)).current;

    useEffect(() => {
        const loadDetail = async () => {
            if (!initialVehicle?.id) {
                setLoading(false);
                return;
            }
            try {
                const r = await api.get(`/vehicles/${initialVehicle.id}`);
                setVehicle(r.data.vehicle);
                setStats(r.data.stats);
            } catch(e) { console.error(e); }
            finally { 
                setLoading(false);
                Animated.parallel([
                    Animated.timing(fadeAnim, { toValue: 1, duration: 600, useNativeDriver: true }),
                    Animated.spring(translateY, { toValue: 0, friction: 6, tension: 40, useNativeDriver: true })
                ]).start();
            }
        };
        loadDetail();
    }, []);

    const fmtMoney = (val) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 }).format(val);
    const fmtKm = (val) => (!val || val === 0) ? '0 km' : Number(val).toLocaleString('tr-TR') + ' km';

    const getDaysRemaining = (date) => {
        if (!date) return null;
        const diff = new Date(date) - new Date();
        const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
        return days;
    };

    const renderRemainingDays = (date) => {
        const days = getDaysRemaining(date);
        if (days === null) return '';
        if (days < 0) return 'SÜRESİ DOLDU';
        return `${days} GÜN KALDI`;
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#040B16', '#0A1526', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']} style={{ paddingBottom: 16 }}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }}>
                            <Icon name="chevron-left" size={28} color="#fff" />
                        </TouchableOpacity>
                        <View style={{flex: 1, alignItems:'center'}}><Text style={s.headerTitle} numberOfLines={1}>Araç Detayı</Text></View>
                        <TouchableOpacity onPress={() => {}} hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }}>
                            <Icon name="dots-horizontal" size={24} color="#fff" />
                        </TouchableOpacity>
                    </View>

                    <View style={s.heroCard}>
                        {vehicle.image_url ? (
                            <Image source={{ uri: vehicle.image_url }} style={s.heroImage} resizeMode="cover" />
                        ) : (
                            <View style={s.heroImagePlaceholder}>
                                <Icon name="car-side" size={48} color="rgba(255,255,255,0.1)" />
                                <Text style={{color:'rgba(255,255,255,0.2)', fontSize: 10, fontWeight:'700', marginTop: 8}}>GÖRSEL YOK</Text>
                            </View>
                        )}
                        <View style={s.heroContent}>
                            <View style={s.heroTop}>
                                <View style={[s.badge, vehicle.is_active ? s.badgeActive : s.badgePassive]}>
                                    <View style={[s.badgeDot, { backgroundColor: vehicle.is_active ? '#10B981' : '#EF4444' }]} />
                                    <Text style={[s.badgeTxt, { color: vehicle.is_active ? '#10B981' : '#EF4444' }]}>
                                        {vehicle.is_active ? 'AKTİF OPERASYON' : 'PASİF'}
                                    </Text>
                                </View>
                                <View style={s.badgeDark}>
                                    <Text style={s.badgeDarkTxt}>{vehicle.model_year || '-'} MODEL</Text>
                                </View>
                            </View>

                            <View style={s.heroMain}>
                                <View style={s.plateWrap}>
                                    <Text style={s.plateTxt}>{vehicle.plate}</Text>
                                </View>
                                <TouchableOpacity style={s.aiBtn}>
                                    <Icon name="robot-outline" size={16} color="#fff" />
                                    <Text style={s.aiBtnTxt}>Aİ ANALİZ</Text>
                                </TouchableOpacity>
                            </View>

                            <View style={s.heroSub}>
                                <Text style={s.brandTxt} numberOfLines={1}>{vehicle.brand || 'Bilinmiyor'} • {vehicle.model || 'Bilinmiyor'} • {vehicle.vehicle_type || 'Belirtilmemiş'}</Text>
                            </View>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={s.scrollContent}>
                {loading ? (
                    <ActivityIndicator size="large" color="#3B82F6" style={{ marginTop: 40 }} />
                ) : (
                    <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY }] }}>
                        {/* 2x2 3D Animated KPI Cards */}
                        <View style={s.statsGrid}>
                            <View style={s.statCol}>
                                <LinearGradient colors={['#10B981', '#059669']} style={[s.statCard3D, { borderBottomColor: '#047857' }]}>
                                    <Text style={s.statLbl}>TOPLAM HASILAT</Text>
                                    <Text style={s.statVal}>{fmtMoney(stats.revenue)}</Text>
                                    <View style={s.statPill}><Text style={s.statPillTxt}>Gelir Akışı</Text></View>
                                </LinearGradient>
                                <LinearGradient colors={['#8B5CF6', '#7C3AED']} style={[s.statCard3D, { borderBottomColor: '#6D28D9' }]}>
                                    <Text style={s.statLbl}>PERSONEL & MAAŞ</Text>
                                    <Text style={s.statVal}>{fmtMoney(stats.salary)}</Text>
                                    <View style={s.statPill}><Text style={s.statPillTxt}>Net Giderler</Text></View>
                                </LinearGradient>
                            </View>
                            <View style={s.statCol}>
                                <LinearGradient colors={['#F97316', '#EA580C']} style={[s.statCard3D, { borderBottomColor: '#C2410C' }]}>
                                    <Text style={s.statLbl}>YAKIT GİDERİ</Text>
                                    <Text style={s.statVal}>{fmtMoney(stats.fuel)}</Text>
                                    <View style={s.statPill}><Text style={s.statPillTxt}>Tüketim</Text></View>
                                </LinearGradient>
                                <LinearGradient colors={['#EF4444', '#DC2626']} style={[s.statCard3D, { borderBottomColor: '#B91C1C' }]}>
                                    <Text style={s.statLbl}>NET KARLILIK</Text>
                                    <Text style={s.statVal}>{fmtMoney(stats.net)}</Text>
                                    <View style={s.statPill}><Text style={s.statPillTxt}>Net Durum</Text></View>
                                </LinearGradient>
                            </View>
                        </View>

                        {/* Info Grid */}
                        <View style={s.grid}>
                            <InfoBox icon="account-tie" color="#3B82F6" label="ŞOFÖR" val={vehicle.driver || 'Atanmamış'} sub="" />
                            <InfoBox icon="speedometer" color="#10B981" label="KİLOMETRE" val={fmtKm(vehicle.current_km)} sub="" />
                            <InfoBox icon="seat-passenger" color="#8B5CF6" label="KOLTUK" val={vehicle.seat_count || '-'} sub="Yolcu Kapasitesi" />
                            <InfoBox icon="car-info" color="#F59E0B" label="ŞASİ NO" val={vehicle.chassis_no || '-'} sub="Kimlik Numarası" />
                            <InfoBox icon="calendar-check" color="#F97316" label="MUAYENE" val={vehicle.inspection_date ? new Date(vehicle.inspection_date).toLocaleDateString('tr-TR') : 'TANIMSIZ'} sub={renderRemainingDays(vehicle.inspection_date)} />
                            <InfoBox icon="shield-car" color="#EF4444" label="KASKO" val={vehicle.kasko_end_date ? new Date(vehicle.kasko_end_date).toLocaleDateString('tr-TR') : 'TANIMSIZ'} sub={renderRemainingDays(vehicle.kasko_end_date)} />
                        </View>

                        {/* Ruhsat & Sahiplik */}
                        <View style={s.licenseCard}>
                            <View style={s.licenseHeader}>
                                <View>
                                    <Text style={s.licenseTitle}>Ruhsat & Sahiplik</Text>
                                    <Text style={s.licenseSub}>YASAL KAYIT DETAYLARI</Text>
                                </View>
                                <View style={s.licenseIconWrap}>
                                    <Icon name="file-document" size={24} color="#fff" />
                                </View>
                            </View>
                            <View style={s.licenseBody}>
                                <View style={s.licenseRowLight}>
                                    <Text style={s.licenseLabel}>SERİ NUMARASI</Text>
                                    <Text style={s.licenseValLight}>{vehicle.license_serial_no || '-'}</Text>
                                </View>
                                <View style={s.licenseRow}>
                                    <Text style={s.licenseLabelGray}>RUHSAT SAHİBİ</Text>
                                    <Text style={s.licenseVal}>{vehicle.license_owner || '-'}</Text>
                                </View>
                                <View style={s.licenseRow}>
                                    <Text style={s.licenseLabelGray}>VERGİ / T.C. NO</Text>
                                    <Text style={s.licenseVal}>{vehicle.owner_tax_or_tc_no || '-'}</Text>
                                </View>
                            </View>
                        </View>

                        {/* Action Buttons Row */}
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.actionRow}>
                            <ActionBtn icon="chart-bar" color="#3B82F6" label="ANALİZ" active onPress={() => navigation.navigate('Trips')} />
                            <ActionBtn icon="folder" color="#F59E0B" label="BELGELER" onPress={() => navigation.navigate('VehicleDocuments', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ActionBtn icon="gas-station" color="#10B981" label="YAKIT" onPress={() => navigation.navigate('VehicleFuels', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ActionBtn icon="wrench" color="#8B5CF6" label="BAKIM" onPress={() => navigation.navigate('VehicleMaintenances', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ActionBtn icon="alert-octagon" color="#EF4444" label="CEZALAR" onPress={() => navigation.navigate('VehiclePenalties', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ActionBtn icon="file-chart" color="#64748B" label="RAPORLAR" onPress={() => navigation.navigate('VehicleReports', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ActionBtn icon="image-multiple" color="#EC4899" label="GALERİ" onPress={() => navigation.navigate('VehicleGallery', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                        </ScrollView>
                    </Animated.View>
                )}
            </ScrollView>
        </View>
    );
}

const InfoBox = ({ icon, color, label, val, sub }) => (
    <View style={s.infoBox}>
        <View style={[s.infoIconWrap, { backgroundColor: color + '15' }]}>
            <Icon name={icon} size={20} color={color} />
        </View>
        <Text style={s.infoLabel}>{label}</Text>
        <Text style={s.infoVal} numberOfLines={3} adjustsFontSizeToFit minimumFontScale={0.5}>{val}</Text>
        {sub ? <Text style={s.infoSub}>{sub}</Text> : null}
    </View>
);

const ActionBtn = ({ icon, color, label, active, onPress }) => (
    <TouchableOpacity style={[s.actionBtnWrap, active && { backgroundColor: color }]} onPress={onPress}>
        <Icon name={icon} size={24} color={active ? '#fff' : color} style={{ marginBottom: 8 }} />
        <Text style={[s.actionBtnLbl, active && { color: '#fff' }]}>{label}</Text>
    </TouchableOpacity>
);

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingHorizontal: 16, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 14, marginBottom: 20, marginTop: 10 },
    headerTitle: { fontSize: 18, fontWeight: '900', color: '#fff', letterSpacing: 0.5 },
    heroCard: { backgroundColor: '#1E293B', borderRadius: 24, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.3, shadowRadius: 20, elevation: 15, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)', overflow: 'hidden' },
    heroImage: { width: '100%', height: 140, backgroundColor: '#0F172A' },
    heroImagePlaceholder: { width: '100%', height: 100, backgroundColor: '#0F172A', alignItems: 'center', justifyContent: 'center' },
    heroContent: { padding: 20 },
    heroTop: { flexDirection: 'row', gap: 8, marginBottom: 16 },
    badge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 20, gap: 6, borderWidth: 1 },
    badgeActive: { backgroundColor: 'rgba(16, 185, 129, 0.1)', borderColor: 'rgba(16, 185, 129, 0.2)' },
    badgePassive: { backgroundColor: 'rgba(239, 68, 68, 0.1)', borderColor: 'rgba(239, 68, 68, 0.2)' },
    badgeDot: { width: 6, height: 6, borderRadius: 3 },
    badgeTxt: { fontSize: 10, fontWeight: '800', letterSpacing: 0.5 },
    badgeDark: { backgroundColor: 'rgba(255,255,255,0.05)', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 20, borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
    badgeDarkTxt: { fontSize: 10, fontWeight: '800', color: '#CBD5E1', letterSpacing: 0.5 },
    heroMain: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 6 },
    plateTxt: { fontSize: 28, fontWeight: '900', color: '#fff', letterSpacing: 1 },
    aiBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#8B5CF6', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, gap: 6 },
    aiBtnTxt: { color: '#fff', fontSize: 11, fontWeight: '800', letterSpacing: 0.5 },
    heroSub: { flexDirection: 'row', alignItems: 'center' },
    brandTxt: { fontSize: 12, color: '#94A3B8', fontWeight: '600', letterSpacing: 0.5, textTransform: 'uppercase' },
    
    scrollContent: { paddingVertical: 20 },
    statsGrid: { flexDirection: 'row', paddingHorizontal: 16, gap: 12, paddingBottom: 24 },
    statCol: { flex: 1, gap: 12 },
    statCard3D: { borderRadius: 20, padding: 14, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.2, shadowRadius: 15, elevation: 12, borderBottomWidth: 4, minHeight: 110, justifyContent: 'center' },
    statLbl: { fontSize: 9, fontWeight: '900', color: 'rgba(255,255,255,0.8)', letterSpacing: 0.5, marginBottom: 6 },
    statVal: { fontSize: 20, fontWeight: '900', color: '#fff', marginBottom: 8, textShadowColor: 'rgba(0,0,0,0.2)', textShadowOffset: { width: 0, height: 2 }, textShadowRadius: 4 },
    statPill: { backgroundColor: 'rgba(255,255,255,0.2)', alignSelf: 'flex-start', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
    statPillTxt: { fontSize: 9, fontWeight: '800', color: '#fff' },

    grid: { flexDirection: 'row', flexWrap: 'wrap', paddingHorizontal: 12, gap: 8, marginBottom: 24 },
    infoBox: { width: '31%', backgroundColor: '#fff', borderRadius: 16, padding: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9', minHeight: 110 },
    infoIconWrap: { width: 32, height: 32, borderRadius: 10, alignItems: 'center', justifyContent: 'center', marginBottom: 10 },
    infoLabel: { fontSize: 8, fontWeight: '800', color: '#94A3B8', letterSpacing: 0.5, marginBottom: 4 },
    infoVal: { fontSize: 11, fontWeight: '800', color: '#0F172A', marginBottom: 2, lineHeight: 14 },
    infoSub: { fontSize: 8, fontWeight: '600', color: '#EF4444' },

    actionRow: { paddingHorizontal: 16, gap: 12, paddingBottom: 40 },
    actionBtnWrap: { backgroundColor: '#fff', width: 80, height: 80, borderRadius: 20, alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 4, borderWidth: 1, borderColor: '#E2E8F0' },
    actionBtnLbl: { fontSize: 9, fontWeight: '800', letterSpacing: 0.5 },

    // License Card
    licenseCard: { backgroundColor: '#fff', borderRadius: 24, marginHorizontal: 16, marginBottom: 24, padding: 20, shadowColor: '#A855F7', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.1, shadowRadius: 20, elevation: 10, borderWidth: 1, borderColor: '#F3E8FF' },
    licenseHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
    licenseTitle: { fontSize: 16, fontWeight: '900', color: '#0F172A', letterSpacing: 0.5 },
    licenseSub: { fontSize: 9, fontWeight: '800', color: '#94A3B8', marginTop: 2, letterSpacing: 1 },
    licenseIconWrap: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#A855F7', alignItems: 'center', justifyContent: 'center', shadowColor: '#A855F7', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 5 },
    
    licenseBody: { gap: 12 },
    licenseRowLight: { backgroundColor: '#FAF5FF', padding: 14, borderRadius: 16, borderWidth: 1, borderColor: '#F3E8FF' },
    licenseRow: { backgroundColor: '#fff', padding: 14, borderRadius: 16, borderWidth: 1, borderColor: '#F1F5F9' },
    licenseLabel: { fontSize: 9, fontWeight: '900', color: '#A855F7', letterSpacing: 1, marginBottom: 4 },
    licenseLabelGray: { fontSize: 9, fontWeight: '900', color: '#94A3B8', letterSpacing: 1, marginBottom: 4 },
    licenseValLight: { fontSize: 13, fontWeight: '900', color: '#7E22CE' },
    licenseVal: { fontSize: 13, fontWeight: '800', color: '#334155' },
});
