import React, { useContext } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Dimensions } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { AuthContext } from '../context/AuthContext';

const { width } = Dimensions.get('window');

const menuItems = [
    { id: 1, icon: 'home-outline', label: 'Ana Sayfa', sub: 'GENEL BAKIŞ', color: '#3B82F6', route: 'HomeTab' },
    { id: 2, icon: 'car-outline', label: 'Araçlar', sub: 'FİLO YÖNETİMİ', color: '#10B981', route: 'VehiclesTab', permission: 'vehicles.view' },
    { id: 3, icon: 'location-outline', label: 'Araç Takip', sub: 'CANLI İZLEME', color: '#06B6D4', route: 'Tracking', permission: 'vehicles.view' },
    { id: 4, icon: 'people-outline', label: 'Personeller', sub: 'PERSONEL YÖNETİMİ', color: '#8B5CF6', route: 'Personnel', permission: 'drivers.view' },
    { id: 5, icon: 'construct-outline', label: 'Bakım / Tamir', sub: 'SERVİS VE BAKIM', color: '#F97316', route: 'Maintenances', permission: 'maintenances.view' },
    { id: 6, icon: 'water-outline', label: 'Yakıt', sub: 'YAKIT TAKİBİ', color: '#0EA5E9', route: 'VehiclesTab', screen: 'Fuels', permission: 'fuels.view' },
    { id: 7, icon: 'warning-outline', label: 'Trafik Cezaları', sub: 'YASAL VE UYUMLULUK', color: '#EF4444', route: 'Penalties', permission: 'penalties.view' },
    { id: 8, icon: 'calendar-outline', label: 'Puantaj / Sefer', sub: 'OPERASYON KAYITLARI', color: '#F59E0B', route: 'Trips', permission: 'trips.view' },
    { id: 9, icon: 'cash-outline', label: 'Maaşlar', sub: 'FİNANSAL KAYITLAR', color: '#F43F5E', route: 'Payrolls', permission: 'payrolls.view' },
    { id: 10, icon: 'business-outline', label: 'Müşteriler', sub: 'MÜŞTERİ YÖNETİMİ', color: '#14B8A6', route: 'Customers', permission: 'customers.view' },
    { id: 11, icon: 'pie-chart-outline', label: 'Raporlar', sub: 'ANALİZ MERKEZİ', color: '#38BDF8', route: 'Reports', permission: 'reports.view' },
    { id: 13, icon: 'time-outline', label: 'Loglar', sub: 'AKTİVİTE KAYITLARI', color: '#94A3B8', route: 'Activity', adminOnly: true },
    { id: 14, icon: 'people-circle-outline', label: 'Kullanıcılar', sub: 'ERİŞİM KONTROLÜ', color: '#6366F1', route: 'CompanyUsers', adminOnly: true },
    { id: 15, icon: 'settings-outline', label: 'Ayarlar', sub: 'SİSTEM YAPILANDIRMASI', color: '#CBD5E1', route: 'Settings' },
    { id: 16, icon: 'bus-outline', label: 'PilotCell', sub: 'ŞOFÖR PANELİ', color: '#8B5CF6', route: 'PilotCellDriver', permission: 'pilotcell.drive' },
];

export default function MenuScreen({ navigation }) {
    const { hasPermission, userInfo } = useContext(AuthContext);

    const visibleItems = menuItems.filter(item => {
        if (item.adminOnly) return !!userInfo?.is_company_admin;
        if (!item.permission) return true;
        return hasPermission(item.permission);
    });

    return (
        <View style={s.container}>
            <SafeAreaView style={{ flex: 1 }}>
                <ScrollView contentContainerStyle={s.scrollContent} showsVerticalScrollIndicator={false}>
                    
                    <View style={s.header}>
                        <Text style={s.headerTitle}>Menü</Text>
                        <Text style={s.headerSub}>Tüm modüllere buradan ulaşın</Text>
                    </View>

                    <View style={s.grid}>
                        {visibleItems.map((item, index) => (
                            <TouchableOpacity 
                                key={item.id} 
                                style={s.cardWrap} 
                                activeOpacity={0.7}
                                onPress={() => {
                                    if (item.route && item.screen) {
                                        navigation.navigate(item.route, { screen: item.screen });
                                    } else if (item.route) {
                                        navigation.navigate(item.route);
                                    }
                                }}
                            >
                                <LinearGradient colors={['#020617', '#1E1B4B']} style={s.card}>
                                    <View style={{ alignItems: 'center', justifyContent: 'center', marginBottom: 16, height: 48 }}>
                                        <Icon name={item.icon} size={42} color={item.color} style={{ textShadowColor: item.color, textShadowOffset: { width: 0, height: 0 }, textShadowRadius: 15 }} />
                                    </View>
                                    <Text style={s.cardTitle}>{item.label}</Text>
                                    <Text style={s.cardSub} numberOfLines={1}>{item.sub}</Text>
                                </LinearGradient>
                            </TouchableOpacity>
                        ))}
                    </View>

                    <View style={{ height: 120 }} />
                </ScrollView>
            </SafeAreaView>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    scrollContent: { paddingHorizontal: 20, paddingTop: 20 },
    
    header: { marginBottom: 30 },
    headerTitle: { fontSize: 32, fontWeight: '900', color: '#0F172A', letterSpacing: -1 },
    headerSub: { fontSize: 14, color: '#64748B', fontWeight: '500', marginTop: 4 },

    grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
    cardWrap: { width: '48%', marginBottom: 16 },
    card: { padding: 20, borderRadius: 24, borderWidth: 1, borderColor: 'rgba(255,255,255,0.05)', alignItems: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowopacity: 1, shadowRadius: 15, elevation: 8 },
    cardTitle: { fontSize: 15, fontWeight: '800', color: '#E2E8F0', marginBottom: 4, textAlign: 'center' },
    cardSub: { fontSize: 11, color: '#64748B', fontWeight: '500', textAlign: 'center' }
});
