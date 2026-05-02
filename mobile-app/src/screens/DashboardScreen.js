import React, { useContext, useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, ActivityIndicator } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { AuthContext } from '../context/AuthContext';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function DashboardScreen({ navigation }) {
    const { userInfo, logout } = useContext(AuthContext);
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const modules = [
        { id: 1, title: 'Araçlar', icon: 'car', route: 'VehiclesTab' },
        { id: 2, title: 'Personeller', icon: 'account-group', route: 'Personnel' },
        { id: 3, title: 'Müşteriler', icon: 'office-building', route: 'Customers' },
        { id: 4, title: 'Seferler', icon: 'map-marker-path', route: 'Trips' },
    ];

    useEffect(() => {
        fetchDashboardData();
    }, []);

    const fetchDashboardData = async () => {
        try {
            setLoading(true);
            setError(null);
            const response = await api.get('/v1/dashboard');
            if (response.data.success) {
                setData(response.data.data);
            } else {
                setError(response.data.message || 'Veriler alınamadı.');
            }
        } catch (err) {
            setError('Veriler yüklenirken bir sorun oluştu.');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <ActivityIndicator size="large" color="#000" />
            </SafeAreaView>
        );
    }

    if (error) {
        return (
            <SafeAreaView style={[styles.container, { justifyContent: 'center', alignItems: 'center' }]}>
                <Text style={{ marginTop: 16, color: '#000' }}>{error}</Text>
                <TouchableOpacity onPress={fetchDashboardData} style={{ marginTop: 24, padding: 12, backgroundColor: '#000', borderRadius: 8 }}>
                    <Text style={{ color: '#fff' }}>Tekrar Dene</Text>
                </TouchableOpacity>
            </SafeAreaView>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <Text style={{color:'#000', fontSize: 24, padding: 20}}>DashboardScreen - Temiz Sayfa</Text>
            <TouchableOpacity onPress={logout} style={{margin: 20}}><Text style={{color:'red'}}>Çıkış Yap</Text></TouchableOpacity>
            <View style={{flexDirection: 'row', flexWrap: 'wrap', gap: 10, padding: 20}}>
                {modules.map((mod) => (
                    <TouchableOpacity key={mod.id} onPress={() => navigation.navigate(mod.route)} style={{padding: 10, borderWidth: 1, borderColor: '#ccc'}}>
                        <Text style={{color: '#000'}}>{mod.title}</Text>
                    </TouchableOpacity>
                ))}
            </View>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#ffffff',
    }
});
