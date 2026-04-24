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

    const getDocStyle = (title) => {
        const t = title.toLowerCase();
        if (t.includes('egzoz')) return { bg: '#F3E8FF', icon: '#A855F7', glow: 'rgba(168,85,247,0.2)' };
        if (t.includes('muayene')) return { bg: '#DBEAFE', icon: '#3B82F6', glow: 'rgba(59,130,246,0.2)' };
        if (t.includes('ruhsat')) return { bg: '#D1FAE5', icon: '#10B981', glow: 'rgba(16,185,129,0.2)' };
        if (t.includes('sigorta') || t.includes('kasko')) return { bg: '#FEF3C7', icon: '#F59E0B', glow: 'rgba(245,158,11,0.2)' };
        return { bg: '#F1F5F9', icon: '#64748B', glow: 'rgba(100,116,139,0.2)' };
    };

    const renderItem = ({ item }) => {
        const days = getDaysRemaining(item.end_date);
        const isExpired = item.is_expired;
        const style = getDocStyle(item.title);

        return (
            <View style={s.card}>
                <View style={s.cardTop}>
                    {/* Left Icon Box */}
                    <View style={[s.docIconBox, { backgroundColor: style.bg, shadowColor: style.icon }]}>
                        <LinearGradient colors={[style.bg, '#fff']} style={StyleSheet.absoluteFillObject} borderRadius={16} />
                        <Icon name="file-document-outline" size={30} color={style.icon} />
                        {item.title.toLowerCase().includes('muayene') && !item.title.toLowerCase().includes('egzoz') && (
                            <View style={{position: 'absolute', bottom: -5, right: -5, backgroundColor: '#fff', borderRadius: 10, padding: 2}}>
                                <Icon name="shield-check" size={16} color={style.icon} />
                            </View>
                        )}
                        {item.title.toLowerCase().includes('ruhsat') && (
                            <View style={{position: 'absolute', bottom: -5, right: -5, backgroundColor: '#fff', borderRadius: 10, padding: 2}}>
                                <Icon name="car-shield" size={16} color={style.icon} />
                            </View>
                        )}
                        {item.title.toLowerCase().includes('sigorta') && (
                            <View style={{position: 'absolute', bottom: -5, right: -5, backgroundColor: '#fff', borderRadius: 10, padding: 2}}>
                                <Icon name="umbrella" size={16} color={style.icon} />
                            </View>
                        )}
                    </View>

                    {/* Middle Info */}
                    <View style={s.cardInfo}>
                        <View style={s.titleRow}>
                            <Text style={s.docTitle}>{item.title}</Text>
                            <Icon name="check-decagram-outline" size={16} color="#94A3B8" />
                        </View>
                        <View style={[s.typeBadge, { backgroundColor: style.bg }]}>
                            <Text style={[s.typeText, { color: style.icon }]}>{item.type || 'RESMİ RAPOR'}</Text>
                        </View>

                        <View style={s.datesRow}>
                            <View style={s.dateCol}>
                                <View style={s.dateLabelRow}><Icon name="calendar-outline" size={12} color="#94A3B8" /><Text style={s.dateLabel}>Başlangıç</Text></View>
                                <Text style={s.dateValue}>{item.start_date ? new Date(item.start_date).toLocaleDateString('tr-TR') : '-'}</Text>
                            </View>
                            <View style={s.dateCol}>
                                <View style={s.dateLabelRow}><Icon name="calendar-outline" size={12} color="#94A3B8" /><Text style={s.dateLabel}>Bitiş</Text></View>
                                <Text style={s.dateValue}>{item.end_date ? new Date(item.end_date).toLocaleDateString('tr-TR') : 'Süresiz'}</Text>
                            </View>
                            
                            <View style={s.remainingWrap}>
                                <View style={[s.remainingBadge, isExpired ? s.expiredBadge : (days !== null ? s.activeBadge : s.permanentBadge)]}>
                                    <Text style={[s.remainingText, isExpired ? s.expiredText : (days !== null ? s.activeText : s.permanentText)]}>
                                        {days === null ? 'Süresiz' : (isExpired ? `${Math.abs(days)} Gün Geçti` : `${days} Gün Kaldı`)}
                                    </Text>
                                </View>
                            </View>
                        </View>
                    </View>

                    {/* Options Dot */}
                    <TouchableOpacity style={s.optionsBtn}><Icon name="dots-horizontal" size={24} color="#94A3B8" /></TouchableOpacity>
                </View>

                <View style={s.cardDivider} />

                {/* Actions Bottom */}
                <View style={s.cardActions}>
                    <TouchableOpacity onPress={() => Linking.openURL(item.file_url)} style={s.actionBtn}>
                        <Icon name="eye-outline" size={18} color="#94A3B8" />
                        <Text style={s.actionTxt}>Görüntüle</Text>
                    </TouchableOpacity>
                    <View style={s.actionDivider} />
                    <TouchableOpacity onPress={() => handleShare(item.file_url)} style={s.actionBtn}>
                        <Icon name="share-variant-outline" size={18} color="#94A3B8" />
                        <Text style={s.actionTxt}>Paylaş</Text>
                    </TouchableOpacity>
                    <View style={s.actionDivider} />
                    <TouchableOpacity style={s.actionBtn}>
                        <Icon name="download-outline" size={18} color="#94A3B8" />
                        <Text style={s.actionTxt}>İndir</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    };

    return (
        <View style={s.container}>
            <LinearGradient colors={['#020617', '#0B1120', '#0F172A']} style={s.header} start={{x: 0, y: 0}} end={{x: 1, y: 1}}>
                <SafeAreaView edges={['top']}>
                    <View style={s.headerRow}>
                        <TouchableOpacity onPress={() => navigation.goBack()} style={s.backBtn}>
                            <Icon name="arrow-left" size={20} color="#fff" />
                        </TouchableOpacity>
                        <View style={s.headerTitleWrap}>
                            <Text style={s.headerTitle}>{plate} · Belgeler</Text>
                            <View style={s.headerSubWrap}>
                                <View style={s.statusDotSmall} />
                                <Text style={s.headerSubTxt}>Aktif • OTOBÜS SULTAN - 2014 MODEL</Text>
                            </View>
                        </View>
                        <TouchableOpacity style={s.topAddBtn} onPress={() => setModalVisible(true)}>
                            <Icon name="plus" size={22} color="#fff" />
                        </TouchableOpacity>
                    </View>

                    {/* KPI Cards inside header */}
                    <View style={s.kpiRow}>
                        <View style={[s.kpiCard, { borderColor: 'rgba(56, 189, 248, 0.4)' }]}>
                            <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                            <View style={s.kpiCardInner}>
                                <View style={[s.kpiIconWrap, { shadowColor: '#38BDF8', elevation: 8, shadowOffset: {width:0, height:4}, shadowOpacity: 0.5, shadowRadius: 10 }]}>
                                    <LinearGradient colors={['#1E3A8A', '#1E40AF']} style={StyleSheet.absoluteFillObject} borderRadius={16} />
                                    <Icon name="file-document-outline" size={24} color="#fff" />
                                </View>
                                <View>
                                    <Text style={s.kpiLabel}>Aktif Belge</Text>
                                    <Text style={s.kpiVal}>{activeDocs.length}</Text>
                                </View>
                            </View>
                            <View style={s.kpiStatusRow}>
                                <Icon name="check-circle" size={12} color="#10B981" />
                                <Text style={s.kpiStatusTxt}>Tümü güncel durumda</Text>
                            </View>
                        </View>

                        <View style={[s.kpiCard, { borderColor: 'rgba(251, 146, 60, 0.4)' }]}>
                            <LinearGradient colors={['rgba(255,255,255,0.05)', 'rgba(255,255,255,0)']} style={StyleSheet.absoluteFillObject} borderRadius={20} />
                            <View style={s.kpiCardInner}>
                                <View style={[s.kpiIconWrap, { shadowColor: '#FB923C', elevation: 8, shadowOffset: {width:0, height:4}, shadowOpacity: 0.5, shadowRadius: 10 }]}>
                                    <LinearGradient colors={['#9A3412', '#C2410C']} style={StyleSheet.absoluteFillObject} borderRadius={16} />
                                    <Icon name="hourglass" size={24} color="#fff" />
                                </View>
                                <View>
                                    <Text style={s.kpiLabel}>Yaklaşan Süre</Text>
                                    <Text style={s.kpiVal}>27 <Text style={{fontSize: 14}}>Gün</Text></Text>
                                </View>
                            </View>
                            <View style={s.kpiStatusRow}>
                                <Icon name="circle" size={10} color="#F59E0B" />
                                <Text style={s.kpiStatusTxt}>Muayene Raporu</Text>
                            </View>
                        </View>
                    </View>
                </SafeAreaView>
            </LinearGradient>

            {/* Floating Action Bar */}
            <View style={s.actionBarWrap}>
                <View style={s.actionBar}>
                    <TouchableOpacity style={s.actionPillBtn} onPress={() => setViewArchived(!viewArchived)}>
                        <Icon name="folder-outline" size={18} color="#64748B" />
                        <Text style={s.actionPillTxt}>Arşiv</Text>
                    </TouchableOpacity>
                    <View style={s.actionPillDivider} />
                    <TouchableOpacity style={s.actionPillBtn}>
                        <Icon name="download-outline" size={18} color="#64748B" />
                        <Text style={s.actionPillTxt}>Toplu İndir</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={s.newDocBtn} onPress={() => setModalVisible(true)}>
                        <Icon name="plus" size={18} color="#fff" />
                        <Text style={s.newDocTxt}>Yeni Belge</Text>
                    </TouchableOpacity>
                </View>
            </View>

            {loading ? <ActivityIndicator style={{marginTop:40}} color="#3B82F6" size="large" /> : (
                <FlatList
                    data={displayDocs}
                    renderItem={renderItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={s.list}
                    ListEmptyComponent={
                        <View style={s.empty}>
                            <View style={s.emptyIconWrap}>
                                <Icon name="folder-open-outline" size={56} color="#CBD5E1" />
                            </View>
                            <Text style={s.emptyTxt}>{viewArchived ? 'Arşivlenmiş belge bulunamadı.' : 'Aktif belge bulunamadı.'}</Text>
                        </View>
                    }
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
    container: { flex: 1, backgroundColor: '#F4F7FA' },
    header: { width: '100%', shadowColor: '#020617', shadowOffset: {width:0, height:16}, shadowOpacity: 0.3, shadowRadius: 30, elevation: 15, zIndex: 10, borderBottomLeftRadius: 40, borderBottomRightRadius: 40, overflow: 'hidden' },
    headerRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: 24, paddingTop: 10, marginBottom: 20 },
    backBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(255,255,255,0.08)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: 'rgba(255,255,255,0.15)', shadowColor: '#fff', shadowOffset: {width:0, height:4}, shadowOpacity: 0.1, shadowRadius: 10 },
    headerTitleWrap: { alignItems: 'center' },
    headerTitle: { color: '#fff', fontSize: 18, fontWeight: '800', letterSpacing: 0.5 },
    headerSubWrap: { flexDirection: 'row', alignItems: 'center', marginTop: 4, gap: 4 },
    statusDotSmall: { width: 6, height: 6, borderRadius: 3, backgroundColor: '#10B981' },
    headerSubTxt: { fontSize: 11, color: '#94A3B8', fontWeight: '600' },
    topAddBtn: { width: 44, height: 44, borderRadius: 16, backgroundColor: 'rgba(59, 130, 246, 0.4)', alignItems: 'center', justifyContent: 'center', borderWidth: 1, borderColor: '#3B82F6', shadowColor: '#3B82F6', shadowOffset: {width:0, height:4}, shadowOpacity: 0.4, shadowRadius: 10, elevation: 8 },

    kpiRow: { flexDirection: 'row', gap: 16, paddingHorizontal: 24, paddingBottom: 60 },
    kpiCard: { flex: 1, backgroundColor: 'rgba(30,41,59,0.4)', borderRadius: 24, padding: 16, borderWidth: 1 },
    kpiCardInner: { flexDirection: 'row', alignItems: 'center', gap: 12, marginBottom: 12 },
    kpiIconWrap: { width: 44, height: 44, borderRadius: 16, alignItems: 'center', justifyContent: 'center' },
    kpiLabel: { fontSize: 11, fontWeight: '600', color: '#CBD5E1', marginBottom: 2 },
    kpiVal: { fontSize: 24, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    kpiStatusRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    kpiStatusTxt: { fontSize: 10, fontWeight: '600', color: '#CBD5E1' },

    actionBarWrap: { marginTop: -35, zIndex: 20, paddingHorizontal: 24, marginBottom: 16 },
    actionBar: { backgroundColor: '#fff', borderRadius: 24, paddingHorizontal: 8, paddingVertical: 8, flexDirection: 'row', alignItems: 'center', shadowColor: '#0A1A3A', shadowOffset: {width:0, height:12}, shadowOpacity: 0.08, shadowRadius: 24, elevation: 8, borderWidth: 1, borderColor: '#F1F5F9' },
    actionPillBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, paddingVertical: 12 },
    actionPillTxt: { fontSize: 13, fontWeight: '700', color: '#334155' },
    actionPillDivider: { width: 1, height: 24, backgroundColor: '#E2E8F0' },
    newDocBtn: { backgroundColor: '#3B82F6', flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 16, paddingVertical: 12, borderRadius: 16, shadowColor: '#3B82F6', shadowOffset: {width:0, height:4}, shadowOpacity: 0.3, shadowRadius: 8, elevation: 4 },
    newDocTxt: { color: '#fff', fontSize: 13, fontWeight: '800' },

    list: { paddingHorizontal: 20, paddingBottom: 100 },
    card: { backgroundColor: '#fff', borderRadius: 24, padding: 16, marginBottom: 16, shadowColor: '#0A1A3A', shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.06, shadowRadius: 20, elevation: 4, borderWidth: 1, borderColor: '#F1F5F9' },
    cardTop: { flexDirection: 'row' },
    docIconBox: { width: 64, height: 64, borderRadius: 20, alignItems: 'center', justifyContent: 'center', marginRight: 16, shadowOffset: {width:0,height:6}, shadowOpacity: 0.3, shadowRadius: 10, elevation: 6 },
    cardInfo: { flex: 1, justifyContent: 'center' },
    titleRow: { flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 4 },
    docTitle: { fontSize: 16, fontWeight: '800', color: '#0F172A', letterSpacing: -0.5 },
    typeBadge: { alignSelf: 'flex-start', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 10, marginBottom: 10 },
    typeText: { fontSize: 9, fontWeight: '900', textTransform: 'uppercase', letterSpacing: 0.5 },
    
    datesRow: { flexDirection: 'row', alignItems: 'flex-end', justifyContent: 'space-between' },
    dateCol: { gap: 4 },
    dateLabelRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
    dateLabel: { fontSize: 10, fontWeight: '600', color: '#94A3B8' },
    dateValue: { fontSize: 11, fontWeight: '800', color: '#334155' },
    remainingWrap: { alignItems: 'flex-end', paddingBottom: 2 },
    remainingBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 10 },
    remainingText: { fontSize: 10, fontWeight: '800' },
    activeBadge: { backgroundColor: '#ECFDF5' },
    activeText: { color: '#10B981' },
    expiredBadge: { backgroundColor: '#FEF2F2' },
    expiredText: { color: '#EF4444' },
    permanentBadge: { backgroundColor: '#F8FAFC' },
    permanentText: { color: '#94A3B8' },

    optionsBtn: { padding: 4 },

    cardDivider: { height: 1, backgroundColor: '#F1F5F9', marginVertical: 16 },
    cardActions: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 10 },
    actionBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8 },
    actionTxt: { fontSize: 13, fontWeight: '700', color: '#64748B' },
    actionDivider: { width: 1, height: 16, backgroundColor: '#E2E8F0' },

    empty: { alignItems: 'center', marginTop: 80 },
    emptyIconWrap: { width: 120, height: 120, borderRadius: 60, backgroundColor: '#fff', alignItems: 'center', justifyContent: 'center', marginBottom: 20, shadowColor: '#000', shadowOffset: {width:0, height:8}, shadowOpacity: 0.05, shadowRadius: 15, elevation: 4 },
    emptyTxt: { color: '#64748B', fontSize: 16, fontWeight: '600' },
    
    mOverlay: { flex: 1, backgroundColor: 'rgba(2,6,23,0.6)', justifyContent: 'flex-end' },
    mContent: { backgroundColor: '#fff', borderTopLeftRadius: 36, borderTopRightRadius: 36, padding: 24, maxHeight: '90%' },
    mHead: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 28 },
    mTitle: { fontSize: 24, fontWeight: '900', color: '#0F172A', letterSpacing: -0.5 },
    mDesc: { fontSize: 13, color: '#64748B', fontWeight: '600', marginTop: 4 },
    closeBtn: { width: 44, height: 44, borderRadius: 22, backgroundColor: '#F1F5F9', alignItems: 'center', justifyContent: 'center' },
    ig: { marginBottom: 20 },
    il: { fontSize: 13, fontWeight: '800', color: '#475569', marginBottom: 10, textTransform: 'uppercase', letterSpacing: 0.5 },
    inp: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 18, fontSize: 15, color: '#0F172A', fontWeight: '600' },
    fileBtn: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, borderColor: '#E2E8F0', borderRadius: 16, padding: 14, gap: 12, backgroundColor: '#F8FAFC' },
    fileBtnTxt: { backgroundColor: '#fff', paddingHorizontal: 14, paddingVertical: 8, borderRadius: 8, fontSize: 13, fontWeight: '700', color: '#334155', borderWidth: 1, borderColor: '#E2E8F0', shadowColor: '#0A1A3A', shadowOffset: {width:0, height:2}, shadowOpacity: 0.05, shadowRadius: 4, elevation: 1 },
    fileDesc: { fontSize: 14, color: '#64748B', fontWeight: '500' },
    mActions: { flexDirection: 'row', justifyContent: 'flex-end', gap: 12, marginTop: 16, marginBottom: 30 },
    cancelBtn: { paddingHorizontal: 24, paddingVertical: 18, borderRadius: 16, borderWidth: 1, borderColor: '#E2E8F0', backgroundColor: '#fff' },
    cancelTxt: { fontSize: 16, fontWeight: '800', color: '#64748B' },
    saveBtn: { backgroundColor: '#3B82F6', paddingHorizontal: 24, paddingVertical: 18, borderRadius: 16, shadowColor: '#3B82F6', shadowOffset: {width:0, height:6}, shadowOpacity: 0.3, shadowRadius: 10, elevation: 4 },
    saveTxt: { fontSize: 16, fontWeight: '800', color: '#fff' }
});
