import React, { useState, useEffect, useContext, useRef, useCallback } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Modal, TextInput, Alert, Animated, PanResponder, KeyboardAvoidingView, Platform, Dimensions, ScrollView, Linking, Image } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useFocusEffect } from '@react-navigation/native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { Picker } from '@react-native-picker/picker';
import dayjs from 'dayjs';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

// Custom Swipeable Row
const SwipeableRow = ({ children, onEdit, onDelete }) => {
    const pan = useRef(new Animated.ValueXY()).current;
    
    const panResponder = useRef(
        PanResponder.create({
            onMoveShouldSetPanResponder: (e, gestureState) => Math.abs(gestureState.dx) > 15 && Math.abs(gestureState.dx) > Math.abs(gestureState.dy),
            onPanResponderMove: (e, gestureState) => {
                if (gestureState.dx < 0 && gestureState.dx > -160) {
                    pan.setValue({ x: gestureState.dx, y: 0 });
                }
            },
            onPanResponderRelease: (e, gestureState) => {
                if (gestureState.dx < -60) {
                    Animated.spring(pan, { toValue: { x: -140, y: 0 }, useNativeDriver: true, tension: 40, friction: 5 }).start();
                } else {
                    Animated.spring(pan, { toValue: { x: 0, y: 0 }, useNativeDriver: true, tension: 40, friction: 5 }).start();
                }
            }
        })
    ).current;

    const close = () => {
        Animated.spring(pan, { toValue: { x: 0, y: 0 }, useNativeDriver: true }).start();
    };

    return (
        <View style={s.swipeContainer}>
            <View style={s.actionButtons}>
                <TouchableOpacity style={[s.actionBtn, { backgroundColor: '#3B82F6' }]} onPress={() => { close(); onEdit(); }}>
                    <Icon name="pencil" size={24} color="#FFF" />
                </TouchableOpacity>
                <TouchableOpacity style={[s.actionBtn, { backgroundColor: '#EF4444' }]} onPress={() => { close(); onDelete(); }}>
                    <Icon name="delete" size={24} color="#FFF" />
                </TouchableOpacity>
            </View>
            <Animated.View style={[s.swipeContent, { transform: [{ translateX: pan.x }] }]} {...panResponder.panHandlers}>
                {children}
            </Animated.View>
        </View>
    );
};

// Custom Select Modal to fix iOS Picker overlap
const SelectInput = ({ icon, placeholder, value, options, onSelect }) => {
    const [open, setOpen] = useState(false);
    const selected = options.find(o => o.value === value);

    return (
        <>
            <TouchableOpacity style={s.fieldWrap} onPress={() => setOpen(true)} activeOpacity={0.7}>
                <Icon name={icon} size={20} color="#94A3B8" style={s.fieldIcon} />
                <Text style={[s.fieldInput, { color: selected ? '#0F172A' : '#94A3B8', paddingTop: Platform.OS === 'ios' ? 16 : 14 }]}>
                    {selected ? selected.label : placeholder}
                </Text>
                <Icon name="chevron-down" size={20} color="#94A3B8" />
            </TouchableOpacity>

            <Modal visible={open} transparent animationType="fade">
                <TouchableOpacity style={s.modalOverlayCenter} activeOpacity={1} onPress={() => setOpen(false)}>
                    <View style={s.centerModal}>
                        <Text style={s.modalTitle}>{placeholder}</Text>
                        <ScrollView style={{ maxHeight: 300 }} showsVerticalScrollIndicator={false}>
                            {options.map((opt, i) => (
                                <TouchableOpacity 
                                    key={i} 
                                    style={[s.menuItem, value === opt.value && { backgroundColor: '#F8FAFC' }]}
                                    onPress={() => { onSelect(opt.value); setOpen(false); }}
                                >
                                    <Text style={[s.menuText, value === opt.value && { color: '#3B82F6' }]}>{opt.label}</Text>
                                    {value === opt.value && <Icon name="check" size={20} color="#3B82F6" style={{ position: 'absolute', right: 16 }} />}
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>
        </>
    );
};

export default function PersonnelScreen({ navigation }) {
    const { hasPermission } = useContext(AuthContext);
    const insets = useSafeAreaInsets();
    
    const [personnel, setPersonnel] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    
    const [vehicles, setVehicles] = useState([]);
    
    // KPI Data
    const [kpi, setKpi] = useState({ total: 0, active: 0, inactive: 0 });
    
    // Bottom Sheet (Search/Filter)
    const [showSearch, setShowSearch] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');

    // Bottom Sheet (Form)
    const [showForm, setShowForm] = useState(false);
    const [saving, setSaving] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const emptyForm = { full_name: '', tc_no: '', phone: '', email: '', license_class: '', src_type: '', start_shift: 'morning', vehicle_id: '' };
    const [formData, setFormData] = useState(emptyForm);

    // Birthday Alert
    const [birthdayPersonnel, setBirthdayPersonnel] = useState([]);
    const [showBirthdayModal, setShowBirthdayModal] = useState(false);
    const [birthdayAlertShown, setBirthdayAlertShown] = useState(false);

    const fetchData = async (hideLoader = false) => {
        if (!hideLoader) setLoading(true);
        try {
            const [resPersonnel, resOptions] = await Promise.all([
                api.get('/v1/personnel'),
                hasPermission('drivers.create') || hasPermission('drivers.edit') ? api.get('/v1/personnel/options') : Promise.resolve({data:{data:{vehicles:[]}}})
            ]);
            
            const data = resPersonnel.data.data || [];
            setPersonnel(data);
            
            // Calculate KPIs
            const activeCount = data.filter(p => p.is_active).length;
            setKpi({
                total: data.length,
                active: activeCount,
                inactive: data.length - activeCount
            });

            // Birthday Check
            if (!birthdayAlertShown) {
                const today = dayjs();
                const todayStr = today.format('YYYY-MM-DD');
                const lastShownDate = await AsyncStorage.getItem('birthdayModalShownDate');

                if (lastShownDate !== todayStr) {
                    const bDays = data.filter(p => {
                        if (!p.birth_date) return false;
                        const bDate = dayjs(p.birth_date);
                        return bDate.date() === today.date() && bDate.month() === today.month();
                    });
                    if (bDays.length > 0) {
                        setBirthdayPersonnel(bDays);
                        setShowBirthdayModal(true);
                        setBirthdayAlertShown(true);
                        await AsyncStorage.setItem('birthdayModalShownDate', todayStr);
                    }
                } else {
                    setBirthdayAlertShown(true); // Zaten bugün gösterilmiş
                }
            }

            if (resOptions.data.data?.vehicles) {
                setVehicles(resOptions.data.data.vehicles);
            }
        } catch (e) {
            console.log(e.response?.data || e.message);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useFocusEffect(useCallback(() => { fetchData(true); }, []));

    const openAddForm = () => {
        setEditingId(null);
        setFormData(emptyForm);
        setShowForm(true);
    };

    const openEditForm = (item) => {
        setEditingId(item.id);
        setFormData({
            full_name: item.full_name || '',
            tc_no: item.tc_no || '',
            phone: item.phone || '',
            email: item.email || '',
            license_class: item.license_class || '',
            src_type: item.src_type || '',
            start_shift: item.start_shift || 'morning',
            vehicle_id: item.vehicle_id ? item.vehicle_id.toString() : ''
        });
        setShowForm(true);
    };

    const handlePhoneChange = (text) => {
        let val = text.replace(/\D/g, '');
        if (val.length > 0 && val[0] !== '0') {
            val = '0' + val;
        }
        let formatted = '';
        if (val.length > 0) formatted += val.substring(0, 1);
        if (val.length > 1) formatted += ' ' + val.substring(1, 4);
        if (val.length > 4) formatted += ' ' + val.substring(4, 7);
        if (val.length > 7) formatted += ' ' + val.substring(7, 9);
        if (val.length > 9) formatted += ' ' + val.substring(9, 11);
        setFormData({...formData, phone: formatted});
    };

    const handleSave = async () => {
        if (!formData.full_name) {
            Alert.alert('Eksik Bilgi', 'Personel adı zorunludur.');
            return;
        }
        setSaving(true);
        try {
            const payload = { ...formData };
            if (!payload.vehicle_id) payload.vehicle_id = null;

            if (editingId) {
                await api.put(`/v1/personnel/${editingId}`, payload);
            } else {
                await api.post('/v1/personnel', payload);
            }
            setShowForm(false);
            fetchData(true);
        } catch (e) {
            console.log(e.response?.data || e.message);
            Alert.alert('Hata', e.response?.data?.message || 'İşlem başarısız.');
        } finally {
            setSaving(false);
        }
    };

    const confirmDelete = (item) => {
        Alert.alert('Silme Onayı', `"${item.full_name}" adlı personeli silmek istediğinize emin misiniz?`, [
            { text: 'Vazgeç', style: 'cancel' },
            { 
                text: 'Sil', 
                style: 'destructive', 
                onPress: async () => {
                    // Optimistic UI update
                    const prev = [...personnel];
                    setPersonnel(prev.filter(p => p.id !== item.id));
                    try {
                        await api.delete(`/v1/personnel/${item.id}`);
                        fetchData(true);
                    } catch (e) {
                        setPersonnel(prev); // revert
                        Alert.alert('Hata', 'Kayıt silinemedi.');
                    }
                }
            }
        ]);
    };

    const filteredPersonnel = personnel.filter(p => {
        const matchSearch = p.full_name?.toLowerCase().includes(searchQuery.toLowerCase()) || 
                            p.tc_no?.includes(searchQuery) || 
                            p.vehicle?.plate?.toLowerCase().includes(searchQuery.toLowerCase());
        const matchStatus = filterStatus === 'all' ? true : (filterStatus === 'active' ? p.is_active : !p.is_active);
        return matchSearch && matchStatus;
    }).sort((a, b) => {
        const plateA = a.vehicle?.plate || 'ZZZZZZ'; // Plakası olmayanları sona atmak için
        const plateB = b.vehicle?.plate || 'ZZZZZZ';
        return plateA.localeCompare(plateB, 'tr', { numeric: true });
    });

    const getInitials = (name) => {
        if (!name) return '?';
        const parts = name.split(' ');
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return name.substring(0, 2).toUpperCase();
    };

    const renderCard = ({ item }) => (
        <SwipeableRow onEdit={() => openEditForm(item)} onDelete={() => confirmDelete(item)}>
            <TouchableOpacity 
                activeOpacity={0.9} 
                style={s.card} 
                onPress={() => navigation.navigate('PersonnelDetail', { id: item.id, personnel: item })}
            >
                <View style={s.cardLeft}>
                    {item.profile_photo_url ? (
                        <Image source={{ uri: item.profile_photo_url }} style={s.avatar} />
                    ) : (
                        <LinearGradient colors={['#3B82F6', '#1E40AF']} start={{x: 0, y: 0}} end={{x: 1, y: 1}} style={s.premiumIconWrap}>
                            <Icon name="badge-account-horizontal-outline" size={26} color="#FFF" style={s.premiumIconShadow} />
                            <View style={s.premiumIconGlow} />
                        </LinearGradient>
                    )}
                </View>
                <View style={s.cardMid}>
                    <Text style={s.cardName} numberOfLines={1}>{item.full_name}</Text>
                    <View style={s.cardSubRow}>
                        {item.vehicle ? (
                            <View style={s.plateBadge}>
                                <Text style={s.plateText}>{item.vehicle.plate}</Text>
                            </View>
                        ) : (
                            <Text style={s.noPlate}>Atanmamış</Text>
                        )}
                        <View style={[s.statusBadge, { backgroundColor: item.is_active ? '#DCFCE7' : '#FEE2E2', marginLeft: 8 }]}>
                            <View style={[s.statusDot, { backgroundColor: item.is_active ? '#10B981' : '#EF4444' }]} />
                            <Text style={[s.statusText, { color: item.is_active ? '#15803D' : '#B91C1C' }]}>{item.is_active ? 'Uygun' : 'Pasif'}</Text>
                        </View>
                    </View>
                </View>
                <TouchableOpacity style={s.callBtn} onPress={() => {
                    if (item.phone) {
                        Linking.openURL(`tel:${item.phone}`);
                    } else {
                        Alert.alert('Bilgi', 'Bu personelin telefon numarası kayıtlı değil.');
                    }
                }}>
                    <Icon name="phone" size={22} color="#10B981" />
                </TouchableOpacity>
            </TouchableOpacity>
        </SwipeableRow>
    );

    return (
        <View style={s.container}>
            <SafeAreaView style={{ flex: 1 }} edges={['top', 'left', 'right']}>
                {/* Header */}
                <View style={s.header}>
                    <View style={{ flex: 1 }}>
                        <Text style={s.headerTitle}>Personeller</Text>
                        <Text style={s.headerSub}>Ekibinizi ve sürücülerinizi yönetin</Text>
                    </View>
                    <TouchableOpacity style={s.searchIconBtn} onPress={() => setShowSearch(true)}>
                        <Icon name="magnify" size={24} color="#0F172A" />
                    </TouchableOpacity>
                </View>

                {/* 3 KPI Cards */}
                <View style={{ paddingHorizontal: 20, paddingBottom: 16, flexDirection: 'row', justifyContent: 'space-between' }}>
                    <TouchableOpacity activeOpacity={0.8} style={s.kpiWrapper} onPress={() => setFilterStatus('all')}>
                        <LinearGradient colors={['#1E293B', '#0F172A']} style={s.kpiCardFix}>
                            <View style={[s.kpiIconWrap, {backgroundColor: 'transparent'}]}><Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/People/Construction%20Worker.png' }} style={{width: 24, height: 24}} resizeMode="contain" /></View>
                            <Text style={s.kpiValue}>{kpi.total}</Text>
                            <Text style={s.kpiLabel}>Toplam</Text>
                        </LinearGradient>
                    </TouchableOpacity>
                    
                    <TouchableOpacity activeOpacity={0.8} style={[s.kpiWrapper, { marginHorizontal: 8 }]} onPress={() => setFilterStatus(filterStatus === 'inactive' ? 'all' : 'inactive')}>
                        <LinearGradient colors={filterStatus === 'inactive' ? ['#F59E0B', '#B45309'] : ['#F59E0B', '#D97706']} style={s.kpiCardFix}>
                            <View style={[s.kpiIconWrap, {backgroundColor: 'transparent'}]}><Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Prohibited.png' }} style={{width: 24, height: 24}} resizeMode="contain" /></View>
                            <Text style={s.kpiValue}>{kpi.inactive}</Text>
                            <Text style={s.kpiLabel}>Pasif</Text>
                        </LinearGradient>
                    </TouchableOpacity>

                    <View style={s.kpiWrapper}>
                        <LinearGradient colors={['#3B82F6', '#2563EB']} style={s.kpiCardFix}>
                            <View style={[s.kpiIconWrap, {backgroundColor: 'transparent'}]}><Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Automobile.png' }} style={{width: 24, height: 24}} resizeMode="contain" /></View>
                            <Text style={s.kpiValue}>{personnel.filter(p => p.vehicle_id).length}</Text>
                            <Text style={s.kpiLabel}>Araçlı</Text>
                        </LinearGradient>
                    </View>
                </View>

                {/* List */}
                {loading ? (
                    <View style={s.loader}><ActivityIndicator size="large" color="#3B82F6" /></View>
                ) : (
                    <FlatList
                        data={filteredPersonnel}
                        keyExtractor={item => item.id.toString()}
                        renderItem={renderCard}
                        contentContainerStyle={{ paddingHorizontal: 20, paddingBottom: 120 }}
                        showsVerticalScrollIndicator={false}
                        ListEmptyComponent={<View style={s.empty}><Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/People/Construction%20Worker.png' }} style={{width: 64, height: 64, opacity: 0.8}} resizeMode="contain" /><Text style={s.emptyText}>Sonuç bulunamadı.</Text></View>}
                    />
                )}
                {/* Birthday Alert Modal */}
            <Modal visible={showBirthdayModal} transparent animationType="fade">
                <View style={s.modalOverlayCenter}>
                    <View style={s.birthdayModal}>
                        <LinearGradient colors={['#FDF2F8', '#FCE7F3']} style={s.birthdayHeader}>
                            <Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Activities/Party%20Popper.png' }} style={{width: 64, height: 64}} resizeMode="contain" />
                            <Text style={s.birthdayTitle}>Bugün Doğum Günü!</Text>
                        </LinearGradient>
                        <View style={s.birthdayContent}>
                            <Text style={s.birthdayDesc}>Aşağıdaki personellerinizin bugün doğum günü. Onları tebrik etmeyi unutmayın!</Text>
                            {birthdayPersonnel.map(p => (
                                <View key={p.id} style={s.birthdayItem}>
                                    <View style={[s.birthdayIcon, {backgroundColor: 'transparent'}]}><Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Food%20and%20drink/Birthday%20Cake.png' }} style={{width: 28, height: 28}} resizeMode="contain" /></View>
                                    <Text style={s.birthdayName}>{p.full_name}</Text>
                                </View>
                            ))}
                            <TouchableOpacity style={s.birthdayBtn} onPress={() => setShowBirthdayModal(false)} activeOpacity={0.8}>
                                <Text style={s.birthdayBtnText}>Teşekkürler, Kapat</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

        </SafeAreaView>

            {/* Floating Action Button */}
            {hasPermission('drivers.create') && (
                <TouchableOpacity style={[s.fab, { bottom: Math.max(insets.bottom + 80, 100) }]} activeOpacity={0.8} onPress={openAddForm}>
                    <LinearGradient colors={['#8B5CF6', '#6D28D9']} style={s.fabGradient}>
                        <Icon name="plus" size={28} color="#FFF" />
                    </LinearGradient>
                </TouchableOpacity>
            )}

            {/* Search/Filter Bottom Sheet */}
            <Modal visible={showSearch} transparent animationType="slide">
                <TouchableOpacity style={s.modalOverlay} activeOpacity={1} onPress={() => setShowSearch(false)}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ width: '100%' }}>
                        <TouchableOpacity activeOpacity={1} style={[s.bottomSheet, { paddingBottom: Math.max(insets.bottom, 20) }]}>
                            <View style={s.sheetHandle} />
                            <Text style={s.sheetTitle}>Personel Keşfet</Text>
                            <View style={s.inputRow}>
                                <Icon name="magnify" size={22} color="#94A3B8" />
                                <TextInput style={s.input} placeholder="Ad, TC veya Plaka Ara..." value={searchQuery} onChangeText={setSearchQuery} autoFocus />
                            </View>
                            <Text style={s.filterLabel}>Durum Filtresi</Text>
                            <View style={s.filterRow}>
                                {['all', 'active', 'inactive'].map(val => (
                                    <TouchableOpacity key={val} style={[s.filterChip, filterStatus === val && s.filterChipActive]} onPress={() => setFilterStatus(val)}>
                                        <Text style={[s.filterChipText, filterStatus === val && s.filterChipTextActive]}>{val === 'all' ? 'Tümü' : val === 'active' ? 'Uygun' : 'Pasif'}</Text>
                                    </TouchableOpacity>
                                ))}
                            </View>
                            <TouchableOpacity style={s.applyBtn} onPress={() => setShowSearch(false)}>
                                <Text style={s.applyBtnText}>Uygula</Text>
                            </TouchableOpacity>
                        </TouchableOpacity>
                    </KeyboardAvoidingView>
                </TouchableOpacity>
            </Modal>

            {/* Add/Edit Form Bottom Sheet */}
            <Modal visible={showForm} transparent animationType="slide">
                <View style={s.modalOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ width: '100%', flex: 1, justifyContent: 'flex-end' }}>
                        <View style={[s.formSheet, { paddingBottom: Math.max(insets.bottom, 20), maxHeight: SCREEN_WIDTH * 2 }]}>
                            <View style={s.sheetHeader}>
                                <Text style={s.sheetTitle}>{editingId ? 'Personel Düzenle' : 'Yeni Personel Tanımla'}</Text>
                                <TouchableOpacity onPress={() => setShowForm(false)} style={s.closeIcon}>
                                    <Icon name="close" size={24} color="#64748B" />
                                </TouchableOpacity>
                            </View>
                            
                            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingHorizontal: 20 }}>
                                <Text style={s.sectionTitle}>Kişisel Bilgiler</Text>
                                <View style={s.fieldWrap}>
                                    <Icon name="account-outline" size={20} color="#94A3B8" style={s.fieldIcon} />
                                    <TextInput style={s.fieldInput} placeholder="Ad Soyad" value={formData.full_name} onChangeText={t => setFormData({...formData, full_name: t})} />
                                </View>
                                <View style={s.fieldWrap}>
                                    <Icon name="card-account-details-outline" size={20} color="#94A3B8" style={s.fieldIcon} />
                                    <TextInput style={s.fieldInput} placeholder="TC Kimlik No" value={formData.tc_no} onChangeText={t => setFormData({...formData, tc_no: t})} keyboardType="number-pad" />
                                </View>
                                <View style={s.fieldWrap}>
                                    <Icon name="phone-outline" size={20} color="#94A3B8" style={s.fieldIcon} />
                                    <TextInput style={s.fieldInput} placeholder="Telefon Numarası" value={formData.phone} onChangeText={handlePhoneChange} keyboardType="phone-pad" maxLength={15} />
                                </View>
                                
                                <Text style={s.sectionTitle}>İş Bilgileri</Text>
                                <SelectInput 
                                    icon="car-outline" 
                                    placeholder="Araç Seçin (veya Kaldır)" 
                                    value={formData.vehicle_id} 
                                    options={[
                                        { label: "Araç Atanmadı", value: "" },
                                        ...vehicles.map(v => ({ label: v.plate, value: v.id.toString() }))
                                    ]}
                                    onSelect={(v) => setFormData({...formData, vehicle_id: v})} 
                                />
                                <View style={s.rowFields}>
                                    <View style={[s.fieldWrap, { flex: 1, marginRight: 8 }]}>
                                        <Icon name="card-text-outline" size={20} color="#94A3B8" style={s.fieldIcon} />
                                        <TextInput style={s.fieldInput} placeholder="Ehliyet (Örn: B)" value={formData.license_class} onChangeText={t => setFormData({...formData, license_class: t})} />
                                    </View>
                                    <View style={[s.fieldWrap, { flex: 1 }]}>
                                        <Icon name="file-document-outline" size={20} color="#94A3B8" style={s.fieldIcon} />
                                        <TextInput style={s.fieldInput} placeholder="SRC (Örn: SRC2)" value={formData.src_type} onChangeText={t => setFormData({...formData, src_type: t})} />
                                    </View>
                                </View>
                                <SelectInput 
                                    icon="clock-outline" 
                                    placeholder="Vardiya" 
                                    value={formData.start_shift} 
                                    options={[
                                        { label: "Sabah Vardiyası", value: "morning" },
                                        { label: "Akşam Vardiyası", value: "evening" }
                                    ]}
                                    onSelect={(v) => setFormData({...formData, start_shift: v})} 
                                />
                                <View style={{ height: 40 }} />
                            </ScrollView>

                            <View style={s.formActions}>
                                <TouchableOpacity style={s.cancelBtn} onPress={() => setShowForm(false)}>
                                    <Text style={s.cancelBtnText}>Vazgeç</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={s.saveBtn} onPress={handleSave} disabled={saving}>
                                    {saving ? <ActivityIndicator color="#FFF" /> : <Text style={s.saveBtnText}>{editingId ? 'Değişiklikleri Kaydet' : 'Kaydet'}</Text>}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </KeyboardAvoidingView>
                </View>
            </Modal>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { flexDirection: 'row', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 10 : 30, paddingBottom: 20, alignItems: 'center' },
    headerTitle: { fontSize: 28, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    headerSub: { fontSize: 13, color: '#64748B', fontWeight: '500', marginTop: 4 },
    searchIconBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#FFF', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 8, elevation: 4 },
    
    kpiWrapper: { flex: 1 },
    kpiCardFix: { padding: 14, borderRadius: 20, justifyContent: 'space-between', shadowColor: '#000', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.15, shadowRadius: 8, elevation: 6 },
    kpiIconWrap: { width: 32, height: 32, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.1)', alignItems: 'center', justifyContent: 'center', marginBottom: 8 },
    kpiValue: { fontSize: 22, fontWeight: '900', color: '#FFF', letterSpacing: -0.5 },
    kpiLabel: { fontSize: 10, color: 'rgba(255,255,255,0.8)', fontWeight: '600', marginTop: 4 },
    
    loader: { flex: 1, justifyContent: 'center', alignItems: 'center', paddingTop: 100 },
    empty: { alignItems: 'center', marginTop: 80 },
    emptyText: { fontSize: 14, color: '#94A3B8', fontWeight: '500', marginTop: 12 },

    swipeContainer: { marginBottom: 12, borderRadius: 16, backgroundColor: '#FFF', overflow: 'hidden' },
    actionButtons: { position: 'absolute', right: 0, top: 0, bottom: 0, flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', width: 140 },
    actionBtn: { width: 70, height: '100%', alignItems: 'center', justifyContent: 'center' },
    swipeContent: { backgroundColor: '#FFF', borderRadius: 16, padding: 16, flexDirection: 'row', alignItems: 'center', borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    
    card: { flex: 1, flexDirection: 'row', alignItems: 'center' },
    cardLeft: { marginRight: 12 },
    premiumIconWrap: { width: 48, height: 48, borderRadius: 16, alignItems: 'center', justifyContent: 'center', overflow: 'hidden', shadowColor: '#3B82F6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 5 },
    premiumIconShadow: { textShadowColor: 'rgba(0, 0, 0, 0.25)', textShadowOffset: { width: 0, height: 2 }, textShadowRadius: 4 },
    premiumIconGlow: { position: 'absolute', top: -10, right: -10, width: 30, height: 30, borderRadius: 15, backgroundColor: 'rgba(255,255,255,0.15)' },
    avatar: { width: 48, height: 48, borderRadius: 16 },
    cardMid: { flex: 1, justifyContent: 'center' },
    cardName: { fontSize: 15, fontWeight: '800', color: '#0F172A', marginBottom: 6 },
    cardSubRow: { flexDirection: 'row', alignItems: 'center' },
    
    statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 8 },
    statusDot: { width: 5, height: 5, borderRadius: 2.5, marginRight: 4 },
    statusText: { fontSize: 10, fontWeight: '800' },
    plateBadge: { backgroundColor: '#FEF08A', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 6, borderWidth: 1, borderColor: '#FDE047' },
    plateText: { fontSize: 10, fontWeight: '800', color: '#854D0E' },
    noPlate: { fontSize: 11, color: '#94A3B8', fontWeight: '600', fontStyle: 'italic' },
    
    callBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#ECFDF5', alignItems: 'center', justifyContent: 'center', marginLeft: 10 },

    fab: { position: 'absolute', right: 20, borderRadius: 28, shadowColor: '#8B5CF6', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.4, shadowRadius: 16, elevation: 10 },
    fabGradient: { width: 56, height: 56, borderRadius: 28, alignItems: 'center', justifyContent: 'center' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end' },
    modalOverlayCenter: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'center', padding: 20 },
    centerModal: { backgroundColor: '#FFF', borderRadius: 24, padding: 24, maxHeight: '80%' },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginBottom: 16 },
    menuItem: { flexDirection: 'row', alignItems: 'center', paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    menuText: { fontSize: 15, fontWeight: '600', color: '#475569' },
    bottomSheet: { backgroundColor: '#FFF', borderTopLeftRadius: 32, borderTopRightRadius: 32, padding: 24 },
    sheetHandle: { width: 40, height: 4, borderRadius: 2, backgroundColor: '#E2E8F0', alignSelf: 'center', marginBottom: 20 },
    sheetTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A', marginBottom: 20 },
    inputRow: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 16, paddingHorizontal: 16, height: 50, borderWidth: 1, borderColor: '#E2E8F0', marginBottom: 20 },
    input: { flex: 1, marginLeft: 10, fontSize: 15, color: '#0F172A', fontWeight: '500' },
    filterLabel: { fontSize: 12, fontWeight: '800', color: '#64748B', letterSpacing: 1, textTransform: 'uppercase', marginBottom: 12 },
    filterRow: { flexDirection: 'row', gap: 10, marginBottom: 30 },
    filterChip: { flex: 1, paddingVertical: 12, borderRadius: 12, backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', alignItems: 'center' },
    filterChipActive: { backgroundColor: '#EEF2FF', borderColor: '#6366F1' },
    filterChipText: { fontSize: 13, fontWeight: '600', color: '#475569' },
    filterChipTextActive: { color: '#6366F1', fontWeight: '800' },
    applyBtn: { backgroundColor: '#0F172A', paddingVertical: 16, borderRadius: 16, alignItems: 'center' },
    applyBtnText: { color: '#FFF', fontSize: 16, fontWeight: '800' },
    // ... existing styles
    formSheet: { backgroundColor: '#FFF', borderTopLeftRadius: 32, borderTopRightRadius: 32, paddingTop: 20 },
    sheetHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 20, marginBottom: 20 },
    closeIcon: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    sectionTitle: { fontSize: 13, fontWeight: '800', color: '#8B5CF6', letterSpacing: 1, textTransform: 'uppercase', marginBottom: 12, marginTop: 10 },
    fieldWrap: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F8FAFC',
        borderWidth: 1,
        borderColor: '#E2E8F0',
        borderRadius: 12,
        paddingHorizontal: 16,
        height: 52,
    },
    birthdayModal: {
        width: '85%',
        backgroundColor: '#FFF',
        borderRadius: 20,
        overflow: 'hidden',
        elevation: 10,
        shadowColor: '#DB2777',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.15,
        shadowRadius: 20,
    },
    birthdayHeader: {
        alignItems: 'center',
        paddingVertical: 24,
    },
    birthdayTitle: {
        fontFamily: 'Inter-Black',
        fontSize: 22,
        color: '#9D174D',
        marginTop: 12,
    },
    birthdayContent: {
        padding: 24,
    },
    birthdayDesc: {
        fontFamily: 'Inter-Regular',
        fontSize: 14,
        color: '#64748B',
        textAlign: 'center',
        marginBottom: 20,
        lineHeight: 20,
    },
    birthdayItem: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FDF2F8',
        padding: 12,
        borderRadius: 12,
        marginBottom: 10,
    },
    birthdayIcon: {
        width: 36, height: 36,
        borderRadius: 18,
        backgroundColor: '#FCE7F3',
        alignItems: 'center', justifyContent: 'center',
        marginRight: 12,
    },
    birthdayName: {
        fontFamily: 'Inter-Bold',
        fontSize: 16,
        color: '#831843',
    },
    birthdayBtn: {
        backgroundColor: '#DB2777',
        paddingVertical: 14,
        borderRadius: 12,
        alignItems: 'center',
        marginTop: 16,
    },
    birthdayBtnText: {
        fontFamily: 'Inter-Bold',
        fontSize: 15,
        color: '#FFF',
    },
    fieldIcon: { marginRight: 12 },
    fieldInput: { flex: 1, fontSize: 15, color: '#0F172A', fontWeight: '500', height: '100%' },
    rowFields: { flexDirection: 'row', justifyContent: 'space-between' },
    picker: { flex: 1, height: '100%' },
    formActions: { flexDirection: 'row', padding: 20, borderTopWidth: 1, borderTopColor: '#F1F5F9', backgroundColor: '#FFF' },
    cancelBtn: { flex: 1, paddingVertical: 16, borderRadius: 16, backgroundColor: '#F1F5F9', alignItems: 'center', marginRight: 12 },
    cancelBtnText: { color: '#475569', fontSize: 15, fontWeight: '700' },
    saveBtn: { flex: 2, paddingVertical: 16, borderRadius: 16, backgroundColor: '#8B5CF6', alignItems: 'center', shadowColor: '#8B5CF6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 6 },
    saveBtnText: { color: '#FFF', fontSize: 15, fontWeight: '800' }
});
