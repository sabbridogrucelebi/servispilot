import React, { useContext, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator, KeyboardAvoidingView, Platform, Alert } from 'react-native';
import { AuthContext } from '../context/AuthContext';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons as Icon } from '@expo/vector-icons';
import SpaceWaves from '../components/SpaceWaves';

export default function LoginScreen() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const { login, isLoading } = useContext(AuthContext);

    const handleLogin = async () => {
        if (!email || !password) {
            Alert.alert('Uyarı', 'Lütfen e-posta ve şifrenizi girin.');
            return;
        }
        try {
            await login(email, password);
        } catch (error) {
            Alert.alert('Giriş Başarısız', error.response?.data?.message || 'Bir hata oluştu.');
        }
    };

    return (
        <View style={styles.container}>
            {/* Arka plan uzay dalgaları animasyonu (Siyah Tema) */}
            <SpaceWaves />

            {/* Asıl Giriş Formu */}
            <View style={styles.formContainer}>
                <SafeAreaView style={{ flex: 1 }}>
                    <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={styles.keyboardView}>
                        
                        <View style={styles.headerContent}>
                            <View style={styles.logoCircle}>
                                <Icon name="domain" size={36} color="#000000" />
                            </View>
                            <Text style={styles.brandName}>IRMAK TURİZM</Text>
                            <Text style={styles.brandSubtitle}>Kurumsal Filo Yönetimi</Text>
                        </View>

                        <View style={styles.formCard}>
                            <Text style={styles.welcomeTitle}>Sisteme Giriş</Text>
                            <Text style={styles.welcomeSubtitle}>Yetkili hesabınızla devam edin</Text>

                            <View style={styles.inputContainer}>
                                <View style={styles.inputIconBox}>
                                    <Icon name="email-outline" size={22} color="#94A3B8" />
                                </View>
                                <TextInput 
                                    style={styles.input}
                                    placeholder="E-posta adresiniz"
                                    placeholderTextColor="#94A3B8"
                                    value={email}
                                    onChangeText={setEmail}
                                    autoCapitalize="none"
                                    keyboardType="email-address"
                                />
                            </View>

                            <View style={styles.inputContainer}>
                                <View style={styles.inputIconBox}>
                                    <Icon name="lock-outline" size={22} color="#94A3B8" />
                                </View>
                                <TextInput 
                                    style={styles.input}
                                    placeholder="Şifreniz"
                                    placeholderTextColor="#94A3B8"
                                    value={password}
                                    onChangeText={setPassword}
                                    secureTextEntry={!showPassword}
                                />
                                <TouchableOpacity onPress={() => setShowPassword(!showPassword)} style={styles.eyeBtn}>
                                    <Icon name={showPassword ? "eye-off-outline" : "eye-outline"} size={22} color="#94A3B8" />
                                </TouchableOpacity>
                            </View>

                            <TouchableOpacity style={styles.forgotBtn}>
                                <Text style={styles.forgotText}>Şifremi Unuttum</Text>
                            </TouchableOpacity>

                            <TouchableOpacity 
                                onPress={handleLogin}
                                disabled={isLoading}
                                activeOpacity={0.85}
                                style={styles.loginBtn}
                            >
                                {isLoading ? (
                                    <ActivityIndicator color="#ffffff" />
                                ) : (
                                    <Text style={styles.loginBtnText}>Güvenli Giriş</Text>
                                )}
                            </TouchableOpacity>
                        </View>
                    </KeyboardAvoidingView>
                </SafeAreaView>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#040B16', // Derin Uzay Mavisi
    },
    formContainer: {
        flex: 1,
        zIndex: 5,
    },
    keyboardView: {
        flex: 1,
        justifyContent: 'center',
        paddingHorizontal: 24,
    },
    headerContent: {
        alignItems: 'center',
        marginBottom: 40,
    },
    logoCircle: {
        width: 80,
        height: 80,
        borderRadius: 40,
        backgroundColor: '#ffffff',
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#ffffff',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.3,
        shadowRadius: 20,
        elevation: 6,
        marginBottom: 16,
    },
    brandName: {
        fontSize: 32,
        fontWeight: '900',
        color: '#ffffff',
        letterSpacing: 1,
    },
    brandSubtitle: {
        fontSize: 14,
        color: 'rgba(255,255,255,0.7)',
        marginTop: 4,
        fontWeight: '500',
        textTransform: 'uppercase',
        letterSpacing: 2,
    },
    formCard: {
        backgroundColor: 'rgba(255,255,255,0.05)',
        borderRadius: 28,
        padding: 28,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
        backdropFilter: 'blur(10px)',
    },
    welcomeTitle: {
        fontSize: 26,
        fontWeight: '900',
        color: '#ffffff',
        marginBottom: 4,
    },
    welcomeSubtitle: {
        fontSize: 15,
        color: '#94A3B8',
        fontWeight: '500',
        marginBottom: 32,
    },
    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(255,255,255,0.1)',
        borderRadius: 16,
        marginBottom: 16,
        paddingHorizontal: 16,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.2)',
    },
    inputIconBox: {
        marginRight: 12,
    },
    input: {
        flex: 1,
        paddingVertical: 18,
        fontSize: 16,
        color: '#ffffff',
        fontWeight: '600',
    },
    eyeBtn: {
        padding: 4,
    },
    forgotBtn: {
        alignSelf: 'flex-end',
        marginBottom: 24,
    },
    forgotText: {
        color: '#3B82F6',
        fontSize: 14,
        fontWeight: '700',
    },
    loginBtn: {
        backgroundColor: '#3B82F6',
        paddingVertical: 18,
        borderRadius: 16,
        alignItems: 'center',
        shadowColor: '#3B82F6',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 0.5,
        shadowRadius: 15,
        elevation: 6,
    },
    loginBtnText: {
        color: '#ffffff',
        fontSize: 17,
        fontWeight: '800',
        letterSpacing: 1,
    },
});
