import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator, TouchableOpacity, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';
import { colors, spacing, radius } from '../theme';
import { Header, EmptyState } from '../components';

export default function FinanceScreen({ navigation }) {
    const [summary, setSummary] = useState(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const fetchSummary = async (isRefresh = false) => {
        try {
            if (isRefresh) setRefreshing(true); else setLoading(true);
            setError(null);
            const response = await api.get('/v1/finance/summary');
            if (response.data.success) setSummary(response.data.data);
            else setError(response.data.message || 'Veri alınamadı.');
        } catch (err) {
            if (err.response?.status === 403) setError('Bu alanı görüntüleme yetkiniz yok.');
            else setError('Bağlantı hatası.');
        } finally {
            setLoading(false); setRefreshing(false);
        }
    };

    useEffect(() => { fetchSummary(); }, []);

    const formatCurrency = (amount) => {
        if (amount === null || amount === undefined) return '- (Yetki Yok)';
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(amount);
    };

    if (loading && !refreshing) {
        return (
            <View style={[s.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#000" />
            </View>
        );
    }

    if (error && !refreshing && !summary) {
        return (
            <View style={s.container}>
                <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
                    <Text style={{color:'#000'}}>{error}</Text>
                    <TouchableOpacity onPress={() => fetchSummary()} style={s.retryBtn}>
                        <Text style={s.retryTxt}>Tekrar Dene</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    }

    return (
        <SafeAreaView style={s.container}>
            <Text style={{color:'#000', fontSize: 24, padding: 20}}>FinanceScreen - Temiz Sayfa</Text>
        </SafeAreaView>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' },
    retryBtn: { marginTop: 20, padding: 12, backgroundColor: '#000', borderRadius: 8 },
    retryTxt: { color: '#fff', fontWeight: 'bold' }
});
