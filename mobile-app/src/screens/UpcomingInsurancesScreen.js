import React, { useState, useCallback, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Alert, Image, Animated, Easing, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import * as Clipboard from 'expo-clipboard';
import api from '../api/axios';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

// StarryBackground and UI components removed

export default function UpcomingInsurancesScreen({ navigation }) {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchVehicles = async () => {
        try {
            const r = await api.get('/vehicles?filter=upcoming_insurance');
            setVehicles(r.data.vehicles || []);
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Araç bilgileri alınamadı.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useFocusEffect(useCallback(() => {
        fetchVehicles();
    }, []));

    const copyToClipboard = async (text, label) => {
        if (!text || text === '-') return;
        await Clipboard.setStringAsync(text);
        Alert.alert('Kopyalandı', `${label} panoya kopyalandı: ${text}`);
    };

    const getVehicleImage = (type) => {
        const t = (type || '').toLowerCase();
        if (t.includes('minibüs')) return require('../../assets/arac_tipleri/servis_pilot_minibus.png');
        if (t.includes('midibüs')) return require('../../assets/arac_tipleri/servis_pilot_midibus.png');
        if (t.includes('otobüs')) return require('../../assets/arac_tipleri/servis_pilot_otobus.png');
        if (t.includes('panelvan')) return require('../../assets/arac_tipleri/servis_pilot_panelvan.png');
        if (t.includes('kamyonet')) return require('../../assets/arac_tipleri/servis_pilot_kamyonet.png');
        if (t.includes('binek') || t.includes('sedan') || t.includes('taksi')) return require('../../assets/arac_tipleri/servis_pilot_taksi.png');
        
        return require('../../assets/arac_tipleri/servis_pilot_panelvan.png');
    };

// renderVehicle removed

    return (
        <SafeAreaView style={s.container}>
            <Text style={{color:'#000', fontSize: 24, padding: 20}}>UpcomingInsurancesScreen - Temiz Sayfa</Text>
        </SafeAreaView>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#FFFFFF' }
});
