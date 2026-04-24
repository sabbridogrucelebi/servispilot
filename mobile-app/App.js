import React, { useContext } from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { AuthProvider, AuthContext } from './src/context/AuthContext';
import { StatusBar } from 'expo-status-bar';
import { View, ActivityIndicator, Platform, StyleSheet, TouchableOpacity, Text } from 'react-native';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import GlobalSplashScreen from './src/components/GlobalSplashScreen';

import LoginScreen from './src/screens/LoginScreen';
import HomeScreen from './src/screens/HomeScreen';
import MenuScreen from './src/screens/MenuScreen';
import VehiclesScreen from './src/screens/VehiclesScreen';
import PersonnelScreen from './src/screens/PersonnelScreen';
import PersonnelDetailScreen from './src/screens/PersonnelDetailScreen';
import CustomersScreen from './src/screens/CustomersScreen';
import CustomerDetailScreen from './src/screens/CustomerDetailScreen';
import TripsScreen from './src/screens/TripsScreen';
import TripDetailScreen from './src/screens/TripDetailScreen';
import PilotChatScreen from './src/screens/PilotChatScreen';
import ProfileScreen from './src/screens/ProfileScreen';
import ActivityScreen from './src/screens/ActivityScreen';
import ReportsScreen from './src/screens/ReportsScreen';
import PayrollScreen from './src/screens/PayrollScreen';
import PayrollDetailScreen from './src/screens/PayrollDetailScreen';
import FinanceScreen from './src/screens/FinanceScreen';
import VehicleDetailScreen from './src/screens/VehicleDetailScreen';
import VehicleDocumentsScreen from './src/screens/VehicleDocumentsScreen';
import VehicleFuelsScreen from './src/screens/VehicleFuelsScreen';
import VehicleMaintenancesScreen from './src/screens/VehicleMaintenancesScreen';
import VehiclePenaltiesScreen from './src/screens/VehiclePenaltiesScreen';
import VehicleGalleryScreen from './src/screens/VehicleGalleryScreen';
import VehicleReportsScreen from './src/screens/VehicleReportsScreen';
import UpcomingInspectionsScreen from './src/screens/UpcomingInspectionsScreen';
import UpcomingInsurancesScreen from './src/screens/UpcomingInsurancesScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

// Tema Renkleri (Uzay Derinliği Mavisi)
const theme = {
    primary: '#2563EB', // Blue 600
    bg: '#F8FAFC', // Slate 50
    inactive: '#94A3B8', // Slate 400
    shadow: '#2563EB'
};

function CustomTabBar({ state, descriptors, navigation }) {
    const tabs = [
        { icon: 'menu', iconActive: 'menu', label: 'Menü' },
        { icon: 'car-outline', iconActive: 'car', label: 'Araçlar' },
        { icon: 'plus', label: '' },
        { icon: 'chat-outline', iconActive: 'chat', label: 'Mesaj' },
        { icon: 'account-outline', iconActive: 'account', label: 'Profil' },
    ];
    return (
        <View style={tabS.wrap}>
            <View style={tabS.bar}>
                {state.routes.map((route, i) => {
                    const focused = state.index === i;
                    const isCenter = i === 2;
                    const t = tabs[i];
                    const onPress = () => {
                        if (!focused) navigation.navigate(route.name);
                    };
                    if (isCenter) return (
                        <TouchableOpacity key={i} style={tabS.centerBtn} onPress={onPress} activeOpacity={0.85}>
                            <View style={tabS.centerInner}><Icon name="plus" size={28} color="#fff" /></View>
                        </TouchableOpacity>
                    );
                    return (
                        <TouchableOpacity key={i} style={tabS.tab} onPress={onPress} activeOpacity={0.7}>
                            <Icon name={focused ? t.iconActive : t.icon} size={26} color={focused ? theme.primary : theme.inactive} />
                            <Text style={[tabS.label, focused && tabS.labelActive]}>{t.label}</Text>
                        </TouchableOpacity>
                    );
                })}
            </View>
        </View>
    );
}

const tabS = StyleSheet.create({
    wrap: { position: 'absolute', bottom: 0, left: 0, right: 0, paddingBottom: Platform.OS === 'ios' ? 20 : 8, paddingHorizontal: 16, backgroundColor: 'transparent' },
    bar: { flexDirection: 'row', backgroundColor: '#ffffff', borderRadius: 28, paddingVertical: 12, paddingHorizontal: 8, alignItems: 'center', justifyContent: 'space-around', shadowColor: '#000', shadowOffset: { width: 0, height: 10 }, shadowOpacity: 0.1, shadowRadius: 20, elevation: 15 },
    tab: { alignItems: 'center', justifyContent: 'center', paddingVertical: 4, flex: 1 },
    label: { fontSize: 10, fontWeight: '600', color: theme.inactive, marginTop: 4 },
    labelActive: { color: theme.primary, fontWeight: '700' },
    centerBtn: { alignItems: 'center', justifyContent: 'center', marginTop: -30 },
    centerInner: { width: 56, height: 56, borderRadius: 28, backgroundColor: theme.primary, alignItems: 'center', justifyContent: 'center', shadowColor: theme.primary, shadowOffset: { width: 0, height: 8 }, shadowOpacity: 0.4, shadowRadius: 16, elevation: 10 },
});

function MainTabs() {
    return (
        <Tab.Navigator tabBar={p => <CustomTabBar {...p} />} screenOptions={{ headerShown: false }}>
            <Tab.Screen name="MenuTab" component={MenuScreen} />
            <Tab.Screen name="VehiclesTab" component={VehiclesScreen} />
            <Tab.Screen name="HomeTab" component={HomeScreen} />
            <Tab.Screen name="ChatTab" component={PilotChatScreen} />
            <Tab.Screen name="ProfileTab" component={ProfileScreen} />
        </Tab.Navigator>
    );
}

function AppNavigation() {
    const { userToken, isInitializing } = useContext(AuthContext);
    if (isInitializing) return <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: theme.bg }}><ActivityIndicator size="large" color={theme.primary} /></View>;
    return (
        <NavigationContainer>
            <Stack.Navigator screenOptions={{ headerShown: false }}>
                {userToken === null ? <Stack.Screen name="Login" component={LoginScreen} /> : (
                    <Stack.Group>
                        <Stack.Screen name="MainTabs" component={MainTabs} />
                        <Stack.Screen name="Vehicles" component={VehiclesScreen} />
                        <Stack.Screen name="VehicleDetail" component={VehicleDetailScreen} />
                        <Stack.Screen name="Personnel" component={PersonnelScreen} />
                        <Stack.Screen name="PersonnelDetail" component={PersonnelDetailScreen} />
                        <Stack.Screen name="Customers" component={CustomersScreen} />
                        <Stack.Screen name="CustomerDetail" component={CustomerDetailScreen} />
                        <Stack.Screen name="Trips" component={TripsScreen} />
                        <Stack.Screen name="TripDetail" component={TripDetailScreen} />
                        <Stack.Screen name="Reports" component={ReportsScreen} />
                        <Stack.Screen name="Payrolls" component={PayrollScreen} />
                        <Stack.Screen name="PayrollDetail" component={PayrollDetailScreen} />
                        <Stack.Screen name="Finance" component={FinanceScreen} />
                        <Stack.Screen name="Activity" component={ActivityScreen} />
                        <Stack.Screen name="VehicleDocuments" component={VehicleDocumentsScreen} />
                        <Stack.Screen name="VehicleFuels" component={VehicleFuelsScreen} />
                        <Stack.Screen name="VehicleMaintenances" component={VehicleMaintenancesScreen} />
                        <Stack.Screen name="VehiclePenalties" component={VehiclePenaltiesScreen} />
                        <Stack.Screen name="VehicleGallery" component={VehicleGalleryScreen} />
                        <Stack.Screen name="VehicleReports" component={VehicleReportsScreen} />
                        <Stack.Screen name="UpcomingInspections" component={UpcomingInspectionsScreen} />
                        <Stack.Screen name="UpcomingInsurances" component={UpcomingInsurancesScreen} />
                    </Stack.Group>
                )}
            </Stack.Navigator>
        </NavigationContainer>
    );
}

export default function App() {
    const [splashFinished, setSplashFinished] = React.useState(false);

    if (Platform.OS === 'web') {
        return (
            <View style={s.webWrap}>
                <View style={s.phone}>
                    <View style={s.notch} />
                    {!splashFinished && <GlobalSplashScreen onFinish={() => setSplashFinished(true)} />}
                    <AuthProvider><StatusBar style="light" /><AppNavigation /></AuthProvider>
                </View>
                <View style={{ marginTop: 24, alignItems: 'center' }}>
                    <div style={{ color: theme.primary, fontFamily: 'system-ui', fontWeight: '800', fontSize: '16px' }}>📱 ServisPilot Mobile</div>
                    <div style={{ color: '#94a3b8', fontSize: '13px', marginTop: '6px', fontFamily: 'system-ui' }}>iPhone 14 Pro Simülasyonu</div>
                </View>
            </View>
        );
    }
    return (
        <>
            {!splashFinished && <GlobalSplashScreen onFinish={() => setSplashFinished(true)} />}
            <AuthProvider><StatusBar style="light" /><AppNavigation /></AuthProvider>
        </>
    );
}

const s = StyleSheet.create({
    webWrap: { flex: 1, backgroundColor: '#E2E8F0', alignItems: 'center', justifyContent: 'center' },
    phone: { width: 393, height: 852, backgroundColor: theme.bg, borderRadius: 55, overflow: 'hidden', borderWidth: 10, borderColor: '#0F172A', position: 'relative', boxShadow: '0 25px 60px -12px rgba(15, 23, 42, 0.4)' },
    notch: { position: 'absolute', top: 0, left: '50%', marginLeft: -60, width: 120, height: 28, backgroundColor: '#0F172A', borderBottomLeftRadius: 20, borderBottomRightRadius: 20, zIndex: 100 },
});
