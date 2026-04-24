import React, { useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, TextInput } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

const demoChats = [
    { id: 1, name: 'Genel Grup', lastMsg: 'Yarınki sefer planı paylaşıldı', time: '09:41', unread: 3, isGroup: true, avatar: 'G', color: '#10B981' },
    { id: 2, name: 'Operasyon Ekibi', lastMsg: 'Araç bakıma alındı', time: '08:30', unread: 1, isGroup: true, avatar: 'O', color: '#3B82F6' },
    { id: 3, name: 'Ahmet Yılmaz', lastMsg: 'Tamam, anlaşıldı', time: 'Dün', unread: 0, isGroup: false, avatar: 'A', color: '#6366F1' },
    { id: 4, name: 'Mehmet Kaya', lastMsg: 'Yakıt aldım, fişi yüklüyorum', time: 'Dün', unread: 0, isGroup: false, avatar: 'M', color: '#F59E0B' },
];

export default function PilotChatScreen() {
    const [search, setSearch] = useState('');

    const renderChat = ({ item }) => (
        <TouchableOpacity style={s.chatItem} activeOpacity={0.85}>
            <View style={[s.avatar, { backgroundColor: item.color }]}>
                {item.isGroup ? <Icon name="account-group" size={24} color="#fff" /> : <Text style={s.avatarText}>{item.avatar}</Text>}
            </View>
            <View style={s.chatInfo}>
                <View style={s.chatTop}>
                    <Text style={s.chatName}>{item.name}</Text>
                    <Text style={[s.chatTime, item.unread > 0 && { color: '#3B82F6', fontWeight: '800' }]}>{item.time}</Text>
                </View>
                <View style={s.chatBottom}>
                    <Text style={[s.chatMsg, item.unread > 0 && { color: '#0F172A', fontWeight: '700' }]} numberOfLines={1}>{item.lastMsg}</Text>
                    {item.unread > 0 && (
                        <View style={s.badge}><Text style={s.badgeText}>{item.unread}</Text></View>
                    )}
                </View>
            </View>
        </TouchableOpacity>
    );

    return (
        <View style={s.container}>
            <LinearGradient colors={['#0F172A', '#1E3A8A', '#3B82F6']} style={s.header}>
                <SpaceWaves />
                <SafeAreaView>
                    <View style={s.headerRow}>
                        <Text style={s.headerTitle}>PilotChat</Text>
                        <TouchableOpacity style={s.newBtn}><Icon name="square-edit-outline" size={26} color="#fff" /></TouchableOpacity>
                    </View>
                    <View style={s.searchBox}>
                        <Icon name="magnify" size={24} color="#94A3B8" />
                        <TextInput style={s.searchInput} placeholder="Sohbet ara..." placeholderTextColor="#94A3B8" value={search} onChangeText={setSearch} />
                    </View>
                </SafeAreaView>
            </LinearGradient>

            <FlatList
                data={demoChats}
                keyExtractor={i => i.id.toString()}
                renderItem={renderChat}
                contentContainerStyle={s.list}
                showsVerticalScrollIndicator={false}
                ItemSeparatorComponent={() => <View style={s.divider} />}
                ListEmptyComponent={
                    <View style={s.empty}><Icon name="chat-processing-outline" size={64} color="#CBD5E1" /><Text style={s.emptyText}>Henüz sohbet yok</Text></View>
                }
            />
        </View>
    );
}

const s = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#F8FAFC' },
    header: { paddingBottom: 24, paddingHorizontal: 24, borderBottomLeftRadius: 32, borderBottomRightRadius: 32 },
    headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingTop: 16 },
    headerTitle: { fontSize: 32, fontWeight: '900', color: '#fff', letterSpacing: -0.5 },
    newBtn: { alignItems: 'center', justifyContent: 'center' },
    searchBox: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 16, paddingHorizontal: 16, paddingVertical: 14, marginTop: 24, gap: 12, shadowColor: '#000', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.1, shadowRadius: 12, elevation: 4 },
    searchInput: { flex: 1, fontSize: 16, color: '#0F172A', fontWeight: '500' },
    list: { padding: 20, paddingBottom: 120 },
    chatItem: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 24, padding: 20, shadowColor: '#0F172A', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.04, shadowRadius: 12, elevation: 2 },
    avatar: { width: 56, height: 56, borderRadius: 28, alignItems: 'center', justifyContent: 'center', marginRight: 16 },
    avatarText: { color: '#fff', fontSize: 22, fontWeight: '900' },
    chatInfo: { flex: 1 },
    chatTop: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 6 },
    chatName: { fontSize: 17, fontWeight: '800', color: '#0F172A' },
    chatTime: { fontSize: 13, color: '#94A3B8', fontWeight: '600' },
    chatBottom: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    chatMsg: { fontSize: 14, color: '#64748B', fontWeight: '500', flex: 1, marginRight: 12 },
    badge: { minWidth: 24, height: 24, borderRadius: 12, backgroundColor: '#3B82F6', alignItems: 'center', justifyContent: 'center', paddingHorizontal: 6 },
    badgeText: { color: '#fff', fontSize: 12, fontWeight: '900' },
    divider: { height: 12 },
    empty: { alignItems: 'center', paddingVertical: 48 },
    emptyText: { color: '#94A3B8', fontSize: 16, marginTop: 16, fontWeight: '600' },
});
