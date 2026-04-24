import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Varsayılan API URL (Bilgisayarın yerel IP adresi veya canlı sunucu adresi)
// 10.0.2.2 Android emülatörler için localhost'tur. Bilgisayar tarayıcısından test ediyorsak localhost olmalı.
import { Platform } from 'react-native';
const BASE_URL = process.env.EXPO_PUBLIC_API_URL || (Platform.OS === 'web' ? 'http://127.0.0.1:8000/api' : 'http://10.0.2.2:8000/api');

const api = axios.create({
    baseURL: BASE_URL,
    timeout: 10000, // 10 seconds timeout
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

console.log("Axios initialized with BASE_URL:", BASE_URL);

// Her istekten önce token'ı ekle
api.interceptors.request.use(
    async (config) => {
        const token = await AsyncStorage.getItem('userToken');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
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
        // 401 Unauthorized (Token geçersiz veya süresi dolmuş)
        if (error.response && error.response.status === 401) {
            await AsyncStorage.removeItem('userToken');
            // Not: İdeal olarak AuthContext üzerinden logout tetiklenmeli 
            // ancak şimdilik token'ı temizlemek uygulamayı giriş ekranına atacaktır.
        }

        // 422 Validation Error (Form doğrulama hatası)
        if (error.response && error.response.status === 422) {
            console.log('Validation Error:', error.response.data.errors);
        }

        // Network Error (Sunucuya ulaşılamıyor)
        if (!error.response) {
            console.log('Network Error: Sunucuya ulaşılamıyor VEYA CORS hatası.', error.message);
        } else {
            console.log(`[API ERROR] ${error.config.url} - Status: ${error.response.status}`);
        }

        return Promise.reject(error);
    }
);

export default api;
