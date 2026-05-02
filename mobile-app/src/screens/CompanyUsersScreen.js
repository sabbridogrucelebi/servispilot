import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import api from '../api/axios';
import { Header } from '../components';

export default function CompanyUsersScreen({ navigation }) {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const fetchUsers = async () => {
        try {
            setError(null);
            const response = await api.get('/v1/company-users');
            setUsers(response.data.data || []);
        } catch (err) {
            setError(err.response?.status === 403 ? 'Bu işlem için yetkiniz yok.' : 'Veriler alınamadı.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useFocusEffect(
        useCallback(() => {
            fetchUsers();
        }, [])
    );

    const onRefresh = () => {
        setRefreshing(true);
        fetchUsers();
    };

    const handleDelete = (user) => {
        Alert.alert(
            "Kullanıcıyı Sil",
            `${user.name} isimli kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamaz.`,
            [
                { text: "Vazgeç", style: "cancel" },
                { 
                    text: "Sil", 
                    style: "destructive",
                    onPress: async () => {
                        try {
                            const res = await api.delete(`/v1/company-users/${user.id}`);
                            if (res.data.success) {
                                setUsers(prev => prev.filter(u => u.id !== user.id));
                            } else {
                                Alert.alert("Hata", res.data.message || "Silinemedi.");
                            }
                        } catch (e) {
                            Alert.alert("Hata", e.response?.data?.message || "Silme işlemi başarısız.");
                        }
                    }
                }
            ]
        );
    };

    const getRoleName = (role) => {
        switch (role) {
            case 'company_admin': return 'Firma Yöneticisi';
            case 'operation': return 'Operasyon';
            case 'accounting': return 'Muhasebe';
            case 'viewer': return 'Gözlemci';
            default: return role;
        }
    };

    const renderItem = ({ item }) => {
        const initials = item.name.substring(0, 2).toUpperCase();
        
        return (
            <TouchableOpacity 
                style={st.card} 
                activeOpacity={0.7} 
                onPress={() => navigation.navigate('CompanyUserForm', { userId: item.id })}
            >
                <View style={st.cardHeader}>
                    <View style={st.avatar}>
                        <Text style={st.avatarTxt}>{initials}</Text>
                    </View>
                    <View style={st.cardInfo}>
                        <Text style={st.cardTitle}>{item.name}</Text>
                        <Text style={st.cardEmail}>{item.email}</Text>
                    </View>
                    <View style={st.statusWrap}>
                        <View style={[st.statusDot, { backgroundColor: item.is_active ? '#10B981' : '#94A3B8' }]} />
                        <Text style={[st.statusTxt, { color: item.is_active ? '#10B981' : '#94A3B8' }]}>
                            {item.is_active ? 'Aktif' : 'Pasif'}
                        </Text>
                    </View>
                </View>
                
                <View style={st.cardFooter}>
                    <View style={st.roleBadge}>
                        <Icon name="shield-account" size={14} color="#6366F1" />
                        <Text style={st.roleTxt}>{getRoleName(item.role)}</Text>
                    </View>
                    <TouchableOpacity 
                        style={st.deleteBtn} 
                        onPress={() => handleDelete(item)}
                        hitSlop={{top:10, bottom:10, left:10, right:10}}
                    >
                        <Icon name="trash-can-outline" size={20} color="#EF4444" />
                    </TouchableOpacity>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <SafeAreaView style={st.container}>
            <Header 
                title="Kullanıcılar" 
                subtitle="Erişim Kontrolü" 
                onBack={() => navigation.goBack()} 
            />

            {loading ? (
                <View style={st.center}><ActivityIndicator size="large" color="#3B82F6" /></View>
            ) : error ? (
                <View style={st.center}>
                    <Icon name="alert-circle-outline" size={48} color="#EF4444" />
                    <Text style={st.errorTxt}>{error}</Text>
                </View>
            ) : (
                <FlatList
                    data={users}
                    keyExtractor={i => i.id.toString()}
                    renderItem={renderItem}
                    contentContainerStyle={st.listContent}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
                    ListEmptyComponent={
                        <View style={st.emptyState}>
                            <Icon name="account-group" size={64} color="#CBD5E1" />
                            <Text style={st.emptyTitle}>Kullanıcı Bulunamadı</Text>
                            <Text style={st.emptyDesc}>Henüz sisteme eklenmiş bir kullanıcı yok.</Text>
                        </View>
                    }
                />
            )}

            <TouchableOpacity 
                style={st.fab} 
                activeOpacity={0.8} 
                onPress={() => navigation.navigate('CompanyUserForm')}
            >
                <Icon name="plus" size={28} color="#FFF" />
            </TouchableOpacity>
        </SafeAreaView>
    );
}

const st = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
    errorTxt: { color: '#64748B', marginTop: 10, textAlign: 'center', fontSize: 16 },
    listContent: { padding: 16, paddingBottom: 100 },
    
    card: { backgroundColor: '#FFF', borderRadius: 20, padding: 16, marginBottom: 12, shadowColor: '#000', shadowOffset: {width:0,height:2}, shadowOpacity: 0.05, shadowRadius: 8, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', alignItems: 'center' },
    avatar: { width: 48, height: 48, borderRadius: 16, backgroundColor: '#EEF2FF', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#E0E7FF' },
    avatarTxt: { fontSize: 18, fontWeight: '900', color: '#6366F1' },
    cardInfo: { flex: 1, marginLeft: 12 },
    cardTitle: { fontSize: 16, fontWeight: '800', color: '#0F172A' },
    cardEmail: { fontSize: 13, color: '#64748B', fontWeight: '500', marginTop: 2 },
    statusWrap: { alignItems: 'flex-end', justifyContent: 'center' },
    statusDot: { width: 8, height: 8, borderRadius: 4, marginBottom: 4 },
    statusTxt: { fontSize: 10, fontWeight: '800', textTransform: 'uppercase' },

    cardFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 16, paddingTop: 16, borderTopWidth: 1, borderTopColor: '#F1F5F9' },
    roleBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#EEF2FF', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 10 },
    roleTxt: { fontSize: 12, fontWeight: '700', color: '#6366F1', marginLeft: 6 },
    deleteBtn: { padding: 6, backgroundColor: '#FEF2F2', borderRadius: 10 },

    emptyState: { alignItems: 'center', justifyContent: 'center', paddingVertical: 60 },
    emptyTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginTop: 16 },
    emptyDesc: { fontSize: 14, color: '#64748B', marginTop: 8, textAlign: 'center' },

    fab: { position: 'absolute', right: 20, bottom: 110, width: 64, height: 64, borderRadius: 32, backgroundColor: '#6366F1', alignItems: 'center', justifyContent: 'center', shadowColor: '#6366F1', shadowOffset: {width:0,height:8}, shadowOpacity: 0.4, shadowRadius: 16, elevation: 10 }
});
