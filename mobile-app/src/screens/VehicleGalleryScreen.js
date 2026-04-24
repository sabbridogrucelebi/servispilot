import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Alert, Platform, Image, Modal, TextInput, KeyboardAvoidingView, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import * as ImagePicker from 'expo-image-picker';
import * as Clipboard from 'expo-clipboard';

export default function VehicleGalleryScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { vehicleId, plate } = route.params || {};

    const [images, setImages] = useState([]);
    const [uploadLink, setUploadLink] = useState('');
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);

    // Modal state
    const [uploadModalVisible, setUploadModalVisible] = useState(false);
    const [uploading, setUploading] = useState(false);
    const [selectedImage, setSelectedImage] = useState(null);
    const [imageTitle, setImageTitle] = useState('');
    const [imageType, setImageType] = useState('other');
    const [isFeatured, setIsFeatured] = useState(false);

    const imageTypes = [
        { label: 'Araç Ön Resmi', value: 'front' },
        { label: 'Sağ Yan', value: 'right_side' },
        { label: 'Sol Yan', value: 'left_side' },
        { label: 'Arka', value: 'rear' },
        { label: 'İç Resim 1', value: 'interior_1' },
        { label: 'İç Resim 2', value: 'interior_2' },
        { label: 'Göğüs', value: 'dashboard' },
        { label: 'Diğer Resimler', value: 'other' }
    ];

    useEffect(() => {
        fetchGallery();
    }, []);

    const fetchGallery = async (isRefreshing = false) => {
        try {
            if (!isRefreshing) setLoading(true);
            const response = await api.get(`/vehicles/${vehicleId}/gallery`);
            setImages(response.data.images);
            setUploadLink(response.data.upload_link);
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Galeri yüklenirken bir sorun oluştu.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    };

    const copyToClipboard = async () => {
        await Clipboard.setStringAsync(uploadLink);
        Alert.alert('Kopyalandı', 'Şoför için hızlı yükleme linki panoya kopyalandı.');
    };

    const setAsFeatured = async (imageId) => {
        try {
            setLoading(true);
            await api.post(`/vehicles/${vehicleId}/gallery/${imageId}/featured`);
            fetchGallery();
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Vitrin resmi ayarlanamadı.');
            setLoading(false);
        }
    };

    const deleteImage = (imageId) => {
        Alert.alert('Onay', 'Bu resmi silmek istediğinize emin misiniz?', [
            { text: 'İptal', style: 'cancel' },
            { 
                text: 'Sil', 
                style: 'destructive',
                onPress: async () => {
                    try {
                        setLoading(true);
                        await api.delete(`/vehicles/${vehicleId}/gallery/${imageId}`);
                        fetchGallery();
                    } catch (e) {
                        console.error(e);
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
            allowsEditing: true,
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
            formData.append('image_type', imageType);
            if (imageTitle) formData.append('title', imageTitle);
            formData.append('is_featured', isFeatured ? '1' : '0');

            await api.post(`/vehicles/${vehicleId}/gallery`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setUploadModalVisible(false);
            setSelectedImage(null);
            setImageTitle('');
            setIsFeatured(false);
            fetchGallery();
        } catch (e) {
            console.error(e);
            Alert.alert('Hata', 'Resim yüklenirken bir sorun oluştu.');
        } finally {
            setUploading(false);
        }
    };

    const renderImageCard = ({ item }) => (
        <View style={s.card}>
            <View style={s.imageBox}>
                <Image source={{ uri: item.url }} style={s.image} resizeMode="cover" />
                
                {/* Transparent Black Overlay for Premium look */}
                <LinearGradient colors={['rgba(0,0,0,0)', 'rgba(0,0,0,0.8)']} style={StyleSheet.absoluteFillObject} />
                
                <View style={s.cardOverlayContent}>
                    {item.is_featured && (
                        <View style={s.featuredBadge}>
                            <Icon name="star" size={10} color="#fff" />
                            <Text style={s.featuredTxt}>VİTRİN</Text>
                        </View>
                    )}
                    
                    <View style={s.overlayBottom}>
                        <Text style={s.imgTitleOverlay} numberOfLines={1}>{item.title || item.type_label}</Text>
                        <Text style={s.imgSubOverlay}>{item.source_label}</Text>
                    </View>
                </View>
            </View>
            
            <View style={s.actionRow}>
                {!item.is_featured && (
                    <TouchableOpacity style={s.actionBtn} onPress={() => setAsFeatured(item.id)}>
                        <Icon name="star-outline" size={16} color="#3B82F6" />
                    </TouchableOpacity>
                )}
                <TouchableOpacity style={[s.actionBtn, {marginLeft: 'auto'}]} onPress={() => deleteImage(item.id)}>
                    <Icon name="trash-can-outline" size={16} color="#EF4444" />
                </TouchableOpacity>
            </View>
        </View>
    );


    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={s.header} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="arrow-left" size={20} color="#fff" />
                        </TouchableOpacity>
                        <View style={s.headerTitleWrap}>
                            <Text style={s.headerTitle}>{plate} · Galeri</Text>
                            <View style={s.headerSubWrap}>
                                <View style={s.statusDotSmall} />
                                <Text style={s.headerSubTxt}>Araç Görselleri Yönetimi</Text>
                            </View>
                        </View>
                        <View style={{flexDirection:'row', gap: 8}}>
                            <TouchableOpacity style={s.topBtn} onPress={fetchGallery}><Icon name="refresh" size={20} color="#fff" /></TouchableOpacity>
                            <TouchableOpacity style={s.topAddBtn} onPress={() => setUploadModalVisible(true)}>
                                <Icon name="plus" size={20} color="#fff" />
                            </TouchableOpacity>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.driverLinkWrapper}>
                <View style={s.driverLinkBox}>
                    <View style={s.driverLinkHead}>
                        <Icon name="cellphone-link" size={20} color="#4F46E5" />
                        <Text style={s.driverLinkTitle}>Şoför Hızlı Yükleme</Text>
                    </View>
                    <View style={s.linkRow}>
                        <Text style={s.linkTxt} numberOfLines={1}>{uploadLink}</Text>
                        <TouchableOpacity style={s.copyBtn} onPress={copyToClipboard}>
                            <Icon name="content-copy" size={16} color="#4F46E5" />
                        </TouchableOpacity>
                    </View>
                </View>
            </View>

            {loading && !refreshing ? <ActivityIndicator style={{marginTop:40}} color="#4F46E5" size="large" /> : (
                <FlatList
                    data={images}
                    renderItem={renderImageCard}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={s.list}
                    refreshing={refreshing}
                    onRefresh={() => fetchGallery(true)}
                    numColumns={2}
                    columnWrapperStyle={{ justifyContent: 'space-between' }}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <Icon name="image-off-outline" size={60} color="#E2E8F0" />
                            <Text style={s.emptyTxt}>Kayıtlı araç görseli bulunamadı.</Text>
                        </View>
                    }
                />
            )}

            {/* Yükleme Modalı */}
            <Modal visible={uploadModalVisible} animationType="slide" transparent>
                <View style={s.modalOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={s.modalContentWrap}>
                        <View style={s.modalContent}>
                            <View style={s.modalHeader}>
                                <Text style={s.modalTitle}>Yeni Resim Yükle</Text>
                                <TouchableOpacity onPress={() => setUploadModalVisible(false)}><Icon name="close" size={24} color="#64748B" /></TouchableOpacity>
                            </View>
                            <ScrollView showsVerticalScrollIndicator={false}>
                                <View style={s.inputGroup}>
                                    <Text style={s.label}>Resim Tipi</Text>
                                    <ScrollView horizontal showsHorizontalScrollIndicator={false} style={s.typeScroll}>
                                        {imageTypes.map(t => (
                                            <TouchableOpacity 
                                                key={t.value} 
                                                style={[s.typeBtn, imageType === t.value && s.typeBtnActive]}
                                                onPress={() => setImageType(t.value)}
                                            >
                                                <Text style={[s.typeBtnTxt, imageType === t.value && s.typeBtnTxtActive]}>{t.label}</Text>
                                            </TouchableOpacity>
                                        ))}
                                    </ScrollView>
                                </View>

                                <View style={s.inputGroup}>
                                    <Text style={s.label}>Resim Başlığı (Opsiyonel)</Text>
                                    <TextInput style={s.input} placeholder="Örn: Ön görünüm" value={imageTitle} onChangeText={setImageTitle} />
                                </View>

                                <View style={s.inputGroup}>
                                    <Text style={s.label}>Resim Dosyası</Text>
                                    <TouchableOpacity style={s.filePickBox} onPress={pickImage}>
                                        {selectedImage ? (
                                            <Image source={{ uri: selectedImage.uri }} style={s.selectedImgPreview} borderRadius={12} />
                                        ) : (
                                            <>
                                                <Icon name="image-plus" size={32} color="#94A3B8" />
                                                <Text style={s.filePickTxt}>Galeriden Seç</Text>
                                            </>
                                        )}
                                    </TouchableOpacity>
                                </View>

                                <TouchableOpacity style={s.checkboxRow} onPress={() => setIsFeatured(!isFeatured)}>
                                    <View style={[s.checkbox, isFeatured && s.checkboxActive]}>
                                        {isFeatured && <Icon name="check" size={14} color="#fff" />}
                                    </View>
                                    <View>
                                        <Text style={s.checkTitle}>Vitrin resmi olarak ayarla</Text>
                                        <Text style={s.checkDesc}>Araç detay ekranında bu görsel gösterilsin.</Text>
                                    </View>
                                </TouchableOpacity>

                                <TouchableOpacity style={[s.submitBtn, uploading && {opacity:0.7}]} onPress={handleUpload} disabled={uploading}>
                                    {uploading ? <ActivityIndicator color="#fff" /> : <Text style={s.submitBtnTxt}>Resim Yükle</Text>}
                                </TouchableOpacity>
                            </ScrollView>
                        </View>
                    </KeyboardAvoidingView>
                </View>
            </Modal>
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden', paddingBottom: 40 },
    headerRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 24, paddingTop: 10, marginBottom: 20 },
    backBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    headerTitleWrap: { alignItems: 'center' },
    headerTitle: { color: '#fff', fontSize: 18, fontWeight: '800', letterSpacing: 0.5 },
    headerSubWrap: { flexDirection: 'row', alignItems: 'center', marginTop: 4, gap: 4 },
    statusDotSmall: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#10B981' },
    headerSubTxt: { fontSize: 11, color: '#94A3B8', fontWeight: '600' },
    topBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)' },
    topAddBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(59, 130, 246, 0.4)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#3B82F6' },
    
    driverLinkWrapper: { paddingHorizontal: 20, marginTop: -30, zIndex: 20 },
    driverLinkBox: { backgroundColor: '#fff', borderRadius: 24, padding: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 12 }, shadowOpacity: 0.08, shadowRadius: 24, elevation: 8, borderWidth: 1, borderColor: '#F1F5F9' },
    driverLinkHead: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 },
    driverLinkTitle: { fontSize: 14, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    linkRow: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 16, paddingLeft: 16, borderWidth: 1, borderColor: '#E2E8F0', height: 48 },
    linkTxt: { flex: 1, fontSize: 13, color: '#475569', fontWeight: '600' },
    copyBtn: { width: 48, height: 48, alignItems: 'center', justifyContent: 'center', borderLeftWidth: 1, borderLeftColor: '#E2E8F0' },

    list: { padding: 20, paddingBottom: 100, paddingTop: 20 },
    card: { width: '48%', backgroundColor: '#fff', borderRadius: 24, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4, borderWidth: 1, borderColor: '#F1F5F9', overflow: 'hidden' },
    imageBox: { width: '100%', height: 180, position: 'relative' },
    image: { width: '100%', height: '100%' },
    
    cardOverlayContent: { position: 'absolute', top: 0, left: 0, right: 0, bottom: 0, padding: 12, justifyContent: 'space-between' },
    featuredBadge: { alignSelf: 'flex-start', backgroundColor: '#F59E0B', flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8 },
    featuredTxt: { color: '#fff', fontSize: 9, fontWeight: '900', letterSpacing: 0.5 },
    
    overlayBottom: { marginTop: 'auto' },
    imgTitleOverlay: { fontSize: 13, fontWeight: '800', color: '#fff', marginBottom: 2, letterSpacing: -0.5 },
    imgSubOverlay: { fontSize: 10, fontWeight: '600', color: 'rgba(255,255,255,0.7)' },
    
    actionRow: { flexDirection: 'row', alignItems: 'center', padding: 8, backgroundColor: '#fff' },
    actionBtn: { width: 36, height: 36, borderRadius: 12, backgroundColor: '#F8FAFC', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#F1F5F9' },

    empty: { alignItems: 'center', marginTop: 60 },
    emptyTxt: { color: '#94A3B8', marginTop: 16, fontWeight: '600', fontSize: 16 },

    /* Modal Styles */
    modalOverlay: { flex: 1, backgroundColor: 'rgba(2, 6, 23, 0.6)', justifyContent: 'flex-end' },
    modalContentWrap: { width: '100%', maxHeight: '90%' },
    modalContent: { backgroundColor: '#fff', borderTopLeftRadius: 36, borderTopRightRadius: 36, padding: 30, paddingBottom: 50 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 },
    modalTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    
    inputGroup: { marginBottom: 24 },
    label: { fontSize: 13, fontWeight: '800', color: '#475569', marginBottom: 10, textTransform: 'uppercase', letterSpacing: 0.5 },
    input: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, paddingHorizontal: 18, paddingVertical: 16, fontSize: 15, color: '#0F172A', fontWeight: '600' },
    
    typeScroll: { flexDirection: 'row', marginHorizontal: -4 },
    typeBtn: { paddingHorizontal: 18, paddingVertical: 12, backgroundColor: '#F1F5F9', borderRadius: 14, marginHorizontal: 4, borderWidth: 1, borderColor: 'transparent' },
    typeBtnActive: { backgroundColor: '#EFF6FF', borderColor: '#3B82F6' },
    typeBtnTxt: { fontSize: 13, fontWeight: '700', color: '#64748B' },
    typeBtnTxtActive: { color: '#3B82F6', fontWeight: '900' },

    filePickBox: { width: '100%', height: 180, backgroundColor: '#F8FAFC', borderRadius: 24, borderWidth: 2, borderColor: '#E2E8F0', borderStyle: 'dashed', alignItems: 'center', justifyContent: 'center' },
    filePickTxt: { fontSize: 14, fontWeight: '800', color: '#94A3B8', marginTop: 10 },
    selectedImgPreview: { width: '100%', height: '100%' },

    checkboxRow: { flexDirection: 'row', alignItems: 'center', gap: 14, marginBottom: 30, backgroundColor: '#F8FAFC', padding: 20, borderRadius: 20, borderWidth: 1, borderColor: '#E2E8F0' },
    checkbox: { width: 24, height: 24, borderRadius: 8, borderWidth: 2, borderColor: '#CBD5E1', alignItems: 'center', justifyContent: 'center' },
    checkboxActive: { backgroundColor: '#3B82F6', borderColor: '#3B82F6' },
    checkTitle: { fontSize: 15, fontWeight: '800', color: '#1E293B', letterSpacing: -0.5 },
    checkDesc: { fontSize: 12, color: '#64748B', marginTop: 4, fontWeight: '500' },

    submitBtn: { backgroundColor: '#3B82F6', paddingVertical: 20, borderRadius: 20, alignItems: 'center', shadowColor: '#3B82F6', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.3, shadowRadius: 15, elevation: 6 },
    submitBtnTxt: { color: '#fff', fontSize: 17, fontWeight: '900' }
});
