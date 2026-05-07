import React, { useState, useEffect, useContext } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, ActivityIndicator, Alert, Modal, TextInput, Platform, Dimensions, KeyboardAvoidingView, Image, Linking, Share } from 'react-native';
import { SafeAreaView, useSafeAreaInsets } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as DocumentPicker from 'expo-document-picker';
import * as ImagePicker from 'expo-image-picker';
import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import { Picker } from '@react-native-picker/picker';
import dayjs from 'dayjs';
import api from '../api/axios';
import { AuthContext } from '../context/AuthContext';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

export default function PersonnelDetailScreen({ route, navigation }) {
    const { id } = route.params;
    const { hasPermission } = useContext(AuthContext);
    const insets = useSafeAreaInsets();

    const [loading, setLoading] = useState(true);
    const [personnel, setPersonnel] = useState(null);
    const [vehicles, setVehicles] = useState([]);
    
    // Tabs state
    const [activeTab, setActiveTab] = useState('genel'); // genel, belge, maas, resim
    const [showArchive, setShowArchive] = useState(false);

    // Modals
    const [showMenu, setShowMenu] = useState(false);
    const [showVehicleModal, setShowVehicleModal] = useState(false);
    const [newVehicleId, setNewVehicleId] = useState('');
    
    // Upload Modals
    const [showDocModal, setShowDocModal] = useState(false);
    const [docTitle, setDocTitle] = useState('');
    const [selectedDoc, setSelectedDoc] = useState(null);
    const [uploading, setUploading] = useState(false);

    const fetchDetail = async () => {
        try {
            const [resDetail, resOptions] = await Promise.all([
                api.get(`/v1/personnel/${id}`),
                hasPermission('drivers.edit') ? api.get('/v1/personnel/options') : Promise.resolve({data:{data:{vehicles:[]}}})
            ]);
            setPersonnel(resDetail.data.data);
            if (resOptions.data.data?.vehicles) {
                setVehicles(resOptions.data.data.vehicles);
            }
        } catch (e) {
            Alert.alert('Hata', 'Personel detayları alınamadı.');
            navigation.goBack();
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchDetail(); }, []);

    // Actions
    const updateStatus = async (isActive, leaveDate = null) => {
        try {
            await api.put(`/v1/personnel/${id}/status`, { is_active: isActive, leave_date: leaveDate });
            fetchDetail();
            setShowMenu(false);
        } catch (e) { Alert.alert('Hata', 'Durum güncellenemedi.'); }
    };

    const changeVehicle = async () => {
        try {
            await api.put(`/v1/personnel/${id}/vehicle`, { vehicle_id: newVehicleId || null });
            setShowVehicleModal(false);
            fetchDetail();
        } catch (e) { Alert.alert('Hata', 'Araç değiştirilemedi.'); }
    };

    const pickDocument = async (isImage = false) => {
        try {
            if (isImage) {
                const res = await ImagePicker.launchImageLibraryAsync({ mediaTypes: ImagePicker.MediaTypeOptions.Images, allowsEditing: true, quality: 0.8 });
                if (!res.canceled) setSelectedDoc({ uri: res.assets[0].uri, name: 'image.jpg', type: 'image/jpeg' });
            } else {
                const res = await DocumentPicker.getDocumentAsync({ type: '*/*' });
                if (!res.canceled && res.assets && res.assets.length > 0) {
                    setSelectedDoc({ uri: res.assets[0].uri, name: res.assets[0].name, type: res.assets[0].mimeType || 'application/octet-stream' });
                }
            }
        } catch (e) { console.log(e); }
    };

    const uploadFile = async (type) => {
        if (!selectedDoc) return Alert.alert('Hata', 'Lütfen bir dosya seçin.');
        setUploading(true);
        try {
            const fd = new FormData();
            fd.append('document', { uri: selectedDoc.uri, name: selectedDoc.name, type: selectedDoc.type });
            fd.append('type', type);
            fd.append('title', docTitle || selectedDoc.name);

            await api.post(`/v1/personnel/${id}/documents`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
            
            setDocTitle(''); setSelectedDoc(null); setShowDocModal(false);
            fetchDetail();
        } catch (e) {
            Alert.alert('Hata', 'Dosya yüklenemedi.');
        } finally {
            setUploading(false);
        }
    };

    const deleteDocument = async (docId) => {
        Alert.alert('Emin misiniz?', 'Bu dosyayı silmek istediğinize emin misiniz?', [
            { text: 'Vazgeç', style: 'cancel' },
            { 
                text: 'Sil', style: 'destructive', onPress: async () => {
                    try {
                        await api.delete(`/v1/personnel/${id}/documents/${docId}`);
                        fetchDetail();
                    } catch (e) { Alert.alert('Hata', 'Silinemedi.'); }
                }
            }
        ]);
    };

    const handleViewDocument = (doc) => {
        const fileUrl = doc.file_path.startsWith('http') ? doc.file_path : `${api.defaults.baseURL.replace('/api', '')}/storage/${doc.file_path}`;
        Linking.openURL(fileUrl).catch(() => Alert.alert('Hata', 'Dosya açılamadı.'));
    };

    const handleShareDocument = async (doc) => {
        try {
            setUploading(true);
            const rawUrl = doc.file_path.startsWith('http') ? doc.file_path : `${api.defaults.baseURL.replace('/api', '')}/storage/${doc.file_path}`;
            const fileUrl = encodeURI(rawUrl);
            
            const ext = doc.file_path.split('.').pop() || 'pdf';
            const safeName = (doc.document_name || `belge_${doc.id}`).replace(/[^a-zA-Z0-9]/g, '_').toLowerCase();
            const localUri = `${FileSystem.cacheDirectory}${safeName}.${ext}`;
            
            const { uri, status } = await FileSystem.downloadAsync(fileUrl, localUri);
            
            if (status !== 200) {
                Alert.alert('Hata', `Dosya sunucudan indirilemedi. (Durum Kodu: ${status})`);
                setUploading(false);
                return;
            }

            if (await Sharing.isAvailableAsync()) {
                await Sharing.shareAsync(uri, { 
                    mimeType: ext === 'pdf' ? 'application/pdf' : 'image/jpeg',
                    dialogTitle: 'Belgeyi Paylaş', 
                    UTI: 'public.item' 
                });
            } else {
                Alert.alert('Bilgi', 'Bu cihazda paylaşım özelliği desteklenmiyor.');
            }
        } catch (error) {
            console.error("Paylaşım hatası:", error);
            Alert.alert('Hata', 'Dosya paylaşılırken bir sorun oluştu. URL geçersiz veya erişilemiyor olabilir.');
        } finally {
            setUploading(false);
        }
    };

    const getStatusInfo = (endDate, archivedAt) => {
        if (archivedAt) return { color: '#EF4444', bg: '#FEF2F2', text: 'ARŞİVDE' };
        if (!endDate) return { color: '#94A3B8', bg: '#F1F5F9', text: 'Tanımsız' };
        const diff = dayjs(endDate).diff(dayjs(), 'day');
        if (diff < 0) return { color: '#EF4444', bg: '#FEF2F2', text: `${Math.abs(diff)} gün geçti` };
        if (diff <= 30) return { color: '#F59E0B', bg: '#FFFBEB', text: `${diff} gün kaldı` };
        return { color: '#10B981', bg: '#ECFDF5', text: `${diff} gün kaldı` };
    };

    if (loading || !personnel) return <View style={s.loader}><ActivityIndicator size="large" color="#3B82F6" /></View>;

    const isImageFile = (path) => /\.(jpg|jpeg|png|gif|webp)$/i.test(path || '');

    const allDocs = personnel.documents?.filter(d => !isImageFile(d.file_path) && d.document_type !== 'image' && d.document_type?.toLowerCase() !== 'resim') || [];
    const images = personnel.documents?.filter(d => isImageFile(d.file_path) || d.document_type === 'image' || d.document_type?.toLowerCase() === 'resim') || [];
    const payrolls = personnel.payrolls || [];

    const isExpired = (d) => {
        if (d.archived_at) return true;
        if (d.end_date && dayjs(d.end_date).endOf('day').isBefore(dayjs())) return true;
        return false;
    };

    const activeDocs = allDocs.filter(d => !isExpired(d));
    const archivedDocs = allDocs.filter(d => isExpired(d));
    const displayDocs = showArchive ? archivedDocs : activeDocs;

    const getInitials = (name) => name ? name.substring(0, 2).toUpperCase() : '?';

    // Format salary beautifully
    const formattedSalary = personnel.base_salary 
        ? new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 }).format(personnel.base_salary) 
        : '-';

    return (
        <SafeAreaView style={s.container} edges={['top', 'left', 'right']}>
            {/* Custom Header */}
            <View style={s.header}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                    <View style={s.backIconWrap}><Icon name="chevron-left" size={26} color="#0F172A" /></View>
                </TouchableOpacity>
                <Text style={s.headerTitle}>Personel Detay</Text>
                <TouchableOpacity onPress={() => setShowMenu(true)} style={s.menuBtn}>
                    <View style={s.menuIconWrap}><Icon name="dots-vertical" size={22} color="#0F172A" /></View>
                </TouchableOpacity>
            </View>

            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 140 }}>
                {/* Hero Section */}
                <View style={s.heroWrapper}>
                    <LinearGradient colors={['#0F172A', '#1E293B']} start={{x: 0, y: 0}} end={{x: 1, y: 1}} style={s.heroGradient}>
                        <View style={s.heroContent}>
                            <View style={s.avatarContainer}>
                                {personnel.profile_photo_url ? (
                                    <Image source={{ uri: personnel.profile_photo_url }} style={s.heroAvatar} />
                                ) : (
                                    <LinearGradient colors={['#3B82F6', '#2563EB']} style={s.heroAvatar}>
                                        <Text style={s.heroAvatarText}>{getInitials(personnel.full_name)}</Text>
                                    </LinearGradient>
                                )}
                                {personnel.is_active && <View style={s.onlineDot} />}
                            </View>
                            <View style={s.heroInfo}>
                                <Text style={s.heroName} numberOfLines={1}>{personnel.full_name}</Text>
                                <Text style={s.heroTc}>{personnel.tc_no || 'TC Kimlik Kayıtlı Değil'}</Text>
                                <View style={s.heroBadges}>
                                    <View style={[s.badge, { backgroundColor: personnel.is_active ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }]}>
                                        <Text style={[s.badgeText, { color: personnel.is_active ? '#34D399' : '#FCA5A5' }]}>{personnel.is_active ? 'UYGUN' : 'PASİF'}</Text>
                                    </View>
                                    <View style={[s.badge, { backgroundColor: 'rgba(255, 255, 255, 0.1)' }]}>
                                        <Text style={[s.badgeText, { color: '#F8FAFC' }]}>{personnel.vehicle?.plate || 'Araç Atanmamış'}</Text>
                                    </View>
                                </View>
                            </View>
                        </View>
                        {/* Decorative glow */}
                        <View style={s.heroGlow1} />
                        <View style={s.heroGlow2} />
                    </LinearGradient>
                </View>

                {/* Premium KPI Cards */}
                <View>
                    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={s.kpiScroll} decelerationRate="fast" snapToInterval={110 + 12}>
                        <View style={s.kpiCard}>
                            <View style={[s.kpiIconBox, { backgroundColor: '#ECFDF5' }]}><Icon name="cash" size={22} color="#10B981" /></View>
                            <Text style={s.kpiVal} numberOfLines={1} adjustsFontSizeToFit>₺{formattedSalary}</Text>
                            <Text style={s.kpiLabel}>Net Maaş</Text>
                        </View>
                        <View style={s.kpiCard}>
                            <View style={[s.kpiIconBox, { backgroundColor: '#EFF6FF' }]}><Icon name="file-document-outline" size={22} color="#3B82F6" /></View>
                            <Text style={s.kpiVal} numberOfLines={1}>{allDocs.length}</Text>
                            <Text style={s.kpiLabel}>Evrak</Text>
                        </View>
                        <View style={s.kpiCard}>
                            <View style={[s.kpiIconBox, { backgroundColor: '#F5F3FF' }]}><Icon name="file-chart-outline" size={22} color="#8B5CF6" /></View>
                            <Text style={s.kpiVal} numberOfLines={1}>{payrolls.length}</Text>
                            <Text style={s.kpiLabel}>Bordro</Text>
                        </View>
                        <View style={s.kpiCard}>
                            <View style={[s.kpiIconBox, { backgroundColor: '#FFFBEB' }]}><Icon name="image-multiple-outline" size={22} color="#F59E0B" /></View>
                            <Text style={s.kpiVal} numberOfLines={1}>{images.length}</Text>
                            <Text style={s.kpiLabel}>Resim</Text>
                        </View>
                    </ScrollView>
                </View>

                {/* Elegant Segmented Tabs */}
                <View style={s.tabsContainer}>
                    <View style={s.tabsWrap}>
                        {['genel', 'belge', 'maas', 'resim'].map(tab => {
                            const isActive = activeTab === tab;
                            return (
                                <TouchableOpacity key={tab} style={[s.tab, isActive && s.activeTab]} onPress={() => setActiveTab(tab)} activeOpacity={0.8}>
                                    <Text style={[s.tabText, isActive && s.activeTabText]}>
                                        {tab === 'genel' ? 'Genel' : tab === 'belge' ? 'Belge' : tab === 'maas' ? 'Maaş' : 'Resim'}
                                    </Text>
                                </TouchableOpacity>
                            );
                        })}
                    </View>
                </View>

                {/* Tab Contents */}
                <View style={s.tabContent}>
                    {activeTab === 'genel' && (
                        <View style={s.infoCard}>
                            <InfoRow icon="phone" color="#10B981" label="Telefon" value={personnel.phone || '-'} />
                            <InfoRow icon="email" color="#3B82F6" label="E-Posta" value={personnel.email || '-'} />
                            <InfoRow icon="card-account-details-outline" color="#8B5CF6" label="Ehliyet Sınıfı" value={personnel.license_class || '-'} />
                            <InfoRow icon="certificate" color="#F59E0B" label="SRC Türü" value={personnel.src_type || '-'} />
                            <InfoRow icon="cake-variant-outline" color="#EC4899" label="Doğum Tarihi" value={personnel.birth_date ? dayjs(personnel.birth_date).format('DD.MM.YYYY') : '-'} />
                            <InfoRow icon="clock-outline" color="#06B6D4" label="Vardiya" value={personnel.start_shift === 'morning' ? 'Sabah Vardiyası' : personnel.start_shift === 'evening' ? 'Akşam Vardiyası' : personnel.start_shift || '-'} />
                            <InfoRow icon="calendar-check" color="#14B8A6" label="İşe Giriş" value={personnel.start_date ? dayjs(personnel.start_date).format('DD.MM.YYYY') : '-'} />
                            <InfoRow icon="map-marker-outline" color="#F43F5E" label="Adres" value={personnel.address || '-'} />
                            <InfoRow icon="text-box-outline" color="#64748B" label="Notlar" value={personnel.notes || '-'} noBorder />
                        </View>
                    )}

                    {activeTab === 'belge' && (
                        <View>
                            <TouchableOpacity style={s.uploadBtn} onPress={() => { setSelectedDoc(null); setDocTitle(''); setShowDocModal(true); }} activeOpacity={0.7}>
                                <View style={s.uploadIconCircle}><Icon name="cloud-upload" size={24} color="#3B82F6" /></View>
                                <Text style={s.uploadBtnText}>Yeni Belge Yükle</Text>
                            </TouchableOpacity>

                            <View style={s.subTabsWrapper}>
                                <TouchableOpacity style={[s.subTabBtn, !showArchive && s.subTabBtnActive]} onPress={() => setShowArchive(false)}>
                                    <Text style={[s.subTabText, !showArchive && s.subTabTextActive]}>Aktif Belgeler</Text>
                                </TouchableOpacity>
                                <TouchableOpacity style={[s.subTabBtn, showArchive && s.subTabBtnActive]} onPress={() => setShowArchive(true)}>
                                    <Text style={[s.subTabText, showArchive && s.subTabTextActive]}>Arşiv Belgeler</Text>
                                </TouchableOpacity>
                            </View>
                            
                            {displayDocs.map(item => {
                                const status = getStatusInfo(item.end_date, item.archived_at);
                                return (
                                    <View key={item.id} style={s.docCard}>
                                        <View style={s.docCardTop}>
                                            <View style={s.docCardIconBox}>
                                                <Icon name="file-document-outline" size={24} color="#3B82F6" />
                                            </View>
                                            <View style={s.docCardInfo}>
                                                <Text style={s.docCardType}>{item.document_type || 'BELGE'}</Text>
                                                <Text style={s.docCardName}>{item.document_name || 'İsimsiz Belge'}</Text>
                                            </View>
                                            <TouchableOpacity onPress={() => deleteDocument(item.id)} style={{ padding: 6 }}>
                                                <Icon name="trash-can-outline" size={20} color="#EF4444" />
                                            </TouchableOpacity>
                                        </View>

                                        <View style={{ alignItems: 'flex-start', marginTop: 4, marginBottom: 16 }}>
                                            <View style={[s.docStatusBadge, { backgroundColor: status.bg }]}>
                                                <Text style={[s.docStatusText, { color: status.color }]}>{status.text}</Text>
                                            </View>
                                        </View>

                                        <View style={s.docCardDates}>
                                            <View style={s.docDateGroup}>
                                                <Icon name="calendar-start" size={14} color="#94A3B8" />
                                                <View style={{ marginLeft: 6 }}>
                                                    <Text style={s.docDateLabel}>Başlangıç</Text>
                                                    <Text style={s.docDateValue}>{item.created_at ? dayjs(item.created_at).format('DD.MM.YYYY') : '-'}</Text>
                                                </View>
                                            </View>
                                            <View style={s.docDateDivider} />
                                            <View style={s.docDateGroup}>
                                                <Icon name="calendar-end" size={14} color="#94A3B8" />
                                                <View style={{ marginLeft: 6 }}>
                                                    <Text style={s.docDateLabel}>Bitiş</Text>
                                                    <Text style={s.docDateValue}>{item.end_date ? dayjs(item.end_date).format('DD.MM.YYYY') : '-'}</Text>
                                                </View>
                                            </View>
                                        </View>

                                        <View style={s.docActionRow}>
                                            <TouchableOpacity style={[s.docActionFullBtn, { backgroundColor: '#EFF6FF' }]} onPress={() => handleViewDocument(item)}>
                                                <Icon name="eye-outline" size={16} color="#3B82F6" />
                                                <Text style={[s.docActionFullText, { color: '#3B82F6' }]}>Görüntüle</Text>
                                            </TouchableOpacity>
                                            <TouchableOpacity style={[s.docActionFullBtn, { backgroundColor: '#ECFDF5' }]} onPress={() => handleViewDocument(item)}>
                                                <Icon name="cloud-download-outline" size={16} color="#10B981" />
                                                <Text style={[s.docActionFullText, { color: '#10B981' }]}>İndir</Text>
                                            </TouchableOpacity>
                                            <TouchableOpacity style={[s.docActionFullBtn, { backgroundColor: '#FFFBEB' }]} onPress={() => handleShareDocument(item)}>
                                                <Icon name="share-variant-outline" size={16} color="#F59E0B" />
                                                <Text style={[s.docActionFullText, { color: '#F59E0B' }]}>Paylaş</Text>
                                            </TouchableOpacity>
                                        </View>
                                    </View>
                                );
                            })}
                            {displayDocs.length === 0 && <View style={s.emptyState}><Icon name="folder-open-outline" size={48} color="#CBD5E1" /><Text style={s.emptyText}>{showArchive ? 'Arşivde belge bulunmuyor.' : 'Henüz aktif belge bulunmuyor.'}</Text></View>}
                        </View>
                    )}

                    {activeTab === 'maas' && (
                        <View>
                            {payrolls.map(p => (
                                <View key={p.id} style={s.docCard}>
                                    <View style={s.docCardTop}>
                                        <View style={[s.docCardIconBox, { backgroundColor: '#DCFCE7' }]}>
                                            <Icon name="cash-register" size={24} color="#10B981" />
                                        </View>
                                        <View style={s.docCardInfo}>
                                            <Text style={[s.docCardType, {color: '#10B981'}]}>BORDRO</Text>
                                            <Text style={s.docCardName}>{dayjs(p.period_month).format('MM/YYYY')}</Text>
                                        </View>
                                        <View style={{ alignItems: 'flex-end', justifyContent: 'center' }}>
                                            <Text style={{ fontSize: 18, fontWeight: '900', color: '#10B981' }}>₺{p.net_salary}</Text>
                                            <Text style={{ fontSize: 11, color: '#64748B', fontWeight: '600' }}>Net Ödenen</Text>
                                        </View>
                                    </View>
                                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', backgroundColor: '#F8FAFC', padding: 12, borderRadius: 12, marginTop: 8 }}>
                                        <View>
                                            <Text style={{ fontSize: 10, color: '#94A3B8', fontWeight: '700', textTransform: 'uppercase' }}>Temel Maaş</Text>
                                            <Text style={{ fontSize: 13, color: '#0F172A', fontWeight: '800' }}>₺{p.base_salary || 0}</Text>
                                        </View>
                                        <View>
                                            <Text style={{ fontSize: 10, color: '#94A3B8', fontWeight: '700', textTransform: 'uppercase' }}>Avans</Text>
                                            <Text style={{ fontSize: 13, color: '#EF4444', fontWeight: '800' }}>₺{p.advance_payment || 0}</Text>
                                        </View>
                                        <View>
                                            <Text style={{ fontSize: 10, color: '#94A3B8', fontWeight: '700', textTransform: 'uppercase' }}>Kesinti</Text>
                                            <Text style={{ fontSize: 13, color: '#EF4444', fontWeight: '800' }}>₺{p.deduction || 0}</Text>
                                        </View>
                                    </View>
                                </View>
                            ))}
                            {payrolls.length === 0 && <View style={s.emptyState}><Icon name="cash-remove" size={48} color="#CBD5E1" /><Text style={s.emptyText}>Maaş bordrosu bulunamadı.</Text></View>}
                        </View>
                    )}

                    {activeTab === 'resim' && (
                        <View>
                            <TouchableOpacity style={[s.uploadBtn, { borderColor: '#DDD6FE', backgroundColor: '#F5F3FF' }]} onPress={() => { setSelectedDoc(null); setDocTitle(''); pickDocument(true); }} activeOpacity={0.7}>
                                <View style={[s.uploadIconCircle, { backgroundColor: '#EDE9FE' }]}><Icon name="image-plus" size={24} color="#8B5CF6" /></View>
                                <Text style={[s.uploadBtnText, { color: '#8B5CF6' }]}>Yeni Resim Yükle</Text>
                            </TouchableOpacity>
                            <View style={s.imageGrid}>
                                {images.map(img => (
                                    <View key={img.id} style={s.imageCard}>
                                        <Image source={{ uri: img.file_path.startsWith('http') ? img.file_path : `${api.defaults.baseURL.replace('/api', '')}/storage/${img.file_path}` }} style={s.gridImage} />
                                        <TouchableOpacity style={s.deleteImgBtn} onPress={() => deleteDocument(img.id)}>
                                            <Icon name="close" size={16} color="#FFF" />
                                        </TouchableOpacity>
                                    </View>
                                ))}
                            </View>
                            {images.length === 0 && <View style={s.emptyState}><Icon name="image-off-outline" size={48} color="#CBD5E1" /><Text style={s.emptyText}>Galeri boş.</Text></View>}
                        </View>
                    )}
                </View>
            </ScrollView>

            {/* Menu Bottom Sheet */}
            <Modal visible={showMenu} transparent animationType="fade">
                <TouchableOpacity style={s.modalOverlay} activeOpacity={1} onPress={() => setShowMenu(false)}>
                    <View style={[s.bottomSheet, { paddingBottom: Math.max(insets.bottom, 20) }]}>
                        <View style={s.sheetHandle} />
                        <Text style={s.sheetTitle}>Aksiyon Menüsü</Text>
                        
                        <TouchableOpacity style={s.menuItem} onPress={() => { setShowMenu(false); updateStatus(!personnel.is_active); }}>
                            <View style={[s.menuIconBox, { backgroundColor: personnel.is_active ? '#FEE2E2' : '#DCFCE7' }]}>
                                <Icon name={personnel.is_active ? "account-off-outline" : "account-check-outline"} size={22} color={personnel.is_active ? "#EF4444" : "#10B981"} />
                            </View>
                            <Text style={[s.menuText, { color: personnel.is_active ? "#EF4444" : "#10B981" }]}>{personnel.is_active ? 'Pasif Yap' : 'Aktif Yap'}</Text>
                        </TouchableOpacity>

                        <TouchableOpacity style={s.menuItem} onPress={() => { setShowMenu(false); updateStatus(false, new Date().toISOString().split('T')[0]); }}>
                            <View style={[s.menuIconBox, { backgroundColor: '#FEF3C7' }]}>
                                <Icon name="exit-run" size={22} color="#F59E0B" />
                            </View>
                            <Text style={[s.menuText, { color: "#F59E0B" }]}>İşten Ayrıldı Olarak İşaretle</Text>
                        </TouchableOpacity>

                        <TouchableOpacity style={s.menuItem} onPress={() => { setShowMenu(false); setShowVehicleModal(true); }}>
                            <View style={[s.menuIconBox, { backgroundColor: '#EFF6FF' }]}>
                                <Icon name="car-shift-pattern" size={22} color="#3B82F6" />
                            </View>
                            <Text style={[s.menuText, { color: "#3B82F6" }]}>Araç Değiştir / Ata</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </Modal>

            {/* Vehicle Modal */}
            <Modal visible={showVehicleModal} transparent animationType="slide">
                <View style={s.modalOverlayCenter}>
                    <View style={s.centerModal}>
                        <Text style={s.modalTitle}>Araç Ata</Text>
                        <View style={s.fieldWrap}>
                            <Picker selectedValue={newVehicleId} onValueChange={setNewVehicleId} style={s.picker}>
                                <Picker.Item label="Araç Seçin (veya Kaldır)" value="" color="#94A3B8" />
                                {vehicles.map(v => <Picker.Item key={v.id} label={v.plate} value={v.id.toString()} color="#0F172A" />)}
                            </Picker>
                        </View>
                        <View style={s.modalActions}>
                            <TouchableOpacity style={s.cancelBtn} onPress={() => setShowVehicleModal(false)}><Text style={s.cancelBtnText}>İptal</Text></TouchableOpacity>
                            <TouchableOpacity style={s.saveBtn} onPress={changeVehicle}><Text style={s.saveBtnText}>Değiştir</Text></TouchableOpacity>
                        </View>
                    </View>
                </View>
            </Modal>

            {/* Document Upload Bottom Sheet */}
            <Modal visible={showDocModal} transparent animationType="slide">
                <View style={s.modalOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={{ width: '100%', flex: 1, justifyContent: 'flex-end' }}>
                        <View style={[s.bottomSheet, { paddingBottom: Math.max(insets.bottom, 20) }]}>
                            <View style={s.sheetHandle} />
                            <Text style={s.sheetTitle}>Belge Yükle</Text>

                            <TouchableOpacity style={s.docPickerBtn} onPress={() => pickDocument(false)}>
                                <Icon name="file-document-outline" size={36} color="#3B82F6" />
                                <Text style={s.docPickerText}>{selectedDoc ? selectedDoc.name : 'Cihazdan Belge Seç (PDF, Word, vb.)'}</Text>
                            </TouchableOpacity>

                            {selectedDoc && (
                                <View style={s.fieldWrap}>
                                    <TextInput style={s.fieldInput} placeholder="Belge Adı (İsteğe Bağlı)" value={docTitle} onChangeText={setDocTitle} />
                                </View>
                            )}

                            <View style={s.formActions}>
                                <TouchableOpacity style={s.cancelBtn} onPress={() => { setShowDocModal(false); setSelectedDoc(null); }}><Text style={s.cancelBtnText}>İptal</Text></TouchableOpacity>
                                <TouchableOpacity style={[s.saveBtn, !selectedDoc && { opacity: 1 }]} onPress={() => uploadFile(activeTab === 'resim' ? 'image' : 'document')} disabled={uploading || !selectedDoc}>
                                    {uploading ? <ActivityIndicator color="#FFF" /> : <Text style={s.saveBtnText}>Yükle</Text>}
                                </TouchableOpacity>
                            </View>
                        </View>
                    </KeyboardAvoidingView>
                </View>
            </Modal>

        </SafeAreaView>
    );
}

const InfoRow = ({ icon, color, label, value, noBorder }) => (
    <View style={[s.infoRow, noBorder && { borderBottomWidth: 0 }]}>
        <View style={s.infoLabelWrap}>
            <View style={[s.infoIconWrap, { backgroundColor: `${color}1A` }]}>
                <Icon name={icon} size={18} color={color} />
            </View>
            <Text style={s.infoLabel}>{label}</Text>
        </View>
        <Text style={s.infoValue}>{value}</Text>
    </View>
);

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    loader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 20, height: 60, backgroundColor: '#F8FAFC' },
    backBtn: { width: 44, height: 44, justifyContent: 'center' },
    backIconWrap: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 4, elevation: 2 },
    headerTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    menuBtn: { width: 44, height: 44, alignItems: 'flex-end', justifyContent: 'center' },
    menuIconWrap: { width: 36, height: 36, borderRadius: 18, backgroundColor: '#FFF', alignItems: 'center', justifyContent: 'center', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 4, elevation: 2 },

    heroWrapper: { marginHorizontal: 20, marginTop: 10, marginBottom: 24, borderRadius: 28, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 12 }, shadowOpacity: 0.15, shadowRadius: 24, elevation: 10 },
    heroGradient: { borderRadius: 28, overflow: 'hidden', padding: 24, position: 'relative' },
    heroContent: { flexDirection: 'row', alignItems: 'center', zIndex: 2 },
    avatarContainer: { marginRight: 20, position: 'relative' },
    heroAvatar: { width: 72, height: 72, borderRadius: 36, borderWidth: 2, borderColor: 'rgba(255,255,255,0.2)', alignItems: 'center', justifyContent: 'center' },
    heroAvatarText: { fontSize: 24, fontWeight: '900', color: '#FFF' },
    onlineDot: { position: 'absolute', bottom: 2, right: 2, width: 16, height: 16, borderRadius: 8, backgroundColor: '#10B981', borderWidth: 3, borderColor: '#0F172A' },
    heroInfo: { flex: 1 },
    heroName: { fontSize: 22, fontWeight: '900', color: '#FFF', marginBottom: 6, letterSpacing: -0.5 },
    heroTc: { fontSize: 13, color: '#94A3B8', fontWeight: '600', marginBottom: 12, letterSpacing: 1 },
    heroBadges: { flexDirection: 'row', gap: 8 },
    badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 10, backdropFilter: 'blur(10px)' },
    badgeText: { fontSize: 10, fontWeight: '900', letterSpacing: 0.5 },
    
    heroGlow1: { position: 'absolute', top: -30, right: -20, width: 100, height: 100, borderRadius: 50, backgroundColor: '#3B82F6', opacity: 0.2, filter: 'blur(20px)' },
    heroGlow2: { position: 'absolute', bottom: -40, left: 20, width: 120, height: 120, borderRadius: 60, backgroundColor: '#8B5CF6', opacity: 0.15, filter: 'blur(30px)' },

    kpiScroll: { paddingHorizontal: 20, gap: 12, marginBottom: 24 },
    kpiCard: { width: 110, backgroundColor: '#FFF', padding: 16, borderRadius: 24, shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.08, shadowRadius: 12, elevation: 4 },
    kpiIconBox: { width: 36, height: 36, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginBottom: 16 },
    kpiVal: { fontSize: 20, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    kpiLabel: { fontSize: 11, color: '#64748B', fontWeight: '600', marginTop: 4 },

    tabsContainer: { paddingHorizontal: 20, marginBottom: 24 },
    tabsWrap: { flexDirection: 'row', backgroundColor: '#F1F5F9', borderRadius: 16, padding: 4 },
    tab: { flex: 1, paddingVertical: 10, alignItems: 'center', borderRadius: 12 },
    activeTab: { backgroundColor: '#FFF', shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.06, shadowRadius: 8, elevation: 2 },
    tabText: { fontSize: 13, fontWeight: '700', color: '#64748B' },
    activeTabText: { color: '#0F172A', fontWeight: '800' },

    tabContent: { paddingHorizontal: 20 },
    infoCard: { backgroundColor: '#FFF', borderRadius: 24, padding: 20, shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.08, shadowRadius: 16, elevation: 4 },
    infoRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F8FAFC' },
    infoLabelWrap: { flexDirection: 'row', alignItems: 'center' },
    infoIconWrap: { width: 32, height: 32, borderRadius: 10, alignItems: 'center', justifyContent: 'center', marginRight: 12 },
    infoLabel: { fontSize: 13, color: '#64748B', fontWeight: '600' },
    infoValue: { fontSize: 14, color: '#0F172A', fontWeight: '800' },

    uploadBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', padding: 20, backgroundColor: '#EFF6FF', borderRadius: 20, borderStyle: 'dashed', borderWidth: 1.5, borderColor: '#BFDBFE', marginBottom: 16 },
    uploadIconCircle: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#DBEAFE', alignItems: 'center', justifyContent: 'center', marginRight: 12 },
    uploadBtnText: { fontSize: 15, fontWeight: '800', color: '#3B82F6' },
    
    subTabsWrapper: { flexDirection: 'row', paddingBottom: 16, borderBottomWidth: 1, borderBottomColor: '#F1F5F9', marginBottom: 16 },
    subTabBtn: { flex: 1, alignItems: 'center', paddingVertical: 12, borderRadius: 12, backgroundColor: '#F8FAFC', marginHorizontal: 4, borderWidth: 1, borderColor: '#F1F5F9' },
    subTabBtnActive: { backgroundColor: '#EFF6FF', borderColor: '#BFDBFE' },
    subTabText: { fontSize: 13, fontWeight: '700', color: '#64748B' },
    subTabTextActive: { color: '#3B82F6' },

    docCard: { backgroundColor: '#fff', borderRadius: 24, padding: 20, marginBottom: 16, shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 6 }, shadowOpacity: 0.08, shadowRadius: 16, elevation: 4 },
    docCardTop: { flexDirection: 'row', alignItems: 'flex-start', marginBottom: 12 },
    docCardIconBox: { width: 44, height: 44, borderRadius: 12, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center' },
    docCardInfo: { flex: 1, marginLeft: 12, justifyContent: 'center' },
    docCardType: { fontSize: 11, fontWeight: '800', color: '#3B82F6', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 2 },
    docCardName: { fontSize: 15, fontWeight: '900', color: '#1E293B', letterSpacing: -0.2 },
    docCardDates: { flexDirection: 'row', alignItems: 'center', marginTop: 4, padding: 12, backgroundColor: '#F8FAFC', borderRadius: 16 },
    docDateGroup: { flex: 1, flexDirection: 'row', alignItems: 'center' },
    docDateDivider: { width: 1, height: 24, backgroundColor: '#E2E8F0', marginHorizontal: 12 },
    docDateLabel: { fontSize: 10, fontWeight: '800', color: '#94A3B8', textTransform: 'uppercase', letterSpacing: 0.5, marginBottom: 2 },
    docDateValue: { fontSize: 13, fontWeight: '900', color: '#0F172A' },
    docStatusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 8 },
    docStatusText: { fontSize: 11, fontWeight: '900' },
    docActionRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginTop: 16, borderTopWidth: 1, borderTopColor: '#F8FAFC', paddingTop: 16, gap: 8 },
    docActionFullBtn: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', flex: 1, gap: 6, paddingVertical: 12, borderRadius: 12 },
    docActionFullText: { fontSize: 12, fontWeight: '800' },

    docItem: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#FFF', padding: 16, borderRadius: 20, marginBottom: 12, shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2 },
    docIconWrap: { width: 48, height: 48, borderRadius: 14, alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    docInfo: { flex: 1 },
    docName: { fontSize: 15, fontWeight: '800', color: '#0F172A', marginBottom: 4 },
    docDate: { fontSize: 12, color: '#64748B', fontWeight: '600' },
    
    imageGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
    imageCard: { width: (SCREEN_WIDTH - 52) / 2, height: 140, borderRadius: 20, overflow: 'hidden', shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 8, elevation: 4 },
    gridImage: { width: '100%', height: '100%' },
    deleteImgBtn: { position: 'absolute', top: 10, right: 10, width: 28, height: 28, borderRadius: 14, backgroundColor: 'rgba(15,23,42,0.6)', alignItems: 'center', justifyContent: 'center', backdropFilter: 'blur(4px)' },

    emptyState: { alignItems: 'center', marginTop: 30, paddingVertical: 20 },
    emptyText: { textAlign: 'center', color: '#94A3B8', marginTop: 12, fontSize: 14, fontWeight: '600' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.5)', justifyContent: 'flex-end', backdropFilter: 'blur(4px)' },
    modalOverlayCenter: { flex: 1, backgroundColor: 'rgba(15,23,42,0.5)', justifyContent: 'center', padding: 20, backdropFilter: 'blur(4px)' },
    bottomSheet: { backgroundColor: '#FFF', borderTopLeftRadius: 32, borderTopRightRadius: 32, padding: 24, shadowColor: '#000', shadowOffset: { width: 0, height: -10 }, shadowOpacity: 0.1, shadowRadius: 20, elevation: 20 },
    sheetHandle: { width: 40, height: 5, borderRadius: 3, backgroundColor: '#E2E8F0', alignSelf: 'center', marginBottom: 24 },
    sheetTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A', marginBottom: 20 },
    
    menuItem: { flexDirection: 'row', alignItems: 'center', paddingVertical: 16, borderBottomWidth: 1, borderBottomColor: '#F8FAFC' },
    menuIconBox: { width: 40, height: 40, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    menuText: { fontSize: 15, fontWeight: '800', marginLeft: 16 },

    centerModal: { backgroundColor: '#FFF', borderRadius: 28, padding: 24, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.2, shadowRadius: 20, elevation: 15 },
    modalTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A', marginBottom: 16 },
    picker: { height: 50, width: '100%' },
    
    docPickerBtn: { padding: 24, backgroundColor: '#F8FAFC', borderRadius: 20, borderWidth: 1.5, borderColor: '#E2E8F0', borderStyle: 'dashed', alignItems: 'center', marginBottom: 20 },
    docPickerText: { fontSize: 14, color: '#64748B', fontWeight: '700', marginTop: 12, textAlign: 'center' },
    
    fieldWrap: { backgroundColor: '#F8FAFC', borderRadius: 16, paddingHorizontal: 16, height: 56, borderWidth: 1, borderColor: '#E2E8F0', marginBottom: 20, justifyContent: 'center' },
    fieldInput: { fontSize: 15, color: '#0F172A', fontWeight: '600' },
    
    formActions: { flexDirection: 'row', gap: 12 },
    modalActions: { flexDirection: 'row', gap: 12, marginTop: 24 },
    cancelBtn: { flex: 1, paddingVertical: 16, borderRadius: 16, backgroundColor: '#F1F5F9', alignItems: 'center' },
    cancelBtnText: { color: '#475569', fontSize: 15, fontWeight: '800' },
    saveBtn: { flex: 2, paddingVertical: 16, borderRadius: 16, backgroundColor: '#8B5CF6', alignItems: 'center', shadowColor: '#8B5CF6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 6 },
    saveBtnText: { color: '#FFF', fontSize: 15, fontWeight: '900' }
});
