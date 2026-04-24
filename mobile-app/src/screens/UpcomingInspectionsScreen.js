import React, { useState, useCallback, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Linking, Alert, Image, Animated, Easing, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import * as Clipboard from 'expo-clipboard';
import api from '../api/axios';

const { width: SCREEN_WIDTH, height: SCREEN_HEIGHT } = Dimensions.get('window');

const Star = ({ size, top, left, delay, duration }) => {
    const opacity = useRef(new Animated.Value(0.05)).current;
    useEffect(() => {
        Animated.loop(
            Animated.sequence([
                Animated.timing(opacity, { toValue: 0.3, duration: duration / 2, easing: Easing.inOut(Easing.ease), useNativeDriver: true, delay }),
                Animated.timing(opacity, { toValue: 0.05, duration: duration / 2, easing: Easing.inOut(Easing.ease), useNativeDriver: true })
            ])
        ).start();
    }, []);
    return <Animated.View style={{ position: 'absolute', top, left, width: size, height: size, borderRadius: size/2, backgroundColor: '#fff', opacity }} />;
};

const ShootingStar = () => {
    const anim = useRef(new Animated.Value(0)).current;
    useEffect(() => {
        const shoot = () => {
            anim.setValue(0);
            Animated.timing(anim, { toValue: 1, duration: 1200, easing: Easing.linear, useNativeDriver: true }).start(() => {
                setTimeout(shoot, 3000 + Math.random() * 5000);
            });
        };
        setTimeout(shoot, 1000 + Math.random() * 3000);
    }, []);
    const translateX = anim.interpolate({ inputRange: [0, 1], outputRange: [SCREEN_WIDTH, -SCREEN_WIDTH/2] });
    const translateY = anim.interpolate({ inputRange: [0, 1], outputRange: [-100, SCREEN_HEIGHT/2] });
    const opacity = anim.interpolate({ inputRange: [0, 0.1, 0.8, 1], outputRange: [0, 1, 1, 0] });
    return <Animated.View style={{ position: 'absolute', width: 2, height: 80, backgroundColor: 'rgba(255,255,255,0.8)', transform: [{ rotate: '45deg' }, { translateX }, { translateY }], opacity, shadowColor: '#fff', shadowOpacity: 1, shadowRadius: 4 }} />;
};

const StarryBackground = () => {
    const stars = React.useMemo(() => {
        return Array.from({length: 300}).map((_, i) => ({ 
            id: i, 
            size: Math.random() * 1.5 + 0.5, 
            top: Math.floor(Math.random() * 100) + '%', 
            left: Math.floor(Math.random() * 100) + '%', 
            delay: Math.random() * 3000, 
            duration: 2000 + Math.random() * 4000 
        }));
    }, []);
    
    return (
        <View style={StyleSheet.absoluteFillObject}>
            <LinearGradient colors={['#020617', '#0A192F', '#09163F', '#020617']} style={StyleSheet.absoluteFillObject} start={{x: 0, y: 0}} end={{x: 1, y: 1}} />
            {stars.map(s => <Star key={s.id} {...s} />)}
            <ShootingStar />
        </View>
    );
};

const AnimatedRandevuButton = ({ onPress }) => {
    const btnStars = React.useMemo(() => {
        return Array.from({length: 20}).map((_, i) => ({ 
            id: i, 
            size: Math.random() * 1.5 + 0.5, 
            top: Math.floor(Math.random() * 100) + '%', 
            left: Math.floor(Math.random() * 100) + '%', 
            delay: Math.random() * 2000, 
            duration: 1000 + Math.random() * 2000 
        }));
    }, []);

    return (
        <TouchableOpacity onPress={onPress} activeOpacity={0.8} style={{marginTop: 4}}>
            <View style={[s.actionBtn, { overflow: 'hidden', shadowColor: '#0A192F', marginTop: 0 }]}>
                <LinearGradient 
                    colors={['#020617', '#0A192F', '#09163F']} 
                    style={s.actionBtnInner} 
                    start={{ x: 0, y: 0 }} 
                    end={{ x: 1, y: 1 }}
                >
                    {btnStars.map(st => <Star key={`bstar_${st.id}`} {...st} />)}
                    <Text style={[s.actionBtnText, {color: '#fff', textTransform: 'capitalize'}]}>Randevu Al</Text>
                    <Icon name="open-in-new" size={18} color="#fff" style={{ marginLeft: 6 }} />
                </LinearGradient>
            </View>
        </TouchableOpacity>
    );
};

export default function UpcomingInspectionsScreen({ navigation }) {
    const [vehicles, setVehicles] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetchVehicles = async () => {
        try {
            const r = await api.get('/vehicles?filter=upcoming_inspection');
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

    const openTuvturk = () => {
        Linking.openURL('https://www.tuvturk.com.tr/hizmetlerimiz/hizli-islemler/arac-muayene-randevusu-alma');
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

    const renderVehicle = ({ item }) => {
        let daysLeftText = '';
        let isOverdue = false;
        let formattedDate = '-';

        if (item.inspection_date) {
            const inspDate = new Date(item.inspection_date);
            const today = new Date();
            inspDate.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            
            const diffTime = inspDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            formattedDate = inspDate.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
            
            if (diffDays < 0) {
                isOverdue = true;
                daysLeftText = `${Math.abs(diffDays)} GÜN GECİKTİ!`;
            } else {
                daysLeftText = `${diffDays} GÜN KALDI`;
            }
        }

        const imgSource = item.image_url ? { uri: item.image_url } : getVehicleImage(item.vehicle_type || item.type);

        return (
            <TouchableOpacity style={s.card} activeOpacity={0.8} onPress={()=>navigation.navigate('VehicleDetail', { vehicle: item })}>
                <View style={s.cardInner}>
                    
                    {/* Sol Resim Kutusu */}
                    <View style={s.cardImageWrap}>
                        <Image source={imgSource} style={{width: 90, height: 90, resizeMode: 'contain'}} />
                    </View>

                    {/* Sağ Bilgi Alanı */}
                    <View style={s.cardContent}>
                        <Text style={s.plateText}>{item.plate}</Text>
                        
                        <View style={{flexDirection: 'row', alignItems: 'center', marginBottom: 6, gap: 6}}>
                            <Text style={[s.brandText, {marginBottom: 0, flexShrink: 1}]} numberOfLines={1}>{item.brand_model || 'Marka / Model Belirtilmemiş'}</Text>
                            {!!item.model_year && (
                                <View style={{backgroundColor: '#FEF08A', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 6}}>
                                    <Text style={{fontSize: 10, fontWeight: '500', color: '#854D0E'}}>{item.model_year}</Text>
                                </View>
                            )}
                        </View>
                        
                        <View style={[s.metaRow, { flexWrap: 'wrap', rowGap: 4, marginBottom: 8 }]}>
                            <View style={{flexDirection: 'row', alignItems: 'center', flexShrink: 1}}>
                                <Icon name="account" size={12} color="#64748B" />
                                <Text style={[s.metaText, {flexShrink: 1}]} numberOfLines={2}>{item.driver || 'Atanmamış'}</Text>
                            </View>
                            
                            {/* Seri No Satırı */}
                            <Icon name="circle-small" size={14} color="#CBD5E1" style={{marginHorizontal: 4}} />
                            <View style={{flexDirection: 'row', alignItems: 'center'}}>
                                <Icon name="barcode-scan" size={12} color="#3B82F6" />
                                <Text style={[s.metaText, {color: '#3B82F6', fontWeight: '700', marginLeft: 2}]}>{item.license_serial_no || 'Belirtilmemiş'}</Text>
                                {item.license_serial_no && item.license_serial_no !== '-' && (
                                    <TouchableOpacity style={{marginLeft: 6, padding: 2}} onPress={() => copyToClipboard(item.license_serial_no, 'Seri Numarası')}>
                                        <Icon name="content-copy" size={14} color="#94A3B8" />
                                    </TouchableOpacity>
                                )}
                            </View>
                        </View>

                    </View>
                </View>

                {/* Alt Kısım: Muayene Bilgisi ve Aksiyonlar */}
                <View style={[s.statusBox, isOverdue && { borderColor: '#FECACA', backgroundColor: '#FEF2F2' }]}>
                    <View style={s.statusTop}>
                        <View style={s.dateWrap}>
                            <Icon name={isOverdue ? "alert-circle" : "clock-outline"} size={16} color={isOverdue ? "#EF4444" : "#D97706"} style={{marginTop: 2}} />
                            <View style={{marginLeft: 6}}>
                                <Text style={[s.dateLabel, isOverdue && { color: '#EF4444' }]}>SON TARİH</Text>
                                <Text style={[s.dateValue, isOverdue && { color: '#EF4444' }]}>{formattedDate}</Text>
                            </View>
                        </View>
                        <Text style={[s.daysLeft, isOverdue && { color: '#DC2626' }]}>{daysLeftText}</Text>
                    </View>

                    <AnimatedRandevuButton onPress={openTuvturk} />
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={s.container}>
            <StarryBackground />
            <SafeAreaView edges={['top']} style={{ flex: 1 }}>
                
                <View style={[s.appBar, { marginTop: 20 }]}>
                    <TouchableOpacity onPress={() => navigation.goBack()} hitSlop={{ top: 15, bottom: 15, left: 15, right: 15 }}>
                        <View style={s.darkGlassBtn}>
                            <Icon name="chevron-left" size={26} color="#fff" />
                        </View>
                    </TouchableOpacity>
                    <View style={{alignItems: 'center', flex: 1, marginRight: 44}}>
                        <Text style={s.appBarTitle}>Yaklaşan Muayeneler</Text>
                    </View>
                </View>

                {loading ? (
                    <View style={s.center}>
                        <ActivityIndicator size="large" color="#F59E0B" />
                        <Text style={s.loadingText}>Canlı veriler çekiliyor...</Text>
                    </View>
                ) : (
                    <FlatList
                        data={vehicles}
                        keyExtractor={i => i.id.toString()}
                        renderItem={renderVehicle}
                        contentContainerStyle={s.listContainer}
                        showsVerticalScrollIndicator={false}
                        refreshControl={
                            <RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); fetchVehicles(); }} tintColor="#F59E0B" />
                        }
                        ListEmptyComponent={
                            <View style={s.emptyState}>
                                <View style={s.emptyIconWrap}>
                                    <Icon name="check-decagram" size={64} color="#10B981" />
                                </View>
                                <Text style={s.emptyTitle}>Harika Haber!</Text>
                                <Text style={s.emptyDesc}>Yakın zamanda muayenesi bitecek veya gecikmiş aracınız bulunmuyor.</Text>
                            </View>
                        }
                    />
                )}
            </SafeAreaView>

            {/* Custom Floating Bottom Bar */}
            <View style={s.bottomBarWrap}>
                <View style={s.bottomBar}>
                    <TouchableOpacity style={s.tabItem} onPress={() => navigation.goBack()}>
                        <Icon name="menu" size={24} color="#94A3B8" />
                        <Text style={s.tabText}>Menü</Text>
                    </TouchableOpacity>
                    
                    <TouchableOpacity style={s.tabItem} onPress={() => navigation.navigate('Vehicles')}>
                        <Icon name="car" size={24} color="#2563EB" />
                        <Text style={[s.tabText, {color: '#2563EB'}]}>Araçlar</Text>
                    </TouchableOpacity>

                    <View style={s.fabWrap}>
                        <TouchableOpacity style={s.fabBtn} activeOpacity={0.8} onPress={() => Alert.alert('Bilgi', 'Yeni kayıt ekleme modülü')}>
                            <Icon name="plus" size={32} color="#fff" />
                        </TouchableOpacity>
                    </View>

                    <TouchableOpacity style={s.tabItem}>
                        <Icon name="message-outline" size={24} color="#94A3B8" />
                        <Text style={s.tabText}>Mesaj</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={s.tabItem}>
                        <Icon name="account-outline" size={24} color="#94A3B8" />
                        <Text style={s.tabText}>Profil</Text>
                    </TouchableOpacity>
                </View>
            </View>

        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#020617' },
    
    // Header
    appBar: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 24, paddingTop: 10, paddingBottom: 24 },
    appBarTitle: { fontSize: 20, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    appBarSub: { fontSize: 10, color: '#F59E0B', marginTop: 4, fontWeight: '800', textTransform: 'uppercase', letterSpacing: 1.5 },
    darkGlassBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
    
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    loadingText: { marginTop: 12, fontSize: 13, color: '#94A3B8', fontWeight: '700' },
    
    // List
    listContainer: { paddingHorizontal: 20, paddingBottom: 130 },
    card: { backgroundColor: '#fff', borderRadius: 30, padding: 16, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4 },
    cardInner: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    cardImageWrap: { width: 90, height: 70, borderRadius: 20, alignItems: 'center', justifyContent: 'center', marginRight: 16, overflow: 'hidden' },
    cardContent: { flex: 1 },
    plateText: { fontSize: 16, fontWeight: '800', color: '#0F172A', letterSpacing: 0.3, marginBottom: 2 },
    brandText: { fontSize: 11, fontWeight: '600', color: '#64748B', marginBottom: 6 },
    metaRow: { flexDirection: 'row', alignItems: 'center' },
    metaText: { fontSize: 10, fontWeight: '600', color: '#94A3B8', marginLeft: 4 },
    
    // Status Box (Alt Kısım)
    statusBox: { borderRadius: 20, padding: 16, borderWidth: 1, borderColor: '#F1F5F9', backgroundColor: '#F8FAFC' },
    statusTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 },
    dateWrap: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: '#fff', paddingHorizontal: 10, paddingVertical: 8, borderRadius: 12, borderWidth: 1, borderColor: '#F1F5F9' },
    dateLabel: { fontSize: 10, fontWeight: '800', color: '#B45309', letterSpacing: 0.5 },
    dateValue: { fontSize: 14, fontWeight: '900', color: '#B45309', marginTop: 2 },
    daysLeft: { fontSize: 20, fontWeight: '900', color: '#0F172A' },
    
    actionBtn: { overflow: 'hidden', borderRadius: 16, shadowColor: '#1E3A8A', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.3, shadowRadius: 12, elevation: 8, marginTop: 4 },
    actionBtnInner: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16 },
    actionBtnText: { fontSize: 13, fontWeight: '900', letterSpacing: 1 },

    emptyState: { alignItems: 'center', paddingVertical: 80, paddingHorizontal: 32 },
    emptyIconWrap: { width: 140, height: 140, borderRadius: 70, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', marginBottom: 30, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.05, shadowRadius: 24, elevation: 8 },
    emptyTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', marginBottom: 12 },
    emptyDesc: { fontSize: 16, color: '#64748B', textAlign: 'center', lineHeight: 24, marginBottom: 36, fontWeight: '500' },
    
    // Bottom Bar
    bottomBarWrap: { position: 'absolute', bottom: 30, left: 20, right: 20, zIndex: 100 },
    bottomBar: { 
        backgroundColor: '#fff', 
        borderRadius: 30, 
        flexDirection: 'row', 
        justifyContent: 'space-between', 
        alignItems: 'center', 
        paddingHorizontal: 20, 
        height: 70,
        shadowColor: '#0A1A3A', 
        shadowOffset: {width:0, height:10}, 
        shadowOpacity: 0.1, 
        shadowRadius: 20, 
        elevation: 10 
    },
    tabItem: { alignItems: 'center', justifyContent: 'center', width: 50 },
    tabText: { fontSize: 10, fontWeight: '700', color: '#94A3B8', marginTop: 4 },
    fabWrap: { width: 64, height: 64, borderRadius: 32, backgroundColor: '#2563EB', alignItems: 'center', justifyContent: 'center', marginTop: -32, shadowColor: '#2563EB', shadowOffset: {width:0, height:8}, shadowOpacity: 0.4, shadowRadius: 16, elevation: 12 },
    fabBtn: { width: '100%', height: '100%', alignItems: 'center', justifyContent: 'center' }
});
