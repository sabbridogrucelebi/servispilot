import React, { useContext } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Alert } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';

export default function ProfileScreen() {
    const { userInfo, logout } = useContext(AuthContext);

    const handleLogout = () => {
        Alert.alert('Çıkış', 'Çıkış yapmak istediğinize emin misiniz?', [
            { text: 'İptal', style: 'cancel' },
            { text: 'Çıkış Yap', style: 'destructive', onPress: logout }
        ]);
    };

    const firstName = userInfo?.name?.split(' ')[0] || 'Kullanıcı';

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.headerRow}>
                        <Text style={s.headerTitle}>Profil</Text>
                        <TouchableOpacity style={s.settingsBtn}><Icon name="cog-outline" size={26} color="#fff" /></TouchableOpacity>
                    </View>
                    <View style={s.profileCard}>
                        <View style={s.avatar}><Text style={s.avatarText}>{firstName.charAt(0)}</Text></View>
                        <View style={s.userInfo}>
                            <Text style={s.userName}>{userInfo?.name || 'Yönetici'}</Text>
                            <Text style={s.userEmail}>{userInfo?.email || 'admin@servispilot.com'}</Text>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <ScrollView contentContainerStyle={s.content} showsVerticalScrollIndicator={false}>
                <View style={s.menuSection}>
                    <Text style={s.sectionTitle}>Hesap Yönetimi</Text>
                    <MenuItem icon="account-edit-outline" color="#3B82F6" title="Kişisel Bilgiler" />
                    <MenuItem icon="shield-lock-outline" color="#10B981" title="Şifre & Güvenlik" />
                    <MenuItem icon="bell-ring-outline" color="#F59E0B" title="Bildirim Ayarları" />
                </View>

                <View style={s.menuSection}>
                    <Text style={s.sectionTitle}>Sistem</Text>
                    <MenuItem icon="help-circle-outline" color="#6366F1" title="Yardım Merkezi" />
                    <MenuItem icon="information-outline" color="#64748B" title="Hakkında" value="v1.0.0" />
                </View>

                <TouchableOpacity style={s.logoutBtn} onPress={handleLogout} activeOpacity={0.85}>
                    <Icon name="logout-variant" size={24} color="#EF4444" />
                    <Text style={s.logoutText}>Güvenli Çıkış Yap</Text>
                </TouchableOpacity>
            </ScrollView>
        </View>
    );
}

function MenuItem({ icon, color, title, value }) {
    return (
        <TouchableOpacity style={s.menuItem} activeOpacity={0.7}>
            <Icon name={icon} size={28} color={color} style={s.menuIcon} />
            <Text style={s.menuTitle}>{title}</Text>
            {value ? <Text style={s.menuValue}>{value}</Text> : <Icon name="chevron-right" size={24} color="#CBD5E1" />}
        </TouchableOpacity>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 40, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    headerTitle: { fontSize: 32, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    settingsBtn: { alignItems: 'center', justifyContent: 'center' },
    profileCard: { flexDirection: 'row', alignItems: 'center', marginTop: 32 },
    avatar: { width: 72, height: 72, borderRadius: 36, backgroundColor: '#ffffff', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 12, elevation: 8 },
    avatarText: { color: '#3B82F6', fontSize: 32, fontWeight: '900' },
    userInfo: { marginLeft: 20, flex: 1 },
    userName: { fontSize: 24, fontWeight: '900', color: '#fff' },
    userEmail: { fontSize: 14, color: 'rgba(255,255,255,0.8)', fontWeight: '500', marginTop: 4 },
    content: { padding: 24, paddingBottom: 120 },
    menuSection: { backgroundColor: '#fff', borderRadius: 24, padding: 8, marginBottom: 24, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.04, shadowRadius: 16, elevation: 3 },
    sectionTitle: { fontSize: 14, fontWeight: '800', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: 1, marginLeft: 16, marginTop: 12, marginBottom: 8 },
    menuItem: { flexDirection: 'row', alignItems: 'center', padding: 16 },
    menuIcon: { marginRight: 16 },
    menuTitle: { flex: 1, fontSize: 16, fontWeight: '700', color: '#0F172A' },
    menuValue: { fontSize: 15, color: '#94A3B8', fontWeight: '600' },
    logoutBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', backgroundColor: '#FEF2F2', paddingVertical: 18, borderRadius: 20, gap: 12, borderWidth: 1, borderColor: '#FECACA' },
    logoutText: { color: '#EF4444', fontSize: 17, fontWeight: '800' },
});
