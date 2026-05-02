import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Alert, TextInput, Switch, LayoutAnimation, UIManager, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import { Header } from '../components';

if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
    UIManager.setLayoutAnimationEnabledExperimental(true);
}

const MODULE_CONFIG = [
    { title: 'Araçlar', icon: 'car-multiple', colors: ['#3B82F6', '#4F46E5'], keys: ['vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete'] },
    { title: 'Personeller', icon: 'account-tie', colors: ['#8B5CF6', '#9333EA'], keys: ['drivers.view', 'drivers.create', 'drivers.edit', 'drivers.delete'] },
    { title: 'Bakım / Tamir', icon: 'wrench', colors: ['#10B981', '#059669'], keys: ['maintenances.view', 'maintenances.create', 'maintenances.edit', 'maintenances.delete'] },
    { title: 'Yakıt', icon: 'gas-station', colors: ['#F59E0B', '#EA580C'], keys: ['fuels.view', 'fuels.create', 'fuels.edit', 'fuels.delete', 'fuel_stations.view', 'fuel_stations.create', 'fuel_stations.edit', 'fuel_stations.delete'] },
    { title: 'Trafik Cezaları', icon: 'police-badge', colors: ['#F43F5E', '#E11D48'], keys: ['penalties.view', 'penalties.create', 'penalties.edit', 'penalties.delete'] },
    { title: 'Puantaj / Sefer', icon: 'calendar-clock', colors: ['#0EA5E9', '#0284C7'], keys: ['trips.view', 'trips.create', 'trips.edit', 'trips.delete'] },
    { title: 'Maaşlar', icon: 'cash-multiple', colors: ['#84CC16', '#16A34A'], keys: ['payrolls.view', 'payrolls.create', 'payrolls.edit', 'payrolls.delete'] },
    { title: 'Müşteriler', icon: 'office-building', colors: ['#14B8A6', '#0D9488'], keys: ['customers.view', 'customers.create', 'customers.edit', 'customers.delete'] },
    { title: 'Belgeler', icon: 'file-document-multiple', colors: ['#64748B', '#334155'], keys: ['documents.view', 'documents.create', 'documents.edit', 'documents.delete'] },
    { title: 'Raporlar & Finans', icon: 'chart-bar', colors: ['#6366F1', '#2563EB'], keys: ['reports.view', 'financials.view', 'dashboard.view'] }
];

export default function CompanyUserFormScreen({ route, navigation }) {
    const { userId } = route.params || {};
    const isEditing = !!userId;

    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [permissionsList, setPermissionsList] = useState([]);

    const [form, setForm] = useState({
        name: '',
        email: '',
        password: '',
        role: 'operation',
        is_active: true,
        permissions: []
    });

    const [errors, setErrors] = useState({});
    const [expandedModules, setExpandedModules] = useState({});

    useEffect(() => {
        const fetchData = async () => {
            try {
                // Fetch options
                const optRes = await api.get('/v1/company-users/options');
                if (optRes.data.success) {
                    setPermissionsList(optRes.data.data.permissions || []);
                }

                // Fetch user if editing
                if (isEditing) {
                    const userRes = await api.get(`/v1/company-users/${userId}`);
                    if (userRes.data.success) {
                        const u = userRes.data.data;
                        setForm({
                            name: u.name,
                            email: u.email,
                            password: '', // Leave empty for edit
                            role: u.role,
                            is_active: !!u.is_active,
                            permissions: u.permission_ids || []
                        });
                    }
                }
            } catch (error) {
                Alert.alert('Hata', 'Veriler alınamadı. Yetkiniz olmayabilir.');
                navigation.goBack();
            } finally {
                setLoading(false);
            }
        };
        fetchData();
    }, [userId]);

    const togglePermission = (permId) => {
        setForm(prev => {
            const current = prev.permissions;
            if (current.includes(permId)) {
                return { ...prev, permissions: current.filter(id => id !== permId) };
            } else {
                return { ...prev, permissions: [...current, permId] };
            }
        });
    };

    const toggleModuleExpander = (index) => {
        LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
        setExpandedModules(prev => ({
            ...prev,
            [index]: !prev[index]
        }));
    };

    const selectAllInModule = (moduleKeys) => {
        const pMap = {};
        permissionsList.forEach(p => pMap[p.key] = p.id);
        
        const idsToToggle = moduleKeys.map(k => pMap[k]).filter(id => id !== undefined);
        const allSelected = idsToToggle.every(id => form.permissions.includes(id));
        
        setForm(prev => {
            let newPerms = [...prev.permissions];
            if (allSelected) {
                newPerms = newPerms.filter(id => !idsToToggle.includes(id));
            } else {
                idsToToggle.forEach(id => {
                    if (!newPerms.includes(id)) newPerms.push(id);
                });
            }
            return { ...prev, permissions: newPerms };
        });
    };

    const getModuleStats = (moduleKeys) => {
        const pMap = {};
        permissionsList.forEach(p => pMap[p.key] = p.id);
        const ids = moduleKeys.map(k => pMap[k]).filter(id => id !== undefined);
        const active = ids.filter(id => form.permissions.includes(id)).length;
        return { total: ids.length, active, ids };
    };

    const handleSave = async () => {
        setErrors({});
        setSaving(true);
        try {
            const payload = { ...form };
            if (isEditing && !payload.password) {
                delete payload.password; // Don't send empty password if editing
            }

            let res;
            if (isEditing) {
                res = await api.put(`/v1/company-users/${userId}`, payload);
            } else {
                res = await api.post('/v1/company-users', payload);
            }

            if (res.data.success) {
                Alert.alert('Başarılı', res.data.message || 'Kullanıcı kaydedildi.');
                navigation.goBack();
            } else {
                Alert.alert('Hata', res.data.message || 'Bir hata oluştu.');
            }
        } catch (e) {
            if (e.response?.status === 422) {
                setErrors(e.response.data.errors || {});
                Alert.alert('Eksik Bilgi', 'Lütfen formu kontrol edin.');
            } else if (e.response?.status === 403) {
                Alert.alert('Hata', e.response.data.message || 'Yetkiniz yok veya limit aşıldı.');
            } else {
                Alert.alert('Hata', 'Sunucu ile bağlantı kurulamadı.');
            }
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <SafeAreaView style={st.container}>
                <Header title={isEditing ? "Kullanıcı Düzenle" : "Yeni Kullanıcı"} onBack={() => navigation.goBack()} />
                <View style={st.center}><ActivityIndicator size="large" color="#6366F1" /></View>
            </SafeAreaView>
        );
    }

    return (
        <SafeAreaView style={st.container}>
            <Header title={isEditing ? "Kullanıcı Düzenle" : "Yeni Kullanıcı"} onBack={() => navigation.goBack()} />

            <ScrollView contentContainerStyle={st.scrollContent} showsVerticalScrollIndicator={false}>
                
                {/* Temel Bilgiler */}
                <View style={st.section}>
                    <View style={st.sectionHeader}>
                        <Icon name="card-account-details-outline" size={24} color="#6366F1" />
                        <Text style={st.sectionTitle}>Hesap Bilgileri</Text>
                    </View>

                    <View style={st.inputGroup}>
                        <Text style={st.label}>Ad Soyad</Text>
                        <TextInput 
                            style={[st.input, errors.name && st.inputError]} 
                            value={form.name} 
                            onChangeText={t => setForm({...form, name: t})} 
                            placeholder="Örn: Sabri Doğru"
                            placeholderTextColor="#94A3B8"
                        />
                        {errors.name && <Text style={st.errorTxt}>{errors.name[0]}</Text>}
                    </View>

                    <View style={st.inputGroup}>
                        <Text style={st.label}>E-posta</Text>
                        <TextInput 
                            style={[st.input, errors.email && st.inputError]} 
                            value={form.email} 
                            onChangeText={t => setForm({...form, email: t})} 
                            placeholder="ornek@firma.com"
                            autoCapitalize="none"
                            keyboardType="email-address"
                            placeholderTextColor="#94A3B8"
                        />
                        {errors.email && <Text style={st.errorTxt}>{errors.email[0]}</Text>}
                    </View>

                    <View style={st.inputGroup}>
                        <Text style={st.label}>{isEditing ? 'Yeni Şifre (Değiştirmek istemiyorsanız boş bırakın)' : 'Şifre'}</Text>
                        <TextInput 
                            style={[st.input, errors.password && st.inputError]} 
                            value={form.password} 
                            onChangeText={t => setForm({...form, password: t})} 
                            placeholder="En az 8 karakter"
                            secureTextEntry
                            placeholderTextColor="#94A3B8"
                        />
                        {errors.password && <Text style={st.errorTxt}>{errors.password[0]}</Text>}
                    </View>

                    <View style={st.inputGroup}>
                        <Text style={st.label}>Yetki Rolü</Text>
                        <View style={st.rolesRow}>
                            {[
                                {val: 'company_admin', label: 'Firma Yöneticisi'},
                                {val: 'operation', label: 'Operasyon'},
                                {val: 'accounting', label: 'Muhasebe'},
                                {val: 'viewer', label: 'Gözlemci'}
                            ].map(role => (
                                <TouchableOpacity 
                                    key={role.val} 
                                    style={[st.roleChip, form.role === role.val && st.roleChipActive]}
                                    onPress={() => setForm({...form, role: role.val})}
                                >
                                    <Text style={[st.roleChipTxt, form.role === role.val && st.roleChipTxtActive]}>
                                        {role.label}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </View>
                        {errors.role && <Text style={st.errorTxt}>{errors.role[0]}</Text>}
                    </View>

                    <View style={st.switchRow}>
                        <View>
                            <Text style={st.switchLabel}>Hesap Aktifliği</Text>
                            <Text style={st.switchSub}>Kullanıcı sisteme giriş yapabilsin mi?</Text>
                        </View>
                        <Switch 
                            value={form.is_active} 
                            onValueChange={v => setForm({...form, is_active: v})} 
                            trackColor={{ false: '#E2E8F0', true: '#10B981' }}
                            thumbColor="#FFF"
                        />
                    </View>
                </View>

                {/* Modül Yetkileri */}
                {form.role !== 'company_admin' && (
                    <View style={st.section}>
                        <View style={st.sectionHeader}>
                            <View style={st.iconBoxPurp}>
                                <Icon name="shield-key-outline" size={24} color="#FFF" />
                            </View>
                            <View style={{ flex: 1, marginLeft: 12 }}>
                                <Text style={st.sectionTitleNoMargin}>Menü Erişimi</Text>
                                <Text style={st.sectionSubNoMargin}>Kullanıcının modül yetkilerini yönetin</Text>
                            </View>
                        </View>

                        {MODULE_CONFIG.map((mod, index) => {
                            const { total, active, ids } = getModuleStats(mod.keys);
                            if (total === 0) return null; // No permissions match this module
                            const isExpanded = !!expandedModules[index];
                            const isActive = active > 0;

                            return (
                                <View key={index} style={[st.moduleCard, isActive ? { borderColor: mod.colors[0] } : null]}>
                                    <TouchableOpacity 
                                        style={st.moduleHeader} 
                                        activeOpacity={0.7} 
                                        onPress={() => toggleModuleExpander(index)}
                                    >
                                        <LinearGradient colors={isActive ? mod.colors : ['#E2E8F0', '#CBD5E1']} style={st.moduleIconWrap}>
                                            <Icon name={mod.icon} size={24} color={isActive ? '#FFF' : '#64748B'} />
                                        </LinearGradient>
                                        
                                        <View style={st.moduleInfo}>
                                            <Text style={st.moduleTitle}>{mod.title}</Text>
                                            <View style={st.moduleCounterWrap}>
                                                <View style={[st.moduleCounterBadge, isActive ? { backgroundColor: mod.colors[0] } : null]}>
                                                    <Text style={[st.moduleCounterTxt, isActive ? { color: '#FFF' } : null]}>{active}</Text>
                                                </View>
                                                <Text style={st.moduleTotalTxt}>/ {total}</Text>
                                            </View>
                                        </View>
                                        <Icon name={isExpanded ? "chevron-up" : "chevron-down"} size={24} color="#94A3B8" />
                                    </TouchableOpacity>

                                    {isExpanded && (
                                        <View style={st.moduleContent}>
                                            <TouchableOpacity 
                                                style={st.selectAllBtn} 
                                                onPress={() => selectAllInModule(mod.keys)}
                                            >
                                                <Text style={st.selectAllBtnTxt}>Hepsini {active === total ? 'Kaldır' : 'Seç'}</Text>
                                            </TouchableOpacity>

                                            <View style={st.permList}>
                                                {mod.keys.map(k => {
                                                    const permObj = permissionsList.find(p => p.key === k);
                                                    if (!permObj) return null;
                                                    const isSelected = form.permissions.includes(permObj.id);
                                                    return (
                                                        <TouchableOpacity 
                                                            key={permObj.id} 
                                                            style={[st.permRow, isSelected && { backgroundColor: mod.colors[0] + '15', borderColor: mod.colors[0] + '40' }]}
                                                            activeOpacity={0.7}
                                                            onPress={() => togglePermission(permObj.id)}
                                                        >
                                                            <Icon name={isSelected ? "checkbox-marked-circle" : "checkbox-blank-circle-outline"} size={22} color={isSelected ? mod.colors[0] : "#CBD5E1"} style={{ marginRight: 12 }} />
                                                            <Text style={[st.permLabel, isSelected && { color: mod.colors[0] }]}>{permObj.label.toUpperCase()}</Text>
                                                        </TouchableOpacity>
                                                    );
                                                })}
                                            </View>
                                        </View>
                                    )}
                                </View>
                            );
                        })}
                    </View>
                )}

                <View style={{ height: 40 }} />
            </ScrollView>

            <View style={st.footer}>
                <TouchableOpacity style={st.saveBtn} onPress={handleSave} disabled={saving}>
                    {saving ? <ActivityIndicator color="#FFF" /> : <Text style={st.saveBtnTxt}>Kaydet</Text>}
                </TouchableOpacity>
            </View>
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    scrollContent: { padding: 16 },
    
    section: { backgroundColor: '#FFF', borderRadius: 24, padding: 20, marginBottom: 16, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.03, shadowRadius: 8, elevation: 2, borderWidth: 1, borderColor: '#F1F5F9' },
    sectionHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 20 },
    sectionTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginLeft: 10 },
    sectionSub: { fontSize: 13, color: '#64748B', marginBottom: 16 },

    inputGroup: { marginBottom: 20 },
    label: { fontSize: 12, fontWeight: '800', color: '#64748B', marginBottom: 8, textTransform: 'uppercase' },
    input: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 16, fontSize: 15, color: '#0F172A', fontWeight: '600' },
    inputError: { borderColor: '#EF4444', backgroundColor: '#FEF2F2' },
    errorTxt: { color: '#EF4444', fontSize: 11, marginTop: 4, fontWeight: '600' },

    rolesRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
    roleChip: { paddingHorizontal: 16, paddingVertical: 10, borderRadius: 12, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0' },
    roleChipActive: { backgroundColor: '#EEF2FF', borderColor: '#6366F1' },
    roleChipTxt: { fontSize: 13, fontWeight: '700', color: '#64748B' },
    roleChipTxtActive: { color: '#6366F1' },

    switchRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 10, paddingTop: 20, borderTopWidth: 1, borderTopColor: '#F1F5F9' },
    switchLabel: { fontSize: 14, fontWeight: '800', color: '#0F172A' },
    switchSub: { fontSize: 12, color: '#94A3B8', marginTop: 2 },

    iconBoxPurp: { width: 44, height: 44, borderRadius: 14, backgroundColor: '#8B5CF6', alignItems: 'center', justifyContent: 'center', shadowColor: '#8B5CF6', shadowOffset: {width:0,height:4}, shadowOpacity: 0.3, shadowRadius: 8, elevation: 4 },
    sectionTitleNoMargin: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    sectionSubNoMargin: { fontSize: 12, color: '#64748B', fontWeight: '500', marginTop: 2 },

    moduleCard: { backgroundColor: '#F8FAFC', borderRadius: 20, borderWidth: 2, borderColor: '#E2E8F0', marginBottom: 12, overflow: 'hidden' },
    moduleHeader: { flexDirection: 'row', alignItems: 'center', padding: 16 },
    moduleIconWrap: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.1, shadowRadius: 4, elevation: 2 },
    moduleInfo: { flex: 1, marginLeft: 16 },
    moduleTitle: { fontSize: 15, fontWeight: '800', color: '#1E293B', marginBottom: 6 },
    moduleCounterWrap: { flexDirection: 'row', alignItems: 'center' },
    moduleCounterBadge: { backgroundColor: '#E2E8F0', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 8, minWidth: 24, alignItems: 'center' },
    moduleCounterTxt: { fontSize: 11, fontWeight: '900', color: '#64748B' },
    moduleTotalTxt: { fontSize: 11, fontWeight: '700', color: '#94A3B8', marginLeft: 6 },
    
    moduleContent: { padding: 16, paddingTop: 0, borderTopWidth: 1, borderTopColor: '#E2E8F0', backgroundColor: '#FFF' },
    selectAllBtn: { alignSelf: 'flex-start', backgroundColor: '#F1F5F9', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, marginVertical: 12 },
    selectAllBtnTxt: { fontSize: 10, fontWeight: '800', color: '#64748B', textTransform: 'uppercase', letterSpacing: 0.5 },
    
    permList: { gap: 8 },
    permRow: { flexDirection: 'row', alignItems: 'center', padding: 12, borderRadius: 12, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0' },
    permLabel: { fontSize: 11, fontWeight: '800', color: '#64748B' },

    footer: { padding: 16, backgroundColor: '#FFF', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingBottom: 110 },
    saveBtn: { backgroundColor: '#6366F1', paddingVertical: 16, borderRadius: 20, alignItems: 'center', shadowColor: '#6366F1', shadowOffset: {width:0,height:6}, shadowOpacity: 0.3, shadowRadius: 12, elevation: 8 },
    saveBtnTxt: { color: '#FFF', fontSize: 16, fontWeight: '800' }
});
