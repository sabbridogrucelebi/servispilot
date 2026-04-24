import React, { useContext, useState, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, Dimensions } from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import { AuthContext } from '../context/AuthContext';
import api from '../api/axios';

const { width } = Dimensions.get('window');

export default function HomeScreen({ navigation }) {
    const { userInfo } = useContext(AuthContext);
    const [stats, setStats] = useState({ vehicles: 0, personnel: 0, customers: 0, trips: 0 });

    useFocusEffect(
        useCallback(() => {
            const fetchStats = async () => {
                try {
                    const [vRes, pRes, cRes, tRes] = await Promise.all([
                        api.get('/vehicles'),
                        api.get('/personnel'),
                        api.get('/customers'),
                        api.get('/trips'),
                    ]);
                    const vData = Array.isArray(vRes.data) ? vRes.data : (vRes.data.vehicles || []);
                    setStats({
                        vehicles: vData.length,
                        personnel: pRes.data.length,
                        customers: cRes.data.length,
                        trips: tRes.data.length,
                    });
                } catch (e) {
                    console.log('Stats fetch error:', e);
                }
            };
            fetchStats();
        }, [])
    );

    const firstName = userInfo?.name?.split(' ')[0] || 'Kullanıcı';

    return (
        <View style={styles.container}>
            <ScrollView showsVerticalScrollIndicator={false} contentContainerStyle={{ paddingBottom: 100 }}>
                
                {/* ─── Uzay Derinliği Mavisi Gradient Header ─── */}
                <LinearGradient
                    colors={['#0F172A', '#1E3A8A', '#3B82F6']}
                    style={styles.header}
                >
                <SpaceWaves />
                    <SafeAreaView>
                        <View style={styles.headerRow}>
                            <View style={styles.headerLeft}>
                                <View style={styles.avatar}>
                                    <Text style={styles.avatarText}>{firstName.charAt(0)}</Text>
                                </View>
                                <View>
                                    <Text style={styles.headerLabel}>Filom</Text>
                                    <Text style={styles.headerSubLabel}>{userInfo?.name || 'Yönetici'}</Text>
                                </View>
                            </View>
                            <View style={styles.monthSelector}>
                                <Icon name="chevron-left" size={18} color="#ffffff" />
                                <Text style={styles.monthText}>Nis 2026</Text>
                                <Icon name="chevron-right" size={18} color="#ffffff" />
                            </View>
                        </View>

                        {/* Big Number */}
                        <Text style={styles.bigNumber}>{stats.vehicles}</Text>
                        <Text style={styles.bigLabel}>Kayıtlı araç</Text>
                    </SafeAreaView>
                </LinearGradient>

                {/* ─── Horizontal Scroll Cards ─── */}
                <ScrollView 
                    horizontal 
                    showsHorizontalScrollIndicator={false} 
                    style={styles.cardsScroll}
                    contentContainerStyle={{ paddingLeft: 20, paddingRight: 8 }}
                >
                    <TouchableOpacity style={[styles.statCard]} onPress={() => navigation.navigate('Vehicles')}>
                        <View style={styles.statIconRow}>
                            <Icon name="car-multiple" size={32} color="#10B981" />
                            <Icon name="arrow-top-right" size={20} color="#CBD5E1" />
                        </View>
                        <Text style={styles.statCardValue}>{stats.vehicles}</Text>
                        <Text style={styles.statCardLabel}>Araçlar</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={[styles.statCard]} onPress={() => navigation.navigate('Personnel')}>
                        <View style={styles.statIconRow}>
                            <Icon name="account-group" size={32} color="#6366F1" />
                            <Icon name="arrow-top-right" size={20} color="#CBD5E1" />
                        </View>
                        <Text style={styles.statCardValue}>{stats.personnel}</Text>
                        <Text style={styles.statCardLabel}>Personeller</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={[styles.statCard]} onPress={() => navigation.navigate('Customers')}>
                        <View style={styles.statIconRow}>
                            <Icon name="office-building" size={32} color="#F59E0B" />
                            <Icon name="arrow-top-right" size={20} color="#CBD5E1" />
                        </View>
                        <Text style={styles.statCardValue}>{stats.customers}</Text>
                        <Text style={styles.statCardLabel}>Müşteriler</Text>
                    </TouchableOpacity>
                </ScrollView>

                {/* ─── Budget Overview Section ─── */}
                <View style={styles.section}>
                    <View style={styles.overviewCard}>
                        <View style={styles.overviewHeader}>
                            <Text style={styles.overviewTitle}>Filo Özeti</Text>
                            <TouchableOpacity>
                                <Icon name="chevron-right" size={24} color="#94A3B8" />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.overviewRow}>
                            <View style={styles.overviewItem}>
                                <Text style={styles.overviewLabel}>Toplam</Text>
                                <Text style={styles.overviewValue}>{stats.vehicles}</Text>
                            </View>
                            <View style={styles.overviewItem}>
                                <Text style={styles.overviewLabel}>Personel</Text>
                                <Text style={styles.overviewValue}>{stats.personnel}</Text>
                            </View>
                            <View style={styles.overviewItem}>
                                <Text style={styles.overviewLabel}>Sefer</Text>
                                <Text style={[styles.overviewValue, { color: '#3B82F6' }]}>{stats.trips}</Text>
                            </View>
                        </View>

                        {/* Progress Bar */}
                        <View style={styles.progressContainer}>
                            <View style={styles.progressBg}>
                                <LinearGradient
                                    colors={['#1E3A8A', '#3B82F6']}
                                    start={{x:0,y:0}} end={{x:1,y:0}}
                                    style={[styles.progressFill, { width: '72%' }]}
                                />
                <SpaceWaves />
                            </View>
                        </View>
                        <View style={styles.progressInfo}>
                            <Icon name="check-decagram" size={18} color="#10B981" />
                            <Text style={styles.progressText}>Filo durumu iyi görünüyor!</Text>
                        </View>
                    </View>
                </View>

                {/* ─── Son İşlemler ─── */}
                <View style={styles.section}>
                    <View style={styles.sectionHeader}>
                        <Text style={styles.sectionTitle}>Son İşlemler</Text>
                        <TouchableOpacity onPress={() => navigation.navigate('Trips')}>
                            <Text style={styles.seeAll}>Tümünü gör &gt;</Text>
                        </TouchableOpacity>
                    </View>

                    <View style={styles.transactionCard}>
                        <TransactionItem 
                            icon="car-arrow-right" 
                            iconColor="#10B981"
                            title="Yeni Araç Eklendi"
                            subtitle="Filo Yönetimi"
                            value="+1"
                            valueColor="#10B981"
                        />
                        <View style={styles.divider} />
                        <TransactionItem 
                            icon="account-plus-outline"
                            iconColor="#6366F1"
                            title="Personel Kaydı"
                            subtitle="İnsan Kaynakları"
                            value="+2"
                            valueColor="#10B981"
                        />
                        <View style={styles.divider} />
                        <TransactionItem 
                            icon="map-marker-check-outline"
                            iconColor="#06B6D4"
                            title="Sefer Tamamlandı"
                            subtitle="Operasyon"
                            value="✓"
                            valueColor="#10B981"
                        />
                        <View style={styles.divider} />
                        <TransactionItem 
                            icon="gas-station-outline"
                            iconColor="#F97316"
                            title="Yakıt Alımı"
                            subtitle="Gider Yönetimi"
                            value="-₺850"
                            valueColor="#EF4444"
                        />
                    </View>
                </View>

            </ScrollView>
        </View>
    );
}

function TransactionItem({ icon, iconColor, title, subtitle, value, valueColor }) {
    return (
        <View style={styles.transItem}>
            <View style={styles.transIconBox}>
                <Icon name={icon} size={28} color={iconColor} />
            </View>
            <View style={styles.transInfo}>
                <Text style={styles.transTitle}>{title}</Text>
                <Text style={styles.transSubtitle}>{subtitle}</Text>
            </View>
            <Text style={[styles.transValue, { color: valueColor }]}>{value}</Text>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8FAFC',
    },
    /* Header */
    header: {
        paddingBottom: 32,
        paddingHorizontal: 24,
        borderBottomLeftRadius: 32,
        borderBottomRightRadius: 32,
    },
    headerRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingTop: 12,
    },
    headerLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 12,
    },
    avatar: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: 'rgba(255,255,255,0.2)',
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1.5,
        borderColor: 'rgba(255,255,255,0.4)',
    },
    avatarText: {
        color: '#ffffff',
        fontSize: 18,
        fontWeight: '800',
    },
    headerLabel: {
        color: '#ffffff',
        fontSize: 18,
        fontWeight: '800',
    },
    headerSubLabel: {
        color: 'rgba(255,255,255,0.7)',
        fontSize: 12,
        fontWeight: '500',
    },
    monthSelector: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.15)',
        paddingHorizontal: 14,
        paddingVertical: 8,
        borderRadius: 20,
        gap: 8,
    },
    monthText: {
        color: '#ffffff',
        fontSize: 13,
        fontWeight: '700',
    },
    bigNumber: {
        fontSize: 56,
        fontWeight: '900',
        color: '#ffffff',
        marginTop: 20,
        letterSpacing: -2,
    },
    bigLabel: {
        fontSize: 15,
        color: 'rgba(255,255,255,0.8)',
        fontWeight: '500',
        marginTop: -4,
    },
    /* Horizontal Cards */
    cardsScroll: {
        marginTop: -20,
    },
    statCard: {
        width: 140,
        borderRadius: 24,
        padding: 20,
        marginRight: 12,
        backgroundColor: '#ffffff',
        shadowColor: '#0F172A',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 12,
        elevation: 3,
    },
    statIconRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 16,
    },
    statCardValue: {
        fontSize: 28,
        fontWeight: '900',
        color: '#0F172A',
    },
    statCardLabel: {
        fontSize: 13,
        color: '#64748B',
        fontWeight: '600',
        marginTop: 4,
    },
    /* Sections */
    section: {
        paddingHorizontal: 20,
        marginTop: 24,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 16,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: '900',
        color: '#0F172A',
    },
    seeAll: {
        fontSize: 14,
        fontWeight: '700',
        color: '#3B82F6',
    },
    /* Overview Card */
    overviewCard: {
        backgroundColor: '#ffffff',
        borderRadius: 24,
        padding: 24,
        shadowColor: '#0F172A',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.05,
        shadowRadius: 16,
        elevation: 4,
    },
    overviewHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 24,
    },
    overviewTitle: {
        fontSize: 18,
        fontWeight: '900',
        color: '#0F172A',
    },
    overviewRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        marginBottom: 24,
    },
    overviewItem: {
        alignItems: 'center',
        flex: 1,
    },
    overviewLabel: {
        fontSize: 13,
        color: '#64748B',
        fontWeight: '600',
        marginBottom: 8,
    },
    overviewValue: {
        fontSize: 24,
        fontWeight: '900',
        color: '#0F172A',
    },
    progressContainer: {
        marginBottom: 12,
    },
    progressBg: {
        height: 10,
        backgroundColor: '#F1F5F9',
        borderRadius: 5,
        overflow: 'hidden',
    },
    progressFill: {
        height: 10,
        borderRadius: 5,
    },
    progressInfo: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
    },
    progressText: {
        fontSize: 13,
        color: '#10B981',
        fontWeight: '700',
    },
    /* Transactions */
    transactionCard: {
        backgroundColor: '#ffffff',
        borderRadius: 24,
        padding: 8,
        shadowColor: '#0F172A',
        shadowOffset: { width: 0, height: 6 },
        shadowOpacity: 0.05,
        shadowRadius: 16,
        elevation: 4,
    },
    transItem: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 16,
    },
    transIconBox: {
        width: 46,
        height: 46,
        alignItems: 'center',
        justifyContent: 'center',
        marginRight: 16,
    },
    transInfo: {
        flex: 1,
    },
    transTitle: {
        fontSize: 16,
        fontWeight: '800',
        color: '#0F172A',
    },
    transSubtitle: {
        fontSize: 13,
        color: '#64748B',
        fontWeight: '500',
        marginTop: 4,
    },
    transValue: {
        fontSize: 16,
        fontWeight: '900',
    },
    divider: {
        height: 1,
        backgroundColor: '#F1F5F9',
        marginHorizontal: 16,
    },
});
