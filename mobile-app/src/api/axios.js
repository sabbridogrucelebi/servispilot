import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as SecureStore from 'expo-secure-store';
import { Platform, Alert, DeviceEventEmitter } from 'react-native';
import { CONFIG } from '../config';

const secureGetItem = async (key) => {
    if (Platform.OS === 'web') {
        return await AsyncStorage.getItem(key);
    } else {
        return await SecureStore.getItemAsync(key);
    }
};

// Dinamik API URL (config.js'den alınır)
const BASE_URL = Platform.OS === 'web' ? CONFIG.WEB_API_URL : CONFIG.API_BASE_URL;

const api = axios.create({
    baseURL: BASE_URL,
    timeout: 10000, // 10 seconds timeout
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json; charset=utf-8',
        'Bypass-Tunnel-Reminder': 'true',
        'ngrok-skip-browser-warning': 'true'
    }
});

console.log("Axios initialized with BASE_URL:", BASE_URL);

// Her istekten önce token'ı ekle
api.interceptors.request.use(
    async (config) => {
        const token = await secureGetItem('userToken');
        if (token) {
            console.log(`[API REQUEST] Token found in SecureStore. Length: ${token.length}`);
            config.headers.Authorization = `Bearer ${token}`;
        } else {
            console.log(`[API REQUEST] WARNING: No token found in SecureStore!`);
        }
        console.log(`[API REQUEST] ${config.method.toUpperCase()} ${config.url}`);
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Yanıtları (response) ve hataları merkezi olarak yönet
api.interceptors.response.use(
    (response) => {
        // İstek başarılıysa doğrudan response'u dön
        console.log(`[API RESPONSE] ${response.config.url} - Status: ${response.status}`);
        return response;
    },
    async (error) => {
        // Network Error veya Timeout (Sunucuya ulaşılamıyor)
        if (!error.response) {
            console.error('[API NETWORK ERROR]', error.message);
            Alert.alert(
                "Bağlantı Hatası", 
                "Sunucuya ulaşılamıyor, lütfen ağ bağlantınızı kontrol edin."
            );
            return Promise.reject(error);
        }

        const status = error.response.status;
        const url = error.config.url;
        
        console.log(`[API ERROR] ${url} - Status: ${status}`);

        // 401 Unauthorized (Token geçersiz veya süresi dolmuş)
        if (status === 401) {
            if (Platform.OS === 'web') {
                await AsyncStorage.removeItem('userToken');
            } else {
                await SecureStore.deleteItemAsync('userToken');
            }
            // İleride AuthContext üzerinden logout tetiklenmesi gerekir
        }

        // 422 Validation Error (Form doğrulama hatası)
        else if (status === 422) {
            console.log('[API VALIDATION ERROR]', error.response.data.errors || error.response.data.message);
        }

        // 403 Forbidden (Yetkisiz İşlem veya Askıya Alınmış Firma)
        else if (status === 403) {
            if (error.response.data?.message === 'company_suspended') {
                if (Platform.OS === 'web') {
                    await AsyncStorage.removeItem('userToken');
                } else {
                    await SecureStore.deleteItemAsync('userToken');
                }
                Alert.alert(
                    "Erişim Engellendi",
                    "Firmanızın lisansı askıya alınmıştır. Lütfen platform yöneticisi ile iletişime geçin.",
                    [{ text: "Tamam" }]
                );
                DeviceEventEmitter.emit('logout');
            } else {
                console.error(`[API HTTP ERROR 403]`, error.response.data);
            }
        }

        // 500 Internal Server Error (Sunucu Hatası)
        else if (status >= 500) {
            console.error('[API 500 SERVER ERROR DETAILS]', {
                url: url,
                message: error.response.data?.message || 'No message provided',
                exception: error.response.data?.exception || 'Unknown exception',
                file: error.response.data?.file || 'N/A',
                line: error.response.data?.line || 'N/A'
            });
            
            // Kullanıcıya detay vermek (Geliştirme aşamasında)
            Alert.alert(
                "Sunucu Hatası (500)", 
                error.response.data?.message ? error.response.data.message.substring(0, 100) : "Sunucuda beklenmeyen bir hata oluştu."
            );
        }
        // 402 - Session/Access expired
        else if (status === 402) {
            console.log('[API ACCESS EXPIRED]', error.response.data.message);
            if (Platform.OS === 'web') {
                await AsyncStorage.removeItem('userToken');
            } else {
                await SecureStore.deleteItemAsync('userToken');
            }
            Alert.alert(
                "Erişim Süresi Doldu",
                "Oturumunuzun süresi doldu. Lütfen tekrar giriş yapın veya sistem yöneticinizle iletişime geçin.",
                [{ text: "Tamam" }]
            );
            DeviceEventEmitter.emit('logout');
        }
        else {
            // Diğer hatalar (404 vb.)
            console.error(`[API HTTP ERROR ${status}]`, JSON.stringify(error.response.data));
        }

        return Promise.reject(error);
    }
);

export default api;
