import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert, Platform, Image, Modal, TextInput, ScrollView, Linking, Share } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import { Picker } from '@react-native-picker/picker';
import api from '../api/axios';
import * as ImagePicker from 'expo-image-picker';
import * as Clipboard from 'expo-clipboard';
import { Header, EmptyState } from '../components';

export default function VehicleGalleryScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { vehicleId, vehicle } = route.params || {};

    const [images, setImages] = useState([]);
    const [uploadLink, setUploadLink] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // Modal state
    const [uploadModalVisible, setUploadModalVisible] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [selectedImage, setSelectedImage] = useState(null);
    const [imageTitle, setImageTitle] = useState('');
    const [imageType, setImageType] = useState('Araç Ön Resmi');
    const [isFeatured, setIsFeatured] = useState(false);

    useEffect(() => {
        fetchGallery();
    }, []);

    const fetchGallery = async (isRefreshing = false) => {
        try {
            if (!isRefreshing) setLoading(true);
            const response = await api.get(`/v1/vehicles/${vehicleId}/gallery`);
            if (response.data && response.data.data) {
                setImages(response.data.data.images || []);
                setUploadLink(response.data.data.driver_upload_link || '');
            }
        } catch (e) {
            console.error('Fetch gallery error:', e);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const copyToClipboard = async () => {
        if (!uploadLink) return;
        await Clipboard.setStringAsync(uploadLink);
        Alert.alert('Kopyalandı', 'Şoför yükleme linki panoya kopyalandı.');
    };

    const setAsFeatured = async (imageId) => {
        try {
            setLoading(true);
            await api.post(`/v1/vehicles/${vehicleId}/gallery/${imageId}/featured`);
            fetchGallery();
        } catch (e) {
            Alert.alert('Hata', 'Vitrin resmi ayarlanamadı.');
            setLoading(false);
        }
    };

    const deleteImage = (imageId) => {
        Alert.alert('Emin misiniz?', 'Bu resmi silmek istediğinize emin misiniz?', [
            { text: 'İptal', style: 'cancel' },
            { 
                text: 'Sil', 
                style: 'destructive',
                onPress: async () => {
                    try {
                        setLoading(true);
                        await api.delete(`/v1/vehicles/${vehicleId}/gallery/${imageId}`);
                        fetchGallery();
                    } catch (e) {
                        Alert.alert('Hata', 'Resim silinemedi.');
                        setLoading(false);
                    }
                }
            }
        ]);
    };

    const pickImage = async () => {
        let result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            allowsEditing: false,
            quality: 0.8,
        });

        if (!result.canceled) {
            setSelectedImage(result.assets[0]);
        }
    };

    const handleUpload = async () => {
        if (!selectedImage) {
            Alert.alert('Uyarı', 'Lütfen bir resim seçin.');
            return;
        }

        try {
            setUploading(true);
            const formData = new FormData();
            
            let localUri = selectedImage.uri;
            let filename = localUri.split('/').pop();
            let match = /\.(\w+)$/.exec(filename);
            let type = match ? `image/${match[1]}` : `image`;

            formData.append('image', { uri: localUri, name: filename, type });
            formData.append('type', imageType);
            if (imageTitle) formData.append('title', imageTitle);
            formData.append('is_featured', isFeatured ? '1' : '0');

            await api.post(`/v1/vehicles/${vehicleId}/gallery`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setUploadModalVisible(false);
            setSelectedImage(null);
            setImageTitle('');
            setIsFeatured(false);
            fetchGallery();
            Alert.alert('Başarılı', 'Resim başarıyla yüklendi.');
        } catch (e) {
            Alert.alert('Hata', 'Resim yüklenirken bir sorun oluştu.');
        } finally {
            setUploading(false);
        }
    };

    const renderImageCard = ({ item }) => (
        <View style={s.card}>
            <View style={s.imageBox}>
                <Image source={{ uri: item.url }} style={s.image} resizeMode="cover" />
                
                <LinearGradient colors={['rgba(0,0,0,0)', 'rgba(0,0,0,0.8)']} style={StyleSheet.absoluteFillObject} />
                
                <View style={s.cardOverlayContent}>
                    <View style={s.overlayTop}>
                        {item.is_featured ? (
                            <View style={s.featuredBadge}>
                                <Icon name="star" size={12} color="#fff" />
                                <Text style={s.featuredTxt}>VİTRİN</Text>
                            </View>
                        ) : <View/>}
                        <View style={[s.sourceBadge, item.source === 'driver' ? {backgroundColor: '#3B82F6'} : {backgroundColor: '#64748B'}]}>
                            <Icon name={item.source === 'driver' ? 'steering' : 'account-cog'} size={10} color="#fff" />
                            <Text style={s.sourceTxt}>{item.source === 'driver' ? 'Şoför Linki' : 'Manuel'}</Text>
                        </View>
                    </View>
                    
                    <View style={s.overlayBottom}>
                        <Text style={s.imgTitleOverlay} numberOfLines={1}>{item.title || item.type}</Text>
                        <Text style={s.imgSubOverlay}>{new Date(item.created_at).toLocaleDateString('tr-TR')}</Text>
                    </View>
                </View>
            </View>
            
            <View style={s.actionRow}>
                {!item.is_featured && (
                    <TouchableOpacity style={s.actionBtn} onPress={() => setAsFeatured(item.id)}>
                        <Icon name="star-outline" size={16} color="#3B82F6" />
                        <Text style={s.actionBtnText}>Vitrin Yap</Text>
                    </TouchableOpacity>
                )}
                <TouchableOpacity style={[s.actionBtn, item.is_featured && {marginLeft: 'auto'}]} onPress={() => Linking.openURL(item.url)}>
                    <Icon name="cloud-download-outline" size={16} color="#10B981" />
                    <Text style={[s.actionBtnText, {color: '#10B981'}]}>İndir</Text>
                </TouchableOpacity>
                <TouchableOpacity style={s.actionBtn} onPress={() => Share.share({ message: `Araç Görseli: ${item.url}` })}>
                    <Icon name="share-variant-outline" size={16} color="#06B6D4" />
                    <Text style={[s.actionBtnText, {color: '#06B6D4'}]}>Paylaş</Text>
                </TouchableOpacity>
                <TouchableOpacity style={[s.actionBtn, !item.is_featured && {marginLeft: 'auto'}]} onPress={() => deleteImage(item.id)}>
                    <Icon name="trash-can-outline" size={16} color="#EF4444" />
                    <Text style={[s.actionBtnText, {color: '#EF4444'}]}>Sil</Text>
                </TouchableOpacity>
            </View>
        </View>
    );

    const renderHeader = () => (
        <View style={s.listHeader}>
            {/* Driver Link Card */}
            <View style={s.driverLinkCard}>
                <LinearGradient colors={['#4F46E5', '#3B82F6']} style={s.driverLinkGradient} start={{x:0, y:0}} end={{x:1, y:1}}>
                    <View style={s.driverLinkContent}>
                        <View style={s.driverLinkIconBox}>
                            <Icon name="link-variant" size={24} color="#fff" />
                        </View>
                        <View style={{ flex: 1 }}>
                            <Text style={s.driverLinkTitle}>Şoför İçin Hızlı Yükleme</Text>
                            <Text style={s.driverLinkDesc}>Şoföre özel linki göndererek araca ait resimleri kendi kamerasından yüklemesini sağlayın.</Text>
                        </View>
                    </View>
                    <TouchableOpacity style={s.copyBtn} onPress={copyToClipboard}>
                        <Icon name="content-copy" size={16} color="#4F46E5" />
                        <Text style={s.copyBtnText}>LİNKİ KOPYALA</Text>
                    </TouchableOpacity>
                </LinearGradient>
            </View>

            <View style={s.sectionHeader}>
                <Text style={s.sectionTitle}>Araç Resimleri</Text>
                <Text style={s.sectionSubtitle}>Toplam {images.length} görsel</Text>
            </View>
        </View>
    );

    return (
        <SafeAreaView style={s.container} edges={['top']}>
            <View style={{ backgroundColor: '#fff', zIndex: 10, paddingBottom: 12 }}>
                <View style={s.header}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                        <Icon name="chevron-left" size={26} color="#0F172A" />
                    </TouchableOpacity>
                    <View style={s.headerCenter}>
                        <Text style={s.headerTitle}>Araç Galerisi</Text>
                        <Text style={s.headerSubtitle}>{vehicle?.plate || 'Görseller'}</Text>
                    </View>
                    <TouchableOpacity style={s.addHeaderBtn} onPress={() => setUploadModalVisible(true)}>
                        <Icon name="plus" size={24} color="#fff" />
                    </TouchableOpacity>
                </View>
            </View>

            {loading && !refreshing ? (
                <View style={s.loader}><ActivityIndicator size="large" color="#4F46E5" /></View>
            ) : (
                <FlatList
                    data={images}
                    keyExtractor={(i) => i.id.toString()}
                    renderItem={renderImageCard}
                    ListHeaderComponent={renderHeader}
                    contentContainerStyle={s.listContent}
                    ListEmptyComponent={<EmptyState icon="image-off-outline" title="Görsel Bulunamadı" message="Bu araca ait henüz hiç resim yüklenmemiş." />}
                />
            )}

            {/* Upload Modal */}
            <Modal visible={uploadModalVisible} animationType="slide" transparent={true} onRequestClose={() => setUploadModalVisible(false)}>
                <View style={s.modalOverlay}>
                    <View style={s.modalContent}>
                        <View style={s.modalHeader}>
                            <Text style={s.modalTitle}>Yeni Resim Yükle</Text>
                            <TouchableOpacity onPress={() => setUploadModalVisible(false)} style={s.modalClose}>
                                <Icon name="close" size={24} color="#64748B" />
                            </TouchableOpacity>
                        </View>
                        
                        <ScrollView style={{ padding: 20 }}>
                            <Text style={s.inputLabel}>RESİM TİPİ *</Text>
                            <View style={s.pickerContainer}>
                                <Picker selectedValue={imageType} onValueChange={(itemValue) => setImageType(itemValue)} style={s.picker}>
                                    <Picker.Item label="Araç Ön Resmi" value="Araç Ön Resmi" />
                                    <Picker.Item label="Sağ Yan" value="Sağ Yan" />
                                    <Picker.Item label="Sol Yan" value="Sol Yan" />
                                    <Picker.Item label="Arka" value="Arka" />
                                    <Picker.Item label="İç Resim 1" value="İç Resim 1" />
                                    <Picker.Item label="İç Resim 2" value="İç Resim 2" />
                                    <Picker.Item label="Göğüs" value="Göğüs" />
                                    <Picker.Item label="Ruhsat Resmi" value="Ruhsat Resmi" />
                                    <Picker.Item label="Diğer" value="Diğer" />
                                </Picker>
                            </View>

                            <Text style={[s.inputLabel, { marginTop: 16 }]}>RESİM BAŞLIĞI / AÇIKLAMA</Text>
                            <TextInput 
                                value={imageTitle}
                                onChangeText={setImageTitle}
                                placeholder="Örn: Ön tamponda hafif çizik"
                                style={s.inputBox}
                            />

                            <Text style={[s.inputLabel, { marginTop: 16 }]}>RESİM DOSYASI *</Text>
                            <TouchableOpacity style={s.uploadArea} onPress={pickImage}>
                                {selectedImage ? (
                                    <Image source={{ uri: selectedImage.uri }} style={s.previewImage} />
                                ) : (
                                    <View style={s.uploadAreaPlaceholder}>
                                        <Icon name="image-plus" size={40} color="#94A3B8" />
                                        <Text style={s.uploadAreaText}>Kameradan veya Galeriden Seç</Text>
                                    </View>
                                )}
                            </TouchableOpacity>

                            <TouchableOpacity 
                                style={[s.checkboxRow, {marginTop: 16}]} 
                                onPress={() => setIsFeatured(!isFeatured)}
                                activeOpacity={0.7}
                            >
                                <View style={[s.checkbox, isFeatured && s.checkboxActive]}>
                                    {isFeatured && <Icon name="check" size={14} color="#fff" />}
                                </View>
                                <Text style={s.checkboxText}>Bu resmi vitrin (ana) resim yap</Text>
                            </TouchableOpacity>

                            <TouchableOpacity style={[s.saveBtn, uploading && { opacity: 0.7 }]} onPress={handleUpload} disabled={uploading}>
                                {uploading ? <ActivityIndicator color="#fff" /> : <Text style={s.saveBtnText}>Resmi Yükle</Text>}
                            </TouchableOpacity>
                            <View style={{ height: 40 }} />
                        </ScrollView>
                    </View>
                </View>
            </Modal>
        </SafeAreaView>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    loader: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 16, paddingTop: Platform.OS === 'ios' ? 44 : 24 },
    backBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    headerCenter: { flex: 1, alignItems: 'center' },
    headerTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
    headerSubtitle: { fontSize: 13, color: '#64748B', marginTop: 2, fontWeight: '500' },
    addHeaderBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#8B5CF6', alignItems: 'center', justifyContent: 'center', shadowColor: '#8B5CF6', shadowOffset: { width: 0, height: 4 }, shadowopacity: 1, shadowRadius: 8, elevation: 4 },

    listContent: { padding: 16, paddingBottom: 100 },
    listHeader: { marginBottom: 20 },

    driverLinkCard: { borderRadius: 20, overflow: 'hidden', marginBottom: 24, shadowColor: '#4F46E5', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.2, shadowRadius: 16, elevation: 6 },
    driverLinkGradient: { padding: 20 },
    driverLinkContent: { flexDirection: 'row', alignItems: 'center', gap: 16, marginBottom: 16 },
    driverLinkIconBox: { width: 48, height: 48, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.2)', alignItems: 'center', justifyContent: 'center' },
    driverLinkTitle: { fontSize: 16, fontWeight: '800', color: '#fff', marginBottom: 4 },
    driverLinkDesc: { fontSize: 12, color: 'rgba(255,255,255,0.8)', lineHeight: 18 },
    copyBtn: { backgroundColor: '#fff', borderRadius: 12, paddingVertical: 12, flexDirection: 'row', justifyContent: 'center', alignItems: 'center', gap: 8 },
    copyBtnText: { color: '#4F46E5', fontSize: 13, fontWeight: '800', letterSpacing: 0.5 },

    sectionHeader: { marginBottom: 12 },
    sectionTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
    sectionSubtitle: { fontSize: 13, color: '#64748B', marginTop: 2 },

    card: { backgroundColor: '#fff', borderRadius: 20, overflow: 'hidden', marginBottom: 16, borderWidth: 1, borderColor: '#F1F5F9', shadowColor: '#94A3B8', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 8, elevation: 2 },
    imageBox: { height: 200, width: '100%', position: 'relative' },
    image: { width: '100%', height: '100%' },
    cardOverlayContent: { position: 'absolute', inset: 0, padding: 16, justifyContent: 'space-between' },
    overlayTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
    featuredBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F59E0B', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, gap: 4 },
    featuredTxt: { color: '#fff', fontSize: 10, fontWeight: '800', letterSpacing: 0.5 },
    sourceBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, gap: 4 },
    sourceTxt: { color: '#fff', fontSize: 10, fontWeight: '700' },
    overlayBottom: { marginTop: 'auto' },
    imgTitleOverlay: { color: '#fff', fontSize: 18, fontWeight: '800', textShadowColor: 'rgba(0,0,0,0.5)', textShadowOffset: {width: 0, height: 1}, textShadowRadius: 4 },
    imgSubOverlay: { color: 'rgba(255,255,255,0.8)', fontSize: 12, fontWeight: '500', marginTop: 4 },
    
    actionRow: { flexDirection: 'row', padding: 12, backgroundColor: '#F8FAFC', borderTopWidth: 1, borderColor: '#F1F5F9' },
    actionBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8, backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0', gap: 6 },
    actionBtnText: { fontSize: 12, fontWeight: '700', color: '#475569' },

    modalOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end' },
    modalContent: { backgroundColor: '#fff', borderTopLeftRadius: 32, borderTopRightRadius: 32, height: '85%' },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 24, borderBottomWidth: 1, borderColor: '#F1F5F9' },
    modalTitle: { fontSize: 18, fontWeight: '800', color: '#0F172A' },
    modalClose: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    
    inputLabel: { fontSize: 12, fontWeight: '700', color: '#475569', marginBottom: 8, letterSpacing: 0.5 },
    pickerContainer: { borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, backgroundColor: '#fff', overflow: 'hidden' },
    picker: { height: 50, width: '100%' },
    inputBox: { borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 12, backgroundColor: '#fff', paddingHorizontal: 16, paddingVertical: 14, fontSize: 15, color: '#0F172A' },
    
    uploadArea: { height: 160, borderWidth: 2, borderColor: '#E2E8F0', borderStyle: 'dashed', borderRadius: 16, backgroundColor: '#F8FAFC', overflow: 'hidden' },
    uploadAreaPlaceholder: { flex: 1, alignItems: 'center', justifyContent: 'center', gap: 12 },
    uploadAreaText: { fontSize: 14, fontWeight: '600', color: '#64748B' },
    previewImage: { width: '100%', height: '100%', resizeMode: 'cover' },
    
    checkboxRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
    checkbox: { width: 20, height: 20, borderRadius: 6, borderWidth: 2, borderColor: '#CBD5E1', alignItems: 'center', justifyContent: 'center' },
    checkboxActive: { backgroundColor: '#8B5CF6', borderColor: '#8B5CF6' },
    checkboxText: { fontSize: 14, fontWeight: '500', color: '#475569' },
    
    saveBtn: { backgroundColor: '#8B5CF6', paddingVertical: 16, borderRadius: 16, alignItems: 'center', marginTop: 24, shadowColor: '#8B5CF6', shadowOffset: { width: 0, height: 6 }, shadowopacity: 1, shadowRadius: 10, elevation: 5 },
    saveBtnText: { color: '#fff', fontSize: 16, fontWeight: '800' }
});
