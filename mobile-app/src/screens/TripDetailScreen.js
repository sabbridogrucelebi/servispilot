import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, ActivityIndicator, Text } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';
import { colors, spacing, radius } from '../theme';
import { Header, EmptyState } from '../components';

export default function TripDetailScreen({ route, navigation }) {
    const { tripId } = route.params;
    const [trip, setTrip] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchTripDetail = async () => {
        try {
            setLoading(true); setError(null);
            const response = await api.get(`/v1/trips/${tripId}`);
            if (response.data.success) setTrip(response.data.data);
            else setError(response.data.message || 'Veri alınamadı.');
        } catch (err) {
            setError('Bağlantı hatası.');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchTripDetail(); }, [tripId]);

    if (loading) {
        return (
            <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#000" />
            </View>
        );
    }

    if (error) {
        return (
            <View style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Text style={{color:'#000'}}>{error}</Text>
            </View>
        );
    }

// InfoRow removed

    return (
        <SafeAreaView style={styles.container}>
            <Text style={{color:'#000', fontSize: 24, padding: 20}}>TripDetailScreen - Temiz Sayfa</Text>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' }
});
