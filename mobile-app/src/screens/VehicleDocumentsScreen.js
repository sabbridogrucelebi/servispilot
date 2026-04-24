import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, Linking, Share, Modal, TextInput, KeyboardAvoidingView, Platform, ScrollView } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import api from '../api/axios';
import DatePickerInput from '../components/DatePickerInput';
import { todayUi, toApiDate } from '../utils/date';

export default function VehicleDocumentsScreen({ route, navigation }) {
    const { vehicleId, plate } = route.params || {};
    const [documents, setDocuments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [modalVisible, setModalVisible] = useState(false);
    const [viewArchived, setViewArchived] = useState(false);
    const [form, setForm] = useState({ title: '', type: '', start_date: todayUi(), end_date: '', note: '' });

    useEffect(() => {
        fetchDocuments();
    }, []);

    const fetchDocuments = async () => {
        if (!vehicleId) {
            setLoading(false);
            return;
        }
        try {
            const r = await api.get(`/vehicles/${vehicleId}/documents`);
            setDocuments(r.data.documents);
        } catch (e) { console.error(e); }
        finally { setLoading(false); }
    };

    const getDaysRemaining = (date) => {
        if (!date) return null;
        const diff = new Date(date) - new Date();
        return Math.ceil(diff / (1000 * 60 * 60 * 24));
    };

    const activeDocs = documents.filter(doc => !doc.is_expired);
    const archivedDocs = documents.filter(doc => doc.is_expired);
    const displayDocs = viewArchived ? archivedDocs : activeDocs;

    const handleShare = async (url) => {
        try {
            await Share.share({ message: `Belgeyi görüntüle: ${url}` });
        } catch (error) { console.error(error.message); }
    };

    const renderItem = ({ item }) => {
        const days = getDaysRemaining(item.end_date);
        const isExpired = item.is_expired;

        return (
            <View style={s.card}>
                <View style={s.cardHeader}>
                    <View style={s.titleGroup}>
                        <Text style={s.docTitle}>{item.title}</Text>
                        <View style={s.typeBadge}>
                            <Text style={s.typeText}>{item.type || 'Belge'}</Text>
                        </View>
                    </View>
                    <View style={{flexDirection:'row', gap: 8}}>
                        <TouchableOpacity onPress={() => Linking.openURL(item.file_url)} style={s.viewBtn}>
                            <Icon name="eye-outline" size={18} color="#3B82F6" />
                        </TouchableOpacity>
                        <TouchableOpacity onPress={() => handleShare(item.file_url)} style={[s.viewBtn, {backgroundColor:'#ECFDF5'}]}>
                            <Icon name="share-variant" size={18} color="#10B981" />
                        </TouchableOpacity>
                    </View>
                </View>

                <View style={s.detailsRow}>
                    <View style={s.detailItem}>
                        <Text style={s.detailLabel}>BAŞLANGIÇ</Text>
                        <Text style={s.detailValue}>{item.start_date ? new Date(item.start_date).toLocaleDateString('tr-TR') : '-'}</Text>
                    </View>
                    <View style={s.detailItem}>
                        <Text style={s.detailLabel}>BİTİŞ</Text>
                        <Text style={s.detailValue}>{item.end_date ? new Date(item.end_date).toLocaleDateString('tr-TR') : 'Süresiz'}</Text>
                    </View>
                    <View style={s.detailItem}>
                        <Text style={s.detailLabel}>KALAN SÜRE</Text>
                        <View style={[s.remainingBadge, isExpired ? s.expiredBadge : (days !== null ? s.activeBadge : s.permanentBadge)]}>
                            <Text style={[s.remainingText, isExpired ? s.expiredText : (days !== null ? s.activeText : s.permanentText)]}>
                                {days === null ? '-' : (isExpired ? `${Math.abs(days)} GÜN GEÇTİ` : `${days} GÜN KALDI`)}
                            </Text>
                        </View>
                    </View>
                </View>
            </View>
        );
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#040B16', '#0D1B2A']} style={s.header}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()}><Icon name="chevron-left" size={28} color="#fff" /></TouchableOpacity>
                        <View style={{flex:1, alignItems:'center'}}><Text style={s.headerTitle}>{plate} - Belgeler</Text></View>
                        <View style={{width:28}} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <View style={s.pageSubHeader}>
                <View style={s.subHeaderLeft}>
                    <Text style={s.pageTitle}>{viewArchived ? 'Arşiv Belgeler' : 'Araç Belgeleri'}</Text>
                    <Text style={s.pageDesc}>{viewArchived ? 'Süresi dolmuş veya arşivlenmiş belgeler' : 'Aktif belgeler ve kalan süre takibi'}</Text>
                </View>
                <View style={{flexDirection:'row', gap:8}}>
                    <TouchableOpacity 
                        style={[s.archiveBtn, viewArchived && s.archiveBtnActive]} 
                        onPress={() => setViewArchived(!viewArchived)}
                    >
                        <Text style={[s.archiveBtnTxt, viewArchived && s.archiveBtnTxtActive]}>Arşiv {viewArchived ? 'X' : ''}</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={s.addBtn} onPress={() => setModalVisible(true)}>
                        <Icon name="plus" size={20} color="#fff" />
                    </TouchableOpacity>
                </View>
            </View>

            {loading ? <ActivityIndicator style={{marginTop:40}} color="#3B82F6" size="large" /> : (
                <FlatList
                    data={displayDocs}
                    renderItem={renderItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={s.list}
                    ListEmptyComponent={<View style={s.empty}><Icon name="folder-open-outline" size={48} color="#CBD5E1" /><Text style={s.emptyTxt}>{viewArchived ? 'Arşivlenmiş belge bulunamadı.' : 'Aktif belge bulunamadı.'}</Text></View>}
                />
            )}

            <Modal visible={modalVisible} animationType="slide" transparent>
                <View style={s.mOverlay}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{flex:1, justifyContent:'flex-end'}}>
                        <View style={s.mContent}>
                            <View style={s.mHead}>
                                <View>
                                    <Text style={s.mTitle}>Yeni Belge Yükle</Text>
                                    <Text style={s.mDesc}>Bu araca ait yeni belge ekleyin</Text>
                                </View>
                                <TouchableOpacity onPress={() => setModalVisible(false)} style={s.closeBtn}>
                                    <Icon name="close" size={22} color="#64748B" />
                                </TouchableOpacity>
                            </View>

                            <ScrollView showsVerticalScrollIndicator={false}>
                                <View style={s.ig}><Text style={s.il}>Belge Adı</Text><TextInput style={s.inp} placeholder="Örn: Ruhsat Ön Yüz" /></View>
                                <View style={s.ig}><Text style={s.il}>Belge Türü</Text><TextInput style={s.inp} placeholder="Belge türünü seçin" /></View>
                                <View style={{flexDirection:'row', gap:10}}>
                                    <View style={{flex:1}}><DatePickerInput label="Başlangıç Tarihi" value={form.start_date} onChange={d => setForm({...form, start_date: d})} /></View>
                                    <View style={{flex:1}}><DatePickerInput label="Bitiş Tarihi" value={form.end_date} onChange={d => setForm({...form, end_date: d})} /></View>
                                </View>
                                <View style={s.ig}><Text style={s.il}>Belge Dosyası</Text><TouchableOpacity style={s.fileBtn}><Text style={s.fileBtnTxt}>Dosya Seç</Text><Text style={s.fileDesc}>Dosya seçilmedi</Text></TouchableOpacity></View>
                                <View style={s.ig}><Text style={s.il}>Not</Text><TextInput style={[s.inp, {height:80}]} placeholder="Belge ile ilgili açıklama..." multiline /></View>

                                <View style={s.mActions}>
                                    <TouchableOpacity style={s.cancelBtn} onPress={() => setModalVisible(false)}><Text style={s.cancelTxt}>Vazgeç</Text></TouchableOpacity>
                                    <TouchableOpacity style={s.saveBtn} onPress={() => { alert('Yükleniyor...'); setModalVisible(false); }}><Text style={s.saveTxt}>Belgeyi Kaydet</Text></TouchableOpacity>
                                </View>
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
    header: { paddingBottom: 16, paddingHorizontal: 16 },
    headerRow: { flexDirection: 'row', alignItems: 'center', paddingTop: 10, marginTop: 10 },
    headerTitle: { color: '#fff', fontSize: 16, fontWeight: '800' },
    
    pageSubHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 20, backgroundColor: '#fff', borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
    subHeaderLeft: { flex: 1 },
    pageTitle: { fontSize: 18, fontWeight: '800', color: '#1E293B', marginBottom: 4 },
    pageDesc: { fontSize: 12, color: '#64748B', fontWeight: '500' },
    addBtn: { width: 40, height: 40, borderRadius: 12, backgroundColor: '#4F46E5', alignItems: 'center', justifyContent: 'center', shadowColor: '#4F46E5', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8, elevation: 4 },

    list: { padding: 16, paddingBottom: 40 },
    card: { backgroundColor: '#fff', borderRadius: 20, padding: 20, marginBottom: 16, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.05, shadowRadius: 15, elevation: 3, borderWidth: 1, borderColor: '#F1F5F9' },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 20 },
    titleGroup: { flex: 1 },
    docTitle: { fontSize: 15, fontWeight: '800', color: '#1E293B', marginBottom: 6 },
    typeBadge: { alignSelf: 'flex-start', backgroundColor: '#F1F5F9', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 6 },
    typeText: { fontSize: 10, fontWeight: '700', color: '#64748B', textTransform: 'uppercase' },
    viewBtn: { width: 36, height: 36, borderRadius: 10, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center' },
    
    detailsRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-end' },
    detailItem: { flex: 1 },
    detailLabel: { fontSize: 9, fontWeight: '800', color: '#94A3B8', marginBottom: 6, letterSpacing: 0.5 },
    detailValue: { fontSize: 12, fontWeight: '700', color: '#334155' },
    
    remainingBadge: { paddingHorizontal: 10, paddingVertical: 6, borderRadius: 8, alignSelf: 'flex-start' },
    remainingText: { fontSize: 10, fontWeight: '800' },
    activeBadge: { backgroundColor: '#ECFDF5' },
    activeText: { color: '#10B981' },
    expiredBadge: { backgroundColor: '#FEF2F2' },
    expiredText: { color: '#EF4444' },
    permanentBadge: { backgroundColor: '#F8FAFC' },
    permanentText: { color: '#94A3B8' },

    empty: { alignItems: 'center', marginTop: 100 },
    emptyTxt: { color: '#94A3B8', marginTop: 12, fontWeight: '600' },
    
    archiveBtn: { backgroundColor: '#F1F5F9', paddingHorizontal: 12, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
    archiveBtnActive: { backgroundColor: '#4F46E5' },
    archiveBtnTxt: { fontSize: 12, fontWeight: '700', color: '#64748B' },
    archiveBtnTxtActive: { color: '#fff' },
    
    mOverlay: { flex: 1, backgroundColor: 'rgba(15,23,42,0.6)', justifyContent: 'flex-end' },
    mContent: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: 24, maxHeight: '90%' },
    mHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 },
    mTitle: { fontSize: 18, fontWeight: '900', color: '#0F172A' },
    mDesc: { fontSize: 12, color: '#64748B', fontWeight: '500', marginTop: 2 },
    closeBtn: { width: 32, height: 32, borderRadius: 16, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    ig: { marginBottom: 16 },
    il: { fontSize: 11, fontWeight: '700', color: '#64748B', marginBottom: 6 },
    inp: { backgroundColor: '#fff', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 10, padding: 14, fontSize: 14, color: '#0F172A' },
    fileBtn: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 10, padding: 10, gap: 10 },
    fileBtnTxt: { backgroundColor: '#F1F5F9', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 6, fontSize: 12, fontWeight: '600', color: '#334155', borderWidth: 1, borderColor: '#CBD5E1' },
    fileDesc: { fontSize: 13, color: '#64748B' },
    mActions: { flexDirection: 'row', justifyContent: 'flex-end', gap: 12, marginTop: 10, marginBottom: 20 },
    cancelBtn: { paddingHorizontal: 20, paddingVertical: 14, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0' },
    cancelTxt: { fontSize: 14, fontWeight: '700', color: '#64748B' },
    saveBtn: { backgroundColor: '#4F46E5', paddingHorizontal: 20, paddingVertical: 14, borderRadius: 12 },
    saveTxt: { fontSize: 14, fontWeight: '700', color: '#fff' }
});
