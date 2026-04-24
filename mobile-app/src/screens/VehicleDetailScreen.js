import React, { useState, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Animated, Alert, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';

export default function VehicleDetailScreen({ route, navigation }) {
    const { vehicle: initialVehicle } = route.params || {};
    const [vehicle, setVehicle] = useState(initialVehicle);
    const [stats, setStats] = useState({ revenue: 0, fuel: 0, salary: 0, net: 0 });
    const [loading, setLoading] = useState(true);

    const fadeAnim = useRef(new Animated.Value(0)).current;
    const translateY = useRef(new Animated.Value(20)).current;

    const getVehicleImage = (type) => {
        const t = (type || '').toLowerCase();
        if (t.includes('minibüs') || t.includes('panelvan')) return 'https://img.icons8.com/?size=512&id=10543&format=png';
        if (t.includes('midibüs')) return 'https://img.icons8.com/?size=512&id=pOSNn91pW5Hk&format=png'; 
        if (t.includes('otobüs')) return 'https://img.icons8.com/?size=512&id=11481&format=png';
        if (t.includes('binek') || t.includes('sedan') || t.includes('taksi')) return 'https://img.icons8.com/?size=512&id=11388&format=png';
        return 'https://img.icons8.com/?size=512&id=11481&format=png';
    };

    useEffect(() => {
        const loadDetail = async () => {
            if (!initialVehicle?.id) {
                setLoading(false);
                return;
            }
            try {
                const r = await api.get(`/v1/vehicles/${initialVehicle.id}`);
                setVehicle(r.data.data.vehicle);
                setStats(r.data.data.stats);
            } catch(e) { 
                if (e.response?.status === 403) {
                    Alert.alert("Erişim Engellendi", "Bu alanı görüntüleme yetkiniz yok.");
                } else if (e.response?.status === 404) {
                    Alert.alert("Bulunamadı", "Araç kaydı bulunamadı.");
                } else if (!e.response) {
                    Alert.alert("Bağlantı Hatası", "Sunucuya ulaşılamıyor.");
                } else {
                    console.error(e); 
                }
            } finally { 
                setLoading(false);
                Animated.parallel([
                    Animated.timing(fadeAnim, { toValue: 1, duration: 500, useNativeDriver: true }),
                    Animated.spring(translateY, { toValue: 0, friction: 8, tension: 40, useNativeDriver: true })
                ]).start();
            }
        };
        loadDetail();
    }, []);

    const fmtMoney = (val) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0 }).format(val || 0);
    const fmtKm = (val) => (!val || val === 0) ? '0 km' : Number(val).toLocaleString('tr-TR') + ' km';

    return (
        <View style={s.container}>
            {/* Neo Fleet Command Premium Header */}
            <View style={s.headerContainer}>
                <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={StyleSheet.absoluteFillObject} start={{x: 0, y: 0}} end={{x: 1, y: 1}} />
                <SafeAreaView edges={['top']} style={{ paddingBottom: 90 }}>
                    <View style={s.appBar}>
                        <TouchableOpacity onPress={() => navigation.goBack()} hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }} style={s.backBtn}>
                            <Icon name="arrow-left" size={22} color="#fff" />
                        </TouchableOpacity>
                        <Text style={s.appBarTitle}>Araç Profili</Text>
                        <TouchableOpacity hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }} style={s.backBtn}>
                            <Icon name="dots-horizontal" size={22} color="#fff" />
                        </TouchableOpacity>
                    </View>

                    {/* Identity Core */}
                    <View style={s.identityCore}>
                        <View style={s.identityRow}>
                            <View style={s.plateWrap}>
                                <Text style={s.plateTxt}>{vehicle?.plate}</Text>
                            </View>
                            <View style={[s.statusPill, vehicle?.is_active ? s.statusPillActive : s.statusPillPassive]}>
                                <View style={[s.statusDot, { backgroundColor: vehicle?.is_active ? '#10B981' : '#EF4444' }]} />
                                <Text style={[s.statusTxt, { color: vehicle?.is_active ? '#10B981' : '#EF4444' }]}>{vehicle?.is_active ? 'Aktif Görevde' : 'Pasif'}</Text>
                            </View>
                        </View>
                        <Text style={s.brandTxt}>{vehicle?.brand} {vehicle?.model}</Text>
                        <View style={s.driverRow}>
                            <Icon name="account" size={18} color="#94A3B8" />
                            <Text style={s.driverTxt}>{vehicle?.driver || 'Şoför Atanmamış'}</Text>
                        </View>
                        
                        {/* Vehicle Large Image */}
                        <View style={s.heroImageWrap}>
                            <Image 
                                source={vehicle?.image_url ? { uri: vehicle.image_url } : { uri: getVehicleImage(vehicle?.type) }} 
                                style={s.heroImage} 
                            />
                        </View>
                    </View>
                </SafeAreaView>
            </View>

            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={s.scrollContent}>
                {loading ? (
                    <View style={s.loader}><ActivityIndicator size="large" color="#0F172A" /></View>
                ) : (
                    <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY }] }}>
                        
                        {/* Financial Stats Grid */}
                        <View style={s.statsGrid}>
                            <View style={s.statCard}>
                                <View style={s.statHeaderRow}>
                                    <View style={[s.statIconWrap, {backgroundColor: '#ECFDF5'}]}><Icon name="cash-plus" size={16} color="#10B981" /></View>
                                    <Text style={s.statLbl}>HASILAT</Text>
                                </View>
                                <Text style={s.statVal}>{fmtMoney(stats.revenue)}</Text>
                                <Text style={s.statPercentUp}>▲ %12,4</Text>
                            </View>
                            
                            <View style={s.statCard}>
                                <View style={s.statHeaderRow}>
                                    <View style={[s.statIconWrap, {backgroundColor: '#FEF2F2'}]}><Icon name="gas-station" size={16} color="#EF4444" /></View>
                                    <Text style={s.statLbl}>YAKIT GİDERİ</Text>
                                </View>
                                <Text style={s.statVal}>{fmtMoney(stats.fuel)}</Text>
                                <Text style={s.statPercentDown}>▼ %3,1</Text>
                            </View>
                            
                            <View style={s.statCard}>
                                <View style={s.statHeaderRow}>
                                    <View style={[s.statIconWrap, {backgroundColor: '#FFFBEB'}]}><Icon name="account-cash" size={16} color="#F59E0B" /></View>
                                    <Text style={s.statLbl}>MAAŞ BORDRO</Text>
                                </View>
                                <Text style={s.statVal}>{fmtMoney(stats.salary)}</Text>
                                <Text style={s.statPercentUp}>▲ %2,5</Text>
                            </View>

                            <View style={[s.statCard, s.statCardNet]}>
                                <LinearGradient colors={['#020617', '#0F172A']} style={StyleSheet.absoluteFillObject} borderRadius={24} />
                                <View style={s.statHeaderRow}>
                                    <View style={[s.statIconWrap, {backgroundColor: '#1E293B'}]}><Icon name="chart-line-variant" size={16} color="#38BDF8" /></View>
                                    <Text style={[s.statLbl, {color: '#94A3B8'}]}>NET KÂRLILIK</Text>
                                </View>
                                <Text style={[s.statVal, {color: '#fff'}]}>{fmtMoney(stats.net)}</Text>
                                <Text style={[s.statPercentUp, {color: '#10B981'}]}>▲ %18,7</Text>
                            </View>
                        </View>

                        {/* Operational Details */}
                        <Text style={s.sectionHeader}>OPERASYONEL BİLGİLER</Text>
                        <View style={s.infoBlock}>
                            <InfoRow icon="speedometer" label="Güncel Kilometre" val={fmtKm(vehicle?.current_km)} />
                            <InfoRow icon="car-info" label="Araç Tipi" val={vehicle?.vehicle_type || '-'} />
                            <InfoRow icon="calendar-check" label="Muayene Geçerlilik" val={vehicle?.inspection_date ? new Date(vehicle.inspection_date).toLocaleDateString('tr-TR') : '-'} />
                            <InfoRow icon="shield-car" label="Kasko Geçerlilik" val={vehicle?.kasko_end_date ? new Date(vehicle.kasko_end_date).toLocaleDateString('tr-TR') : '-'} />
                            <InfoRow icon="seat-passenger" label="Koltuk Kapasitesi" val={vehicle?.seat_count ? `${vehicle.seat_count} Koltuk` : '-'} last />
                        </View>

                        {/* Modules Shortcut Grid */}
                        <Text style={s.sectionHeader}>YÖNETİM MODÜLLERİ</Text>
                        <View style={s.modulesGrid}>
                            <ModuleBtn icon="file-document-multiple-outline" label="Belgeler" subLabel="Tüm belgeler" onPress={() => navigation.navigate('VehicleDocuments', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ModuleBtn icon="gas-station-outline" label="Yakıtlar" subLabel="Yakıt işlemleri" onPress={() => navigation.navigate('VehicleFuels', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ModuleBtn icon="wrench-outline" label="Bakımlar" subLabel="Bakım geçmişi" onPress={() => navigation.navigate('VehicleMaintenances', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ModuleBtn icon="alert-octagon-outline" label="Cezalar" subLabel="Ceza kayıtları" onPress={() => navigation.navigate('VehiclePenalties', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ModuleBtn icon="poll" label="Raporlar" subLabel="Analiz & raporlar" onPress={() => navigation.navigate('VehicleReports', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                            <ModuleBtn icon="image-multiple-outline" label="Galeri" subLabel="Fotoğraf & dosya" onPress={() => navigation.navigate('VehicleGallery', { vehicleId: vehicle.id, plate: vehicle.plate })} />
                        </View>
                        
                    </Animated.View>
                )}
            </ScrollView>
        </View>
    );
}

const InfoRow = ({ icon, label, val, last }) => (
    <View style={[s.infoRow, !last && s.infoRowBorder]}>
        <View style={s.infoRowLeft}>
            <View style={s.infoIconWrap}>
                <Icon name={icon} size={20} color="#64748B" />
            </View>
            <Text style={s.infoLabel}>{label}</Text>
        </View>
        <Text style={s.infoVal}>{val}</Text>
    </View>
);

const ModuleBtn = ({ icon, label, subLabel, onPress }) => (
    <TouchableOpacity style={s.moduleBtn} onPress={onPress} activeOpacity={0.85}>
        <View style={s.moduleBtnInner}>
            <View style={s.moduleIconBox}>
                <Icon name={icon} size={24} color="#3B82F6" />
            </View>
            <View style={s.moduleTexts}>
                <Text style={s.moduleLabel}>{label}</Text>
                <Text style={s.moduleSub}>{subLabel || 'Detayları görüntüle'}</Text>
            </View>
            <Icon name="chevron-right" size={20} color="#CBD5E1" />
        </View>
    </TouchableOpacity>
);

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    
    // Neo Fleet Command Header 2.0 - Extended
    headerContainer: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden' },
    appBar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, paddingTop: 10, marginBottom: 24 },
    backBtn: { width: 46, height: 46, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', shadowColor: '#fff', shadowOffset: {width:0, height:4}, shadowOpacity: 0.1, shadowRadius: 10 },
    appBarTitle: { fontSize: 18, fontWeight: '800', color: '#fff', letterSpacing: 0.5 },
    
    identityCore: { paddingHorizontal: 28 },
    identityRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    plateWrap: { backgroundColor: '#fff', paddingHorizontal: 16, paddingVertical: 8, borderRadius: 12, shadowColor: '#000', shadowOffset: {width:0,height:8}, shadowOpacity: 0.15, shadowRadius: 15, elevation: 5 },
    plateTxt: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: 1 },
    statusPill: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 14, gap: 6, backgroundColor: 'rgba(16,185,129,0.15)', borderWidth: 1, borderColor: 'rgba(16,185,129,0.3)', shadowColor: '#000', shadowOffset: {width:0,height:4}, shadowOpacity: 0.2, shadowRadius: 5 },
    statusPillActive: { borderColor: 'rgba(16,185,129,0.3)', backgroundColor: 'rgba(16,185,129,0.15)' },
    statusPillPassive: { borderColor: 'rgba(239,68,68,0.3)', backgroundColor: 'rgba(239,68,68,0.15)' },
    statusDot: { width: 6, height: 6, borderRadius: 3 },
    statusTxt: { fontSize: 11, fontWeight: '800' },
    brandTxt: { fontSize: 30, fontWeight: '900', color: '#fff', marginBottom: 6, letterSpacing: -1, textTransform: 'uppercase' },
    driverRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
    driverTxt: { fontSize: 15, fontWeight: '600', color: '#CBD5E1' },

    heroImageWrap: { width: '100%', height: 160, alignItems: 'center', justifyContent: 'center', marginTop: 10, marginBottom: -50 },
    heroImage: { width: '90%', height: '100%', resizeMode: 'contain', opacity: 0.9 },

    // Scroll Area
    scrollContent: { paddingHorizontal: 20, paddingBottom: 80, marginTop: -40, zIndex: 20 },
    loader: { marginTop: 100, alignItems: 'center' },

    sectionHeader: { fontSize: 12, fontWeight: '800', color: '#64748B', letterSpacing: 1.5, marginBottom: 16, marginTop: 12, marginLeft: 6, textTransform: 'uppercase' },

    // Financial Grid (3D KPI)
    statsGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', gap: 14, marginBottom: 36 },
    statCard: { width: '47.5%', backgroundColor: '#fff', borderRadius: 24, padding: 16, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.08, shadowRadius: 24, elevation: 8 },
    statCardNet: { shadowColor: '#0A1A3A', shadowOffset: {width:0,height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 12, overflow: 'hidden' },
    statHeaderRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
    statIconWrap: { width: 28, height: 28, borderRadius: 8, alignItems: 'center', justifyContent: 'center', marginRight: 8 },
    statLbl: { fontSize: 11, fontWeight: '700', color: '#64748B', letterSpacing: 0.5 },
    statVal: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    statPercentUp: { fontSize: 12, fontWeight: '800', color: '#10B981', alignSelf: 'flex-end', marginTop: 4 },
    statPercentDown: { fontSize: 12, fontWeight: '800', color: '#EF4444', alignSelf: 'flex-end', marginTop: 4 },

    // Info Block
    infoBlock: { backgroundColor: '#fff', borderRadius: 24, paddingHorizontal: 20, shadowColor: '#0A1A3A', shadowOffset: {width:0,height:12}, shadowOpacity: 0.05, shadowRadius: 24, elevation: 6, marginBottom: 36 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 18 },
    infoRowBorder: { borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    infoRowLeft: { flexDirection: 'row', alignItems: 'center', gap: 14 },
    infoIconWrap: { width: 36, height: 36, borderRadius: 12, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center' },
    infoLabel: { fontSize: 14, fontWeight: '600', color: '#64748B' },
    infoVal: { fontSize: 14, fontWeight: '800', color: '#0F172A' },

    // Modules Grid
    modulesGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', gap: 14, marginBottom: 50 },
    moduleBtn: { width: '47.5%', backgroundColor: '#fff', borderRadius: 24, padding: 12, shadowColor: '#0A1A3A', shadowOffset: {width:0,height:8}, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4 },
    moduleBtnInner: { flexDirection: 'row', alignItems: 'center' },
    moduleIconBox: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center', marginRight: 10 },
    moduleTexts: { flex: 1 },
    moduleLabel: { fontSize: 13, fontWeight: '800', color: '#0F172A', marginBottom: 2 },
    moduleSub: { fontSize: 11, fontWeight: '600', color: '#94A3B8' }
});
