import React, { useContext, useState, useCallback, useRef } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, RefreshControl, Animated, Dimensions } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';
import api from '../api/axios';
import { LinearGradient } from 'expo-linear-gradient';
import dayjs from 'dayjs';
import 'dayjs/locale/tr';

dayjs.locale('tr');
const { width } = Dimensions.get('window');

const toTitleCase = (str) => {
    if (!str) return '';
    return str.toLocaleLowerCase('tr-TR').split(' ').map(word => word.charAt(0).toLocaleUpperCase('tr-TR') + word.slice(1)).join(' ');
};

export default function HomeScreen({ navigation }) {
    const { userInfo } = useContext(AuthContext);
    const [stats, setStats] = useState(null);
    const [refreshing, setRefreshing] = useState(false);
    
    const fadeAnim = useRef(new Animated.Value(0)).current;
    const slideAnim = useRef(new Animated.Value(20)).current;

    const fetchDashboard = async () => {
        try {
            const res = await api.get('/v1/dashboard');
            setStats(res.data.data);
            
            Animated.parallel([
                Animated.timing(fadeAnim, { toValue: 1, duration: 600, useNativeDriver: true }),
                Animated.timing(slideAnim, { toValue: 0, duration: 600, useNativeDriver: true })
            ]).start();
            
        } catch (e) {
            console.log('Dashboard fetch error:', e);
        }
    };

    useFocusEffect(
        useCallback(() => {
            fetchDashboard();
        }, [])
    );

    const onRefresh = useCallback(async () => {
        setRefreshing(true);
        await fetchDashboard();
        setRefreshing(false);
    }, []);

    const firstName = toTitleCase(userInfo?.name?.split(' ')[0] || 'Kullanıcı');
    const currentDate = dayjs().format('D MMMM YYYY, dddd');

    const KpiCard = ({ icon, title, value, gradientColors, darkColor, isHalf }) => (
        <View style={[styles.kpi3DBase, { backgroundColor: darkColor }]}>
            <LinearGradient colors={gradientColors} style={[styles.kpiCard, isHalf && { padding: 18, flexDirection: 'column', alignItems: 'flex-start' }]} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <View style={[styles.kpiIconBox, { shadowColor: darkColor }, isHalf && { width: 46, height: 46, borderRadius: 14, marginBottom: 12, marginRight: 0 }]}>
                    <Icon name={icon} size={isHalf ? 24 : 32} color="#FFF" />
                </View>
                <View style={[styles.kpiInfo, isHalf && { width: '100%' }]}>
                    <Text style={[styles.kpiValue, isHalf && { fontSize: 28 }]} adjustsFontSizeToFit numberOfLines={1}>{value !== undefined ? value : '-'}</Text>
                    <Text style={[styles.kpiTitle, isHalf && { fontSize: 13 }]} adjustsFontSizeToFit numberOfLines={1}>{title}</Text>
                </View>
            </LinearGradient>
        </View>
    );

    const renderMaintenanceHealth = () => {
        if (!stats?.maintenance_health || stats.maintenance_health.length === 0) {
            return (
                <View style={styles.emptyState}>
                    <Icon name="check-circle" size={48} color="#10B981" style={{ marginBottom: 16 }} />
                    <Text style={styles.emptyTitle}>Harika Haber!</Text>
                    <Text style={styles.emptyDesc}>Acil bakım veya yağlama gerektiren aracınız bulunmuyor. Filonuz tamamen güvende.</Text>
                </View>
            );
        }

        return stats.maintenance_health.map((mh, i) => (
            <View key={i} style={styles.mhCard}>
                <LinearGradient colors={['rgba(255,255,255,0.05)', 'transparent']} style={StyleSheet.absoluteFillObject} />
                <View style={styles.mhHeader}>
                    <View style={styles.mhHeaderLeft}>
                        <View style={styles.mhPlateBox}>
                            <Icon name="car-sports" size={18} color="#60A5FA" />
                            <Text style={styles.mhPlateText}>{mh.plate}</Text>
                        </View>
                        <View style={styles.mhKmBadge}>
                            <Icon name="speedometer" size={14} color="#94A3B8" />
                            <Text style={styles.mhCurrentKm}>{new Intl.NumberFormat('tr-TR').format(mh.current_km)} KM</Text>
                        </View>
                    </View>
                    <TouchableOpacity style={styles.mhActionBtn} onPress={() => navigation.navigate('VehiclesTab', { screen: 'VehicleMaintenances', params: { vehicleId: mh.vehicle_id, vehicle: mh } })}>
                        <Text style={styles.mhActionText}>Göz At</Text>
                        <Icon name="arrow-right-circle" size={18} color="#fff" />
                    </TouchableOpacity>
                </View>
                <View style={styles.mhAlerts}>
                    {mh.alerts.map((alert, idx) => {
                        const isOverdue = alert.remaining <= 0;
                        const alertColor = isOverdue ? '#F43F5E' : '#F59E0B';
                        const alertBg = isOverdue ? 'rgba(244, 63, 94, 0.12)' : 'rgba(245, 158, 11, 0.12)';
                        const alertIcon = isOverdue ? 'alert-decagram' : 'alert-circle-outline';
                        const statusText = isOverdue ? `Gecikti (${Math.abs(alert.remaining)} KM)` : `Yaklaştı (${alert.remaining} KM Kaldı)`;

                        return (
                            <View key={idx} style={[styles.alertRow, { backgroundColor: alertBg, borderColor: alertColor + '50' }]}>
                                <View style={[styles.alertIconWrap, { backgroundColor: alertColor + '20' }]}>
                                    <Icon name={alertIcon} size={22} color={alertColor} />
                                </View>
                                <View style={styles.alertContent}>
                                    <Text style={[styles.alertType, { color: alertColor }]}>{alert.type}</Text>
                                    <Text style={[styles.alertStatus, { color: alertColor }]}>{statusText}</Text>
                                </View>
                                {isOverdue && <View style={[styles.pulseDot, { backgroundColor: alertColor }]} />}
                            </View>
                        );
                    })}
                </View>
            </View>
        ));
    };

    return (
        <View style={styles.container}>
            <LinearGradient colors={['#020617', '#0F172A', '#1E1B4B']} style={StyleSheet.absoluteFillObject} />
            
            <SafeAreaView style={{ flex: 1 }}>
                <ScrollView 
                    contentContainerStyle={styles.scrollContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#8B5CF6" />}
                    showsVerticalScrollIndicator={false}
                >
                    <View style={styles.header}>
                        <View>
                            <Text style={styles.dateText}>{currentDate}</Text>
                            <Text style={styles.welcomeText}>Hoş Geldin, <Text style={styles.userName}>{firstName}</Text></Text>
                        </View>
                        <View style={styles.profileAvatar}>
                            <Text style={styles.profileInitials}>{firstName.charAt(0)}</Text>
                        </View>
                    </View>

                    {stats ? (
                        <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>
                            
                            {/* KPI Grid */}
                            <Text style={styles.sectionTitle}>Filo Özeti</Text>
                            <View style={styles.kpiContainer}>
                                <KpiCard icon="bus-multiple" title="Toplam Araç" value={stats.vehicle_count} gradientColors={['#3B82F6', '#1D4ED8']} darkColor="#1E3A8A" />
                                <View style={styles.kpiRow}>
                                    <View style={{ flex: 1, marginRight: 6 }}>
                                        <KpiCard icon="account-tie" title="Şoförler" value={stats.driver_count} gradientColors={['#8B5CF6', '#6D28D9']} darkColor="#4C1D95" isHalf />
                                    </View>
                                    <View style={{ flex: 1, marginLeft: 6 }}>
                                        <KpiCard icon="domain" title="Müşteriler" value={stats.customer_count} gradientColors={['#10B981', '#047857']} darkColor="#064E3B" isHalf />
                                    </View>
                                </View>
                            </View>

                            {/* Bakım Sağlığı (Maintenance Health) */}
                            <View style={styles.sectionHeaderWrap}>
                                <Icon name="heart-pulse" size={24} color="#F43F5E" style={{ marginRight: 8 }} />
                                <Text style={[styles.sectionTitle, { marginBottom: 0 }]}>Bakım Sağlığı</Text>
                            </View>
                            <Text style={styles.sectionSubtitle}>200 KM altına düşen veya süresi geçen araçlar.</Text>
                            
                            <View style={styles.mhContainer}>
                                {renderMaintenanceHealth()}
                            </View>

                            <View style={{ height: 120 }} />
                        </Animated.View>
                    ) : (
                        <View style={{ marginTop: 50, alignItems: 'center' }}>
                            <Icon name="loading" size={32} color="#8B5CF6" />
                        </View>
                    )}
                </ScrollView>
            </SafeAreaView>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#020617' },
    scrollContent: { paddingHorizontal: 20, paddingTop: 10 },
    
    header: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 30 },
    dateText: { fontSize: 13, color: '#94A3B8', fontWeight: '600', letterSpacing: 0.5, marginBottom: 4 },
    welcomeText: { fontSize: 28, color: '#fff', fontWeight: '500' },
    userName: { fontWeight: '900', color: '#8B5CF6' },
    profileAvatar: { width: 48, height: 48, borderRadius: 24, backgroundColor: 'rgba(139, 92, 246, 0.2)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(139, 92, 246, 0.4)' },
    profileInitials: { color: '#C4B5FD', fontSize: 20, fontWeight: '800' },

    sectionTitle: { fontSize: 18, fontWeight: '800', color: '#F8FAFC', marginBottom: 16, letterSpacing: 0.5 },
    sectionHeaderWrap: { flexDirection: 'row', alignItems: 'center', marginTop: 32, marginBottom: 4 },
    sectionSubtitle: { fontSize: 13, color: '#94A3B8', marginBottom: 16, fontWeight: '500' },

    kpiContainer: { gap: 12 },
    kpiRow: { flexDirection: 'row' },
    kpi3DBase: { borderRadius: 28, paddingBottom: 8, paddingRight: 2, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.5, shadowRadius: 15, elevation: 12, marginBottom: 8 },
    kpiCard: { padding: 22, flexDirection: 'row', alignItems: 'center', borderRadius: 28, borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)' },
    kpiIconBox: { width: 60, height: 60, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center', marginRight: 16, shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 6, elevation: 4 },
    kpiInfo: { flex: 1 },
    kpiValue: { fontSize: 36, fontWeight: '900', color: '#FFF', letterSpacing: -1, marginBottom: 2, textShadowColor: 'rgba(0,0,0,0.3)', textShadowOffset: { width: 0, height: 2 }, textShadowRadius: 4 },
    kpiTitle: { fontSize: 14, fontWeight: '700', color: 'rgba(255,255,255,0.9)', letterSpacing: 0.5 },

    mhContainer: { gap: 16 },
    mhCard: { backgroundColor: '#0F172A', borderRadius: 24, padding: 20, borderWidth: 1, borderColor: '#1E293B', shadowColor: '#000', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.5, shadowRadius: 12, elevation: 8, overflow: 'hidden' },
    mhHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    mhHeaderLeft: { flex: 1 },
    mhPlateBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(59, 130, 246, 0.15)', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 10, alignSelf: 'flex-start', marginBottom: 8, borderWidth: 1, borderColor: 'rgba(59, 130, 246, 0.3)' },
    mhPlateText: { color: '#60A5FA', fontSize: 16, fontWeight: '900', marginLeft: 8, letterSpacing: 0.5 },
    mhKmBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.05)', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, alignSelf: 'flex-start' },
    mhCurrentKm: { color: '#CBD5E1', fontSize: 12, fontWeight: '700', marginLeft: 4 },
    
    mhActionBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(59, 130, 246, 0.2)', paddingHorizontal: 16, paddingVertical: 10, borderRadius: 14, borderWidth: 1, borderColor: 'rgba(59, 130, 246, 0.4)' },
    mhActionText: { color: '#60A5FA', fontSize: 13, fontWeight: '800', marginRight: 6 },

    mhAlerts: { gap: 10 },
    alertRow: { flexDirection: 'row', alignItems: 'center', padding: 14, borderRadius: 16, borderWidth: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.2, shadowRadius: 4, elevation: 2 },
    alertIconWrap: { width: 40, height: 40, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    alertContent: { marginLeft: 14, flex: 1 },
    alertType: { fontSize: 15, fontWeight: '900', marginBottom: 2 },
    alertStatus: { fontSize: 13, fontWeight: '700', opacity: 0.9 },
    pulseDot: { width: 8, height: 8, borderRadius: 4, shadowColor: '#F43F5E', shadowOffset: { width: 0, height: 0 }, shadowOpacity: 1, shadowRadius: 6, elevation: 4 },

    emptyState: { backgroundColor: 'rgba(16, 185, 129, 0.05)', borderRadius: 28, padding: 32, alignItems: 'center', borderWidth: 1, borderColor: 'rgba(16, 185, 129, 0.15)', shadowColor: '#10B981', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 10, elevation: 4 },
    emptyTitle: { fontSize: 20, fontWeight: '900', color: '#10B981', marginBottom: 8 },
    emptyDesc: { fontSize: 14, color: '#94A3B8', textAlign: 'center', lineHeight: 22, fontWeight: '500' }
});
