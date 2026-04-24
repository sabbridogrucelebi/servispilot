import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Varsayılan API URL (Bilgisayarın yerel IP adresi veya canlı sunucu adresi)
// 10.0.2.2 Android emülatörler için localhost'tur. Bilgisayar tarayıcısından test ediyorsak localhost olmalı.
import { Platform } from 'react-native';
const BASE_URL = process.env.EXPO_PUBLIC_API_URL || (Platform.OS === 'web' ? 'http://localhost:8000/api' : 'http://10.0.2.2:8000/api');

const api = axios.create({
    baseURL: BASE_URL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Her istekten önce token'ı ekle
api.interceptors.request.use(
    async (config) => {
        const token = await AsyncStorage.getItem('userToken');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default api;
