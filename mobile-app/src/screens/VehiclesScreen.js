import React, { useState, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Modal, TextInput, Alert, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import api from '../api/axios';

export default function VehiclesScreen({ navigation }) {
    const [vehicles, setVehicles] = useState([]);
    const [kpi, setKpi] = useState({ total: 0, upcoming_inspection: 0, upcoming_insurance: 0 });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');
    const [showFilter, setShowFilter] = useState(false);
    const [modalVisible, setModalVisible] = useState(false);
    const [saving, setSaving] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [actionItem, setActionItem] = useState(null); // for 3-dot action sheet
    const emptyForm = { plate:'', brand:'', model:'', vehicle_type:'', model_year:'', current_km:'', engine_no:'', chassis_no:'', fuel_type:'', color:'', seat_count:'', is_active:true };
    const [formData, setFormData] = useState(emptyForm);

    const fetchVehicles = async () => {
        try {
            const params = {};
            if (statusFilter !== 'all') params.status = statusFilter;
            const r = await api.get('/vehicles', { params });
            setVehicles(r.data.vehicles || []);
            setKpi(r.data.kpi || { total:0, upcoming_inspection:0, upcoming_insurance:0 });
        } catch(e) { console.error(e); }
        finally { setLoading(false); setRefreshing(false); }
    };
    useFocusEffect(useCallback(() => { fetchVehicles(); }, [statusFilter]));

    const filtered = vehicles.filter(v => {
        const q = searchQuery.toLowerCase();
        return !q || (v.plate||'').toLowerCase().includes(q) || (v.brand_model||'').toLowerCase().includes(q) || (v.driver||'').toLowerCase().includes(q);
    });

    const openAdd = () => { setEditingId(null); setFormData(emptyForm); setModalVisible(true); };
    const openEdit = (v) => { setEditingId(v.id); setFormData({ plate:v.plate||'', brand:v.brand||'', model:v.model||'', vehicle_type:v.vehicle_type||'', model_year:v.model_year?String(v.model_year):'', current_km:v.current_km?String(v.current_km):'', engine_no:v.engine_no||'', chassis_no:v.chassis_no||'', fuel_type:v.fuel_type||'', color:v.color||'', seat_count:v.seat_count?String(v.seat_count):'', is_active:v.is_active }); setModalVisible(true); setActionItem(null); };

    const save = async () => {
        if(!formData.plate){Alert.alert('Hata','Plaka zorunlu.');return;}
        setSaving(true);
        try { editingId ? await api.put(`/vehicles/${editingId}`,formData) : await api.post('/vehicles',formData); setModalVisible(false); fetchVehicles(); }
        catch(e){Alert.alert('Hata',e.response?.data?.message||'Hata oluştu.');}
        finally{setSaving(false);}
    };

    const del = (id,plate) => { setActionItem(null); const go=async()=>{try{await api.delete(`/vehicles/${id}`);fetchVehicles();}catch{Alert.alert('Hata','Silinemedi.');}}; Platform.OS==='web'?window.confirm(`${plate} silinsin mi?`)&&go():Alert.alert('Sil',`${plate} silinsin mi?`,[{text:'İptal',style:'cancel'},{text:'Sil',style:'destructive',onPress:go}]); };

    const fmtKm = k => (!k||k===0) ? '0 km' : Number(k).toLocaleString('tr-TR')+' km';
    const filterLabel = statusFilter==='active'?'Aktif':statusFilter==='passive'?'Pasif':'Tümü';

    const renderRow = ({ item, index }) => (
        <View style={[s.row, index%2===0&&{backgroundColor:'#F8FAFC'}]}>
            <View style={s.cPlaka}><Text style={s.plaka}>{item.plate}</Text><Text style={s.marka} numberOfLines={1}>{item.brand_model||'-'}</Text></View>
            <View style={s.cTip}><Text style={s.tipTxt} numberOfLines={1}>{item.vehicle_type||'-'}</Text><Text style={s.yilTxt}>{item.model_year?item.model_year+' Model':'-'}</Text></View>
            <View style={s.cPersonel}><Text style={[s.tipTxt,item.driver?{color:'#0F172A',fontWeight:'700'}:{color:'#94A3B8'}]}>{item.driver||'Atanmamış'}</Text></View>
            <View style={s.cKm}><Text style={s.kmTxt}>{fmtKm(item.current_km)}</Text></View>
            <TouchableOpacity style={s.cAksiyon} onPress={()=>setActionItem(item)} hitSlop={{top:10,bottom:10,left:10,right:10}}>
                <Icon name="dots-vertical" size={18} color="#64748B"/>
            </TouchableOpacity>
        </View>
    );

    return (
        <View style={s.container}>
            {/* Header */}
            <LinearGradient colors={['#040B16','#0A1526','#0D1B2A']} style={s.header}>
                <SpaceWaves/>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={()=>navigation.goBack()} hitSlop={{top:10,bottom:10,left:10,right:10}}><Icon name="chevron-left" size={28} color="#fff"/></TouchableOpacity>
                        <Text style={s.headerTitle}>Araçlar</Text>
                        <TouchableOpacity onPress={openAdd} style={s.addBtn}><Icon name="plus" size={20} color="#040B16"/></TouchableOpacity>
                    </View>
                    {/* KPI */}
                    <View style={s.kpiRow}>
                        <View style={s.kpi}><View style={[s.kpiIcon,{backgroundColor:'rgba(59,130,246,0.15)'}]}><Icon name="car-multiple" size={18} color="#60A5FA"/></View><Text style={s.kpiVal}>{kpi.total}</Text><Text style={s.kpiLbl}>Toplam Araç</Text></View>
                        <TouchableOpacity style={s.kpi} onPress={() => navigation.navigate('UpcomingInspections')} activeOpacity={0.8}>
                            <View style={[s.kpiIcon,{backgroundColor:'rgba(245,158,11,0.15)'}]}><Icon name="clipboard-check-outline" size={18} color="#FBBF24"/></View>
                            <Text style={s.kpiVal}>{kpi.upcoming_inspection}</Text>
                            <Text style={s.kpiLbl}>Muayene</Text>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.kpi} onPress={() => navigation.navigate('UpcomingInsurances')} activeOpacity={0.8}>
                            <View style={[s.kpiIcon,{backgroundColor:'rgba(239,68,68,0.15)'}]}><Icon name="shield-alert-outline" size={18} color="#F87171"/></View>
                            <Text style={s.kpiVal}>{kpi.upcoming_insurance}</Text>
                            <Text style={s.kpiLbl}>Sigorta</Text>
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Arama + Filtre */}
            <View style={s.searchRow}>
                <View style={s.searchBox}><Icon name="magnify" size={18} color="#94A3B8"/><TextInput style={s.searchInput} placeholder="Plaka veya marka ara..." placeholderTextColor="#64748B" value={searchQuery} onChangeText={setSearchQuery}/>{searchQuery.length>0&&<TouchableOpacity onPress={()=>setSearchQuery('')}><Icon name="close-circle" size={16} color="#CBD5E1"/></TouchableOpacity>}</View>
                <View>
                    <TouchableOpacity style={s.filterBtn} onPress={()=>setShowFilter(!showFilter)}><Icon name="filter-variant" size={16} color={statusFilter!=='all'?'#fff':'#94A3B8'}/><Text style={[s.filterBtnTxt,statusFilter!=='all'&&{color:'#fff'}]}>{filterLabel}</Text></TouchableOpacity>
                    {showFilter&&<View style={s.filterDrop}>{['all','active','passive'].map(v=><TouchableOpacity key={v} style={[s.filterOpt,statusFilter===v&&s.filterOptA]} onPress={()=>{setStatusFilter(v);setShowFilter(false);}}><Text style={[s.filterOptTxt,statusFilter===v&&{color:'#3B82F6',fontWeight:'800'}]}>{v==='all'?'Tümü':v==='active'?'Aktif':'Pasif'}</Text></TouchableOpacity>)}</View>}
                </View>
            </View>

            {/* Kolon Başlık */}
            <LinearGradient colors={['#EFF6FF','#F1F5F9']} style={s.tHead}><Text style={[s.th,s.cPlaka]}>PLAKA</Text><Text style={[s.th,s.cTip]}>TİP / YIL</Text><Text style={[s.th,s.cPersonel]}>PERSONEL</Text><Text style={[s.th,s.cKm]}>KM</Text><View style={s.cAksiyon}/></LinearGradient>

            {/* Liste */}
            {loading?<View style={s.center}><ActivityIndicator size="large" color="#3B82F6"/></View>:(
                <FlatList data={filtered} keyExtractor={i=>i.id.toString()} renderItem={renderRow} contentContainerStyle={{paddingBottom:120}} showsVerticalScrollIndicator={false} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={()=>{setRefreshing(true);fetchVehicles();}} tintColor="#3B82F6"/>} ListEmptyComponent={<View style={s.empty}><Icon name="car-off" size={44} color="#CBD5E1"/><Text style={s.emptyTxt}>{searchQuery?'Sonuç bulunamadı.':'Henüz araç yok.'}</Text></View>}/>
            )}

            {/* ─── 3 Nokta İşlem Menüsü (Bottom Sheet Modal) ─── */}
            <Modal visible={!!actionItem} transparent animationType="fade" onRequestClose={()=>setActionItem(null)}>
                <TouchableOpacity style={s.actionOverlay} activeOpacity={1} onPress={()=>setActionItem(null)}>
                    <View style={s.actionSheet}>
                        <View style={s.actionHandle}/>
                        <Text style={s.actionPlate}>{actionItem?.plate}</Text>
                        <Text style={s.actionSub}>{actionItem?.brand_model}</Text>
                        <View style={s.actionDivider}/>
                        <TouchableOpacity style={s.actionBtn} onPress={()=>{const item=actionItem; setActionItem(null); navigation.navigate('VehicleDetail', { vehicle: item });}}>
                            <View style={[s.actionIconBox,{backgroundColor:'#EFF6FF'}]}><Icon name="eye-outline" size={20} color="#3B82F6"/></View>
                            <Text style={s.actionBtnTxt}>Detay Görüntüle</Text>
                            <Icon name="chevron-right" size={20} color="#CBD5E1"/>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.actionBtn} onPress={()=>{if(actionItem)openEdit(actionItem);}}>
                            <View style={[s.actionIconBox,{backgroundColor:'#FEF9C3'}]}><Icon name="pencil-outline" size={20} color="#F59E0B"/></View>
                            <Text style={s.actionBtnTxt}>Düzenle</Text>
                            <Icon name="chevron-right" size={20} color="#CBD5E1"/>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.actionBtn} onPress={()=>{if(actionItem)del(actionItem.id,actionItem.plate);}}>
                            <View style={[s.actionIconBox,{backgroundColor:'#FEF2F2'}]}><Icon name="trash-can-outline" size={20} color="#EF4444"/></View>
                            <Text style={[s.actionBtnTxt,{color:'#EF4444'}]}>Sil</Text>
                            <Icon name="chevron-right" size={20} color="#FECACA"/>
                        </TouchableOpacity>
                        <TouchableOpacity style={s.actionCancel} onPress={()=>setActionItem(null)}>
                            <Text style={s.actionCancelTxt}>İptal</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </Modal>

            {/* ─── Ekleme / Düzenleme Modalı ─── */}
            <Modal visible={modalVisible} animationType="slide" transparent>
                <View style={s.mOverlay}><KeyboardAvoidingView behavior={Platform.OS==='ios'?'padding':'height'} style={{flex:1,justifyContent:'flex-end'}}><View style={s.mContent}>
                    <View style={s.mHead}><Text style={s.mTitle}>{editingId?'Aracı Düzenle':'Yeni Araç Ekle'}</Text><TouchableOpacity onPress={()=>setModalVisible(false)} style={s.closeBtn}><Icon name="close" size={22} color="#64748B"/></TouchableOpacity></View>
                    <ScrollView showsVerticalScrollIndicator={false}>
                        <FI label="Plaka *" val={formData.plate} onSet={t=>setFormData({...formData,plate:t})} caps/>
                        <View style={{flexDirection:'row',gap:10}}><FI label="Marka" val={formData.brand} onSet={t=>setFormData({...formData,brand:t})} half/><FI label="Model" val={formData.model} onSet={t=>setFormData({...formData,model:t})} half/></View>
                        <View style={{flexDirection:'row',gap:10}}><FI label="Araç Tipi" val={formData.vehicle_type} onSet={t=>setFormData({...formData,vehicle_type:t})} half/><FI label="Model Yılı" val={formData.model_year} onSet={t=>setFormData({...formData,model_year:t})} half num/></View>
                        <View style={{flexDirection:'row',gap:10}}><FI label="Güncel KM" val={formData.current_km} onSet={t=>setFormData({...formData,current_km:t})} half num/><FI label="Koltuk" val={formData.seat_count} onSet={t=>setFormData({...formData,seat_count:t})} half num/></View>
                        <View style={{flexDirection:'row',gap:10}}><FI label="Motor No" val={formData.engine_no} onSet={t=>setFormData({...formData,engine_no:t})} half/><FI label="Şasi No" val={formData.chassis_no} onSet={t=>setFormData({...formData,chassis_no:t})} half/></View>
                        <View style={{flexDirection:'row',gap:10}}><FI label="Yakıt" val={formData.fuel_type} onSet={t=>setFormData({...formData,fuel_type:t})} half/><FI label="Renk" val={formData.color} onSet={t=>setFormData({...formData,color:t})} half/></View>
                        <TouchableOpacity style={[s.toggle,formData.is_active?s.togOn:s.togOff]} onPress={()=>setFormData({...formData,is_active:!formData.is_active})}><Icon name={formData.is_active?"check-circle":"close-circle"} size={18} color={formData.is_active?"#10B981":"#EF4444"}/><Text style={{color:formData.is_active?'#10B981':'#EF4444',fontWeight:'700',fontSize:13}}>{formData.is_active?'Aktif':'Pasif'}</Text></TouchableOpacity>
                        <TouchableOpacity style={s.saveBtn} onPress={save} disabled={saving}>{saving?<ActivityIndicator color="#fff"/>:<Text style={s.saveTxt}>{editingId?'Güncelle':'Kaydet'}</Text>}</TouchableOpacity>
                    </ScrollView>
                </View></KeyboardAvoidingView></View>
            </Modal>
        </View>
    );
}

function FI({label,val,onSet,half,num,caps}){return(<View style={[s.ig,half&&{flex:1}]}><Text style={s.il}>{label}</Text><TextInput style={s.inp} value={val} onChangeText={onSet} keyboardType={num?'number-pad':'default'} autoCapitalize={caps?'characters':'sentences'}/></View>);}

const s = StyleSheet.create({
    container:{flex:1,backgroundColor:'#F8FAFC'},
    header:{paddingBottom:16,paddingHorizontal:16,paddingTop:14,borderBottomLeftRadius:24,borderBottomRightRadius:24},
    headerRow:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',paddingTop:4,marginBottom:14},
    headerTitle:{fontSize:18,fontWeight:'900',color:'#fff',letterSpacing:0.5},
    addBtn:{width:32,height:32,borderRadius:16,backgroundColor:'#fff',alignItems:'center',justifyContent:'center'},
    kpiRow:{flexDirection:'row',gap:8,marginBottom:4},
    kpi:{flex:1,backgroundColor:'rgba(255,255,255,0.08)',borderRadius:14,padding:10,alignItems:'center',gap:4,borderWidth:1,borderColor:'rgba(255,255,255,0.1)'},
    kpiIcon:{width:32,height:32,borderRadius:10,alignItems:'center',justifyContent:'center'},
    kpiVal:{fontSize:22,fontWeight:'900',color:'#fff'},
    kpiLbl:{fontSize:8,fontWeight:'800',color:'rgba(255,255,255,0.6)',textTransform:'uppercase',letterSpacing:1},
    searchRow:{flexDirection:'row',paddingHorizontal:12,paddingTop:10,paddingBottom:6,gap:8,alignItems:'flex-start'},
    searchBox:{flex:1,flexDirection:'row',alignItems:'center',backgroundColor:'#fff',borderRadius:10,paddingHorizontal:10,paddingVertical:8,gap:6,borderWidth:1,borderColor:'#E2E8F0'},
    searchInput:{flex:1,fontSize:13,color:'#0F172A',fontWeight:'500',padding:0},
    filterBtn:{flexDirection:'row',alignItems:'center',gap:4,backgroundColor:'#0F172A',paddingHorizontal:12,paddingVertical:9,borderRadius:10},
    filterBtnTxt:{fontSize:12,fontWeight:'700',color:'#94A3B8'},
    filterDrop:{position:'absolute',top:40,right:0,backgroundColor:'#fff',borderRadius:10,padding:4,shadowColor:'#000',shadowOffset:{width:0,height:4},shadowOpacity:0.15,shadowRadius:12,elevation:10,zIndex:999,minWidth:100},
    filterOpt:{paddingVertical:8,paddingHorizontal:14,borderRadius:6},
    filterOptA:{backgroundColor:'#EFF6FF'},
    filterOptTxt:{fontSize:13,fontWeight:'600',color:'#334155'},
    tHead:{flexDirection:'row',paddingHorizontal:12,paddingVertical:9,borderBottomWidth:1,borderBottomColor:'#DBEAFE'},
    th:{fontSize:10,fontWeight:'800',color:'#3B82F6',letterSpacing:0.8},
    row:{flexDirection:'row',alignItems:'center',paddingHorizontal:12,paddingVertical:10,borderBottomWidth:1,borderBottomColor:'#F1F5F9',backgroundColor:'#fff'},
    cPlaka:{flex:2.2,paddingRight:4},cTip:{flex:1.8,paddingRight:4},cPersonel:{flex:2,paddingRight:4},cKm:{flex:1.6,alignItems:'flex-end',paddingRight:2},cAksiyon:{width:28,alignItems:'center'},
    plaka:{fontSize:12,fontWeight:'900',color:'#0F172A'},marka:{fontSize:9,color:'#64748B',fontWeight:'500',marginTop:1},
    tipTxt:{fontSize:10,color:'#334155',fontWeight:'600'},yilTxt:{fontSize:9,color:'#94A3B8',fontWeight:'500',marginTop:1},
    kmTxt:{fontSize:11,fontWeight:'700',color:'#0F172A'},
    // Action Sheet
    actionOverlay:{flex:1,backgroundColor:'rgba(15,23,42,0.5)',justifyContent:'flex-end'},
    actionSheet:{backgroundColor:'#fff',borderTopLeftRadius:24,borderTopRightRadius:24,padding:20,paddingBottom:36},
    actionHandle:{width:40,height:4,borderRadius:2,backgroundColor:'#E2E8F0',alignSelf:'center',marginBottom:16},
    actionPlate:{fontSize:20,fontWeight:'900',color:'#0F172A',textAlign:'center'},
    actionSub:{fontSize:13,color:'#64748B',fontWeight:'500',textAlign:'center',marginTop:2,marginBottom:12},
    actionDivider:{height:1,backgroundColor:'#F1F5F9',marginBottom:8},
    actionBtn:{flexDirection:'row',alignItems:'center',paddingVertical:14,paddingHorizontal:4,gap:12},
    actionIconBox:{width:40,height:40,borderRadius:12,alignItems:'center',justifyContent:'center'},
    actionBtnTxt:{flex:1,fontSize:15,fontWeight:'700',color:'#0F172A'},
    actionCancel:{marginTop:8,backgroundColor:'#F1F5F9',padding:14,borderRadius:12,alignItems:'center'},
    actionCancelTxt:{fontSize:15,fontWeight:'800',color:'#64748B'},
    center:{flex:1,justifyContent:'center',alignItems:'center'},
    empty:{alignItems:'center',paddingVertical:40},emptyTxt:{color:'#94A3B8',fontSize:13,marginTop:10,fontWeight:'600'},
    mOverlay:{flex:1,backgroundColor:'rgba(15,23,42,0.6)',justifyContent:'flex-end'},
    mContent:{backgroundColor:'#fff',borderTopLeftRadius:24,borderTopRightRadius:24,padding:20,maxHeight:'92%'},
    mHead:{flexDirection:'row',justifyContent:'space-between',alignItems:'center',marginBottom:16},
    mTitle:{fontSize:18,fontWeight:'900',color:'#0F172A'},
    closeBtn:{width:32,height:32,borderRadius:16,backgroundColor:'#F1F5F9',alignItems:'center',justifyContent:'center'},
    ig:{marginBottom:10},il:{fontSize:10,fontWeight:'700',color:'#64748B',marginBottom:4,marginLeft:2,textTransform:'uppercase',letterSpacing:0.3},
    inp:{backgroundColor:'#F8FAFC',borderWidth:1,borderColor:'#E2E8F0',borderRadius:10,padding:12,fontSize:13,fontWeight:'600',color:'#0F172A'},
    toggle:{flexDirection:'row',alignItems:'center',justifyContent:'center',padding:12,borderRadius:10,marginBottom:14,gap:6,borderWidth:1},
    togOn:{backgroundColor:'#ECFDF5',borderColor:'#A7F3D0'},togOff:{backgroundColor:'#FEF2F2',borderColor:'#FECACA'},
    saveBtn:{backgroundColor:'#3B82F6',padding:14,borderRadius:12,alignItems:'center',marginBottom:36},
    saveTxt:{color:'#fff',fontSize:15,fontWeight:'800'},
});
