import React, { useContext } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ScrollView, Image } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import SpaceWaves from '../components/SpaceWaves';
import { AuthContext } from '../context/AuthContext';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';

export default function DashboardScreen({ navigation }) {
    const { userInfo, logout } = useContext(AuthContext);

    const modules = [
        { id: 1, title: 'Araçlar', icon: 'car', route: 'Vehicles' },
        { id: 2, title: 'Personeller', icon: 'account-group', route: 'Personnel' },
        { id: 3, title: 'Müşteriler', icon: 'office-building', route: 'Customers' },
        { id: 4, title: 'Seferler', icon: 'map-marker-path', route: 'Trips' },
    ];

    return (
        <SafeAreaView style={styles.container}>
            <LinearGradient
                colors={['#eef2ff', '#f8fafc', '#ffffff']}
                style={StyleSheet.absoluteFillObject}
            />
                <SpaceWaves />
            
            <ScrollView contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
                
                {/* Header */}
                <View style={styles.header}>
                    <View style={styles.headerLeft}>
                        <View style={styles.avatarBox}>
                            <Text style={styles.avatarText}>{userInfo?.name?.charAt(0) || 'U'}</Text>
                        </View>
                        <Text style={styles.greeting}>Hello, {userInfo?.name?.split(' ')[0] || 'User'}</Text>
                    </View>
                    <TouchableOpacity style={styles.bellBtn} onPress={logout}>
                        <Icon name="logout" size={24} color="#0f172a" />
                    </TouchableOpacity>
                </View>

                {/* Balance Section */}
                <View style={styles.balanceSection}>
                    <Text style={styles.balanceLabel}>Total active units</Text>
                    <Text style={styles.balanceValue}>125</Text>
                </View>

                {/* Main Card (Like the purple credit card) */}
                <View style={styles.sectionHeader}>
                    <Text style={styles.sectionTitle}>OVERVIEW</Text>
                    <TouchableOpacity><Text style={styles.addText}>Details +</Text></TouchableOpacity>
                </View>

                <LinearGradient
                    colors={['#8b5cf6', '#a855f7', '#d946ef']}
                    start={{x: 0, y: 0}} end={{x: 1, y: 1}}
                    style={styles.mainCard}
                >
                <SpaceWaves />
                    <Text style={styles.cardLabel}>Company License</Text>
                    <Text style={styles.cardValue}>Premium</Text>
                    
                    <View style={styles.cardFooter}>
                        <Text style={styles.cardFooterText}>** 2027</Text>
                        <Text style={styles.cardLogo}>ServisPilot</Text>
                    </View>
                </LinearGradient>

                {/* Quick Actions */}
                <View style={styles.actionRow}>
                    <View style={styles.actionItem}>
                        <TouchableOpacity style={styles.actionBtn}>
                            <Icon name="plus" size={24} color="#0f172a" />
                        </TouchableOpacity>
                        <Text style={styles.actionLabel}>Add new</Text>
                    </View>
                    <View style={styles.actionItem}>
                        <TouchableOpacity style={styles.actionBtn}>
                            <Icon name="pause" size={24} color="#0f172a" />
                        </TouchableOpacity>
                        <Text style={styles.actionLabel}>Freeze</Text>
                    </View>
                    <View style={styles.actionItem}>
                        <TouchableOpacity style={styles.actionBtn}>
                            <Icon name="cog" size={24} color="#0f172a" />
                        </TouchableOpacity>
                        <Text style={styles.actionLabel}>Settings</Text>
                    </View>
                </View>

                {/* Modules Grid (White soft cards) */}
                <View style={styles.sectionHeader}>
                    <Text style={styles.sectionTitle}>MODULES</Text>
                    <TouchableOpacity><Text style={styles.addText}>See all ></Text></TouchableOpacity>
                </View>

                <View style={styles.grid}>
                    {modules.map((mod) => (
                        <TouchableOpacity 
                            key={mod.id} 
                            style={styles.gridItem}
                            onPress={() => navigation.navigate(mod.route)}
                        >
                            <View style={styles.gridIconBox}>
                                <Icon name={mod.icon} size={28} color="#4f46e5" />
                            </View>
                            <Text style={styles.gridTitle}>{mod.title}</Text>
                        </TouchableOpacity>
                    ))}
                </View>

            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#ffffff',
    },
    scrollContent: {
        padding: 24,
        paddingBottom: 40,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 32,
    },
    headerLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 12,
    },
    avatarBox: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#8b5cf6',
        alignItems: 'center',
        justifyContent: 'center',
    },
    avatarText: {
        color: '#ffffff',
        fontSize: 18,
        fontWeight: 'bold',
    },
    greeting: {
        fontSize: 20,
        fontWeight: '700',
        color: '#0f172a',
    },
    bellBtn: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#ffffff',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.05,
        shadowRadius: 5,
        elevation: 2,
    },
    balanceSection: {
        marginBottom: 32,
    },
    balanceLabel: {
        fontSize: 14,
        color: '#64748b',
        fontWeight: '600',
        marginBottom: 8,
    },
    balanceValue: {
        fontSize: 48,
        fontWeight: '900',
        color: '#0f172a',
        letterSpacing: -1,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 16,
    },
    sectionTitle: {
        fontSize: 12,
        fontWeight: '800',
        color: '#94a3b8',
        letterSpacing: 1.5,
    },
    addText: {
        fontSize: 14,
        fontWeight: '600',
        color: '#4f46e5',
    },
    mainCard: {
        borderRadius: 28,
        padding: 24,
        height: 200,
        justifyContent: 'space-between',
        marginBottom: 24,
        shadowColor: '#8b5cf6',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.3,
        shadowRadius: 20,
        elevation: 10,
    },
    cardLabel: {
        color: 'rgba(255,255,255,0.8)',
        fontSize: 14,
        fontWeight: '500',
    },
    cardValue: {
        color: '#ffffff',
        fontSize: 32,
        fontWeight: '800',
        marginTop: 4,
    },
    cardFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-end',
    },
    cardFooterText: {
        color: 'rgba(255,255,255,0.8)',
        fontSize: 14,
        fontWeight: '600',
        letterSpacing: 2,
    },
    cardLogo: {
        color: '#ffffff',
        fontSize: 20,
        fontWeight: '900',
        fontStyle: 'italic',
    },
    actionRow: {
        flexDirection: 'row',
        justifyContent: 'space-around',
        marginBottom: 40,
    },
    actionItem: {
        alignItems: 'center',
    },
    actionBtn: {
        width: 60,
        height: 60,
        borderRadius: 20,
        backgroundColor: '#ffffff',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.05,
        shadowRadius: 10,
        elevation: 3,
        marginBottom: 8,
    },
    actionLabel: {
        fontSize: 13,
        fontWeight: '600',
        color: '#0f172a',
    },
    grid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: 16,
    },
    gridItem: {
        width: '47%',
        backgroundColor: '#ffffff',
        borderRadius: 24,
        padding: 20,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.03,
        shadowRadius: 10,
        elevation: 2,
    },
    gridIconBox: {
        width: 48,
        height: 48,
        borderRadius: 16,
        backgroundColor: '#eef2ff',
        alignItems: 'center',
        justifyContent: 'center',
        marginBottom: 16,
    },
    gridTitle: {
        fontSize: 15,
        fontWeight: '700',
        color: '#0f172a',
    }
});
