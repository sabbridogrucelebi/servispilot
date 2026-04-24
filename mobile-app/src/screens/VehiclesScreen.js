import React, { useState, useCallback, useEffect, useRef } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Modal, TextInput, Alert, KeyboardAvoidingView, Platform, ScrollView, Image, Animated, Easing, Dimensions } from 'react-native';

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
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useFocusEffect } from '@react-navigation/native';
import api from '../api/axios';

export default function VehiclesScreen({ navigation }) {
    const [vehicles, setVehicles] = useState([]);
    const [kpi, setKpi] = useState({ total: 0, upcoming_inspection: 0, upcoming_insurance: 0, active_count: 0 });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    
    const [page, setPage] = useState(1);
    const [hasMore, setHasMore] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);

    const [searchQuery, setSearchQuery] = useState('');
    const [debouncedSearch, setDebouncedSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [showFilter, setShowFilter] = useState(false);
    
    const [modalVisible, setModalVisible] = useState(false);
    const [saving, setSaving] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [actionItem, setActionItem] = useState(null); 
    const emptyForm = { plate:'', brand:'', model:'', vehicle_type:'', model_year:'', current_km:'', engine_no:'', chassis_no:'', fuel_type:'', color:'', seat_count:'', is_active:true };
    const [formData, setFormData] = useState(emptyForm);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(searchQuery);
        }, 500);
        return () => clearTimeout(handler);
    }, [searchQuery]);

    const fetchVehicles = async (pageNumber = 1) => {
        if (pageNumber === 1 && !refreshing) setLoading(true);
        else if (pageNumber > 1) setLoadingMore(true);

        try {
            const params = { page: pageNumber, per_page: 20 };
            if (statusFilter !== 'all') params.status = statusFilter;
            if (debouncedSearch) params.search = debouncedSearch;

            const r = await api.get('/v1/vehicles', { params });
            const newVehicles = r.data.data.vehicles || [];
            
            if (pageNumber === 1) setVehicles(newVehicles);
            else setVehicles(prev => [...prev, ...newVehicles]);

            setKpi(r.data.data.kpi || { total:0, upcoming_inspection:0, upcoming_insurance:0, active_count:0 });
            
            const meta = r.data.meta;
            if (meta) {
                setHasMore(meta.current_page < meta.last_page);
                setPage(meta.current_page);
            } else {
                setHasMore(false);
            }
        } catch(e) { 
            if (e.response && e.response.status === 403) {
                Alert.alert("Erişim Engellendi", "Bu alanı görüntüleme yetkiniz yok.");
            } else if (!e.response) {
                // Network Error
                Alert.alert("Bağlantı Hatası", "Sunucuya ulaşılamıyor. Lütfen API URL (IP adresi) ayarlarınızı kontrol edin.");
            } else {
                console.error(e); 
            }
        } finally { 
            setLoading(false); 
            setRefreshing(false); 
            setLoadingMore(false);
        }
    };

    useFocusEffect(useCallback(() => { 
        fetchVehicles(1); 
    }, [statusFilter, debouncedSearch]));

    const loadMore = () => {
        if (hasMore && !loadingMore && !loading) fetchVehicles(page + 1);
    };

    const openAdd = () => { setEditingId(null); setFormData(emptyForm); setModalVisible(true); };
    const openEdit = (v) => { setEditingId(v.id); setFormData({ plate:v.plate||'', brand:v.brand||'', model:v.model||'', vehicle_type:v.vehicle_type||'', model_year:v.model_year?String(v.model_year):'', current_km:v.current_km?String(v.current_km):'', engine_no:v.engine_no||'', chassis_no:v.chassis_no||'', fuel_type:v.fuel_type||'', color:v.color||'', seat_count:v.seat_count?String(v.seat_count):'', is_active:v.is_active }); setModalVisible(true); setActionItem(null); };

    const save = async () => {
        if(!formData.plate){Alert.alert('Eksik Bilgi','Araç plakası zorunludur.');return;}
        setSaving(true);
        try { editingId ? await api.put(`/vehicles/${editingId}`,formData) : await api.post('/vehicles',formData); setModalVisible(false); fetchVehicles(1); }
        catch(e){Alert.alert('İşlem Başarısız',e.response?.data?.message||'Sistemsel bir hata oluştu.');}
        finally{setSaving(false);}
    };

    const del = (id,plate) => { setActionItem(null); const go=async()=>{try{await api.delete(`/vehicles/${id}`);fetchVehicles(1);}catch{Alert.alert('İşlem Başarısız','Kayıt silinemedi.');}}; Platform.OS==='web'?window.confirm(`${plate} plakalı aracı silmek istediğinize emin misiniz?`)&&go():Alert.alert('Kayıt Silinecek',`${plate} plakalı aracı silmek istediğinize emin misiniz?`,[{text:'Vazgeç',style:'cancel'},{text:'Sil',style:'destructive',onPress:go}]); };

    const fmtKm = k => (!k||k===0) ? '0 km' : Number(k).toLocaleString('tr-TR')+' km';
    const filterLabel = statusFilter==='active'?'Sadece Aktif':statusFilter==='passive'?'Sadece Pasif':'Tüm Araçlar';

    const getWarningIcon = (item) => {
        const check = (date) => date && (new Date(date) - new Date() < 1000 * 60 * 60 * 24 * 15);
        if (check(item.inspection_date) || check(item.kasko_end_date)) {
            return <Icon name="alert" size={16} color="#F59E0B" style={{marginLeft: 6}} />;
        }
        return null;
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

    const renderRow = ({ item }) => {
        const imgSource = item.image_url ? { uri: item.image_url } : getVehicleImage(item.vehicle_type || item.type);

        return (
            <TouchableOpacity style={s.card} activeOpacity={0.8} onPress={()=>navigation.navigate('VehicleDetail', { vehicle: item })} onLongPress={()=>setActionItem(item)}>
                <View style={s.cardInner}>
                    
                    {/* Left Image Box */}
                    <View style={s.cardImageWrap}>
                        <Image source={imgSource} style={{width: 90, height: 90, resizeMode: 'contain'}} />
                    </View>

                {/* Center Content */}
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
                    
                    <View style={[s.metaRow, { flexWrap: 'wrap', rowGap: 4 }]}>
                        <View style={{flexDirection: 'row', alignItems: 'center', flexShrink: 1}}>
                            <Icon name="account" size={12} color="#64748B" />
                            <Text style={[s.metaText, {flexShrink: 1}]} numberOfLines={2}>{item.driver || 'Atanmamış'}</Text>
                        </View>
                        <Icon name="circle-small" size={14} color="#CBD5E1" style={{marginHorizontal: 4}} />
                        <View style={{flexDirection: 'row', alignItems: 'center'}}>
                            <Icon name="speedometer" size={12} color="#10B981" />
                            <Text style={[s.metaText, {color: '#10B981', fontWeight: '700', marginLeft: 2}]}>{fmtKm(item.current_km)}</Text>
                        </View>
                    </View>
                </View>

                {/* Right Area */}
                <View style={s.cardRight}>
                    <View style={s.statusWrap}>
                        <View style={[s.statusDot, { backgroundColor: item.status === 'active' ? '#10B981' : '#EF4444' }]} />
                        <Text style={[s.statusTxt, { color: item.status === 'active' ? '#10B981' : '#EF4444' }]}>
                            {item.status === 'active' ? 'Aktif' : 'Pasif'}
                        </Text>
                    </View>
                    <Icon name="chevron-right" size={24} color="#CBD5E1" style={{marginTop: 12}} />
                </View>

            </View>
        </TouchableOpacity>
        );
    };

    const renderFooter = () => {
        if (!loadingMore) return null;
        return <View style={s.footerLoader}><ActivityIndicator size="small" color="#0F172A" /></View>;
    };

    return (
        <View style={s.container}>
            {/* Neo Fleet Command Premium Header */}
            <StarryBackground />
            <SafeAreaView edges={['top']} style={{ flex: 1 }}>
                
                <View style={[s.appBar, { marginTop: 20 }]}>
                    <View>
                        <Text style={s.appBarTitle}>Filo Merkezi</Text>
                        <Text style={s.appBarSub}>Canlı filo durumu ve operasyon takibi</Text>
                    </View>
                    <View style={{flexDirection: 'row', gap: 10}}>
                        <TouchableOpacity style={s.darkGlassBtn}>
                            <Icon name="bell-outline" size={22} color="#fff" />
                        </TouchableOpacity>
                        <TouchableOpacity onPress={openAdd} style={s.darkGlassBtn}>
                            <Icon name="plus" size={24} color="#fff" />
                        </TouchableOpacity>
                    </View>
                </View>

                {/* KPI Summary - Centered */}
                <View style={s.kpiWrapper}>
                    <View style={s.kpiRow}>
                        
                        <View style={s.kpiCard}>
                            <View style={[s.kpiIconWrap, {backgroundColor: '#EFF6FF'}]}>
                                <Icon name="car" size={18} color="#3B82F6" />
                            </View>
                            <Text style={s.kpiLabel}>Toplam Araç</Text>
                            <Text style={s.kpiValue}>{kpi.total}</Text>
                        </View>

                        <TouchableOpacity style={s.kpiCard} onPress={() => navigation.navigate('UpcomingInspections')} activeOpacity={0.85}>
                            <View style={[s.kpiIconWrap, {backgroundColor: '#FFF7ED'}]}>
                                <Icon name="shield-check" size={18} color="#EA580C" />
                            </View>
                            <Text style={s.kpiLabel}>Muayene Yaklaşan</Text>
                            <Text style={s.kpiValue}>{kpi.upcoming_inspection}</Text>
                        </TouchableOpacity>

                        <TouchableOpacity style={s.kpiCard} onPress={() => navigation.navigate('UpcomingInsurances')} activeOpacity={0.85}>
                            <View style={[s.kpiIconWrap, {backgroundColor: '#FEF2F2'}]}>
                                <Icon name="shield-alert" size={18} color="#DC2626" />
                            </View>
                            <Text style={s.kpiLabel}>Sigorta Yaklaşan</Text>
                            <Text style={s.kpiValue}>{kpi.upcoming_insurance}</Text>
                        </TouchableOpacity>

                    </View>
                </View>

                {/* White Bottom Sheet Content */}
                <View style={s.mainContent}>
                    {/* Search & Filter */}
                    <View style={s.searchWrap}>
                        <View style={s.searchBox}>
                            <Icon name="magnify" size={22} color="#94A3B8"/>
                            <TextInput style={s.searchInput} placeholder="Plaka, marka, model veya şoför ara..." placeholderTextColor="#94A3B8" value={searchQuery} onChangeText={setSearchQuery}/>
                            {searchQuery.length>0 && (
                                <TouchableOpacity onPress={()=>setSearchQuery('')} hitSlop={{top:10,bottom:10,left:10,right:10}}>
                                    <Icon name="close-circle" size={18} color="#CBD5E1"/>
                                </TouchableOpacity>
                            )}
                        </View>
                        <View>
                            <TouchableOpacity style={[s.filterBtn, statusFilter !== 'all' && s.filterBtnActive]} onPress={()=>setShowFilter(!showFilter)}>
                                <Icon name="filter-variant" size={20} color={statusFilter !== 'all' ? '#2563EB' : '#0F172A'}/>
                            </TouchableOpacity>
                            {showFilter && (
                                <View style={s.filterMenu}>
                                {['all','active','passive'].map(v=>(
                                    <TouchableOpacity key={v} style={[s.filterMenuItem, statusFilter===v && s.filterMenuItemActive]} onPress={()=>{setStatusFilter(v);setShowFilter(false);}}>
                                        <Text style={[s.filterMenuText, statusFilter===v && {color:'#2563EB', fontWeight: '600'}]}>
                                            {v==='all' ? 'Tüm Araçlar' : v==='active' ? 'Sadece Aktif' : 'Sadece Pasif'}
                                        </Text>
                                        {statusFilter===v && <Icon name="check" size={18} color="#2563EB"/>}
                                    </TouchableOpacity>
                                ))}
                            </View>
                        )}
                    </View>
                </View>

                {/* List Content */}
                {loading ? (
                    <View style={s.center}><ActivityIndicator size="large" color="#0F172A"/></View>
                ) : (
                    <FlatList 
                        data={vehicles} 
                        keyExtractor={i=>i.id.toString()} 
                        renderItem={renderRow} 
                        contentContainerStyle={s.listContainer} 
                        showsVerticalScrollIndicator={false} 
                        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={()=>{setRefreshing(true);fetchVehicles(1);}} tintColor="#0F172A"/>} 
                        onEndReached={loadMore} 
                        onEndReachedThreshold={0.5} 
                        ListFooterComponent={renderFooter} 
                        ListEmptyComponent={
                            <View style={s.emptyState}>
                                <View style={s.emptyIconWrap}>
                                    <Icon name="car-off" size={64} color="#CBD5E1"/>
                                </View>
                                <Text style={s.emptyTitle}>Kayıt Bulunamadı</Text>
                                <Text style={s.emptyDesc}>{searchQuery ? 'Arama kriterlerinize uyan araç sistemde bulunmuyor.' : 'Henüz filonuza araç eklenmemiş.'}</Text>
                                {!searchQuery && (
                                    <TouchableOpacity style={s.emptyBtn} onPress={openAdd}>
                                        <Text style={s.emptyBtnTxt}>İlk Aracı Ekle</Text>
                                    </TouchableOpacity>
                                )}
                            </View>
                        }
                    />
                )}
                </View>
            </SafeAreaView>

            {/* Action Sheet Modal */}
            <Modal visible={!!actionItem} transparent animationType="fade" onRequestClose={()=>setActionItem(null)}>
                <TouchableOpacity style={s.modalOverlay} activeOpacity={1} onPress={()=>setActionItem(null)}>
                    <View style={s.actionSheet}>
                        <View style={s.sheetHandle}/>
                        <View style={s.sheetHeader}>
                            <Text style={s.sheetTitle}>{actionItem?.plate}</Text>
                            <Text style={s.sheetSub}>{actionItem?.brand_model}</Text>
                        </View>
                        <TouchableOpacity style={s.sheetAction} onPress={()=>{const item=actionItem; setActionItem(null); navigation.navigate('VehicleDetail', { vehicle: item });}}>
                            <View style={[s.sheetActionIcon, {backgroundColor: '#F1F5F9'}]}>
                                <Icon name="file-document-outline" size={22} color="#0F172A"/>
                            </View>
                            <Text style={s.sheetActionTxt}>Detayları Görüntüle</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.sheetAction} onPress={()=>{if(actionItem)openEdit(actionItem);}}>
                            <View style={[s.sheetActionIcon, {backgroundColor: '#FEF9C3'}]}>
                                <Icon name="pencil-outline" size={22} color="#D97706"/>
                            </View>
                            <Text style={s.sheetActionTxt}>Aracı Düzenle</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.sheetAction} onPress={()=>{if(actionItem)del(actionItem.id,actionItem.plate);}}>
                            <View style={[s.sheetActionIcon, {backgroundColor: '#FEF2F2'}]}>
                                <Icon name="trash-can-outline" size={22} color="#DC2626"/>
                            </View>
                            <Text style={[s.sheetActionTxt, {color: '#DC2626'}]}>Aracı Sil</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </Modal>

            {/* Form Modal */}
            <Modal visible={modalVisible} animationType="slide" transparent>
                <View style={s.modalOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS==='ios'?'padding':'height'} style={{flex:1,justifyContent:'flex-end'}}>
                        <View style={s.formSheet}>
                            <View style={s.formHeader}>
                                <Text style={s.formTitle}>{editingId?'Aracı Düzenle':'Sisteme Araç Ekle'}</Text>
                                <TouchableOpacity onPress={()=>setModalVisible(false)} hitSlop={{top:10,bottom:10,left:10,right:10}} style={s.closeBtn}>
                                    <Icon name="close" size={20} color="#64748B"/>
                                </TouchableOpacity>
                            </View>
                            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{paddingBottom: 40}}>
                                <FI label="Plaka Numarası" val={formData.plate} onSet={t=>setFormData({...formData,plate:t})} caps/>
                                <View style={s.formRow}><FI label="Marka" val={formData.brand} onSet={t=>setFormData({...formData,brand:t})} half/><FI label="Model" val={formData.model} onSet={t=>setFormData({...formData,model:t})} half/></View>
                                <View style={s.formRow}><FI label="Araç Tipi" val={formData.vehicle_type} onSet={t=>setFormData({...formData,vehicle_type:t})} half/><FI label="Model Yılı" val={formData.model_year} onSet={t=>setFormData({...formData,model_year:t})} half num/></View>
                                <View style={s.formRow}><FI label="Güncel Kilometre" val={formData.current_km} onSet={t=>setFormData({...formData,current_km:t})} half num/><FI label="Koltuk Kapasitesi" val={formData.seat_count} onSet={t=>setFormData({...formData,seat_count:t})} half num/></View>
                                <View style={s.formRow}><FI label="Motor No" val={formData.engine_no} onSet={t=>setFormData({...formData,engine_no:t})} half/><FI label="Şasi No" val={formData.chassis_no} onSet={t=>setFormData({...formData,chassis_no:t})} half/></View>
                                <View style={s.formRow}><FI label="Yakıt Tipi" val={formData.fuel_type} onSet={t=>setFormData({...formData,fuel_type:t})} half/><FI label="Renk" val={formData.color} onSet={t=>setFormData({...formData,color:t})} half/></View>
                                <View style={s.formSwitchWrap}>
                                    <Text style={s.formLabel}>Operasyon Durumu</Text>
                                    <TouchableOpacity style={[s.formSwitch, formData.is_active ? s.formSwitchOn : s.formSwitchOff]} onPress={()=>setFormData({...formData,is_active:!formData.is_active})}>
                                        <View style={[s.formSwitchKnob, formData.is_active ? s.formSwitchKnobOn : s.formSwitchKnobOff]} />
                                    </TouchableOpacity>
                                </View>
                                <TouchableOpacity style={s.submitBtn} onPress={save} disabled={saving}>
                                    {saving?<ActivityIndicator color="#fff"/>:<Text style={s.submitBtnTxt}>{editingId?'Değişiklikleri Uygula':'Aracı Kaydet'}</Text>}
                                </TouchableOpacity>
                            </ScrollView>
                        </View>
                    </KeyboardAvoidingView>
                </View>
            </Modal>
        </View>
    );
}

function FI({label,val,onSet,half,num,caps}){
    return(
        <View style={[s.inputGroup,half&&{flex:1}]}>
            <Text style={s.formLabel}>{label}</Text>
            <TextInput style={s.input} value={val} onChangeText={onSet} keyboardType={num?'number-pad':'default'} autoCapitalize={caps?'characters':'sentences'}/>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#020617' },
    
    // Header
    appBar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, paddingTop: 10, paddingBottom: 24 },
    appBarTitle: { fontSize: 26, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    appBarSub: { fontSize: 12, color: 'rgba(255,255,255,0.7)', marginTop: 4, fontWeight: '600', letterSpacing: 0.2 },
    darkGlassBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.1)' },
    
    // KPI
    kpiWrapper: { alignItems: 'center', marginBottom: 20 },
    kpiRow: { flexDirection: 'row', justifyContent: 'center', gap: 12 },
    kpiCard: { 
        backgroundColor: '#fff', 
        borderRadius: 24, 
        paddingVertical: 14,
        paddingHorizontal: 10,
        width: 105, 
        height: 110,
        shadowColor: '#000', 
        shadowOffset: {width:0, height:12}, 
        shadowOpacity: 0.15, 
        shadowRadius: 16, 
        elevation: 8, 
        alignItems: 'center',
        justifyContent: 'space-between',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,1)',
        borderBottomWidth: 4,
        borderBottomColor: '#E2E8F0', // 3D effect base
    },
    kpiIconWrap: { width: 32, height: 32, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    kpiLabel: { fontSize: 11, fontWeight: '700', color: '#64748B', lineHeight: 14, textAlign: 'center', height: 28 },
    kpiValue: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },

    // Main Content (White Sheet)
    mainContent: { flex: 1, backgroundColor: '#F4F7FA', borderTopLeftRadius: 40, borderTopRightRadius: 40, paddingTop: 24, shadowColor: '#000', shadowOffset: {width:0, height:-10}, shadowOpacity: 0.1, shadowRadius: 20, elevation: 15 },

    // Search
    searchWrap: { flexDirection: 'row', paddingHorizontal: 20, gap: 12, marginBottom: 20, zIndex: 10 },
    searchBox: { flex: 1, flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 24, paddingHorizontal: 20, height: 56, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.05, shadowRadius: 16, elevation: 4 },
    searchInput: { flex: 1, fontSize: 14, color: '#0F172A', marginLeft: 12, fontWeight: '600' },
    filterBtn: { width: 56, height: 56, borderRadius: 20, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', shadowColor: '#0A1A3A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.08, shadowRadius: 16, elevation: 6 },
    filterBtnActive: { backgroundColor: '#F8FAFC' },
    
    filterMenu: { position: 'absolute', top: 66, right: 0, backgroundColor: '#fff', borderRadius: 24, padding: 10, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.15, shadowRadius: 30, elevation: 12, minWidth: 220 },
    filterMenuItem: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 16, paddingHorizontal: 16, borderRadius: 16 },
    filterMenuItemActive: { backgroundColor: '#F8FAFC' },
    filterMenuText: { fontSize: 15, fontWeight: '700', color: '#334155' },

    // List
    listContainer: { paddingHorizontal: 20, paddingBottom: 140 },
    card: { backgroundColor: '#fff', borderRadius: 30, padding: 16, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4 },
    cardInner: { flexDirection: 'row', alignItems: 'center' },
    cardImageWrap: { width: 90, height: 70, borderRadius: 20, alignItems: 'center', justifyContent: 'center', marginRight: 16, overflow: 'hidden' },
    cardContent: { flex: 1 },
    plateText: { fontSize: 16, fontWeight: '800', color: '#0F172A', letterSpacing: 0.3, marginBottom: 2 },
    brandText: { fontSize: 11, fontWeight: '600', color: '#64748B', marginBottom: 6 },
    metaRow: { flexDirection: 'row', alignItems: 'center' },
    metaText: { fontSize: 10, fontWeight: '600', color: '#94A3B8', marginLeft: 4 },
    
    cardRight: { alignItems: 'flex-end', justifyContent: 'center' },
    statusWrap: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    statusDot: { width: 6, height: 6, borderRadius: 3 },
    statusTxt: { fontSize: 12, fontWeight: '800' },

    footerLoader: { paddingVertical: 30, alignItems: 'center' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    
    // Empty State
    emptyState: { alignItems: 'center', paddingVertical: 80, paddingHorizontal: 32 },
    emptyIconWrap: { width: 140, height: 140, borderRadius: 70, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', marginBottom: 30, shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.05, shadowRadius: 24, elevation: 8 },
    emptyTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', marginBottom: 12 },
    emptyDesc: { fontSize: 16, color: '#64748B', textAlign: 'center', lineHeight: 24, marginBottom: 36, fontWeight: '500' },
    emptyBtn: { backgroundColor: '#0F172A', paddingHorizontal: 32, paddingVertical: 18, borderRadius: 16, shadowColor: '#0F172A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.3, shadowRadius: 15, elevation: 6 },
    emptyBtnTxt: { color: '#fff', fontSize: 16, fontWeight: '800' },

    // Modals
    modalOverlay: { flex: 1, backgroundColor: 'rgba(2,6,23,0.7)', justifyContent: 'flex-end' },
    actionSheet: { backgroundColor: '#fff', borderTopLeftRadius: 36, borderTopRightRadius: 36, padding: 24, paddingBottom: 50 },
    sheetHandle: { width: 48, height: 6, borderRadius: 3, backgroundColor: '#E2E8F0', alignSelf: 'center', marginBottom: 24 },
    sheetHeader: { marginBottom: 24, paddingBottom: 20, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    sheetTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', textAlign: 'center', marginBottom: 8 },
    sheetSub: { fontSize: 16, color: '#64748B', textAlign: 'center', fontWeight: '600' },
    sheetAction: { flexDirection: 'row', alignItems: 'center', paddingVertical: 16, marginBottom: 12, borderRadius: 20, backgroundColor: '#F8FAFC', paddingHorizontal: 16 },
    sheetActionIcon: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    sheetActionTxt: { fontSize: 17, fontWeight: '700', color: '#0F172A' },
    
    formSheet: { backgroundColor: '#fff', borderTopLeftRadius: 36, borderTopRightRadius: 36, padding: 24, maxHeight: '92%' },
    formHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 },
    formTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A' },
    closeBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    formRow: { flexDirection: 'row', gap: 16 },
    inputGroup: { marginBottom: 24 },
    formLabel: { fontSize: 13, fontWeight: '800', color: '#475569', marginBottom: 10, textTransform: 'uppercase', letterSpacing: 0.5 },
    input: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 18, fontSize: 16, color: '#0F172A', fontWeight: '600' },
    formSwitchWrap: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', backgroundColor: '#F8FAFC', padding: 20, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0', marginBottom: 40, marginTop: 10 },
    formSwitch: { width: 56, height: 34, borderRadius: 17, justifyContent: 'center', paddingHorizontal: 2 },
    formSwitchOn: { backgroundColor: '#10B981' },
    formSwitchOff: { backgroundColor: '#CBD5E1' },
    formSwitchKnob: { width: 30, height: 30, borderRadius: 15, backgroundColor: '#fff', shadowColor: '#000', shadowOffset: {width:0, height:2}, shadowOpacity: 0.1, shadowRadius: 4, elevation: 2 },
    formSwitchKnobOn: { alignSelf: 'flex-end' },
    formSwitchKnobOff: { alignSelf: 'flex-start' },
    submitBtn: { backgroundColor: '#0F172A', padding: 20, borderRadius: 20, alignItems: 'center', marginBottom: 20, shadowColor: '#0F172A', shadowOffset: {width:0, height:8}, shadowOpacity: 0.3, shadowRadius: 15, elevation: 6 },
    submitBtnTxt: { color: '#fff', fontSize: 18, fontWeight: '900' }
});
