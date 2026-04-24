import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, ScrollView, TextInput } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function TripsScreen({ navigation }) {
    const [trips, setTrips] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const fetchTrips = async () => {
        try {
            setError(null);
            const response = await api.get('/v1/trips');
            if (response.data.success) {
                setTrips(response.data.data);
            } else {
                setError(response.data.message || 'Sefer verileri alınamadı.');
            }
        } catch (err) {
            console.error(err);
            setError('Bağlantı hatası oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => { fetchTrips(); }, []);
    const onRefresh = () => { setRefreshing(true); fetchTrips(); };

    const renderItem = ({ item }) => {
        // Mock icons based on route/customer name
        const c = (item.customer_name || '').toLowerCase();
        let logoBg = '#EFF6FF';
        let logoColor = '#3B82F6';
        let iconName = 'hexagon-multiple';
        
        if (c.includes('renka')) { logoBg = '#FEF2F2'; logoColor = '#EF4444'; iconName = 'fire'; }
        else if (c.includes('eco')) { logoBg = '#ECFDF5'; logoColor = '#10B981'; iconName = 'leaf'; }
        else if (c.includes('mavi')) { logoBg = '#FFFBEB'; logoColor = '#F59E0B'; iconName = 'cube-outline'; }

        // Mock status
        let statusTxt = 'Aktif';
        let statusColor = '#10B981';
        let statusBg = '#ECFDF5';
        if (item.id % 4 === 1) { statusTxt = 'Planlandı'; statusColor = '#3B82F6'; statusBg = '#EFF6FF'; }
        else if (item.id % 4 === 2) { statusTxt = 'Tamamlandı'; statusColor = '#8B5CF6'; statusBg = '#F3E8FF'; }
        else if (item.id % 4 === 3) { statusTxt = 'Beklemede'; statusColor = '#F59E0B'; statusBg = '#FEF3C7'; }

        return (
            <TouchableOpacity 
                style={s.card} 
                activeOpacity={0.85}
                onPress={() => navigation.navigate('TripDetail', { tripId: item.id })}
            >
                <View style={s.cardTop}>
                    <View style={[s.logoBox, { backgroundColor: logoBg }]}>
                        <Icon name={iconName} size={32} color={logoColor} />
                    </View>
                    <View style={s.cardHeaderContent}>
                        <View style={s.chrRow}>
                            <Text style={s.cTitle} numberOfLines={1}>{item.customer_name || 'Müşteri Adı'}</Text>
                            <View style={[s.statusBadge, { backgroundColor: statusBg }]}>
                                <View style={[s.statusDot, { backgroundColor: statusColor }]} />
                                <Text style={[s.statusTxt, { color: statusColor }]}>{statusTxt}</Text>
                            </View>
                        </View>
                        <View style={s.chrRow}>
                            <Text style={s.cRoute}>{item.route_name || 'Güzergah Belirtilmemiş'}</Text>
                            <View style={s.plateBadge}>
                                <Text style={s.plateTxt}>{item.vehicle_plate || 'PLAKA'}</Text>
                            </View>
                        </View>
                        <View style={s.cMetaRow}>
                            <Icon name="calendar-blank" size={14} color="#94A3B8" />
                            <Text style={s.cMetaTxt}>{item.trip_date || 'Tarih Yok'}</Text>
                            <Icon name="account-outline" size={14} color="#94A3B8" style={{marginLeft: 12}} />
                            <Text style={s.cMetaTxt} numberOfLines={1}>{item.driver_name || 'Bilinmiyor'}</Text>
                        </View>
                    </View>
                </View>
                
                <View style={s.divider} />
                
                <View style={s.cardBottom}>
                    <View style={s.cbItem}>
                        <Icon name="map-marker-distance" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>Mesafe</Text>
                            <Text style={s.cbVal}>512 km</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={s.cbItem}>
                        <Icon name="clock-outline" size={16} color="#94A3B8" />
                        <View>
                            <Text style={s.cbLabel}>Tahmini Varış</Text>
                            <Text style={s.cbVal}>16:45</Text>
                        </View>
                    </View>
                    <View style={s.cbDivider} />
                    <View style={[s.cbItem, {flex:1.2}]}>
                        <Icon name="account-group-outline" size={16} color="#94A3B8" />
                        <View style={{flex:1}}>
                            <Text style={s.cbLabel}>Yolcu / Yük</Text>
                            <Text style={s.cbVal}>32 Yolcu</Text>
                        </View>
                        <Icon name="chevron-right" size={20} color="#CBD5E1" />
                    </View>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={s.header} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <SafeAreaView edges={['top']}>
                    <View style={s.hRow}>
                        <TouchableOpacity style={s.menuBtn}>
                            <Icon name="menu" size={24} color="#fff" />
                        </TouchableOpacity>
                        <View style={s.hTitleWrap}>
                            <Text style={s.hTitle}>Seferler</Text>
                            <View style={s.hSubWrap}>
                                <View style={s.dotBlue} />
                                <Text style={s.hSub}>Canlı operasyon yönetimi</Text>
                            </View>
                        </View>
                        <TouchableOpacity style={s.addBtnBlue}>
                            <Icon name="plus" size={24} color="#fff" />
                        </TouchableOpacity>
                    </View>

                    {/* KPI Cards */}
                    <View style={s.kpiWrapper}>
                        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.kpiScroll}>
                            
                            <View style={[s.kpiCard, { borderColor: 'rgba(59, 130, 246, 0.4)' }]}>
                                <LinearGradient colors={['rgba(37,99,235,0.15)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: '#1E3A8A'}]}><Icon name="map-marker-path" size={18} color="#60A5FA" /></View>
                                    <Text style={s.kpiLabel}>Bugünkü Sefer</Text>
                                </View>
                                <Text style={s.kpiValue}>18</Text>
                                <Text style={[s.kpiSub, {color: '#10B981'}]}>▲ %12,5 dününe göre</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(20, 184, 166, 0.4)' }]}>
                                <LinearGradient colors={['rgba(13,148,136,0.15)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: '#134E4A'}]}><Icon name="crosshairs-gps" size={18} color="#2DD4BF" /></View>
                                    <Text style={s.kpiLabel}>Aktif Rota</Text>
                                </View>
                                <Text style={s.kpiValue}>6</Text>
                                <Text style={[s.kpiSub, {color: '#10B981'}]}>● Canlı takipte</Text>
                            </View>

                            <View style={[s.kpiCard, { borderColor: 'rgba(245, 158, 11, 0.4)' }]}>
                                <LinearGradient colors={['rgba(217,119,6,0.15)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                                <View style={s.kpiHeader}>
                                    <View style={[s.kpiIconWrap, {backgroundColor: '#78350F'}]}><Icon name="hourglass" size={18} color="#FBBF24" /></View>
                                    <Text style={s.kpiLabel}>Bekleyen</Text>
                                </View>
                                <Text style={s.kpiValue}>4</Text>
                                <Text style={[s.kpiSub, {color: '#F59E0B'}]}>● Onay bekliyor</Text>
                            </View>

                        </ScrollView>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.filterWrapper}>
                <View style={s.filterInner}>
                    <View style={s.searchBar}>
                        <Icon name="magnify" size={20} color="#94A3B8" />
                        <TextInput style={s.searchInput} placeholder="Sefer, müşteri, güzergah ara..." />
                        <TouchableOpacity style={s.filterBtn}>
                            <Icon name="filter-variant" size={18} color="#0F172A" />
                            <Text style={s.filterBtnTxt}>Filtrele</Text>
                            <View style={s.filterBadge}><Text style={s.filterBadgeTxt}>2</Text></View>
                        </TouchableOpacity>
                    </View>

                    <View style={s.pillRow}>
                        <TouchableOpacity style={s.pillBtn}>
                            <Icon name="calendar-blank" size={16} color="#64748B" />
                            <View>
                                <Text style={s.pillLbl}>Tarih</Text>
                                <Text style={s.pillTxt}>24.04.2026</Text>
                            </View>
                            <Icon name="chevron-down" size={16} color="#94A3B8" />
                        </TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}>
                            <Icon name="earth" size={16} color="#64748B" />
                            <View>
                                <Text style={s.pillLbl}>Durum</Text>
                                <Text style={s.pillTxt}>Tümü</Text>
                            </View>
                            <Icon name="chevron-down" size={16} color="#94A3B8" />
                        </TouchableOpacity>
                        <TouchableOpacity style={s.pillBtn}>
                            <Icon name="bus" size={16} color="#64748B" />
                            <View>
                                <Text style={s.pillLbl}>Araç</Text>
                                <Text style={s.pillTxt}>Tümü</Text>
                            </View>
                            <Icon name="chevron-down" size={16} color="#94A3B8" />
                        </TouchableOpacity>
                        <TouchableOpacity style={s.sortBtn} onPress={fetchTrips}><Icon name="refresh" size={20} color="#64748B" /></TouchableOpacity>
                    </View>
                </View>
            </View>

            {loading ? (
                <View style={s.centerContent}><ActivityIndicator size="large" color="#3B82F6" /></View>
            ) : error ? (
                <View style={s.centerContent}>
                    <Icon name="alert-circle-outline" size={48} color="#ef4444" />
                    <Text style={[s.emptyT, {color: '#ef4444', marginTop: 12}]}>{error}</Text>
                    <TouchableOpacity onPress={fetchTrips} style={{ marginTop: 24, padding: 12, backgroundColor: '#3B82F6', borderRadius: 8 }}>
                        <Text style={{ color: '#fff', fontWeight: 'bold' }}>Tekrar Dene</Text>
                    </TouchableOpacity>
                </View>
            ) : (
                <FlatList 
                    data={trips} 
                    keyExtractor={i=>i.id.toString()} 
                    renderItem={renderItem} 
                    contentContainerStyle={s.list} 
                    showsVerticalScrollIndicator={false}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor="#3B82F6" />}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <Icon name="map-marker-off-outline" size={64} color="#CBD5E1" />
                            <Text style={s.emptyT}>Sefer bulunamadı.</Text>
                        </View>
                    }
                />
            )}
        </View>
    );
}

const s = StyleSheet.create({
    container:{flex:1,backgroundColor:'#F4F7FA'},
    
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden', paddingBottom: 40 },
    hRow:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',paddingTop:16, paddingHorizontal: 24, marginBottom: 24},
    menuBtn:{width:46,height:46,borderRadius:16,backgroundColor:'rgba(255,255,255,0.08)',alignItems:'center',justifyContent:'center',borderWidth:1,borderColor:'rgba(255,255,255,0.15)'},
    hTitleWrap:{alignItems:'center'},
    hTitle:{fontSize:22,fontWeight:'900',color:'#fff',letterSpacing:-0.5},
    hSubWrap:{flexDirection:'row',alignItems:'center',gap:6,marginTop:2},
    dotBlue:{width:6,height:6,borderRadius:3,backgroundColor:'#3B82F6'},
    hSub:{color:'#94A3B8',fontSize:12,fontWeight:'600'},
    addBtnBlue:{width:46,height:46,borderRadius:16,backgroundColor:'rgba(59, 130, 246, 0.4)',alignItems:'center',justifyContent:'center',borderWidth:1,borderColor:'#3B82F6'},

    kpiWrapper: { height: 130 },
    kpiScroll: { paddingHorizontal: 20, gap: 14 },
    kpiCard: { backgroundColor: 'rgba(30,41,59,0.4)', borderRadius: 24, padding: 18, width: 170, borderWidth: 1 },
    kpiHeader: { flexDirection: 'row', alignItems: 'center', gap: 10, marginBottom: 12 },
    kpiIconWrap: { width: 36, height: 36, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    kpiLabel: { fontSize: 13, fontWeight: '700', color: '#CBD5E1' },
    kpiValue: { fontSize: 26, fontWeight: '900', color: '#fff', letterSpacing: -0.5, marginBottom: 6 },
    kpiSub: { fontSize: 11, fontWeight: '700' },

    filterWrapper: { paddingHorizontal: 20, marginTop: -40, zIndex: 20 },
    filterInner: { backgroundColor: '#fff', borderRadius: 24, padding: 16, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.08, shadowRadius: 24, elevation: 8, borderWidth: 1, borderColor: '#F1F5F9' },
    searchBar: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 16, paddingHorizontal: 16, height: 50, borderWidth: 1, borderColor: '#E2E8F0', marginBottom: 12 },
    searchInput: { flex: 1, fontSize: 14, color: '#0F172A', fontWeight: '600', marginLeft: 8 },
    filterBtn: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingLeft: 12, borderLeftWidth: 1, borderLeftColor: '#E2E8F0' },
    filterBtnTxt: { fontSize: 13, fontWeight: '700', color: '#0F172A' },
    filterBadge: { backgroundColor: '#3B82F6', width: 16, height: 16, borderRadius: 8, alignItems: 'center', justifyContent: 'center', position: 'absolute', top: -8, right: -4 },
    filterBadgeTxt: { color: '#fff', fontSize: 9, fontWeight: 'bold' },
    
    pillRow: { flexDirection: 'row', gap: 8 },
    pillBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 8, paddingVertical: 8, backgroundColor: '#F8FAFC', borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    pillLbl: { fontSize: 9, color: '#64748B', fontWeight: '700', marginBottom: 2 },
    pillTxt: { fontSize: 11, fontWeight: '800', color: '#0F172A' },
    sortBtn: { width: 42, height: 42, borderRadius: 12, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#E2E8F0' },

    list:{padding:20,paddingTop:24,paddingBottom:120},
    card:{backgroundColor:'#fff',borderRadius:24,padding:16,marginBottom:16,shadowColor:'#0A1A3A',shadowOffset:{width:0,height:8},shadowOpacity:0.06,shadowRadius:20,elevation:4,borderWidth:1,borderColor:'#F1F5F9'},
    cardTop:{flexDirection:'row',alignItems:'flex-start',marginBottom:12},
    logoBox:{width:60,height:60,borderRadius:16,alignItems:'center',justifyContent:'center',marginRight:16},
    cardHeaderContent:{flex:1},
    chrRow:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',marginBottom:6},
    cTitle:{fontSize:16,fontWeight:'900',color:'#0F172A',flex:1,letterSpacing:-0.5},
    statusBadge:{flexDirection:'row',alignItems:'center',paddingHorizontal:8,paddingVertical:4,borderRadius:10},
    statusDot:{width:6,height:6,borderRadius:3,marginRight:4},
    statusTxt:{fontSize:10,fontWeight:'800',letterSpacing:0.5},
    cRoute:{fontSize:13,fontWeight:'600',color:'#64748B',flex:1},
    plateBadge:{backgroundColor:'#F1F5F9',paddingHorizontal:10,paddingVertical:4,borderRadius:8,borderWidth:1,borderColor:'#E2E8F0'},
    plateTxt:{fontSize:12,fontWeight:'900',color:'#0F172A',letterSpacing:1},
    cMetaRow:{flexDirection:'row',alignItems:'center',marginTop:4},
    cMetaTxt:{fontSize:11,fontWeight:'600',color:'#94A3B8',marginLeft:4},
    
    divider:{height:1,backgroundColor:'#F1F5F9',marginBottom:16},

    cardBottom: { flexDirection: 'row', alignItems: 'flex-start' },
    cbItem: { flex: 1, flexDirection: 'row', alignItems: 'flex-start', gap: 8 },
    cbLabel: { fontSize: 10, fontWeight: '600', color: '#94A3B8', marginBottom: 2 },
    cbVal: { fontSize: 11, fontWeight: '800', color: '#334155' },
    cbDivider: { width: 1, height: 30, backgroundColor: '#F1F5F9', marginHorizontal: 8 },

    centerContent:{flex:1,justifyContent:'center',alignItems:'center'},
    empty:{alignItems:'center',paddingVertical:48},
    emptyT:{color:'#94A3B8',fontSize:16,marginTop:16,fontWeight:'600'},
});
