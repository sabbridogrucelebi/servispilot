import React, { useState, useEffect, useContext, useRef, useMemo } from 'react';
import { Modal, View, StyleSheet, FlatList, ActivityIndicator, Alert, TextInput, Text, TouchableOpacity, Animated, Platform, ScrollView, Switch, Image } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';
import { colors, spacing, radius } from '../theme';
import { Header, FilterChipRow, EmptyState, Skeleton, Fab, BottomSheetModal, FormField, EmptyIcon3D } from '../components';
import { todayUi, toApiDate } from '../utils/date';
import DatePickerInput from '../components/DatePickerInput';

export default function CustomersScreen({ navigation }) {
    const { hasPermission } = useContext(AuthContext);
    const [customers, setCustomers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);
    
    // Filters
    const [searchQuery, setSearchQuery] = useState('');
    const [statusFilter, setStatusFilter] = useState('all'); // all, active, passive
    const [typeFilter, setTypeFilter] = useState('all'); // all, company, individual, school

    // Form states
    const [modalVisible, setModalVisible] = useState(false);
    const [saving, setSaving] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const [formData, setFormData] = useState({
        customer_type: '',
        company_name: '',
        company_title: '',
        authorized_person: '',
        authorized_phone: '',
        email: '',
        address: '',
        contract_start_date: '',
        contract_end_date: '',
        vat_rate: '20',
        withholding_rate: '',
        notes: '',
        is_active: true
    });
    const [validationErrors, setValidationErrors] = useState({});
    
    // Custom Selection Modal States
    const [selectionModalVisible, setSelectionModalVisible] = useState(false);
    const [selectionType, setSelectionType] = useState(null);

    const fetchCustomers = async () => {
        try {
            setError(null);
            const response = await api.get('/v1/customers');
            if (response.data.success) {
                setCustomers(response.data.data);
            } else {
                setError(response.data.message || 'Veri alınamadı.');
            }
        } catch (err) {
            console.error(err);
            setError('Bağlantı hatası oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    useEffect(() => { fetchCustomers(); }, []);
    const onRefresh = () => { setRefreshing(true); fetchCustomers(); };

    const openAdd = () => {
        setEditingId(null);
        setFormData({
            customer_type: '',
            company_name: '',
            company_title: '',
            authorized_person: '',
            authorized_phone: '',
            email: '',
            address: '',
            contract_start_date: todayUi(),
            contract_end_date: '',
            vat_rate: '20',
            withholding_rate: '',
            notes: '',
            is_active: true
        });
        setValidationErrors({});
        setModalVisible(true);
    };

    const openEdit = (item) => {
        setEditingId(item.id);
        setFormData({
            customer_type: item.customer_type || '',
            company_name: item.company_name || '',
            company_title: item.company_title || '',
            authorized_person: item.authorized_person || '',
            authorized_phone: item.authorized_phone || '',
            email: item.email || '',
            address: item.address || '',
            contract_start_date: item.contract_start_date ? new Date(item.contract_start_date).toLocaleDateString('tr-TR') : '',
            contract_end_date: item.contract_end_date ? new Date(item.contract_end_date).toLocaleDateString('tr-TR') : '',
            vat_rate: item.vat_rate ? item.vat_rate.toString() : '20',
            withholding_rate: item.withholding_rate || '',
            notes: item.notes || '',
            is_active: item.is_active !== 0
        });
        setValidationErrors({});
        setModalVisible(true);
    };

    // Selection Handlers
    const getSelectionData = () => {
        if (selectionType === 'customer_type') {
            return [
                { label: 'Seçiniz', value: '' },
                { label: 'Fabrika', value: 'Fabrika' },
                { label: 'Okul', value: 'Okul' },
                { label: 'Resmi Daire', value: 'Resmi Daire' },
                { label: 'Diğer Servisler', value: 'Diğer Servisler' }
            ];
        } else if (selectionType === 'vat_rate') {
            return [
                { label: '%0', value: '0' },
                { label: '%1', value: '1' },
                { label: '%10', value: '10' },
                { label: '%20', value: '20' }
            ];
        } else if (selectionType === 'withholding_rate') {
            return [
                { label: 'Tevkifat Yok', value: '' },
                { label: '2/10', value: '2/10' },
                { label: '3/10', value: '3/10' },
                { label: '4/10', value: '4/10' },
                { label: '5/10', value: '5/10' },
                { label: '7/10', value: '7/10' },
                { label: '9/10', value: '9/10' }
            ];
        }
        return [];
    };

    const handleSelectOption = (val) => {
        setFormData({ ...formData, [selectionType]: val });
        setSelectionModalVisible(false);
    };

    const save = async () => {
        setValidationErrors({});
        try {
            setSaving(true);
            const payload = {
                ...formData,
                contract_start_date: toApiDate(formData.contract_start_date),
                contract_end_date: toApiDate(formData.contract_end_date)
            };
            let res;
            if (editingId) {
                res = await api.put(`/v1/customers/${editingId}`, payload);
            } else {
                res = await api.post('/v1/customers', payload);
            }

            if (res.data.success) {
                setModalVisible(false);
                fetchCustomers();
            } else {
                Alert.alert('Hata', res.data.message || 'Kayıt başarısız.');
            }
        } catch (error) {
            if (error.response && error.response.status === 422) {
                setValidationErrors(error.response.data.errors || {});
            } else {
                Alert.alert('Hata', 'Sunucu bağlantı hatası.');
            }
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = (id) => {
        Alert.alert('Silme Onayı', 'Bu müşteriyi silmek istediğinize emin misiniz?', [
            { text: 'İptal', style: 'cancel' },
            { text: 'Sil', style: 'destructive', onPress: async () => {
                try {
                    const res = await api.delete(`/v1/customers/${id}`);
                    if (res.data.success) {
                        setModalVisible(false);
                        Alert.alert('Başarılı', 'Müşteri başarıyla silindi.');
                        fetchCustomers();
                    } else {
                        Alert.alert('Hata', res.data.message);
                    }
                } catch (e) {
                    Alert.alert('Hata', 'Silme işlemi başarısız.');
                }
            }}
        ]);
    };

    // Calculate KPIs
    const totalCustomers = customers.length;
    const activeCustomers = customers.filter(c => c.is_active !== 0).length;
    const passiveCustomers = customers.filter(c => c.is_active === 0).length;
    
    // Dinamik Müşteri Türleri (Okul, Fabrika, Kurumsal vb.)
    const dynamicTypes = useMemo(() => {
        const counts = customers.reduce((acc, c) => {
            const t = c.customer_type || 'Diğer';
            acc[t] = (acc[t] || 0) + 1;
            return acc;
        }, {});
        
        const typesArr = Object.keys(counts).map(type => {
            let label = type;
            let icon = 'domain';
            if (type === 'company' || type.toLowerCase() === 'kurumsal') { label = 'Kurumsal'; icon = 'domain'; }
            else if (type === 'individual' || type.toLowerCase() === 'bireysel') { label = 'Bireysel'; icon = 'account'; }
            else if (type === 'school' || type.toLowerCase() === 'okul') { label = 'Okul'; icon = 'school'; }
            else if (type === 'factory' || type.toLowerCase() === 'fabrika') { label = 'Fabrika'; icon = 'factory'; }
            else { label = type.charAt(0).toUpperCase() + type.slice(1); icon = 'tag'; }
            
            return { type, label, count: counts[type], icon };
        });

        return typesArr.length > 0 ? typesArr : [{ type: 'empty', label: 'Kayıt Yok', count: 0, icon: 'help-circle' }];
    }, [customers]);

    const [dynamicTypeIndex, setDynamicTypeIndex] = useState(0);
    const dynamicFade = useRef(new Animated.Value(1)).current;

    // 3 Saniyede bir kartı çevir (Fade in/out)
    useEffect(() => {
        if (dynamicTypes.length <= 1) return;
        
        const interval = setInterval(() => {
            Animated.timing(dynamicFade, { toValue: 0, duration: 400, useNativeDriver: true }).start(() => {
                setDynamicTypeIndex(prev => (prev + 1) % dynamicTypes.length);
                Animated.timing(dynamicFade, { toValue: 1, duration: 400, useNativeDriver: true }).start();
            });
        }, 3000);

        return () => clearInterval(interval);
    }, [dynamicTypes.length]);

    const currentDynamicType = dynamicTypes[dynamicTypeIndex] || dynamicTypes[0];

    const get3DIcon = (iconName) => {
        switch (iconName) {
            case 'domain': return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Office%20Building.png';
            case 'account': return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/People/Man%20Office%20Worker.png';
            case 'school': return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/School.png';
            case 'factory': return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Factory.png';
            case 'help-circle': return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Question%20Mark.png';
            default: return 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Objects/Label.png';
        }
    };

    const filteredData = customers.filter(c => {
        // Search Filter
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            const matchName = c.company_name?.toLowerCase().includes(query);
            const matchPerson = c.authorized_person?.toLowerCase().includes(query);
            const matchPhone = c.authorized_phone?.toLowerCase().includes(query);
            if (!matchName && !matchPerson && !matchPhone) return false;
        }
        // Status Filter
        if (statusFilter === 'active' && c.is_active === 0) return false;
        if (statusFilter === 'passive' && c.is_active !== 0) return false;
        // Type Filter
        if (typeFilter !== 'all' && c.customer_type !== typeFilter) return false;
        
        return true;
    });

    const filterChips = [
        { label: 'Tüm Türler', value: 'all', count: customers.length },
        { label: 'Fabrika', value: 'Fabrika', count: customers.filter(c => c.customer_type === 'Fabrika').length },
        { label: 'Okul', value: 'Okul', count: customers.filter(c => c.customer_type === 'Okul').length },
        { label: 'Resmi Daire', value: 'Resmi Daire', count: customers.filter(c => c.customer_type === 'Resmi Daire').length },
        { label: 'Diğer', value: 'Diğer Servisler', count: customers.filter(c => c.customer_type === 'Diğer Servisler').length },
    ];

    // Premium 3D Kart Render Metodu
    const renderCustomer = ({ item, index }) => {
        const type = item.customer_type;
        let iconName = 'domain';
        let gradientColors = ['#64748B', '#475569']; // Default
        
        if (type === 'Fabrika') {
            iconName = 'factory';
            gradientColors = ['#0284C7', '#0369A1']; // Premium Blue
        } else if (type === 'Okul') {
            iconName = 'school';
            gradientColors = ['#F59E0B', '#D97706']; // Premium Orange
        } else if (type === 'Resmi Daire') {
            iconName = 'bank';
            gradientColors = ['#8B5CF6', '#6D28D9']; // Premium Purple
        } else if (type === 'Diğer Servisler') {
            iconName = 'office-building';
            gradientColors = ['#14B8A6', '#0F766E']; // Premium Teal
        }

        const title = item.company_name || item.authorized_person || 'İsimsiz Müşteri';
        const sub = item.company_name ? `Yetkili: ${item.authorized_person || '-'}` : (item.authorized_person ? 'Bireysel / Şahıs' : '-');
        
        const isActive = !!item.is_active;
        
        return (
            <TouchableOpacity activeOpacity={0.8} onPress={() => navigation.navigate('CustomerDetail', { customerId: item.id, customerName: title })} style={styles.cardWrapper}>
                <View style={styles.cardContainer}>
                    <LinearGradient
                        colors={gradientColors}
                        style={styles.cardIconBox}
                        start={{ x: 0, y: 0 }}
                        end={{ x: 1, y: 1 }}
                    >
                        <Icon name={iconName} size={28} color="#FFF" style={styles.iconGlow} />
                    </LinearGradient>
                    
                    <View style={styles.cardBody}>
                        <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 6 }}>
                            <Text style={styles.cardTitle} numberOfLines={1}>{title}</Text>
                            <View style={[styles.statusBadge, { backgroundColor: isActive ? '#DCFCE7' : '#FEE2E2' }]}>
                                <Text style={[styles.statusText, { color: isActive ? '#166534' : '#991B1B' }]}>
                                    {isActive ? 'Aktif' : 'Pasif'}
                                </Text>
                            </View>
                        </View>
                        <View style={styles.cardSubRow}>
                            <Icon name="account" size={14} color="#64748B" />
                            <Text style={styles.cardSubText} numberOfLines={1}>{sub}</Text>
                        </View>
                        <View style={styles.cardSubRow}>
                            <Icon name="calendar-range" size={14} color="#64748B" />
                            <Text style={styles.cardSubText}>
                                Sözleşme: {item.contract_start_date ? new Date(item.contract_start_date).toLocaleDateString('tr-TR') : 'Belirtilmemiş'}
                            </Text>
                        </View>
                    </View>

                    <View style={styles.cardRight}>
                        {hasPermission('customers.edit') ? (
                            <TouchableOpacity style={[styles.actionBtn, { backgroundColor: '#EEF2FF' }]} onPress={() => openEdit(item)}>
                                <Icon name="pencil" size={20} color="#4F46E5" />
                            </TouchableOpacity>
                        ) : (
                            <View style={styles.actionBtn}>
                                <Icon name="chevron-right" size={24} color="#CBD5E1" />
                            </View>
                        )}
                    </View>
                </View>
            </TouchableOpacity>
        );
    };

    return (
        <SafeAreaView style={styles.container}>
            <Header 
                title="Müşteriler" 
                leftIcon="arrow-left" 
                onLeftPress={() => navigation.goBack()} 
                rightIcon={hasPermission('customers.create') ? "plus" : null}
                onRightPress={hasPermission('customers.create') ? openAdd : null}
            />
            
            <View style={styles.content}>
                {/* Premium Web-Parity KPI Cards (2x2 Grid - Ekrana Sığan) */}
                <View style={styles.kpiGrid}>
                    <View style={styles.kpiCardWrapper}>
                        <LinearGradient colors={['#3B82F6', '#2563EB', '#1E3A8A']} style={styles.kpiCardGradient} start={{x:0, y:0}} end={{x:1, y:1}}>
                            <View style={styles.kpiPattern1} />
                            <Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Travel%20and%20places/Office%20Building.png' }} style={[styles.kpiIcon3D, { width: 64, height: 64, opacity: 1 }]} resizeMode="contain" />
                            <Text style={styles.kpiValue}>{totalCustomers}</Text>
                            <Text style={styles.kpiTitle}>Toplam Müşteri</Text>
                        </LinearGradient>
                    </View>
                    
                    <View style={styles.kpiCardWrapper}>
                        <LinearGradient colors={['#10B981', '#059669', '#064E3B']} style={styles.kpiCardGradient} start={{x:0, y:0}} end={{x:1, y:1}}>
                            <View style={styles.kpiPattern1} />
                            <Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Check%20Mark%20Button.png' }} style={[styles.kpiIcon3D, { width: 64, height: 64, opacity: 1 }]} resizeMode="contain" />
                            <Text style={styles.kpiValue}>{activeCustomers}</Text>
                            <Text style={styles.kpiTitle}>Aktif Müşteri</Text>
                        </LinearGradient>
                    </View>

                    <View style={styles.kpiCardWrapper}>
                        <LinearGradient colors={['#F43F5E', '#E11D48', '#881337']} style={styles.kpiCardGradient} start={{x:0, y:0}} end={{x:1, y:1}}>
                            <View style={styles.kpiPattern1} />
                            <Image source={{ uri: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis/Symbols/Prohibited.png' }} style={[styles.kpiIcon3D, { width: 64, height: 64, opacity: 1 }]} resizeMode="contain" />
                            <Text style={styles.kpiValue}>{passiveCustomers}</Text>
                            <Text style={styles.kpiTitle}>Pasif Müşteri</Text>
                        </LinearGradient>
                    </View>

                    <View style={styles.kpiCardWrapper}>
                        <LinearGradient colors={['#0EA5E9', '#0284C7', '#0C4A6E']} style={styles.kpiCardGradient} start={{x:0, y:0}} end={{x:1, y:1}}>
                            <View style={styles.kpiPattern1} />
                            
                            <Animated.View style={{ opacity: dynamicFade, flex: 1, justifyContent: 'center' }}>
                                <Image source={{ uri: get3DIcon(currentDynamicType.icon) }} style={[styles.kpiIcon3D, { width: 64, height: 64, opacity: 1 }]} resizeMode="contain" />
                                <Text style={styles.kpiValue}>{currentDynamicType.count}</Text>
                                <Text style={styles.kpiTitle}>{currentDynamicType.label} Müşterisi</Text>
                            </Animated.View>

                        </LinearGradient>
                    </View>
                </View>

                {/* Search and Filters */}
                <View style={styles.searchSection}>
                    <View style={styles.searchInputBox}>
                        <Icon name="magnify" size={20} color="#94A3B8" style={{marginLeft: 12}} />
                        <TextInput
                            style={styles.searchInput}
                            placeholder="Firma adı veya yetkili ara..."
                            placeholderTextColor="#94A3B8"
                            value={searchQuery}
                            onChangeText={setSearchQuery}
                        />
                    </View>
                    <View style={styles.filterRow}>
                        <View style={styles.filterDropBtn}>
                            <Text style={styles.filterDropText}>Durum: {statusFilter === 'all' ? 'Tümü' : (statusFilter === 'active' ? 'Aktif' : 'Pasif')}</Text>
                            <Icon name="chevron-down" size={16} color="#64748B" />
                        </View>
                        <View style={{flex: 1}}>
                            <FilterChipRow chips={filterChips} activeValue={typeFilter} onSelect={setTypeFilter} />
                        </View>
                    </View>
                </View>

                {loading ? (
                    <View style={{ padding: 20 }}>
                        <Skeleton height={80} radius={20} mb={15} />
                        <Skeleton height={80} radius={20} mb={15} />
                        <Skeleton height={80} radius={20} mb={15} />
                    </View>
                ) : filteredData.length === 0 ? (
                    <EmptyState 
                        icon={<EmptyIcon3D icon="account-group" color="#3B82F6" />}
                        title="Müşteri Bulunamadı"
                        message={filter === 'all' ? "Henüz kayıtlı bir müşteri yok." : "Bu filtreye uygun müşteri bulunamadı."}
                    />
                ) : (
                    <FlatList
                        data={filteredData}
                        keyExtractor={(item) => item.id.toString()}
                        renderItem={renderCustomer}
                        contentContainerStyle={styles.listContent}
                        showsVerticalScrollIndicator={false}
                        refreshing={refreshing}
                        onRefresh={onRefresh}
                    />
                )}
            </View>

            {hasPermission('customers.create') && (
                <Fab icon="plus" onPress={openAdd} style={{ bottom: Platform.OS === 'ios' ? 105 : 85 }} />
            )}

            <BottomSheetModal
                visible={modalVisible}
                onClose={() => setModalVisible(false)}
                title={editingId ? "Müşteriyi Düzenle" : "Yeni Müşteri"}
                footer={
                    <View style={styles.modalActions}>
                        {editingId && hasPermission('customers.delete') && (
                            <TouchableOpacity style={styles.deleteBtn} onPress={() => handleDelete(editingId)}>
                                <Icon name="trash-can-outline" size={24} color="#EF4444" />
                            </TouchableOpacity>
                        )}
                        <TouchableOpacity style={[styles.saveBtn, saving && { opacity: 0.7 }]} onPress={save} disabled={saving}>
                            {saving ? <ActivityIndicator color="#fff" /> : <Text style={styles.saveBtnText}>Kaydet</Text>}
                        </TouchableOpacity>
                    </View>
                }
                overlayContent={
                    selectionModalVisible && (
                        <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end', zIndex: 9999 }]}>
                            <View style={[styles.modalContent, { maxHeight: '80%' }]}>
                                <View style={styles.modalHeader}>
                                    <Text style={styles.modalTitle}>Lütfen Seçiniz</Text>
                                    <TouchableOpacity onPress={() => setSelectionModalVisible(false)} style={styles.modalCloseBtn}>
                                        <Icon name="close" size={24} color="#64748B" />
                                    </TouchableOpacity>
                                </View>
                                <ScrollView contentContainerStyle={{ padding: 16 }}>
                                    {getSelectionData().map((item, index) => (
                                        <TouchableOpacity 
                                            key={index} 
                                            style={styles.selectionListItem}
                                            onPress={() => handleSelectOption(item.value)}
                                        >
                                            <Text style={styles.selectionListText}>{item.label}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </ScrollView>
                            </View>
                        </View>
                    )
                }
            >
                <Text style={styles.inputLabel}>MÜŞTERİ TÜRÜ</Text>
                <TouchableOpacity 
                    style={styles.selectorBtn} 
                    onPress={() => { setSelectionType('customer_type'); setSelectionModalVisible(true); }}
                >
                    <Text style={[styles.selectorBtnText, !formData.customer_type && { color: '#94A3B8' }]}>
                        {formData.customer_type || 'Seçiniz'}
                    </Text>
                    <Icon name="chevron-down" size={20} color="#64748B" />
                </TouchableOpacity>

                <FormField label="Firma Adı" value={formData.company_name} onChangeText={(t) => setFormData({...formData, company_name: t})} error={validationErrors.company_name} required />
                <FormField label="Firma Ünvanı" value={formData.company_title} onChangeText={(t) => setFormData({...formData, company_title: t})} error={validationErrors.company_title} />
                <FormField label="Yetkili Kişi" value={formData.authorized_person} onChangeText={(t) => setFormData({...formData, authorized_person: t})} error={validationErrors.authorized_person} required />
                <FormField label="Yetkili Telefon" value={formData.authorized_phone} onChangeText={(t) => setFormData({...formData, authorized_phone: t})} error={validationErrors.authorized_phone} keyboardType="phone-pad" required />
                <FormField label="E-Posta Adresi" value={formData.email} onChangeText={(t) => setFormData({...formData, email: t})} error={validationErrors.email} keyboardType="email-address" autoCapitalize="none" />
                <FormField label="Firma Adresi" value={formData.address} onChangeText={(t) => setFormData({...formData, address: t})} error={validationErrors.address} multiline />
                <FormField label="Notlar" value={formData.notes} onChangeText={(t) => setFormData({...formData, notes: t})} error={validationErrors.notes} multiline />
                
                <View style={{ flexDirection: 'row', gap: 10, marginTop: 10 }}>
                    <View style={{ flex: 1 }}>
                        <DatePickerInput label="Sözleşme Başlangıç" value={formData.contract_start_date} onChange={(d) => setFormData({...formData, contract_start_date: d})} error={validationErrors.contract_start_date} />
                    </View>
                    <View style={{ flex: 1 }}>
                        <DatePickerInput label="Sözleşme Bitiş" value={formData.contract_end_date} onChange={(d) => setFormData({...formData, contract_end_date: d})} error={validationErrors.contract_end_date} />
                    </View>
                </View>

                <Text style={styles.inputLabel}>KDV ORANI</Text>
                <TouchableOpacity 
                    style={styles.selectorBtn} 
                    onPress={() => { setSelectionType('vat_rate'); setSelectionModalVisible(true); }}
                >
                    <Text style={[styles.selectorBtnText, !formData.vat_rate && { color: '#94A3B8' }]}>
                        {formData.vat_rate ? `%${formData.vat_rate}` : 'Seçiniz'}
                    </Text>
                    <Icon name="chevron-down" size={20} color="#64748B" />
                </TouchableOpacity>

                <Text style={styles.inputLabel}>TEVKİFAT ORANI</Text>
                <TouchableOpacity 
                    style={styles.selectorBtn} 
                    onPress={() => { setSelectionType('withholding_rate'); setSelectionModalVisible(true); }}
                >
                    <Text style={[styles.selectorBtnText, !formData.withholding_rate && { color: '#94A3B8' }]}>
                        {formData.withholding_rate || 'Tevkifat Yok'}
                    </Text>
                    <Icon name="chevron-down" size={20} color="#64748B" />
                </TouchableOpacity>

                <View style={styles.switchRow}>
                    <Text style={styles.switchLabel}>Müşteri aktif olarak kaydedilsin</Text>
                    <Switch
                        value={formData.is_active}
                        onValueChange={(val) => setFormData({...formData, is_active: val})}
                        trackColor={{ false: '#CBD5E1', true: '#3B82F6' }}
                    />
                </View>

            </BottomSheetModal>



        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F1F5F9' },
    content: { flex: 1 },
    
    // KPI Cards (2x2 Grid)
    kpiGrid: { 
        flexDirection: 'row', 
        flexWrap: 'wrap', 
        justifyContent: 'space-between', 
        paddingHorizontal: 16, 
        paddingVertical: 12, 
        gap: 12 
    },
    kpiCardWrapper: {
        width: '48%', // Ekrana 2 tane sığar
        height: 100,
        ...Platform.select({
            ios: { shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.25, shadowRadius: 12 },
            android: { elevation: 10 },
            web: { boxShadow: '0px 10px 20px rgba(0,0,0,0.25)' }
        })
    },
    kpiCardGradient: {
        flex: 1,
        borderRadius: 22,
        padding: 16,
        overflow: 'hidden',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.15)'
    },
    kpiPattern1: { position: 'absolute', right: -20, bottom: -20, width: 80, height: 80, borderRadius: 40, backgroundColor: 'rgba(255,255,255,0.08)' },
    kpiIcon3D: { 
        position: 'absolute', 
        right: 10, 
        bottom: 10, 
        ...Platform.select({
            ios: { shadowColor: '#000', shadowOffset: { width: -2, height: 4 }, shadowopacity: 1, shadowRadius: 5 },
            android: { textShadowColor: 'rgba(0,0,0,0.5)', textShadowOffset: { width: -2, height: 4 }, textShadowRadius: 5 },
            web: { filter: 'drop-shadow(-2px 4px 5px rgba(0,0,0,0.5))' }
        })
    },
    kpiValue: { color: '#FFF', fontSize: 30, fontWeight: '900', marginBottom: 2, letterSpacing: -1, textShadowColor: 'rgba(0,0,0,0.3)', textShadowOffset: {width: 0, height: 2}, textShadowRadius: 4 },
    kpiTitle: { color: 'rgba(255,255,255,0.85)', fontSize: 12, fontWeight: '700' },

    // Search & Filter
    searchSection: { paddingHorizontal: 16, marginBottom: 10 },
    searchInputBox: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        borderRadius: 14,
        height: 48,
        borderWidth: 1,
        borderColor: '#E2E8F0',
        marginBottom: 10
    },
    searchInput: { flex: 1, paddingHorizontal: 10, fontSize: 14, color: '#0F172A', height: '100%' },
    filterRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    filterDropBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFFFFF', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 10, borderWidth: 1, borderColor: '#E2E8F0' },
    filterDropText: { fontSize: 13, color: '#64748B', fontWeight: '600', marginRight: 4 },

    listContent: { padding: 16, paddingBottom: 100 },
    cardWrapper: {
        marginBottom: 16,
        ...Platform.select({
            ios: { shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.15, shadowRadius: 10 },
            android: { elevation: 4 },
            web: { boxShadow: '0px 6px 15px rgba(148, 163, 184, 0.15)' }
        })
    },
    cardContainer: {
        flexDirection: 'row',
        backgroundColor: '#FFFFFF',
        borderRadius: 20,
        padding: 16,
        alignItems: 'center',
    },
    cardIconBox: {
        width: 52,
        height: 52,
        borderRadius: 16,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 16,
    },
    iconGlow: {
        ...Platform.select({
            web: { filter: 'drop-shadow(0px 2px 4px rgba(255,255,255,0.5))' }
        })
    },
    cardBody: { flex: 1 },
    cardTitle: { flex: 1, fontSize: 16, fontWeight: '800', color: '#0F172A', letterSpacing: -0.3, marginRight: 8 },
    statusBadge: { paddingHorizontal: 8, paddingVertical: 2, borderRadius: 6 },
    statusText: { fontSize: 10, fontWeight: '700' },
    cardSubRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 4 },
    cardSubText: { fontSize: 12, color: '#64748B', marginLeft: 6, fontWeight: '500' },
    cardRight: { paddingLeft: 12 },
    actionBtn: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    
    // Form Elements
    selectorBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 14, paddingHorizontal: 16, height: 50, marginBottom: 16 },
    selectorBtnText: { fontSize: 14, color: '#0F172A', fontWeight: '500' },
    inputLabel: { fontSize: 11, fontWeight: '800', color: '#64748B', marginBottom: 6, marginLeft: 4, letterSpacing: 0.5 },
    switchRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 12, borderTopWidth: 1, borderTopColor: '#F1F5F9', marginTop: 16, marginBottom: 30 },
    switchLabel: { fontSize: 14, fontWeight: '600', color: '#334155' },

    // Modals
    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: '#FFFFFF', borderTopLeftRadius: 32, borderTopRightRadius: 32 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 24, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    modalTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A' },
    modalCloseBtn: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    selectionListItem: { paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    selectionListText: { fontSize: 16, fontWeight: '700', color: '#1E293B', textAlign: 'center' },
    
    modalActions: { flexDirection: 'row', gap: 12, paddingTop: 10 },
    deleteBtn: { width: 56, height: 56, borderRadius: 16, backgroundColor: '#FEF2F2', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#FEE2E2' },
    saveBtn: { flex: 1, height: 56, borderRadius: 16, backgroundColor: '#3B82F6', alignItems: 'center', justifyContent: 'center', shadowColor: '#3B82F6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 4 },
    saveBtnText: { color: '#FFFFFF', fontSize: 16, fontWeight: '800', letterSpacing: 0.5 },

    // Dummy Tab
    dummyTabBar: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E2E8F0', paddingBottom: Platform.OS === 'ios' ? 20 : 0, flexDirection: 'row', height: Platform.OS === 'ios' ? 85 : 65, alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 10 },
    dummyTab: { flex: 1, alignItems: 'center', justifyContent: 'center', height: '100%' },
    dummyTabLabel: { fontSize: 10, fontWeight: '600', marginTop: 4, color: '#94A3B8' },
    dummyTabCenter: { flex: 1, alignItems: 'center' },
    dummyTabCenterInner: { width: 56, height: 56, borderRadius: 28, backgroundColor: '#2563EB', alignItems: 'center', justifyContent: 'center', marginTop: -35, shadowColor: '#2563EB', shadowOffset: {width:0, height:4}, shadowOpacity:0.3, shadowRadius:8, elevation: 5 }
});
