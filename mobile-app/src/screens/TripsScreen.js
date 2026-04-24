import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import api from '../api/axios';

export default function TripsScreen({ navigation }) {
    const [trips, setTrips] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    const fetch = async () => {
        try { setTrips((await api.get('/trips')).data); }
        catch (e) { console.error(e); }
        finally { setLoading(false); setRefreshing(false); }
    };
    useEffect(() => { fetch(); }, []);

    const renderItem = ({ item }) => (
        <View style={s.card}>
            <View style={s.cardTop}>
                <View style={s.dateBadge}>
                    <Icon name="calendar-month-outline" size={20} color="#3B82F6" />
                    <Text style={s.dateText}>{item.date}</Text>
                </View>
                <View style={s.plateBadge}>
                    <Text style={s.plateText}>{item.vehicle_plate}</Text>
                </View>
            </View>
            <View style={s.route}>
                <View style={s.line} />
                <View style={s.point}>
                    <View style={[s.dot, { borderColor: '#3B82F6' }]} />
                    <Text style={s.routeMain} numberOfLines={1}>{item.customer}</Text>
                </View>
                <View style={s.point}>
                    <View style={[s.dot, { borderColor: '#10B981' }]} />
                    <Text style={s.routeSub} numberOfLines={1}>{item.route}</Text>
                </View>
            </View>
            <View style={s.driverRow}>
                <Icon name="steering" size={22} color="#94A3B8" />
                <Text style={s.driverText}>{item.driver}</Text>
            </View>
        </View>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.hRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.hBtn}>
                            <Icon name="chevron-left" size={28} color="#fff" />
                        </TouchableOpacity>
                        <Text style={s.hTitle}>Sefer Kayıtları</Text>
                        <TouchableOpacity style={s.hBtn}>
                            <Icon name="magnify" size={26} color="#fff" />
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>
            {loading ? <View style={s.center}><ActivityIndicator size="large" color="#3B82F6" /></View> :
            <FlatList data={trips} keyExtractor={i=>i.id.toString()} renderItem={renderItem} contentContainerStyle={s.list} showsVerticalScrollIndicator={false}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={()=>{setRefreshing(true);fetch();}} tintColor="#3B82F6" />}
                ListEmptyComponent={<View style={s.empty}><Icon name="map-marker-off-outline" size={64} color="#CBD5E1" /><Text style={s.emptyT}>Sefer bulunamadı.</Text></View>}
            />}
        </View>
    );
}

const s = StyleSheet.create({
    container:{flex:1,backgroundColor:'#F8FAFC'},
    header:{paddingBottom:24,paddingHorizontal:24,borderBottomLeftRadius:32,borderBottomRightRadius:32},
    hRow:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',paddingTop:16},
    hBtn:{alignItems:'center',justifyContent:'center'},
    hTitle:{fontSize:24,fontWeight:'900',color:'#fff',letterSpacing:-0.5},
    list:{padding:20,paddingBottom:120},
    card:{backgroundColor:'#fff',borderRadius:24,padding:24,marginBottom:16,shadowColor:'#0F172A',shadowOffset:{width:0,height:6},shadowOpacity:0.05,shadowRadius:16,elevation:4},
    cardTop:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',marginBottom:24},
    dateBadge:{flexDirection:'row',alignItems:'center',backgroundColor:'#EFF6FF',paddingHorizontal:12,paddingVertical:8,borderRadius:12,gap:8},
    dateText:{color:'#3B82F6',fontWeight:'800',fontSize:14},
    plateBadge:{backgroundColor:'#F8FAFC',paddingHorizontal:14,paddingVertical:8,borderRadius:12,borderWidth:1,borderColor:'#E2E8F0'},
    plateText:{color:'#0F172A',fontWeight:'900',fontSize:15,letterSpacing:1},
    route:{paddingLeft:8,marginBottom:24},
    line:{position:'absolute',left:15,top:18,bottom:18,width:2,backgroundColor:'#E2E8F0'},
    point:{flexDirection:'row',alignItems:'center',marginVertical:12},
    dot:{width:16,height:16,borderRadius:8,marginRight:16,borderWidth:4,backgroundColor:'#ffffff'},
    routeMain:{fontSize:18,fontWeight:'900',color:'#0F172A',flex:1},
    routeSub:{fontSize:15,color:'#64748B',fontWeight:'600',flex:1},
    driverRow:{flexDirection:'row',alignItems:'center',backgroundColor:'#F8FAFC',padding:16,borderRadius:16,gap:12,borderWidth:1,borderColor:'#F1F5F9'},
    driverText:{color:'#475569',fontSize:15,fontWeight:'700'},
    center:{flex:1,justifyContent:'center',alignItems:'center'},
    empty:{alignItems:'center',paddingVertical:48},
    emptyT:{color:'#94A3B8',fontSize:16,marginTop:16,fontWeight:'600'},
});
