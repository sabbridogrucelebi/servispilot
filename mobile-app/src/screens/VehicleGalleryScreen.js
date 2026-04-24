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
                {item.is_featured && (
                    <View style={s.featuredBadge}>
                        <Icon name="star" size={12} color="#fff" />
                        <Text style={s.featuredTxt}>Vitrin</Text>
                    </View>
                )}
            </View>
            <View style={s.cardInfo}>
                <Text style={s.imgTitle}>{item.title}</Text>
                <View style={s.tagsRow}>
                    <View style={s.tag}><Text style={s.tagTxt}>{item.type_label}</Text></View>
                    <View style={[s.tag, {backgroundColor: '#F1F5F9'}]}><Text style={[s.tagTxt, {color: '#64748B'}]}>Kaynak: {item.source_label}</Text></View>
                </View>
                <View style={s.actionRow}>
                    {!item.is_featured && (
                        <TouchableOpacity style={s.actionBtn} onPress={() => setAsFeatured(item.id)}>
                            <Icon name="star-outline" size={14} color="#3B82F6" />
                            <Text style={[s.actionTxt, {color: '#3B82F6'}]}>Vitrin Yap</Text>
                        </TouchableOpacity>
                    )}
                    <TouchableOpacity style={s.actionBtn} onPress={() => deleteImage(item.id)}>
                        <Icon name="trash-can-outline" size={14} color="#EF4444" />
                        <Text style={[s.actionTxt, {color: '#EF4444'}]}>Sil</Text>
                    </TouchableOpacity>
                </View>
            </View>
        </View>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#040B16', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()}><Icon name="chevron-left" size={28} color="#fff" /></TouchableOpacity>
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Galeri</Text></View>
                        <TouchableOpacity style={s.addBtn} onPress={() => setUploadModalVisible(true)}>
                            <Icon name="cloud-upload" size={24} color="#fff" />
                        </TouchableOpacity>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.driverLinkBox}>
                <View style={s.driverLinkHead}>
                    <Icon name="link-variant" size={20} color="#4F46E5" />
                    <Text style={s.driverLinkTitle}>Şoför İçin Hızlı Yükleme Linki</Text>
                </View>
                <Text style={s.driverLinkDesc}>Bu linki şoföre gönderin, telefondan kamerayla direkt araç resimlerini yüklesin.</Text>
                <View style={s.linkRow}>
                    <Text style={s.linkTxt} numberOfLines={1}>{uploadLink}</Text>
                    <TouchableOpacity style={s.copyBtn} onPress={copyToClipboard}>
                        <Icon name="content-copy" size={16} color="#fff" />
                        <Text style={s.copyBtnTxt}>Kopyala</Text>
                    </TouchableOpacity>
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
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 30, borderBottomLeftRadius: 30, borderBottomRightRadius: 30 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 20, paddingTop: Platform.OS === 'ios' ? 0 : 40 },
    headerTitle: { color: '#fff', fontSize: 16, fontWeight: '800' },
    addBtn: { width: 36, height: 36, borderRadius: 10, backgroundColor: 'rgba(255,255,255,0.15)', alignItems: 'center', justifyContent: 'center' },

    driverLinkBox: { marginHorizontal: 16, marginTop: -20, backgroundColor: '#fff', borderRadius: 24, padding: 20, shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.05, shadowRadius: 20, elevation: 5, borderWidth: 1, borderColor: '#F1F5F9' },
    driverLinkHead: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 6 },
    driverLinkTitle: { fontSize: 14, fontWeight: '800', color: '#1E293B' },
    driverLinkDesc: { fontSize: 11, color: '#64748B', lineHeight: 16, marginBottom: 15 },
    linkRow: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderRadius: 12, paddingLeft: 12, borderWidth: 1, borderColor: '#E2E8F0', overflow: 'hidden' },
    linkTxt: { flex: 1, fontSize: 11, color: '#475569', fontWeight: '500' },
    copyBtn: { backgroundColor: '#4F46E5', flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 16, paddingVertical: 12 },
    copyBtnTxt: { color: '#fff', fontSize: 12, fontWeight: '700' },

    list: { padding: 16, paddingBottom: 40 },
    card: { width: '48%', backgroundColor: '#fff', borderRadius: 20, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 15, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9', overflow: 'hidden' },
    imageBox: { width: '100%', height: 120, backgroundColor: '#E2E8F0', position: 'relative' },
    image: { width: '100%', height: '100%' },
    featuredBadge: { position: 'absolute', top: 8, left: 8, backgroundColor: '#10B981', flexDirection: 'row', alignItems: 'center', gap: 4, paddingHorizontal: 8, paddingVertical: 4, borderRadius: 20 },
    featuredTxt: { color: '#fff', fontSize: 9, fontWeight: '800' },
    
    cardInfo: { padding: 12 },
    imgTitle: { fontSize: 13, fontWeight: '800', color: '#1E293B', marginBottom: 8 },
    tagsRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 6, marginBottom: 12 },
    tag: { backgroundColor: '#EEF2FF', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    tagTxt: { fontSize: 9, fontWeight: '700', color: '#4F46E5' },
    actionRow: { flexDirection: 'row', justifyContent: 'space-between', borderTopWidth: 1, borderTopColor: '#F1F5F9', paddingTop: 10 },
    actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    actionTxt: { fontSize: 10, fontWeight: '700' },

    empty: { alignItems: 'center', marginTop: 80 },
    emptyTxt: { color: '#94A3B8', marginTop: 12, fontWeight: '600' },

    /* Modal Styles */
    modalOverlay: { flex: 1, backgroundColor: 'rgba(15, 23, 42, 0.6)', justifyContent: 'flex-end' },
    modalContentWrap: { width: '100%', maxHeight: '90%' },
    modalContent: { backgroundColor: '#fff', borderTopLeftRadius: 32, borderTopRightRadius: 32, padding: 24, paddingBottom: 40 },
    modalHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 },
    modalTitle: { fontSize: 20, fontWeight: '900', color: '#0F172A' },
    
    inputGroup: { marginBottom: 20 },
    label: { fontSize: 13, fontWeight: '700', color: '#475569', marginBottom: 8 },
    input: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 14, paddingHorizontal: 16, paddingVertical: 14, fontSize: 14, color: '#0F172A' },
    
    typeScroll: { flexDirection: 'row', marginHorizontal: -4 },
    typeBtn: { paddingHorizontal: 16, paddingVertical: 10, backgroundColor: '#F1F5F9', borderRadius: 20, marginHorizontal: 4, borderWidth: 1, borderColor: 'transparent' },
    typeBtnActive: { backgroundColor: '#EEF2FF', borderColor: '#4F46E5' },
    typeBtnTxt: { fontSize: 12, fontWeight: '600', color: '#64748B' },
    typeBtnTxtActive: { color: '#4F46E5', fontWeight: '800' },

    filePickBox: { width: '100%', height: 160, backgroundColor: '#F8FAFC', borderRadius: 20, borderWidth: 2, borderColor: '#E2E8F0', borderStyle: 'dashed', alignItems: 'center', justifyContent: 'center' },
    filePickTxt: { fontSize: 13, fontWeight: '600', color: '#94A3B8', marginTop: 8 },
    selectedImgPreview: { width: '100%', height: '100%' },

    checkboxRow: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 24, backgroundColor: '#F8FAFC', padding: 16, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0' },
    checkbox: { width: 20, height: 20, borderRadius: 6, borderWidth: 2, borderColor: '#CBD5E1', alignItems: 'center', justifyContent: 'center' },
    checkboxActive: { backgroundColor: '#4F46E5', borderColor: '#4F46E5' },
    checkTitle: { fontSize: 13, fontWeight: '700', color: '#1E293B' },
    checkDesc: { fontSize: 11, color: '#64748B', marginTop: 2 },

    submitBtn: { backgroundColor: '#A855F7', paddingVertical: 16, borderRadius: 16, alignItems: 'center' },
    submitBtnTxt: { color: '#fff', fontSize: 15, fontWeight: '800' }
});
