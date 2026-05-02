import React, { useState, useEffect, useContext } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Alert, Platform, Modal, Linking } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as DocumentPicker from 'expo-document-picker';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';
import { colors, spacing, radius, typography, shadow } from '../theme';
import { Header, EmptyState, BottomSheetModal, FormField, ListItemCard, Skeleton } from '../components';
import { toApiDate, todayUi } from '../utils/date';
import DatePickerInput from '../components/DatePickerInput';

export default function CustomerDetailScreen({ route, navigation }) {
    const { hasPermission } = useContext(AuthContext);
    const { customerId, customerName } = route.params;
    const [customer, setCustomer] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('info');

    // Contracts Modal
    const [contractModal, setContractModal] = useState(false);
    const [contractEditingId, setContractEditingId] = useState(null);
    const [savingContract, setSavingContract] = useState(false);
    const [contractErrors, setContractErrors] = useState({});
    const [contractData, setContractData] = useState({ year: '', start_date: '', end_date: '', contract_file: null });

    // Routes Modal & Data
    const [routeModal, setRouteModal] = useState(false);
    const [routeEditingId, setRouteEditingId] = useState(null);
    const [savingRoute, setSavingRoute] = useState(false);
    const [routeErrors, setRouteErrors] = useState({});
    const [vehicles, setVehicles] = useState([]);
    
    // Select Picker State
    const [selectConfig, setSelectConfig] = useState({ visible: false, title: '', options: [], onSelect: null });

    const [routeData, setRouteData] = useState({
        route_name: '', service_type: 'both', vehicle_type: '',
        morning_vehicle_id: null, evening_vehicle_id: null,
        fee_type: 'paid', saturday_pricing: 0, sunday_pricing: 0,
        morning_fee: '', evening_fee: '', fallback_morning_fee: '', fallback_evening_fee: ''
    });

    // Invoices State
    const [invoiceSummary, setInvoiceSummary] = useState(null);
    const [invoiceLoading, setInvoiceLoading] = useState(false);
    const [selectedMonth, setSelectedMonth] = useState(new Date().getMonth() + 1);
    const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());

    // Users Modal & Data
    const [userModal, setUserModal] = useState(false);
    const [userEditingId, setUserEditingId] = useState(null);
    const [savingUser, setSavingUser] = useState(false);
    const [userErrors, setUserErrors] = useState({});
    const [userData, setUserData] = useState({
        name: '', username: '', email: '', password: '', is_active: true
    });

    const fetchCustomerDetail = async () => {
        try {
            setLoading(true); setError(null);
            const [custRes, vehRes] = await Promise.all([
                api.get(`/v1/customers/${customerId}`),
                api.get('/v1/vehicles').catch(() => ({ data: { data: { vehicles: [] } } }))
            ]);
            
            if (custRes.data.success) setCustomer(custRes.data.data);
            else setError(custRes.data.message || 'Veri alınamadı.');
            
            const fetchedVehicles = vehRes?.data?.data?.vehicles || vehRes?.data?.data || [];
            setVehicles(Array.isArray(fetchedVehicles) ? fetchedVehicles : []);

        } catch (err) { setError('Bağlantı hatası.'); }
        finally { setLoading(false); }
    };

    const fetchInvoiceSummary = async () => {
        try {
            setInvoiceLoading(true);
            const res = await api.get(`/v1/customers/${customerId}/invoices`, { params: { month: selectedMonth, year: selectedYear } });
            if (res.data.success) setInvoiceSummary(res.data.data);
        } catch (err) {
            console.error('Invoice fetch error:', err);
        } finally {
            setInvoiceLoading(false);
        }
    };

    useEffect(() => {
        fetchCustomerDetail();
    }, [customerId]);

    useEffect(() => {
        if (activeTab === 'invoices') {
            fetchInvoiceSummary();
        }
    }, [activeTab, selectedMonth, selectedYear]);

    const openContractAdd = () => {
        setContractEditingId(null);
        setContractData({ year: new Date().getFullYear().toString(), start_date: todayUi(), end_date: todayUi(), contract_file: null });
        setContractErrors({});
        setContractModal(true);
    };

    const openContractEdit = (item) => {
        setContractEditingId(item.id);
        setContractData({
            year: item.year?.toString() || '',
            start_date: item.start_date ? new Date(item.start_date).toLocaleDateString('tr-TR') : '',
            end_date: item.end_date ? new Date(item.end_date).toLocaleDateString('tr-TR') : '',
            contract_file: null
        });
        setContractErrors({});
        setContractModal(true);
    };

    const pickContractFile = async () => {
        try {
            const result = await DocumentPicker.getDocumentAsync({
                type: ['application/pdf', 'image/jpeg', 'image/png'],
                copyToCacheDirectory: true
            });
            if (result.assets && result.assets.length > 0) {
                setContractData({ ...contractData, contract_file: result.assets[0] });
            }
        } catch (err) {
            console.log('Document picker error:', err);
        }
    };

    const handleContractSave = async () => {
        setContractErrors({});
        if (!contractData.year || !contractData.start_date || !contractData.end_date) {
            Alert.alert('Hata', 'Yıl, Başlangıç ve Bitiş tarihleri zorunludur.'); return;
        }

        const formData = new FormData();
        formData.append('customer_id', customerId);
        formData.append('year', contractData.year);
        formData.append('start_date', toApiDate(contractData.start_date));
        formData.append('end_date', toApiDate(contractData.end_date));

        if (contractData.contract_file) {
            formData.append('contract_file', {
                uri: contractData.contract_file.uri,
                name: contractData.contract_file.name,
                type: contractData.contract_file.mimeType || 'application/pdf'
            });
        }

        if (contractEditingId) {
            formData.append('_method', 'PUT');
        }

        setSavingContract(true);
        try {
            if (contractEditingId) {
                await api.post(`/v1/contracts/${contractEditingId}`, formData, { headers: { 'Content-Type': 'multipart/form-data' } });
            } else {
                await api.post('/v1/contracts', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
            }
            setContractModal(false);
            fetchCustomerDetail();
        } catch (e) {
            if (e.response?.status === 422) setContractErrors(e.response.data.errors || {});
            else Alert.alert('Hata', 'Kaydedilemedi.');
        } finally { setSavingContract(false); }
    };

    const confirmContractDelete = (id) => {
        const del = async () => {
            try { await api.delete(`/v1/contracts/${id}`); fetchCustomerDetail(); } catch (e) { Alert.alert('Hata', 'Kayıt silinemedi.'); }
        };
        Alert.alert('Kayıt Silinecek', 'Bu sözleşmeyi silmek istediğinize emin misiniz?', [
            { text: 'Vazgeç', style: 'cancel' }, { text: 'Sil', style: 'destructive', onPress: del }
        ]);
    };

    const openRouteAdd = () => {
        setRouteEditingId(null);
        setRouteData({
            route_name: '', service_type: 'both', vehicle_type: '',
            morning_vehicle_id: null, evening_vehicle_id: null,
            fee_type: 'paid', saturday_pricing: 0, sunday_pricing: 0,
            morning_fee: '', evening_fee: '', fallback_morning_fee: '', fallback_evening_fee: ''
        });
        setRouteErrors({});
        setRouteModal(true);
    };

    const openRouteEdit = (item) => {
        setRouteEditingId(item.id);
        setRouteData({
            route_name: item.route_name || '',
            service_type: item.service_type || 'both',
            vehicle_type: item.vehicle_type || '',
            morning_vehicle_id: item.morning_vehicle_id || null,
            evening_vehicle_id: item.evening_vehicle_id || null,
            fee_type: item.fee_type || 'paid',
            saturday_pricing: item.saturday_pricing ? 1 : 0,
            sunday_pricing: item.sunday_pricing ? 1 : 0,
            morning_fee: item.morning_fee?.toString() || '',
            evening_fee: item.evening_fee?.toString() || '',
            fallback_morning_fee: item.fallback_morning_fee?.toString() || '',
            fallback_evening_fee: item.fallback_evening_fee?.toString() || ''
        });
        setRouteErrors({});
        setRouteModal(true);
    };

    const handleRouteSave = async () => {
        setRouteErrors({});
        if (!routeData.route_name) { Alert.alert('Hata', 'Güzergah adı zorunludur.'); return; }

        const payload = {
            customer_id: customerId,
            route_name: routeData.route_name,
            service_type: routeData.service_type,
            vehicle_type: routeData.vehicle_type,
            morning_vehicle_id: (routeData.service_type === 'morning' || routeData.service_type === 'both' || routeData.service_type === 'shift') ? routeData.morning_vehicle_id : null,
            evening_vehicle_id: (routeData.service_type === 'evening' || routeData.service_type === 'both' || routeData.service_type === 'shift') ? routeData.evening_vehicle_id : null,
            fee_type: routeData.fee_type,
            saturday_pricing: routeData.saturday_pricing,
            sunday_pricing: routeData.sunday_pricing,
            morning_fee: routeData.morning_fee,
            evening_fee: routeData.evening_fee,
            fallback_morning_fee: routeData.fallback_morning_fee,
            fallback_evening_fee: routeData.fallback_evening_fee
        };

        setSavingRoute(true);
        try {
            if (routeEditingId) await api.put(`/v1/service-routes/${routeEditingId}`, payload);
            else await api.post('/v1/service-routes', payload);
            setRouteModal(false);
            fetchCustomerDetail();
        } catch (e) {
            if (e.response?.status === 422) {
                const errs = e.response.data.errors || {};
                setRouteErrors(errs);
                const firstErr = Object.values(errs).flat()[0];
                if (firstErr) Alert.alert('Eksik Bilgi', firstErr);
            }
            else Alert.alert('Hata', 'Kaydedilemedi.');
        } finally { setSavingRoute(false); }
    };

    const handleToggleRouteStatus = async (item) => {
        try {
            const payload = { is_active: item.is_active ? 0 : 1 };
            await api.put(`/v1/service-routes/${item.id}`, payload);
            fetchCustomerDetail();
        } catch (e) {
            Alert.alert('Hata', 'Durum güncellenemedi.');
        }
    };

    // User Operations
    const openUserAdd = () => {
        setUserEditingId(null);
        setUserData({ name: '', username: '', email: '', password: '', is_active: true });
        setUserErrors({});
        setUserModal(true);
    };

    const openUserEdit = (item) => {
        setUserEditingId(item.id);
        setUserData({
            name: item.name || '',
            username: item.username || '',
            email: item.email || '',
            password: '', // Boş bırak, sadece girilirse değişecek
            is_active: item.is_active ? true : false
        });
        setUserErrors({});
        setUserModal(true);
    };

    const handleUserSave = async () => {
        setUserErrors({});
        if (!userData.name || !userData.username) { 
            Alert.alert('Hata', 'Ad Soyad ve Kullanıcı Adı zorunludur.'); 
            return; 
        }
        if (!userEditingId && !userData.password) {
            Alert.alert('Hata', 'Yeni kullanıcı için şifre zorunludur.');
            return;
        }

        const payload = { ...userData };

        setSavingUser(true);
        try {
            if (userEditingId) await api.put(`/v1/customers/${customerId}/portal-users/${userEditingId}`, payload);
            else await api.post(`/v1/customers/${customerId}/portal-users`, payload);
            setUserModal(false);
            fetchCustomerDetail();
        } catch (e) {
            if (e.response?.status === 422) {
                const errs = e.response.data.errors || {};
                setUserErrors(errs);
                const firstErr = Object.values(errs).flat()[0];
                if (firstErr) Alert.alert('Eksik Bilgi', firstErr);
            }
            else Alert.alert('Hata', 'Kullanıcı kaydedilemedi.');
        } finally { setSavingUser(false); }
    };

    const confirmUserDelete = (id) => {
        const del = async () => {
            try { await api.delete(`/v1/customers/${customerId}/portal-users/${id}`); fetchCustomerDetail(); } catch (e) { Alert.alert('Hata', 'Kullanıcı silinemedi.'); }
        };
        Alert.alert('Kayıt Silinecek', 'Bu kullanıcıyı silmek istediğinize emin misiniz?', [
            { text: 'Vazgeç', style: 'cancel' }, { text: 'Sil', style: 'destructive', onPress: del }
        ]);
    };

    const handleToggleUserStatus = async (item) => {
        try {
            const payload = { is_active: item.is_active ? 0 : 1 };
            await api.patch(`/v1/customers/${customerId}/portal-users/${item.id}/toggle-status`, payload);
            fetchCustomerDetail();
        } catch (e) {
            Alert.alert('Hata', 'Durum güncellenemedi.');
        }
    };

    const confirmRouteDelete = (id) => {
        const del = async () => {
            try { await api.delete(`/v1/service-routes/${id}`); fetchCustomerDetail(); } catch (e) { Alert.alert('Hata', 'Kayıt silinemedi.'); }
        };
        Alert.alert('Kayıt Silinecek', 'Bu rotayı silmek istediğinize emin misiniz?', [
            { text: 'Vazgeç', style: 'cancel' }, { text: 'Sil', style: 'destructive', onPress: del }
        ]);
    };

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

    const renderInfoTab = () => {
        return (
            <View style={styles.tabContent}>
                <View style={styles.sectionCard}>
                    <Text style={styles.sectionTitle}>Firma Bilgileri</Text>
                    <Text style={styles.sectionSubtitle}>Müşteriye ait temel iletişim ve kayıt bilgileri</Text>
                    
                    <View style={styles.infoGrid}>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>FİRMA ADI</Text>
                            <Text style={styles.infoValue}>{customer.company_name || '-'}</Text>
                        </View>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>FİRMA ÜNVANI</Text>
                            <Text style={styles.infoValue}>{customer.company_title || '-'}</Text>
                        </View>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>YETKİLİ KİŞİ</Text>
                            <Text style={styles.infoValue}>{customer.authorized_person || '-'}</Text>
                        </View>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>YETKİLİ TELEFON</Text>
                            <Text style={styles.infoValue}>{customer.authorized_phone || '-'}</Text>
                        </View>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>E-POSTA</Text>
                            <Text style={styles.infoValue}>{customer.email || '-'}</Text>
                        </View>
                        <View style={styles.infoCol}>
                            <Text style={styles.infoLabel}>MÜŞTERİ TÜRÜ</Text>
                            <Text style={styles.infoValue}>{customer.customer_type || 'Kurumsal'}</Text>
                        </View>
                        <View style={[styles.infoCol, { width: '100%' }]}>
                            <Text style={styles.infoLabel}>ADRES BİLGİSİ</Text>
                            <Text style={styles.infoValue}>{customer.address || 'Adres belirtilmemiş'}</Text>
                        </View>
                    </View>
                </View>

                <View style={[styles.sectionCard, { marginTop: 16 }]}>
                    <Text style={styles.sectionTitle}>Yönetici Özeti</Text>
                    <Text style={styles.sectionSubtitle}>Kısa durum görünümü</Text>
                    
                    <View style={styles.summaryBox}>
                        <Text style={styles.infoLabel}>DURUM</Text>
                        <Text style={[styles.infoValue, { color: customer.is_active ? '#10B981' : '#EF4444' }]}>
                            {customer.is_active ? 'Aktif Müşteri' : 'Pasif Müşteri'}
                        </Text>
                    </View>
                    <View style={styles.summaryBox}>
                        <Text style={styles.infoLabel}>YETKİLİ</Text>
                        <Text style={styles.infoValue}>{customer.authorized_person || '-'}</Text>
                    </View>
                    <View style={styles.summaryBox}>
                        <Text style={styles.infoLabel}>İLETİŞİM</Text>
                        <Text style={styles.infoValue}>{customer.authorized_phone || '-'}</Text>
                        <Text style={[styles.infoValue, { fontSize: 12, color: '#64748B', marginTop: 4 }]}>{customer.email}</Text>
                    </View>
                </View>
            </View>
        );
    };

    const renderServicesTab = () => {
        const routes = customer.service_routes || customer.serviceRoutes || [];
        const total = routes.length;
        const active = routes.filter(r => r.is_active).length;
        const passive = total - active;
        
        return (
            <View style={styles.tabContent}>
                <View style={styles.tabHeaderRow}>
                    <View style={{flex:1}}>
                        <Text style={styles.sectionTitle}>Servis Güzergahları</Text>
                        <Text style={styles.sectionSubtitle}>Müşteriye ait tanımlanan güzergahları yönetin.</Text>
                    </View>
                    <TouchableOpacity style={styles.premiumAddBtn} onPress={openRouteAdd} activeOpacity={0.8}>
                        <LinearGradient colors={['#3B82F6', '#2563EB']} style={styles.premiumAddBtnGradient}>
                            <Icon name="map-marker-plus" size={18} color="#FFF" style={{marginRight: 6}} />
                            <Text style={styles.premiumAddBtnText}>Güzergah Tanımla</Text>
                        </LinearGradient>
                    </TouchableOpacity>
                </View>

                {/* Route KPIs */}
                <View style={styles.routeKpiRow}>
                    <LinearGradient colors={['#3B82F6', '#1D4ED8']} style={styles.routeKpiCard} start={{x:0, y:0}} end={{x:1, y:1}}>
                        <Icon name="map-marker-multiple" size={40} color="rgba(255,255,255,0.15)" style={styles.routeKpiBgIcon} />
                        <Text style={styles.routeKpiLabel} numberOfLines={1}>TOPLAM</Text>
                        <Text style={styles.routeKpiValue}>{total}</Text>
                    </LinearGradient>
                    
                    <LinearGradient colors={['#10B981', '#047857']} style={styles.routeKpiCard} start={{x:0, y:0}} end={{x:1, y:1}}>
                        <Icon name="check-decagram" size={40} color="rgba(255,255,255,0.15)" style={styles.routeKpiBgIcon} />
                        <Text style={styles.routeKpiLabel} numberOfLines={1}>AKTİF</Text>
                        <Text style={styles.routeKpiValue}>{active}</Text>
                    </LinearGradient>
                    
                    <LinearGradient colors={['#F43F5E', '#BE123C']} style={styles.routeKpiCard} start={{x:0, y:0}} end={{x:1, y:1}}>
                        <Icon name="close-octagon" size={40} color="rgba(255,255,255,0.15)" style={styles.routeKpiBgIcon} />
                        <Text style={styles.routeKpiLabel} numberOfLines={1}>PASİF</Text>
                        <Text style={styles.routeKpiValue}>{passive}</Text>
                    </LinearGradient>
                </View>

                {/* Route List */}
                {routes.map(item => {
                    const isActive = !!item.is_active;
                    return (
                        <View key={item.id} style={styles.routeCard}>
                            <View style={styles.routeCardHeader}>
                                <LinearGradient colors={['#8B5CF6', '#4C1D95']} style={styles.routeIconBox}>
                                    <Icon name="map-marker-path" size={20} color="#FFF" />
                                </LinearGradient>
                                <View style={{flex: 1}}>
                                    <View style={{flexDirection: 'row', alignItems: 'center'}}>
                                        <Text style={styles.routeTitle}>{item.route_name}</Text>
                                        <View style={[styles.statusBadge, { backgroundColor: isActive ? '#DCFCE7' : '#FEE2E2', marginLeft: 8 }]}>
                                            <Text style={[styles.statusBadgeText, { color: isActive ? '#166534' : '#991B1B' }]}>{isActive ? 'Aktif' : 'Pasif'}</Text>
                                        </View>
                                    </View>
                                    <Text style={styles.routeSub}>{item.start_location} ➔ {item.end_location}</Text>
                                </View>
                            </View>
                            
                            <View style={styles.routeDetailsGrid}>
                                <View style={styles.routeGridRow}>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Araç Cinsi:</Text>
                                        <Text style={styles.routeDetailValue}>{item.vehicle_type || '-'}</Text>
                                    </View>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Servis Türü:</Text>
                                        <Text style={styles.routeDetailValue}>{item.service_type === 'both' ? 'Sabah ve Akşam' : item.service_type === 'morning' ? 'Sadece Sabah' : item.service_type === 'evening' ? 'Sadece Akşam' : '-'}</Text>
                                    </View>
                                </View>
                                
                                <View style={styles.routeGridRow}>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Ücret Türü:</Text>
                                        <Text style={styles.routeDetailValue}>{item.fee_type === 'free' ? 'Ücretsiz' : 'Ücretli'}</Text>
                                    </View>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Hafta Sonu:</Text>
                                        <Text style={styles.routeDetailValue}>Cmt {item.saturday_pricing ? 'Evet' : 'Hayır'} / Paz {item.sunday_pricing ? 'Evet' : 'Hayır'}</Text>
                                    </View>
                                </View>

                                <View style={styles.routeGridRow}>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Sabah Aracı:</Text>
                                        <Text style={styles.routeDetailValue}>{item.morning_vehicle?.plate || item.vehicle?.plate || '-'}</Text>
                                    </View>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Akşam Aracı:</Text>
                                        <Text style={styles.routeDetailValue}>{item.evening_vehicle?.plate || item.vehicle?.plate || '-'}</Text>
                                    </View>
                                </View>

                                <View style={styles.routeGridRow}>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Sabah Ücret:</Text>
                                        <Text style={styles.routeDetailValue}>{item.morning_fee ? item.morning_fee + ' TL' : '-'}</Text>
                                    </View>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Akşam Ücret:</Text>
                                        <Text style={styles.routeDetailValue}>{item.evening_fee ? item.evening_fee + ' TL' : '-'}</Text>
                                    </View>
                                </View>

                                <View style={styles.routeGridRow}>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Kayıt Dışı Sabah (Yedek):</Text>
                                        <Text style={styles.routeDetailValue}>{item.fallback_morning_fee ? item.fallback_morning_fee + ' TL' : '-'}</Text>
                                    </View>
                                    <View style={styles.routeCol}>
                                        <Text style={styles.routeDetailLabel}>Kayıt Dışı Akşam (Yedek):</Text>
                                        <Text style={styles.routeDetailValue}>{item.fallback_evening_fee ? item.fallback_evening_fee + ' TL' : '-'}</Text>
                                    </View>
                                </View>
                            </View>

                            <View style={styles.routeActions}>
                                <TouchableOpacity style={styles.routeActionBtn} onPress={() => openRouteEdit(item)}>
                                    <Icon name="pencil" size={14} color="#3B82F6" />
                                    <Text style={[styles.routeActionText, { color: '#3B82F6' }]}>Düzenle</Text>
                                </TouchableOpacity>
                                
                                <TouchableOpacity style={styles.routeActionBtn} onPress={() => handleToggleRouteStatus(item)}>
                                    <Icon name={isActive ? "lock-open" : "check"} size={14} color={isActive ? "#F59E0B" : "#10B981"} />
                                    <Text style={[styles.routeActionText, { color: isActive ? '#F59E0B' : '#10B981' }]}>{isActive ? 'Pasif Yap' : 'Aktif Et'}</Text>
                                </TouchableOpacity>

                                <TouchableOpacity style={styles.routeActionBtn} onPress={() => confirmRouteDelete(item.id)}>
                                    <Icon name="trash-can" size={14} color="#EF4444" />
                                    <Text style={[styles.routeActionText, { color: '#EF4444' }]}>Sil</Text>
                                </TouchableOpacity>
                            </View>
                        </View>
                    );
                })}
                {routes.length === 0 && (
                    <EmptyState icon="map-marker-off" message="Kayıtlı güzergah bulunamadı." />
                )}
            </View>
        );
    };

    const renderContractsTab = () => {
        const contracts = customer.contracts || [];
        return (
            <View style={styles.tabContent}>
                <View style={styles.tabHeaderRow}>
                    <View style={{flex:1}}>
                        <Text style={styles.sectionTitle}>Sözleşme Listesi</Text>
                        <Text style={styles.sectionSubtitle}>En güncel ve geçerli sözleşme en üstte görünür</Text>
                    </View>
                    <TouchableOpacity style={styles.premiumAddBtn} onPress={openContractAdd} activeOpacity={0.8}>
                        <LinearGradient colors={['#10B981', '#059669']} style={styles.premiumAddBtnGradient}>
                            <Icon name="file-document-plus" size={18} color="#FFF" style={{marginRight: 6}} />
                            <Text style={styles.premiumAddBtnText}>Yeni Sözleşme Ekle</Text>
                        </LinearGradient>
                    </TouchableOpacity>
                </View>

                {contracts.map((item, index) => {
                    const isActive = item.is_active;
                    const isLatest = index === 0;

                    return (
                        <View key={item.id} style={styles.premiumContractCard}>
                            <View style={styles.premiumContractHeader}>
                                <View style={styles.premiumContractIconBox}>
                                    <Icon name="file-certificate" size={28} color="#0EA5E9" />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 4 }}>
                                        <Text style={styles.premiumContractTitle}>{item.year} Yılı Sözleşmesi</Text>
                                        {isLatest && (
                                            <View style={styles.badgeDark}>
                                                <Text style={styles.badgeDarkText}>Güncel</Text>
                                            </View>
                                        )}
                                    </View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <View style={[styles.statusBadge, { backgroundColor: isActive ? 'rgba(16, 185, 129, 0.15)' : 'rgba(244, 63, 94, 0.15)' }]}>
                                            <View style={[styles.statusDot, { backgroundColor: isActive ? '#10B981' : '#F43F5E' }]} />
                                            <Text style={[styles.statusText, { color: isActive ? '#10B981' : '#F43F5E' }]}>
                                                {isActive ? 'Geçerli Sözleşme' : 'Süresi Doldu'}
                                            </Text>
                                        </View>
                                    </View>
                                </View>
                            </View>

                            <View style={styles.premiumContractBody}>
                                <View style={styles.contractDateCol}>
                                    <Text style={styles.contractLabel}>BAŞLANGIÇ</Text>
                                    <Text style={styles.contractDate}>{new Date(item.start_date).toLocaleDateString('tr-TR')}</Text>
                                </View>
                                <View style={styles.contractDateDivider} />
                                <View style={styles.contractDateCol}>
                                    <Text style={styles.contractLabel}>BİTİŞ</Text>
                                    <Text style={styles.contractDate}>{new Date(item.end_date).toLocaleDateString('tr-TR')}</Text>
                                </View>
                            </View>

                            {item.original_name && (
                                <View style={styles.contractFileBox}>
                                    <Icon name="paperclip" size={16} color="#64748B" />
                                    <Text style={styles.contractFileName} numberOfLines={1}>{item.original_name}</Text>
                                </View>
                            )}

                            <View style={styles.premiumContractActions}>
                                {item.file_path && (
                                    <TouchableOpacity style={[styles.pcActionBtn, { backgroundColor: '#10B981', flex: 1 }]} onPress={() => Linking.openURL(`${api.defaults.baseURL.replace('/api', '')}/storage/${item.file_path}`)}>
                                        <Icon name="eye-outline" size={18} color="#FFF" />
                                        <Text style={[styles.pcActionText, { color: '#FFF' }]}>Görüntüle</Text>
                                    </TouchableOpacity>
                                )}
                                <TouchableOpacity style={[styles.pcActionBtnOutline, { flex: item.file_path ? 1 : 2 }]} onPress={() => openContractEdit(item)}>
                                    <Icon name="pencil-outline" size={18} color="#3B82F6" />
                                    <Text style={[styles.pcActionText, { color: '#3B82F6' }]}>Düzenle</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={[styles.pcActionBtnOutline, { borderColor: '#FECDD3', backgroundColor: '#FFF1F2', width: 44, paddingHorizontal: 0 }]} onPress={() => confirmContractDelete(item.id)}>
                                    <Icon name="trash-can-outline" size={18} color="#F43F5E" />
                                </TouchableOpacity>
                            </View>
                        </View>
                    );
                })}
                {contracts.length === 0 && (
                    <EmptyState icon="file-cancel-outline" title="Henüz sözleşme kaydı yok" message="İlk sözleşmeyi yükleyerek arşiv oluşturabilirsiniz." />
                )}
            </View>
        );
    };

    const renderInvoicesTab = () => {
        const monthOptions = [
            { label: 'Ocak', value: 1 }, { label: 'Şubat', value: 2 }, { label: 'Mart', value: 3 }, { label: 'Nisan', value: 4 },
            { label: 'Mayıs', value: 5 }, { label: 'Haziran', value: 6 }, { label: 'Temmuz', value: 7 }, { label: 'Ağustos', value: 8 },
            { label: 'Eylül', value: 9 }, { label: 'Ekim', value: 10 }, { label: 'Kasım', value: 11 }, { label: 'Aralık', value: 12 },
        ];
        const currentYear = new Date().getFullYear();
        const yearOptions = [currentYear+1, currentYear, currentYear-1, currentYear-2, currentYear-3].map(y => ({ label: y.toString(), value: y }));

        const formatMoney = (val) => Number(val || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        return (
            <View style={styles.tabContent}>
                <View style={styles.sectionCard}>
                    <View style={{flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16}}>
                        <View style={{flex: 1}}>
                            <Text style={styles.sectionTitle}>Aylık Puantaj ve Fatura Özeti</Text>
                            <Text style={styles.sectionSubtitle}>Seçili aya ait puantaj hakedişleri.</Text>
                        </View>
                        <TouchableOpacity 
                            style={{flexDirection: 'row', alignItems: 'center', backgroundColor: '#EFF6FF', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, borderWidth: 1, borderColor: '#BFDBFE', marginLeft: 8}}
                            onPress={() => navigation.navigate('Trips', { customer_id: customerId, month: selectedMonth, year: selectedYear })}
                        >
                            <Text style={{color: '#1D4ED8', fontSize: 12, fontWeight: '700', marginRight: 4}}>Puantaj Detayına Git</Text>
                            <Icon name="open-in-new" size={14} color="#1D4ED8" />
                        </TouchableOpacity>
                    </View>

                    <View style={{flexDirection: 'row', gap: 12, marginBottom: 20}}>
                        {renderSelectField('AY', monthOptions.find(m => m.value === selectedMonth)?.label, monthOptions, setSelectedMonth)}
                        {renderSelectField('YIL', selectedYear.toString(), yearOptions, setSelectedYear)}
                    </View>

                    {invoiceLoading ? (
                        <ActivityIndicator size="large" color="#3B82F6" style={{ marginVertical: 40 }} />
                    ) : invoiceSummary ? (
                        <View style={{gap: 12}}>
                            <View style={{ backgroundColor: '#F8FAFC', borderRadius: 16, padding: 16, borderWidth: 1, borderColor: '#E2E8F0', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                <View>
                                    <Text style={{ fontSize: 11, fontWeight: '800', color: '#64748B', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 }}>Ara Toplam</Text>
                                    <Text style={{ fontSize: 18, fontWeight: '800', color: '#0F172A' }}>₺{formatMoney(invoiceSummary.subtotal)}</Text>
                                </View>
                                <Icon name="calculator" size={24} color="#94A3B8" />
                            </View>

                            <View style={{ backgroundColor: '#F8FAFC', borderRadius: 16, padding: 16, borderWidth: 1, borderColor: '#E2E8F0', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                <View>
                                    <Text style={{ fontSize: 11, fontWeight: '800', color: '#64748B', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 }}>KDV (%{Number(invoiceSummary.vat_rate)})</Text>
                                    <Text style={{ fontSize: 18, fontWeight: '800', color: '#0F172A' }}>₺{formatMoney(invoiceSummary.vat_amount)}</Text>
                                </View>
                                <Icon name="percent" size={24} color="#94A3B8" />
                            </View>

                            <View style={{ backgroundColor: '#F8FAFC', borderRadius: 16, padding: 16, borderWidth: 1, borderColor: '#E2E8F0', flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' }}>
                                <View>
                                    <Text style={{ fontSize: 11, fontWeight: '800', color: '#64748B', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 4 }}>Tevkifat ({invoiceSummary.withholding_rate || 'Yok'})</Text>
                                    <Text style={{ fontSize: 18, fontWeight: '800', color: '#0F172A' }}>₺{formatMoney(invoiceSummary.withholding_amount)}</Text>
                                </View>
                                <Icon name="bank-minus" size={24} color="#94A3B8" />
                            </View>

                            <LinearGradient colors={['#312E81', '#0F172A']} style={{ borderRadius: 16, padding: 20, shadowColor: '#312E81', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.3, shadowRadius: 8, elevation: 5, flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
                                <View>
                                    <Text style={{ fontSize: 11, fontWeight: '800', color: '#A5B4FC', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 6 }}>Net Fatura Tutarı</Text>
                                    <Text style={{ fontSize: 24, fontWeight: '900', color: '#FFF' }}>₺{formatMoney(invoiceSummary.net_total)}</Text>
                                </View>
                                <Icon name="cash-multiple" size={32} color="#818CF8" />
                            </LinearGradient>
                        </View>
                    ) : null}
                </View>
            </View>
        );
    };

    const renderUsersTab = () => {
        const users = customer?.portal_users || [];
        
        return (
            <View style={styles.tabContent}>
                <View style={{flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16}}>
                    <View style={{flex: 1}}>
                        <Text style={styles.sectionTitle}>Portal Kullanıcıları</Text>
                        <Text style={styles.sectionSubtitle}>Müşterinin panele erişim sağlayacak kullanıcı hesapları.</Text>
                    </View>
                    {hasPermission('customers.edit') && (
                        <TouchableOpacity style={{marginLeft: 8}} onPress={openUserAdd} activeOpacity={0.8}>
                            <LinearGradient 
                                colors={['#3B82F6', '#2563EB']} 
                                start={{x: 0, y: 0}} 
                                end={{x: 1, y: 1}} 
                                style={{
                                    flexDirection: 'row', 
                                    alignItems: 'center', 
                                    paddingHorizontal: 16, 
                                    paddingVertical: 10, 
                                    borderRadius: 12,
                                    shadowColor: '#2563EB',
                                    shadowOffset: { width: 0, height: 4 },
                                    shadowOpacity: 0.3,
                                    shadowRadius: 6,
                                    elevation: 5,
                                    borderWidth: 1,
                                    borderColor: 'rgba(255,255,255,0.2)'
                                }}
                            >
                                <Icon name="plus-circle" size={18} color="#FFF" style={{marginRight: 6}} />
                                <Text style={{color: '#FFF', fontSize: 13, fontWeight: '800', letterSpacing: 0.5}}>Yeni Ekle</Text>
                            </LinearGradient>
                        </TouchableOpacity>
                    )}
                </View>

                {users.map((item, index) => {
                    const isActive = item.is_active;
                    return (
                        <View key={item.id} style={{ backgroundColor: '#FFF', borderRadius: 16, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#F1F5F9', shadowColor: '#000', shadowOffset: {width: 0, height: 2}, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2 }}>
                            <View style={{ flexDirection: 'row', alignItems: 'center', marginBottom: 12 }}>
                                {/* Avatar */}
                                <View style={{ width: 48, height: 48, borderRadius: 24, backgroundColor: '#3B82F6', alignItems: 'center', justifyContent: 'center', marginRight: 12 }}>
                                    <Text style={{ color: '#FFF', fontSize: 20, fontWeight: '800' }}>{item.name ? item.name.charAt(0).toUpperCase() : 'U'}</Text>
                                </View>
                                
                                {/* Info */}
                                <View style={{ flex: 1 }}>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap', gap: 6, marginBottom: 4 }}>
                                        <Text style={{ fontSize: 15, fontWeight: '800', color: '#0F172A' }}>{item.name}</Text>
                                        {isActive ? (
                                            <View style={{ backgroundColor: '#ECFDF5', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 }}>
                                                <Text style={{ color: '#059669', fontSize: 10, fontWeight: '700' }}>Aktif</Text>
                                            </View>
                                        ) : (
                                            <View style={{ backgroundColor: '#FEF2F2', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 }}>
                                                <Text style={{ color: '#DC2626', fontSize: 10, fontWeight: '700' }}>Pasif</Text>
                                            </View>
                                        )}
                                        <View style={{ backgroundColor: '#1E293B', paddingHorizontal: 6, paddingVertical: 2, borderRadius: 4 }}>
                                            <Text style={{ color: '#F8FAFC', fontSize: 10, fontWeight: '700' }}>Portal Kullanıcısı</Text>
                                        </View>
                                    </View>
                                    <View style={{ flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap' }}>
                                        <Text style={{ fontSize: 11, color: '#64748B', fontWeight: '600' }}>Kullanıcı Adı: <Text style={{ color: '#0F172A' }}>{item.username}</Text></Text>
                                        <Text style={{ fontSize: 11, color: '#CBD5E1', marginHorizontal: 6 }}>•</Text>
                                        <Text style={{ fontSize: 11, color: '#64748B', fontWeight: '600' }}>E-Posta: <Text style={{ color: '#0F172A' }}>{item.email || '-'}</Text></Text>
                                    </View>
                                    <Text style={{ fontSize: 11, color: '#64748B', fontWeight: '600', marginTop: 2 }}>
                                        Son Durum: <Text style={{ color: isActive ? '#059669' : '#DC2626' }}>{isActive ? 'Giriş yapabilir' : 'Girişi engellendi'}</Text>
                                    </Text>
                                </View>
                            </View>

                            {/* Actions */}
                            {hasPermission('customers.edit') && (
                                <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'flex-end', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 12, gap: 16 }}>
                                    <TouchableOpacity onPress={() => openUserEdit(item)} style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <Icon name="pencil" size={14} color="#F59E0B" />
                                        <Text style={{ fontSize: 12, fontWeight: '700', color: '#F59E0B', marginLeft: 4 }}>Düzenle</Text>
                                    </TouchableOpacity>

                                    <TouchableOpacity onPress={() => handleToggleUserStatus(item)} style={{ flexDirection: 'row', alignItems: 'center' }}>
                                        <Icon name={isActive ? "lock" : "lock-open"} size={14} color={isActive ? "#F59E0B" : "#10B981"} />
                                        <Text style={{ fontSize: 12, fontWeight: '700', color: isActive ? "#F59E0B" : "#10B981", marginLeft: 4 }}>{isActive ? 'Pasif Yap' : 'Aktif Yap'}</Text>
                                    </TouchableOpacity>

                                    <TouchableOpacity onPress={() => confirmUserDelete(item.id)} style={{ flexDirection: 'row', alignItems: 'center', backgroundColor: '#FEF2F2', paddingHorizontal: 10, paddingVertical: 6, borderRadius: 6 }}>
                                        <Icon name="trash-can-outline" size={14} color="#EF4444" />
                                        <Text style={{ fontSize: 12, fontWeight: '700', color: '#EF4444', marginLeft: 4 }}>Sil</Text>
                                    </TouchableOpacity>
                                </View>
                            )}
                        </View>
                    );
                })}
                {users.length === 0 && (
                    <EmptyState icon="account-group" title="Kullanıcı bulunamadı" message="Bu müşteriye henüz portal kullanıcısı eklenmemiş." />
                )}
            </View>
        );
    };

    const type = customer?.customer_type;
    let headerIcon = 'domain';
    let headerColors = ['#64748B', '#475569']; // Varsayılan Kurumsal
    
    if (type === 'Fabrika') {
        headerIcon = 'factory';
        headerColors = ['#0284C7', '#0369A1'];
    } else if (type === 'Okul') {
        headerIcon = 'school';
        headerColors = ['#F59E0B', '#D97706'];
    } else if (type === 'Resmi Daire') {
        headerIcon = 'bank';
        headerColors = ['#8B5CF6', '#6D28D9'];
    } else if (type === 'Diğer Servisler') {
        headerIcon = 'office-building';
        headerColors = ['#14B8A6', '#0F766E'];
    }

    const mainTitle = customer?.company_name || customer?.authorized_person || 'İsimsiz Kurum';
    const subTitle = customer?.company_title || 'Kurumsal Müşteri';

    // Helper to render fake dropdown fields
    const renderSelectField = (label, valueLabel, options, onSelect) => (
        <View style={{marginBottom: 16, flex: 1}}>
            <Text style={{fontSize: 12, fontWeight: '700', color: '#64748B', marginBottom: 6}}>{label}</Text>
            <TouchableOpacity 
                style={{flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, paddingHorizontal: 16, paddingVertical: 14}}
                onPress={() => setSelectConfig({ visible: true, title: label, options, onSelect })}
            >
                <Text style={{fontSize: 14, color: valueLabel ? '#0F172A' : '#94A3B8', fontWeight: '500'}} numberOfLines={1}>
                    {valueLabel || 'Seçiniz'}
                </Text>
                <Icon name="chevron-down" size={20} color="#94A3B8" />
            </TouchableOpacity>
        </View>
    );

    return (
        <SafeAreaView style={styles.container} edges={['top']}>
            <Header title="Müşteri Detayı" showBack />
            
            <ScrollView style={styles.scroll} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                {/* 1. Header Profile Card */}
                <View style={styles.profileCard}>
                    <View style={styles.profileTopRow}>
                        <LinearGradient colors={headerColors} style={styles.avatarBox}>
                            <Icon name={headerIcon} size={32} color="#FFF" style={styles.avatarIcon} />
                        </LinearGradient>
                        
                        <View style={styles.profileInfo}>
                            <View style={styles.badgeRow}>
                                <View style={styles.typeBadge}>
                                    <Text style={styles.typeBadgeText}>{type || 'Kurumsal'}</Text>
                                </View>
                                <View style={[styles.statusBadge, { backgroundColor: customer.is_active ? '#DCFCE7' : '#FEE2E2' }]}>
                                    <Text style={[styles.statusBadgeText, { color: customer.is_active ? '#166534' : '#991B1B' }]}>
                                        {customer.is_active ? 'Aktif Müşteri' : 'Pasif Müşteri'}
                                    </Text>
                                </View>
                            </View>
                            <Text style={styles.profileTitle}>{mainTitle}</Text>
                            <Text style={styles.profileSub}>{subTitle}</Text>
                        </View>
                    </View>

                    <View style={styles.profileContactRow}>
                        <Text style={styles.contactText}><Text style={{fontWeight: '700'}}>Yetkili:</Text> {customer.authorized_person || '-'}</Text>
                        <Text style={styles.contactText}><Text style={{fontWeight: '700'}}>Telefon:</Text> {customer.authorized_phone || '-'}</Text>
                    </View>
                </View>

                {/* Tabs & Content mapping... */}
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.tabsScroll}>
                    {['info', 'services', 'invoices', 'contracts', 'users'].map((tab) => {
                        const labels = { info: 'Firma Bilgileri', services: 'Servisler', invoices: 'Faturalar', contracts: 'Sözleşmeler', users: 'Kullanıcılar' };
                        const icons = { info: 'domain', services: 'bus', invoices: 'file-document-outline', contracts: 'file-sign', users: 'account-group' };
                        const isActive = activeTab === tab;
                        return (
                            <TouchableOpacity key={tab} onPress={() => setActiveTab(tab)} style={[styles.tabBtn, isActive && styles.tabBtnActive]}>
                                <Icon name={icons[tab]} size={18} color={isActive ? '#FFF' : '#64748B'} />
                                <Text style={[styles.tabTxt, isActive && styles.tabTxtActive]}>{labels[tab]}</Text>
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>

                {/* 3. Content */}
                {activeTab === 'info' && renderInfoTab()}
                {activeTab === 'services' && renderServicesTab()}
                {activeTab === 'contracts' && renderContractsTab()}
                {activeTab === 'invoices' && renderInvoicesTab()}
                {activeTab === 'users' && renderUsersTab()}
            </ScrollView>

            {/* Modals */}
            <BottomSheetModal 
                visible={routeModal} 
                onClose={() => setRouteModal(false)} 
                title={routeEditingId ? 'Güzergah Düzenle' : 'Yeni Güzergah Tanımla'}
                overlayContent={
                    selectConfig.visible && (
                        <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end', zIndex: 9999 }]}>
                            <View style={{ backgroundColor: '#FFF', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '60%' }}>
                                <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                                    <Text style={{ fontSize: 18, fontWeight: '800', color: '#0F172A' }}>{selectConfig.title}</Text>
                                    <TouchableOpacity onPress={() => setSelectConfig({...selectConfig, visible: false})}>
                                        <Icon name="close-circle" size={28} color="#94A3B8" />
                                    </TouchableOpacity>
                                </View>
                                <ScrollView showsVerticalScrollIndicator={false}>
                                    {selectConfig.options.map((opt, i) => (
                                        <TouchableOpacity 
                                            key={i} 
                                            style={{ paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' }}
                                            onPress={() => {
                                                selectConfig.onSelect(opt.value);
                                                setSelectConfig({...selectConfig, visible: false});
                                            }}
                                        >
                                            <Text style={{ fontSize: 16, color: '#334155', fontWeight: '500' }}>{opt.label}</Text>
                                        </TouchableOpacity>
                                    ))}
                                </ScrollView>
                            </View>
                        </View>
                    )
                }
            >
                <FormField label="GÜZERGAH ADI" placeholder="Örn: VARDİA SERVİSİ" value={routeData.route_name} onChangeText={(t) => setRouteData({...routeData, route_name: t})} error={routeErrors.route_name} />
                
                <View style={{flexDirection: 'row', gap: 12}}>
                    {renderSelectField('SERVİS TÜRÜ', 
                        routeData.service_type === 'morning' ? 'Sadece Sabah' : routeData.service_type === 'evening' ? 'Sadece Akşam' : 'Sabah ve Akşam',
                        [{label: 'Sadece Sabah', value: 'morning'}, {label: 'Sadece Akşam', value: 'evening'}, {label: 'Sabah ve Akşam', value: 'both'}],
                        (v) => setRouteData({...routeData, service_type: v})
                    )}
                    {renderSelectField('ARAÇ CİNSİ', routeData.vehicle_type || 'Seçiniz',
                        [{label: 'MİNİBÜS (16+1)', value: 'MİNİBÜS (16+1)'}, {label: 'MİDİBÜS (27+1)', value: 'MİDİBÜS (27+1)'}, {label: 'OTOBÜS (45+)', value: 'OTOBÜS (45+)'}],
                        (v) => setRouteData({...routeData, vehicle_type: v})
                    )}
                </View>

                {(routeData.service_type === 'morning' || routeData.service_type === 'both') && renderSelectField('SABAH SEFERİNİ YAPACAK ARAÇ', 
                    (vehicles || []).find(v => v.id === routeData.morning_vehicle_id)?.plate || 'Araç Seçiniz',
                    [{label: 'Araç Seçiniz', value: null}, ...(vehicles || []).map(v => ({label: `${v.plate} - ${v.brand || ''}`, value: v.id}))],
                    (v) => setRouteData({...routeData, morning_vehicle_id: v})
                )}

                {(routeData.service_type === 'evening' || routeData.service_type === 'both') && renderSelectField('AKŞAM SEFERİNİ YAPACAK ARAÇ', 
                    (vehicles || []).find(v => v.id === routeData.evening_vehicle_id)?.plate || 'Araç Seçiniz',
                    [{label: 'Araç Seçiniz', value: null}, ...(vehicles || []).map(v => ({label: `${v.plate} - ${v.brand || ''}`, value: v.id}))],
                    (v) => setRouteData({...routeData, evening_vehicle_id: v})
                )}

                <View style={{flexDirection: 'row', gap: 12}}>
                    {renderSelectField('ÜCRET TÜRÜ', routeData.fee_type === 'free' ? 'Ücretsiz' : 'Ücretli',
                        [{label: 'Ücretli', value: 'paid'}, {label: 'Ücretsiz', value: 'free'}],
                        (v) => setRouteData({...routeData, fee_type: v})
                    )}
                    {renderSelectField('CUMARTESİ ÜCRET', routeData.saturday_pricing ? 'Evet' : 'Hayır',
                        [{label: 'Evet', value: 1}, {label: 'Hayır', value: 0}],
                        (v) => setRouteData({...routeData, saturday_pricing: v})
                    )}
                    {renderSelectField('PAZAR ÜCRET', routeData.sunday_pricing ? 'Evet' : 'Hayır',
                        [{label: 'Evet', value: 1}, {label: 'Hayır', value: 0}],
                        (v) => setRouteData({...routeData, sunday_pricing: v})
                    )}
                </View>

                {routeData.fee_type === 'paid' && (
                    <View style={{ backgroundColor: '#F8FAFC', padding: 16, borderRadius: 16, marginBottom: 16, borderWidth: 1, borderColor: '#E2E8F0' }}>
                        <Text style={{fontSize: 14, fontWeight: '800', color: '#0F172A', marginBottom: 4}}>Standart Sefer Ücretleri</Text>
                        <Text style={{fontSize: 12, color: '#64748B', marginBottom: 16}}>Tanımlı araçlar kendi güzergahına gittiğinde esas alınacak ücretler</Text>
                        
                        <View style={{flexDirection: 'row', gap: 12}}>
                            {(routeData.service_type === 'morning' || routeData.service_type === 'both') && (
                                <View style={{flex: 1}}>
                                    <FormField label="SABAH SEFER ÜCRETİ" placeholder="0.00" keyboardType="decimal-pad" value={routeData.morning_fee?.toString()} onChangeText={(t) => setRouteData({...routeData, morning_fee: t.replace(/,/g, '.')})} />
                                </View>
                            )}
                            {(routeData.service_type === 'evening' || routeData.service_type === 'both') && (
                                <View style={{flex: 1}}>
                                    <FormField label="AKŞAM SEFER ÜCRETİ" placeholder="0.00" keyboardType="decimal-pad" value={routeData.evening_fee?.toString()} onChangeText={(t) => setRouteData({...routeData, evening_fee: t.replace(/,/g, '.')})} />
                                </View>
                            )}
                        </View>
                    </View>
                )}

                {/* Her Zaman Gözüksün (Ücretsiz ise sadece bu, Ücretli ise her ikisi) */}
                <View style={{ backgroundColor: '#FFF1F2', padding: 16, borderRadius: 16, marginBottom: 16, borderWidth: 1, borderColor: '#FECDD3' }}>
                        <Text style={{fontSize: 14, fontWeight: '800', color: '#9F1239', marginBottom: 4}}>Tanımlı Araç Dışı Ücret Kuralı (Yedek Araç)</Text>
                        <Text style={{fontSize: 12, color: '#BE123C', marginBottom: 16}}>Eğer puantaj işlenirken tanımladığınız araçtan farklı bir plaka girilirse geçerli olacak ücretler</Text>
                        
                        <View style={{flexDirection: 'row', gap: 12}}>
                            {(routeData.service_type === 'morning' || routeData.service_type === 'both') && (
                                <View style={{flex: 1}}>
                                    <FormField label="YEDEK SABAH ÜCRETİ" placeholder="0.00" keyboardType="decimal-pad" value={routeData.fallback_morning_fee?.toString()} onChangeText={(t) => setRouteData({...routeData, fallback_morning_fee: t.replace(/,/g, '.')})} />
                                </View>
                            )}
                            {(routeData.service_type === 'evening' || routeData.service_type === 'both') && (
                                <View style={{flex: 1}}>
                                    <FormField label="YEDEK AKŞAM ÜCRETİ" placeholder="0.00" keyboardType="decimal-pad" value={routeData.fallback_evening_fee?.toString()} onChangeText={(t) => setRouteData({...routeData, fallback_evening_fee: t.replace(/,/g, '.')})} />
                                </View>
                            )}
                        </View>
                    </View>
                
                <TouchableOpacity style={styles.saveBtn} onPress={handleRouteSave} disabled={savingRoute}>
                    {savingRoute ? <ActivityIndicator color="#FFF" /> : <Text style={styles.saveBtnText}>{routeEditingId ? 'Güncelle' : 'Kaydet'}</Text>}
                </TouchableOpacity>
            </BottomSheetModal>

            <BottomSheetModal visible={contractModal} onClose={() => setContractModal(false)} title={contractEditingId ? 'Sözleşme Düzenle' : 'Yeni Sözleşme Yükle'}>
                <View style={styles.premiumModalHeader}>
                    <View style={styles.premiumModalIconWrap}>
                        <Icon name="file-certificate" size={28} color="#3B82F6" />
                    </View>
                    <Text style={styles.premiumModalDesc}>Müşteriniz ile aranızdaki güncel taşıma sözleşmesini sisteme kaydedin.</Text>
                </View>

                <FormField label="SÖZLEŞME YILI" placeholder="2026" keyboardType="number-pad" value={contractData.year} onChangeText={(t) => setContractData({...contractData, year: t})} error={contractErrors.year} />
                <View style={{flexDirection: 'row', gap: 12}}>
                    <View style={{flex: 1}}>
                        <DatePickerInput label="BAŞLANGIÇ TARİHİ" value={contractData.start_date} onSelect={(d) => setContractData({...contractData, start_date: d})} error={contractErrors.start_date} />
                    </View>
                    <View style={{flex: 1}}>
                        <DatePickerInput label="BİTİŞ TARİHİ" value={contractData.end_date} onSelect={(d) => setContractData({...contractData, end_date: d})} error={contractErrors.end_date} />
                    </View>
                </View>

                <View style={{ marginBottom: 24, marginTop: 8 }}>
                    <Text style={styles.premiumUploadLabel}>DOSYA YÜKLE (PDF / JPG)</Text>
                    <TouchableOpacity 
                        style={[styles.premiumUploadBox, contractData.contract_file && styles.premiumUploadBoxActive]}
                        onPress={pickContractFile}
                        activeOpacity={0.8}
                    >
                        {contractData.contract_file ? (
                            <>
                                <View style={styles.premiumUploadSuccessIcon}>
                                    <Icon name="check-circle" size={24} color="#10B981" />
                                </View>
                                <View style={{ flex: 1 }}>
                                    <Text style={styles.premiumUploadFileName} numberOfLines={1}>{contractData.contract_file.name}</Text>
                                    <Text style={styles.premiumUploadFileSize}>Dosya Eklendi</Text>
                                </View>
                                <Icon name="sync" size={20} color="#94A3B8" />
                            </>
                        ) : (
                            <>
                                <View style={styles.premiumUploadIconCircle}>
                                    <Icon name="cloud-upload" size={28} color="#3B82F6" />
                                </View>
                                <View style={{ flex: 1, marginLeft: 16 }}>
                                    <Text style={styles.premiumUploadTitle}>Sözleşme Dosyasını Seç</Text>
                                    <Text style={styles.premiumUploadSubtitle}>Cihazınızdan seçmek için dokunun</Text>
                                </View>
                            </>
                        )}
                    </TouchableOpacity>
                    {contractErrors.contract_file && <Text style={{ color: '#EF4444', fontSize: 12, marginTop: 6, fontWeight: '500' }}>{contractErrors.contract_file[0]}</Text>}
                </View>

                <TouchableOpacity style={styles.premiumSaveBtn} onPress={handleContractSave} disabled={savingContract}>
                    <LinearGradient colors={['#2563EB', '#1D4ED8']} style={styles.premiumSaveBtnGradient} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                        {savingContract ? <ActivityIndicator color="#FFF" /> : (
                            <>
                                <Icon name="content-save-check" size={20} color="#FFF" style={{marginRight: 8}} />
                                <Text style={styles.premiumSaveBtnText}>{contractEditingId ? 'Sözleşmeyi Güncelle' : 'Sözleşmeyi Yükle'}</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
            </BottomSheetModal>

            {/* Standalone Select Modal for outside BottomSheets */}
            {!routeModal && selectConfig.visible && (
                <Modal visible transparent animationType="fade">
                    <View style={[StyleSheet.absoluteFill, { backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end', zIndex: 9999 }]}>
                        <View style={{ backgroundColor: '#FFF', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 20, maxHeight: '60%' }}>
                            <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                                <Text style={{ fontSize: 18, fontWeight: '800', color: '#0F172A' }}>{selectConfig.title}</Text>
                                <TouchableOpacity onPress={() => setSelectConfig({...selectConfig, visible: false})}>
                                    <Icon name="close-circle" size={28} color="#94A3B8" />
                                </TouchableOpacity>
                            </View>
                            <ScrollView showsVerticalScrollIndicator={false}>
                                {selectConfig.options.map((opt, i) => (
                                    <TouchableOpacity 
                                        key={i} 
                                        style={{ paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' }}
                                        onPress={() => {
                                            if (selectConfig.onSelect) selectConfig.onSelect(opt.value);
                                            setSelectConfig({...selectConfig, visible: false});
                                        }}
                                    >
                                        <Text style={{ fontSize: 16, color: '#334155', fontWeight: '500' }}>{opt.label}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        </View>
                    </View>
                </Modal>
            )}

            {/* User Bottom Sheet Modal */}
            <BottomSheetModal visible={userModal} onClose={() => setUserModal(false)} title={userEditingId ? 'Kullanıcı Düzenle' : 'Yeni Kullanıcı Ekle'}>
                <View style={styles.premiumModalHeader}>
                    <View style={styles.premiumModalIconWrap}>
                        <Icon name="account-key" size={28} color="#3B82F6" />
                    </View>
                    <Text style={styles.premiumModalDesc}>
                        {userEditingId ? 'Kullanıcı bilgilerini güncelleyin.' : 'Müşterinin panele giriş yapabileceği yeni bir kullanıcı hesabı oluşturun.'}
                    </Text>
                </View>

                <FormField 
                    label="AD SOYAD *" 
                    placeholder="Örn: Ahmet Yılmaz" 
                    value={userData.name} 
                    onChangeText={(t) => setUserData({...userData, name: t})} 
                    error={userErrors.name} 
                />
                
                <FormField 
                    label="KULLANICI ADI *" 
                    placeholder="Örn: ahmetyilmaz" 
                    value={userData.username} 
                    onChangeText={(t) => setUserData({...userData, username: t})} 
                    error={userErrors.username} 
                    autoCapitalize="none"
                />
                
                <FormField 
                    label="E-POSTA ADRESİ" 
                    placeholder="Örn: ahmet@firma.com" 
                    value={userData.email} 
                    onChangeText={(t) => setUserData({...userData, email: t})} 
                    error={userErrors.email} 
                    keyboardType="email-address"
                    autoCapitalize="none"
                />
                
                <FormField 
                    label={userEditingId ? "YENİ ŞİFRE (Opsiyonel)" : "ŞİFRE *"} 
                    placeholder="Şifre belirleyin" 
                    value={userData.password} 
                    onChangeText={(t) => setUserData({...userData, password: t})} 
                    error={userErrors.password} 
                    secureTextEntry
                />

                <View style={{ flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingVertical: 12, borderTopWidth: 1, borderTopColor: '#F1F5F9', marginTop: 8 }}>
                    <View>
                        <Text style={{ fontSize: 13, fontWeight: '700', color: '#334155' }}>Kullanıcı Durumu</Text>
                        <Text style={{ fontSize: 11, color: '#64748B', marginTop: 2 }}>Hesabın sisteme erişimi olsun mu?</Text>
                    </View>
                    <TouchableOpacity 
                        style={[styles.statusToggleBtn, userData.is_active ? styles.statusToggleActive : styles.statusTogglePassive]}
                        onPress={() => setUserData({...userData, is_active: !userData.is_active})}
                    >
                        <Icon name={userData.is_active ? "check-circle" : "close-circle"} size={16} color={userData.is_active ? "#059669" : "#DC2626"} />
                        <Text style={[styles.statusToggleText, userData.is_active ? {color: '#059669'} : {color: '#DC2626'}]}>
                            {userData.is_active ? 'Aktif' : 'Pasif'}
                        </Text>
                    </TouchableOpacity>
                </View>

                <TouchableOpacity style={[styles.premiumSaveBtn, { marginTop: 24 }]} onPress={handleUserSave} disabled={savingUser}>
                    <LinearGradient colors={['#2563EB', '#1D4ED8']} style={styles.premiumSaveBtnGradient} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                        {savingUser ? <ActivityIndicator color="#FFF" /> : (
                            <>
                                <Icon name="content-save-check" size={20} color="#FFF" style={{marginRight: 8}} />
                                <Text style={styles.premiumSaveBtnText}>{userEditingId ? 'Kullanıcıyı Güncelle' : 'Kullanıcıyı Kaydet'}</Text>
                            </>
                        )}
                    </LinearGradient>
                </TouchableOpacity>
                <View style={{ height: 20 }} />
            </BottomSheetModal>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F1F5F9' },
    scroll: { flex: 1 },
    scrollContent: { padding: 16, paddingBottom: 100 },

    // Header Profile Card
    profileCard: {
        backgroundColor: '#FFFFFF',
        borderRadius: 24,
        padding: 20,
        marginBottom: 16,
        ...Platform.select({
            ios: { shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.15, shadowRadius: 12 },
            android: { elevation: 6 }
        })
    },
    profileTopRow: { flexDirection: 'row', alignItems: 'center', marginBottom: 16 },
    avatarBox: { width: 64, height: 64, borderRadius: 20, alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    avatarIcon: { ...Platform.select({ ios: { shadowColor: '#FFF', shadowOffset: { width: 0, height: 0 }, shadowOpacity: 0.8, shadowRadius: 5 }, android: { textShadowColor: 'rgba(255,255,255,0.6)', textShadowOffset: { width: 0, height: 0 }, textShadowRadius: 10 } }) },
    profileInfo: { flex: 1 },
    badgeRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 8 },
    typeBadge: { backgroundColor: '#F1F5F9', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    typeBadgeText: { fontSize: 10, fontWeight: '700', color: '#64748B' },
    statusBadge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    statusBadgeText: { fontSize: 10, fontWeight: '700' },
    profileTitle: { fontSize: 22, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5, marginBottom: 4 },
    profileSub: { fontSize: 13, color: '#64748B', fontWeight: '500' },
    profileContactRow: { flexDirection: 'row', alignItems: 'center', gap: 16, borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 16 },
    contactText: { fontSize: 12, color: '#475569' },

    // Mini Stat Cards
    statsScroll: { gap: 12, marginBottom: 20 },
    statCard: {
        width: 160,
        backgroundColor: '#FFFFFF',
        borderRadius: 20,
        padding: 16,
        ...Platform.select({ ios: { shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 8 }, android: { elevation: 3 } })
    },
    statLabel: { fontSize: 10, fontWeight: '700', color: '#94A3B8', letterSpacing: 0.5, marginBottom: 8 },
    statValue: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginLeft: 6 },
    statDesc: { fontSize: 11, color: '#64748B', marginTop: 8 },

    // Tabs
    tabsScroll: { gap: 8, marginBottom: 20 },
    tabBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFFFFF', paddingHorizontal: 16, paddingVertical: 12, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0', gap: 8 },
    tabBtnActive: { backgroundColor: '#1E293B', borderColor: '#1E293B' },
    tabTxt: { fontSize: 13, fontWeight: '600', color: '#64748B' },
    tabTxtActive: { color: '#FFFFFF' },

    // Content
    tabContent: { gap: 16 },
    sectionCard: {
        backgroundColor: '#FFFFFF',
        borderRadius: 20,
        padding: 20,
        ...Platform.select({ ios: { shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 8 }, android: { elevation: 3 } })
    },
    sectionTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A', marginBottom: 4 },
    sectionSubtitle: { fontSize: 13, color: '#64748B', marginBottom: 20 },
    
    infoGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 16 },
    infoCol: { width: '45%', marginBottom: 8 },
    infoLabel: { fontSize: 10, fontWeight: '700', color: '#94A3B8', letterSpacing: 0.5, marginBottom: 6 },
    infoValue: { fontSize: 14, fontWeight: '600', color: '#0F172A' },
    
    summaryBox: { backgroundColor: '#F8FAFC', padding: 12, borderRadius: 12, marginBottom: 8 },

    emptyContainer: { alignItems: 'center', justifyContent: 'center', paddingVertical: 60 },
    emptyText: { color: '#94A3B8', marginTop: 16, fontSize: 14, fontWeight: '500' },

    tabHeaderRow: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: 16 },
    addBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#3B82F6', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 12, gap: 6 },
    addBtnText: { color: '#FFF', fontSize: 12, fontWeight: '700' },

    premiumAddBtn: { borderRadius: 12, overflow: 'hidden' },
    premiumAddBtnGradient: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 16, paddingVertical: 10, borderRadius: 12 },
    premiumAddBtnText: { color: '#FFF', fontSize: 13, fontWeight: '800' },

    routeKpiRow: { flexDirection: 'row', gap: 8, marginBottom: 16 },
    routeKpiCard: { flex: 1, padding: 12, borderRadius: 16, justifyContent: 'center', overflow: 'hidden', borderWidth: 1, borderColor: 'rgba(255,255,255,0.2)', ...Platform.select({ ios: { shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 }, android: { elevation: 6 } }) },
    routeKpiBgIcon: { position: 'absolute', right: -10, bottom: -10, opacity: 0.5 },
    routeKpiLabel: { color: 'rgba(255,255,255,0.9)', fontSize: 10, fontWeight: '800', letterSpacing: 0.5, marginBottom: 4 },
    routeKpiValue: { color: '#FFF', fontSize: 24, fontWeight: '900' },

    routeCard: { backgroundColor: '#FFFFFF', borderRadius: 16, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#F1F5F9' },
    routeCardHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: 12 },
    routeIconBox: { width: 40, height: 40, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginRight: 12 },
    routeTitle: { fontSize: 14, fontWeight: '800', color: '#0F172A' },
    routeSub: { fontSize: 12, color: '#64748B', marginTop: 2 },
    
    routeDetailsGrid: { backgroundColor: '#F8FAFC', padding: 12, borderRadius: 12, marginBottom: 12, gap: 12 },
    routeGridRow: { flexDirection: 'row', justifyContent: 'space-between' },
    routeCol: { flex: 1, paddingRight: 8 },
    routeDetailLabel: { fontSize: 10, color: '#94A3B8', fontWeight: '600', marginBottom: 2 },
    routeDetailValue: { fontSize: 12, color: '#0F172A', fontWeight: '700' },

    routeActions: { flexDirection: 'row', justifyContent: 'flex-end', gap: 8, borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 12 },
    routeActionBtn: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 8, gap: 4 },
    routeActionText: { fontSize: 12, fontWeight: '600' },

    premiumContractCard: { backgroundColor: '#FFF', borderRadius: 20, padding: 16, marginBottom: 16, borderWidth: 1, borderColor: '#E2E8F0', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 10, elevation: 3 },
    premiumContractHeader: { flexDirection: 'row', marginBottom: 16 },
    premiumContractIconBox: { width: 48, height: 48, borderRadius: 14, backgroundColor: '#F0F9FF', alignItems: 'center', justifyContent: 'center', marginRight: 12, borderWidth: 1, borderColor: '#E0F2FE' },
    premiumContractTitle: { fontSize: 15, fontWeight: '800', color: '#0F172A' },
    badgeDark: { backgroundColor: '#1E293B', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 8 },
    badgeDarkText: { color: '#F8FAFC', fontSize: 10, fontWeight: '700', letterSpacing: 0.5 },
    statusBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
    statusDot: { width: 6, height: 6, borderRadius: 3, marginRight: 6 },
    statusText: { fontSize: 11, fontWeight: '700' },
    
    premiumContractBody: { flexDirection: 'row', backgroundColor: '#F8FAFC', borderRadius: 12, padding: 12, marginBottom: 12, borderWidth: 1, borderColor: '#F1F5F9' },
    contractDateCol: { flex: 1 },
    contractLabel: { fontSize: 10, color: '#94A3B8', fontWeight: '700', marginBottom: 4, letterSpacing: 0.5 },
    contractDate: { fontSize: 13, color: '#334155', fontWeight: '600' },
    contractDateDivider: { width: 1, backgroundColor: '#E2E8F0', marginHorizontal: 12 },
    
    contractFileBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F1F5F9', padding: 10, borderRadius: 10, marginBottom: 16 },
    contractFileName: { fontSize: 12, color: '#475569', fontWeight: '500', marginLeft: 8, flex: 1 },
    
    premiumContractActions: { flexDirection: 'row', gap: 8 },
    pcActionBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 10, borderRadius: 10 },
    pcActionBtnOutline: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 10, borderRadius: 10, borderWidth: 1, borderColor: '#DBEAFE', backgroundColor: '#EFF6FF', flex: 1 },
    pcActionText: { fontSize: 13, fontWeight: '700', marginLeft: 6 },

    premiumModalHeader: { alignItems: 'center', marginBottom: 24, paddingHorizontal: 16 },
    premiumModalIconWrap: { width: 64, height: 64, borderRadius: 20, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center', marginBottom: 12, borderWidth: 1, borderColor: '#DBEAFE' },
    premiumModalDesc: { fontSize: 13, color: '#64748B', textAlign: 'center', lineHeight: 20, fontWeight: '500' },
    
    premiumUploadLabel: { fontSize: 12, fontWeight: '800', color: '#64748B', marginBottom: 8, letterSpacing: 0.5 },
    premiumUploadBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', padding: 20, borderRadius: 16, borderWidth: 2, borderColor: '#E2E8F0', borderStyle: 'dashed' },
    premiumUploadBoxActive: { backgroundColor: '#ECFDF5', borderColor: '#10B981', borderStyle: 'solid' },
    premiumUploadIconCircle: { width: 56, height: 56, borderRadius: 28, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center' },
    premiumUploadSuccessIcon: { width: 48, height: 48, borderRadius: 16, backgroundColor: 'rgba(16, 185, 129, 0.1)', alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    premiumUploadTitle: { fontSize: 15, fontWeight: '800', color: '#0F172A', marginBottom: 4 },
    premiumUploadSubtitle: { fontSize: 13, color: '#94A3B8', fontWeight: '500' },
    premiumUploadFileName: { fontSize: 14, fontWeight: '700', color: '#065F46', marginBottom: 2 },
    premiumUploadFileSize: { fontSize: 12, color: '#10B981', fontWeight: '600' },
    
    premiumSaveBtn: { borderRadius: 14, overflow: 'hidden', marginTop: 10 },
    premiumSaveBtnGradient: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 16 },
    premiumSaveBtnText: { color: '#FFF', fontSize: 16, fontWeight: '800', letterSpacing: 0.5 },

    saveBtn: { backgroundColor: '#3B82F6', padding: 16, borderRadius: 16, alignItems: 'center', marginTop: 10 },
    saveBtnText: { color: '#FFF', fontSize: 16, fontWeight: '800' },

    // Dummy Tab
    dummyTabBar: { position: 'absolute', bottom: 0, left: 0, right: 0, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#E2E8F0', paddingBottom: Platform.OS === 'ios' ? 20 : 0, flexDirection: 'row', height: Platform.OS === 'ios' ? 85 : 65, alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 10 },
    dummyTab: { flex: 1, alignItems: 'center', justifyContent: 'center', height: '100%' },
    dummyTabLabel: { fontSize: 10, fontWeight: '600', marginTop: 4, color: '#94A3B8' },
    dummyTabCenter: { flex: 1, alignItems: 'center' },
    dummyTabCenterInner: { width: 56, height: 56, borderRadius: 28, backgroundColor: '#2563EB', alignItems: 'center', justifyContent: 'center', marginTop: -35, shadowColor: '#2563EB', shadowOffset: {width:0, height:4}, shadowOpacity:0.3, shadowRadius:8, elevation: 5 }
});
